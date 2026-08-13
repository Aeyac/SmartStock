<?php

require_once "../db.php";

session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated.']);
    exit;
}

$userId = $_SESSION['user_id'];
$mydb = new myDB();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // GET - List Sales, or one Sale + line items via ?id=

    case 'GET':

        if (isset($_GET['id'])) {

            $id = (int) $_GET['id'];

            $mydb->select('sales', '*', ['id' => $id, 'user_id' => $userId]);
            $sale = $mydb->res ? $mydb->res->fetch_assoc() : null;

            if (!$sale) {
                echo json_encode(['status' => 'error', 'message' => 'Sale not found.']);
                exit;
            }

            $stmt = $mydb->conn->prepare(
                "SELECT si.*, i.name AS item_name FROM sale_items si
                 INNER JOIN items i ON i.id = si.item_id
                 WHERE si.sale_id = ?"
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $sale['items'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            echo json_encode(['status' => 'success', 'sale' => $sale]);
            exit;
        }

        $mydb->select('sales', '*', ['user_id' => $userId]);
        $sales = $mydb->res ? $mydb->res->fetch_all(MYSQLI_ASSOC) : [];

        echo json_encode(['status' => 'success', 'sales' => $sales]);
        break;



    //  POST - Record Sale (header + line items + stock + ledger, all-or-nothing)

    case 'POST':

        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $saleDate = $data['sale_date'] ?? '';
        $items = $data['items'] ?? [];

        $errors = [];

        if ($saleDate === '' || !strtotime($saleDate)) {
            $errors['sale_date'] = 'A valid sale date is required.';
        }

        if (empty($items) || !is_array($items)) {
            $errors['items'] = 'At least one item is required.';
        } else {
            foreach ($items as $index => $line) {
                $itemId = (int) ($line['item_id'] ?? 0);
                $quantity = $line['quantity'] ?? null;

                $mydb->select('items', '*', ['id' => $itemId, 'user_id' => $userId]);
                $item = $mydb->res ? $mydb->res->fetch_assoc() : null;

                if (!$item) {
                    $errors["items.$index.item_id"] = 'Invalid item selected.';
                    continue;
                }
                if (!is_numeric($quantity) || $quantity <= 0) {
                    $errors["items.$index.quantity"] = 'Quantity must be greater than zero.';
                    continue;
                }
                // business rule: can't sell more than what's currently in stock
                if ($quantity > $item['stock']) {
                    $errors["items.$index.quantity"] =
                        "Only {$item['stock']} unit(s) of \"{$item['name']}\" left in stock.";
                }
            }
        }

        if (!empty($errors)) {
            echo json_encode(['status' => 'error', 'errors' => $errors]);
            exit;
        }

        $mydb->conn->begin_transaction();

        try {
            $saleId = $mydb->insert('sales', [
                'user_id' => $userId,
                'sale_date' => $saleDate,
                'total_amount' => 0
            ]);

            $total = 0;

            foreach ($items as $line) {
                $itemId = (int) $line['item_id'];
                $quantity = (int) $line['quantity'];

                // selling price ALWAYS comes from the item record, never trusted from the client
                $mydb->select('items', 'selling_price', ['id' => $itemId, 'user_id' => $userId]);
                $unitPrice = (float) $mydb->res->fetch_assoc()['selling_price'];
                $subtotal = $quantity * $unitPrice;

                $mydb->insert('sale_items', [
                    'sale_id' => $saleId,
                    'item_id' => $itemId,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal
                ]);

                // stock decreases as a side effect of the sale, not the source of the revenue number
                $stmt = $mydb->conn->prepare("UPDATE items SET stock = stock - ? WHERE id = ? AND user_id = ?");
                $stmt->bind_param('iii', $quantity, $itemId, $userId);
                $stmt->execute();
                $stmt->close();

                $mydb->select('items', 'stock', ['id' => $itemId, 'user_id' => $userId]);
                $newStock = (int) $mydb->res->fetch_assoc()['stock'];

                $mydb->insert('stock_ledger', [
                    'item_id' => $itemId,
                    'type' => 'sale',
                    'reference_id' => $saleId,
                    'quantity_change' => -$quantity,
                    'balance_after' => $newStock
                ]);

                $total += $subtotal;
            }

            $mydb->update('sales', ['total_amount' => $total], ['id' => $saleId]);

            $mydb->conn->commit();

            echo json_encode([
                'status' => 'success',
                'message' => 'Sale recorded successfully.',
                'sale' => ['id' => $saleId, 'total_amount' => $total]
            ]);
        } catch (Throwable $e) {
            $mydb->conn->rollback();
            error_log('Sale Error: ' . $e->getMessage());

            echo json_encode(['status' => 'error', 'message' => 'Could not record sale.']);
        }
        break;



    default:
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
}