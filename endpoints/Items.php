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

    /*
    |--------------------------------------------------------------------------
    | GET - List Items (?low_stock=1 for the Low Stock Alert view)
    |--------------------------------------------------------------------------
    */

    case 'GET':

        if (isset($_GET['low_stock'])) {

            // Manual query: "stock <= safety_stock" compares two columns —
            // this IS the Low Stock Alert check, done live.
            $stmt = $mydb->conn->prepare(
                "SELECT * FROM items WHERE user_id = ? AND stock <= safety_stock ORDER BY stock ASC"
            );
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            echo json_encode(['status' => 'success', 'items' => $items]);
            exit;
        }

        // Manual query: only reason this can't use select() is the JOIN for category name
        $stmt = $mydb->conn->prepare(
            "SELECT i.*, c.name AS category_name FROM items i
             INNER JOIN categories c ON c.id = i.category_id
             WHERE i.user_id = ?
             ORDER BY i.name ASC"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        echo json_encode(['status' => 'success', 'items' => $items]);
        break;



    /*
    |--------------------------------------------------------------------------
    | POST - Create Item
    |--------------------------------------------------------------------------
    */

    case 'POST':

        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $name = trim($data['name'] ?? '');
        $categoryId = (int) ($data['category_id'] ?? 0);
        $safetyStock = $data['safety_stock'] ?? null;
        $sellingPrice = $data['selling_price'] ?? null;

        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Item name is required.';
        } else {
            $mydb->select('items', 'id', ['name' => $name, 'user_id' => $userId]);
            if ($mydb->res && $mydb->res->fetch_assoc()) {
                $errors['name'] = 'An item with this name already exists.';
            }
        }

        $mydb->select('categories', 'id', ['id' => $categoryId, 'user_id' => $userId]);
        if (!$mydb->res || !$mydb->res->fetch_assoc()) {
            $errors['category_id'] = 'Please select a valid category.';
        }

        if ($safetyStock === null || !is_numeric($safetyStock) || $safetyStock < 0) {
            $errors['safety_stock'] = 'Safety stock must be a non-negative number.';
        }

        if ($sellingPrice === null || !is_numeric($sellingPrice) || $sellingPrice < 0) {
            $errors['selling_price'] = 'Selling price must be a non-negative number.';
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'errors' => $errors]);
            exit;
        }

        $id = $mydb->insert('items', [
            'user_id' => $userId,
            'category_id' => $categoryId,
            'name' => $name,
            'stock' => 0, // stock always starts at 0 — only a recorded purchase increases it
            'safety_stock' => (int) $safetyStock,
            'selling_price' => (float) $sellingPrice
        ]);

        http_response_code(201);
        echo json_encode(['status' => 'success', 'message' => 'Item created successfully.', 'id' => $id]);
        break;



    /*
    |--------------------------------------------------------------------------
    | PUT - Update Item
    |--------------------------------------------------------------------------
    */

    case 'PUT':

        $id = (int) ($_GET['id'] ?? 0);
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $name = trim($data['name'] ?? '');
        $categoryId = (int) ($data['category_id'] ?? 0);
        $safetyStock = $data['safety_stock'] ?? null;
        $sellingPrice = $data['selling_price'] ?? null;

        $mydb->select('items', 'id', ['id' => $id, 'user_id' => $userId]);
        if (!$mydb->res || !$mydb->res->fetch_assoc()) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Item not found.']);
            exit;
        }

        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Item name is required.';
        } else {
            $mydb->select('items', 'id', ['name' => $name, 'user_id' => $userId]);
            $existing = $mydb->res ? $mydb->res->fetch_assoc() : null;
            if ($existing && (int) $existing['id'] !== $id) {
                $errors['name'] = 'An item with this name already exists.';
            }
        }

        $mydb->select('categories', 'id', ['id' => $categoryId, 'user_id' => $userId]);
        if (!$mydb->res || !$mydb->res->fetch_assoc()) {
            $errors['category_id'] = 'Please select a valid category.';
        }

        if ($safetyStock === null || !is_numeric($safetyStock) || $safetyStock < 0) {
            $errors['safety_stock'] = 'Safety stock must be a non-negative number.';
        }

        if ($sellingPrice === null || !is_numeric($sellingPrice) || $sellingPrice < 0) {
            $errors['selling_price'] = 'Selling price must be a non-negative number.';
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'errors' => $errors]);
            exit;
        }

        $mydb->update('items', [
            'name' => $name,
            'category_id' => $categoryId,
            'safety_stock' => (int) $safetyStock,
            'selling_price' => (float) $sellingPrice
        ], ['id' => $id, 'user_id' => $userId]);

        echo json_encode(['status' => 'success', 'message' => 'Item updated successfully.']);
        break;



    // DELETE - Delete Item (permanent; blocked if it has purchase/sale history)

    case 'DELETE':

        $id = (int) ($_GET['id'] ?? 0);

        $mydb->select('items', 'id', ['id' => $id, 'user_id' => $userId]);
        if (!$mydb->res || !$mydb->res->fetch_assoc()) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Item not found.']);
            exit;
        }

        $mydb->delete('items', ['id' => $id, 'user_id' => $userId]);

        echo json_encode(['status' => 'success', 'message' => 'Item deleted successfully.']);
        break;



    default:

        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
}