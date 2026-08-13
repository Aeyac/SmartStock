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
    // $supplierId = (int) ($data['supplier_id'] ?? 0);
    $safetyStock = $data['safety_stock'] ?? null;
    $sellingPrice = $data['selling_price'] ?? null;

    $errors = [];

    //  Name validation & uniqueness per user
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

    // Category validation
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

    // Safety Stock validation
    if ($safetyStock === null || $safetyStock === '' || !is_numeric($safetyStock) || (int) $safetyStock < 0) {
        $errors['safety_stock'] = 'Safety stock must be a non-negative number.';
    }

    //  Selling Price validation
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
    // GET - List Items
    case 'GET':
        // If ?trashed=1, show soft-deleted items instead of active ones
        $showTrashed = isset($_GET['trashed']) && $_GET['trashed'] === '1';

        $deletedCondition = $showTrashed
            ? "i.deleted_at IS NOT NULL"
            : "i.deleted_at IS NULL";

        $stmt = $mydb->conn->prepare(
            "SELECT
            i.*, c.name AS category_name FROM items i
            LEFT JOIN categories c ON c.id = i.category_id
            WHERE i.user_id = ? AND $deletedCondition
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
            'user_id' => $userId,
            'deleted_at' => null,
        ]);

        if (!$mydb->res || !$mydb->res->fetch_assoc()) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Item not found.'
            ]);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $validation = validateItemInput($mydb, $userId, $input, $id);

        if (!$validation['isValid']) {
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
                'user_id' => $userId,
                'deleted_at' => null
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
            'user_id' => $userId,
            'deleted_at' => null
        ]);

        if (!$mydb->res || !$mydb->res->fetch_assoc()) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Item not found.'
            ]);
            exit;
        }

        $mydb->update(
            'items',
            ['deleted_at' => date('Y-m-d H:i:s')],
            [
                'id' => $id,
                'user_id' => $userId
            ]
        );

        echo json_encode([
            'status' => 'success',
            'message' => 'Item deleted successfully.'
        ]);
        break;

    // PATCH - Restore Item
    case 'PATCH':
        $id = (int) ($_GET['id'] ?? 0);

        $mydb->select('items', '*', [
            'id' => $id,
            'user_id' => $userId
        ]);
        $existingItem = $mydb->res ? $mydb->res->fetch_assoc() : null;

        if (!$existingItem) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Item not found.'
            ]);
            exit;
        }

        if ($existingItem['deleted_at'] === null) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Item is not deleted.'
            ]);
            exit;
        }

        // Prevent restoring into a name that's already active again
        $mydb->select('items', 'id', [
            'name' => $existingItem['name'],
            'user_id' => $userId,
            'deleted_at' => null
        ]);
        $duplicate = $mydb->res ? $mydb->res->fetch_assoc() : null;

        if ($duplicate) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Cannot restore: an active item with this name already exists.'
            ]);
            exit;
        }

        $mydb->update(
            'items',
            ['deleted_at' => null],
            [
                'id' => $id,
                'user_id' => $userId
            ]
        );

        echo json_encode([
            'status' => 'success',
            'message' => 'Item restored successfully.'
        ]);
        break;

    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Method Not Allowed.'
        ]);
}