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

switch ($method) {

    // GET - List all ledger entries for the logged-in user's items.
    // Filtering by type/item is handled client-side, same as the other
    // controllers (suppliers, purchases, sales) — no query params needed here.
    // stock_ledger has no user_id column, so scoping is done through the
    // items table via a JOIN, the same way purchase_items/sale_items are.
    case 'GET':

        $stmt = $mydb->conn->prepare(
            "SELECT sl.*, i.name AS item_name FROM stock_ledger sl
             INNER JOIN items i ON i.id = sl.item_id
             WHERE i.user_id = ?
             ORDER BY sl.created_at DESC"
        );

        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $entries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        echo json_encode(['status' => 'success', 'entries' => $entries]);
        break;



    // POST - Record a manual stock adjustment (damaged goods, recount, etc.)
    // Purchase/sale ledger rows are NEVER created here — those are written
    // transactionally inside Purchases.php and Sales.php alongside their
    // own stock updates. This endpoint only ever writes type = 'adjustment'.
    case 'POST':

        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $itemId = (int) ($data['item_id'] ?? 0);
        $quantityChange = $data['quantity_change'] ?? null;

        $errors = [];

        $mydb->select('items', '*', ['id' => $itemId, 'user_id' => $userId]);
        $item = $mydb->res ? $mydb->res->fetch_assoc() : null;

        if (!$item) {
            $errors['item_id'] = 'Invalid item selected.';
        }

        if (!is_numeric($quantityChange) || (int) $quantityChange === 0) {
            $errors['quantity_change'] = 'Quantity change must be a non-zero whole number.';
        }

        if ($item && is_numeric($quantityChange)) {
            $newBalance = (int) $item['stock'] + (int) $quantityChange;
            if ($newBalance < 0) {
                $errors['quantity_change'] = "Adjustment would drop stock below zero (currently {$item['stock']}).";
            }
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'errors' => $errors]);
            exit;
        }

        $quantityChange = (int) $quantityChange;

        $mydb->conn->begin_transaction();

        try {
            $stmt = $mydb->conn->prepare("UPDATE items SET stock = stock + ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param('iii', $quantityChange, $itemId, $userId);
            $stmt->execute();
            $stmt->close();

            $mydb->select('items', 'stock', ['id' => $itemId, 'user_id' => $userId]);
            $newStock = (int) $mydb->res->fetch_assoc()['stock'];

            $ledgerId = $mydb->insert('stock_ledger', [
                'item_id' => $itemId,
                'type' => 'adjustment',
                'reference_id' => null,
                'quantity_change' => $quantityChange,
                'balance_after' => $newStock
            ]);

            $mydb->conn->commit();

            http_response_code(201);
            echo json_encode([
                'status' => 'success',
                'message' => 'Stock adjustment recorded successfully.',
                'entry' => ['id' => $ledgerId, 'balance_after' => $newStock]
            ]);
        } catch (Throwable $e) {
            $mydb->conn->rollback();
            error_log('Ledger Error: ' . $e->getMessage());

            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Could not record adjustment.']);
        }
        break;



    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
}