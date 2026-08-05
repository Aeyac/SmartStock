<?php
session_start();

require_once "../db.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Not authenticated.'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];
$mydb = new myDB();

$method = $_SERVER['REQUEST_METHOD'];

// Helper function: Input Validation & Formatting
function validateItemInput($mydb, $userId, $data, $currentItemId = null)
{
    $name = trim($data['name'] ?? '');
    $categoryId = (int) ($data['category_id'] ?? 0);
    $safetyStock = $data['safety_stock'] ?? null;
    $sellingPrice = $data['selling_price'] ?? null;

    $errors = [];

    // 1. Name validation & uniqueness per user
    if ($name === '') {
        $errors['name'] = 'Item name is required.';
    } else {
        $mydb->select('items', '*', [
            'name' => $name,
            'user_id' => $userId
        ]);
        $existing = $mydb->res ? $mydb->res->fetch_assoc() : null;

        if ($existing && (int) $existing['id'] !== (int) $currentItemId) {
            $errors['name'] = 'An item with this name already exists.';
        }
    }

    // 2. Category validation
    if ($categoryId <= 0) {
        $errors['category_id'] = 'Please select a valid category.';
    } else {
        $mydb->select('categories', 'id', [
            'id' => $categoryId,
            'user_id' => $userId
        ]);
        if (!$mydb->res || !$mydb->res->fetch_assoc()) {
            $errors['category_id'] = 'Selected category does not exist.';
        }
    }

    // 3. Safety Stock validation
    if ($safetyStock === null || $safetyStock === '' || !is_numeric($safetyStock) || (int) $safetyStock < 0) {
        $errors['safety_stock'] = 'Safety stock must be a non-negative number.';
    }

    // 4. Selling Price validation
    if ($sellingPrice === null || $sellingPrice === '' || !is_numeric($sellingPrice) || (float) $sellingPrice < 0) {
        $errors['selling_price'] = 'Selling price must be a non-negative number.';
    }

    return [
        'isValid' => empty($errors),
        'errors' => $errors,
        'data' => [
            'name' => $name,
            'category_id' => $categoryId,
            'safety_stock' => (int) $safetyStock,
            'selling_price' => (float) $sellingPrice
        ]
    ];
}

switch ($method) {

    // GET - List Items (Supports ?low_stock=1)
    case 'GET':
        if (isset($_GET['low_stock'])) {
            $stmt = $mydb->conn->prepare(
                "SELECT i.*, c.name AS category_name 
                 FROM items i
                 LEFT JOIN categories c ON c.id = i.category_id
                 WHERE i.user_id = ? AND i.stock <= i.safety_stock 
                 ORDER BY i.stock ASC"
            );
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            echo json_encode([
                'status' => 'success',
                'items' => $items
            ]);
            exit;
        }

        $stmt = $mydb->conn->prepare(
            "SELECT i.*, c.name AS category_name 
             FROM items i
             LEFT JOIN categories c ON c.id = i.category_id
             WHERE i.user_id = ?
             ORDER BY i.name ASC"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        echo json_encode([
            'status' => 'success',
            'items' => $items
        ]);
        break;

    // POST - Create Item
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $validation = validateItemInput($mydb, $userId, $input);

        if (!$validation['isValid']) {
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'errors' => $validation['errors']
            ]);
            exit;
        }

        $validData = $validation['data'];

        $id = $mydb->insert('items', [
            'user_id' => $userId,
            'category_id' => $validData['category_id'],
            'name' => $validData['name'],
            'stock' => 0, // Starts at 0 until recorded via purchases
            'safety_stock' => $validData['safety_stock'],
            'selling_price' => $validData['selling_price']
        ]);

        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'message' => 'Item created successfully.',
            'id' => $id
        ]);
        break;

    // PUT - Update Item
    case 'PUT':
        $id = (int) ($_GET['id'] ?? 0);

        $mydb->select('items', 'id', [
            'id' => $id,
            'user_id' => $userId
        ]);

        if (!$mydb->res || !$mydb->res->fetch_assoc()) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Item not found.'
            ]);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $validation = validateItemInput($mydb, $userId, $input, $id);

        if (!$validation['isValid']) {
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'errors' => $validation['errors']
            ]);
            exit;
        }

        $validData = $validation['data'];

        $mydb->update(
            'items',
            [
                'name' => $validData['name'],
                'category_id' => $validData['category_id'],
                'safety_stock' => $validData['safety_stock'],
                'selling_price' => $validData['selling_price']
            ],
            [
                'id' => $id,
                'user_id' => $userId
            ]
        );

        echo json_encode([
            'status' => 'success',
            'message' => 'Item updated successfully.'
        ]);
        break;

    // DELETE - Delete Item
    case 'DELETE':
        $id = (int) ($_GET['id'] ?? 0);

        $mydb->select('items', 'id', [
            'id' => $id,
            'user_id' => $userId
        ]);

        if (!$mydb->res || !$mydb->res->fetch_assoc()) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Item not found.'
            ]);
            exit;
        }

        $mydb->delete('items', [
            'id' => $id,
            'user_id' => $userId
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Item deleted successfully.'
        ]);
        break;

    default:
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Method Not Allowed.'
        ]);
}