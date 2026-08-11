<?php
session_start();

require_once "../db.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated.']);
    exit;
}

$userId = $_SESSION['user_id'];
$mydb = new myDB();
$method = $_SERVER['REQUEST_METHOD'];

function validatePurchaseInput($mydb, $userId, $data)
{
    $supplierId = (int) ($data['supplier_id'] ?? 0);
    $purchaseDate = trim($data['purchase_date'] ?? '');
    $items = $data['items'] ?? [];

    $errors = [];

    $mydb->select('suppliers', 'id', ['id' => $supplierId, 'user_id' => $userId]);
    if (!$mydb->res || !$mydb->res->fetch_assoc()) {
        $errors['supplier_id'] = 'A valid supplier is required.';
    }

    if ($purchaseDate === '' || !strtotime($purchaseDate)) {
        $errors['purchase_date'] = 'A valid purchase date is required.';
    }

    if (empty($items) || !is_array($items)) {
        $errors['items'] = 'At least one item is required.';
    } else {
        foreach ($items as $index => $line) {
            $itemId = (int) ($line['item_id'] ?? 0);
            $quantity = $line['quantity'] ?? null;
            $unitCost = $line['unit_cost'] ?? null;

            $mydb->select('items', 'id', ['id' => $itemId, 'user_id' => $userId]);
            if (!$mydb->res || !$mydb->res->fetch_assoc()) {
                $errors["items.$index.item_id"] = 'Invalid selected item.';
            }
            if (!is_numeric($quantity) || $quantity <= 0) {
                $errors["items.$index.quantity"] = 'Quantity must be greater than zero.';
            }
            if (!is_numeric($unitCost) || $unitCost < 0) {
                $errors["items.$index.unit_cost"] = 'Unit cost must be zero or greater.';
            }
        }
    }

    return [
        'isValid' => empty($errors),
        'errors' => $errors,
        'data' => [
            'supplier_id' => $supplierId,
            'purchase_date' => $purchaseDate,
            'items' => $items,
        ]
    ];
}

switch ($method) {

    // GET - List Purchases, or one Purchase + line items via ?id=
    case 'GET':

        if (isset($_GET['id'])) {

            $id = (int) $_GET['id'];

            $stmt = $mydb->conn->prepare(
                "SELECT p.*, s.name AS supplier_name FROM purchases p
                INNER JOIN suppliers s ON s.id = p.supplier_id
                WHERE p.id = ? AND p.user_id = ?"
            );
            $stmt->bind_param('ii', $id, $userId);
            $stmt->execute();
            $purchase = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$purchase) {
                echo json_encode(['status' => 'error', 'message' => 'Purchase not found.']);
                exit;
            }

            $stmt = $mydb->conn->prepare(
                "SELECT pi.*, i.name AS item_name FROM purchase_items pi
                 INNER JOIN items i ON i.id = pi.item_id
                 WHERE pi.purchase_id = ?"
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $purchase['items'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            echo json_encode(['status' => 'success', 'purchase' => $purchase]);
            exit;
        }

        // Manual query: needs a JOIN to show the supplier's name alongside each purchase
        $stmt = $mydb->conn->prepare(
            "SELECT p.*, s.name AS supplier_name FROM purchases p
             INNER JOIN suppliers s ON s.id = p.supplier_id
             WHERE p.user_id = ?
             ORDER BY p.purchase_date DESC"
        );

        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $purchases = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        echo json_encode(['status' => 'success', 'purchases' => $purchases]);
        break;



    // POST - Record Purchase (header + line items + stock + ledger, all-or-nothing)

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $validation = validatePurchaseInput($mydb, $userId, $data);

        if (!$validation['isValid']) {
            echo json_encode(['status' => 'error', 'errors' => $validation['errors']]);
            exit;
        }

        $supplierId = $validation['data']['supplier_id'];
        $purchaseDate = $validation['data']['purchase_date'];
        $items = $validation['data']['items'];

        $mydb->conn->begin_transaction();

        try {
            $purchaseId = $mydb->insert('purchases', [
                'user_id' => $userId,
                'supplier_id' => $supplierId,
                'purchase_date' => $purchaseDate,
                'total_amount' => 0
            ]);

            $total = 0;

            foreach ($items as $line) {
                $itemId = (int) $line['item_id'];
                $quantity = (int) $line['quantity'];
                $unitCost = (float) $line['unit_cost'];
                $subtotal = $quantity * $unitCost;

                $mydb->insert('purchase_items', [
                    'purchase_id' => $purchaseId,
                    'item_id' => $itemId,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal
                ]);

                // stock only ever increases through a real recorded purchase
                $stmt = $mydb->conn->prepare("UPDATE items SET stock = stock + ? WHERE id = ? AND user_id = ?");
                $stmt->bind_param('iii', $quantity, $itemId, $userId);
                $stmt->execute();
                $stmt->close();

                $mydb->select('items', 'stock', ['id' => $itemId, 'user_id' => $userId]);
                $newStock = (int) $mydb->res->fetch_assoc()['stock'];

                $mydb->insert('stock_ledger', [
                    'item_id' => $itemId,
                    'type' => 'purchase',
                    'reference_id' => $purchaseId,
                    'quantity_change' => $quantity,
                    'balance_after' => $newStock
                ]);

                $total += $subtotal;
            }

            $mydb->update('purchases', ['total_amount' => $total], ['id' => $purchaseId]);

            $mydb->conn->commit();

            echo json_encode([
                'status' => 'success',
                'message' => 'Purchase recorded successfully.',
                'purchase' => ['id' => $purchaseId, 'total_amount' => $total]
            ]);
        } catch (Throwable $e) {
            $mydb->conn->rollback();
            error_log('Purchase Error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Could not record purchase.']);
        }
        break;



    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
}