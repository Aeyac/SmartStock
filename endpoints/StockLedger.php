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

    // GET - Stock Ledger for one item (?item_id=)

    case 'GET':

        $itemId = (int) ($_GET['item_id'] ?? 0);

        $mydb->select('items', '*', ['id' => $itemId, 'user_id' => $userId]);
        $item = $mydb->res ? $mydb->res->fetch_assoc() : null;

        if (!$item) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Item not found.']);
            exit;
        }

        // Manual query: stock_ledger has no user_id of its own (it's a child
        // of items), so per-user scoping needs a JOIN back to items.
        $stmt = $mydb->conn->prepare(
            "SELECT sl.* FROM stock_ledger sl
             INNER JOIN items i ON i.id = sl.item_id
             WHERE sl.item_id = ? AND i.user_id = ?
             ORDER BY sl.created_at DESC"
        );
        $stmt->bind_param('ii', $itemId, $userId);
        $stmt->execute();
        $ledger = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        echo json_encode(['status' => 'success', 'item' => $item, 'ledger' => $ledger]);
        break;



    default:

        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
}