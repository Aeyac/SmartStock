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

    case 'GET':
        $mydb->select('categories', '*', ['user_id' => $userId]);
        $categories = $mydb->res ? $mydb->res->fetch_all(MYSQLI_ASSOC) : [];

        echo json_encode(['status' => 'success', 'categories' => $categories]);
        break;


    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $name = trim($data['name'] ?? '');

        if ($name === '') {
            echo json_encode(['status' => 'error', 'message' => 'Category name is required.']);
            exit;
        }

        // Checking here first means so the user gets a clear message instead
        $mydb->select('categories', 'id', ['name' => $name, 'user_id' => $userId]);
        if ($mydb->res && $mydb->res->fetch_assoc()) {
            echo json_encode([
                'status' => 'error',
                'message' => 'This category already exists. Please check your Archived list. It may be sitting there instead.'
            ]);
            exit;
        }

        $mydb->insert('categories', ['user_id' => $userId, 'name' => $name]);
        echo json_encode(['status' => 'success', 'message' => 'Category created successfully.']);
        break;


    // PUT - Rename a Category (archived categories are excluded)
    case 'PUT':
        $id = (int) ($_GET['id'] ?? 0);

        $mydb->select('categories', '*', [
            'id' => $id,
            'user_id' => $userId,
        ]);

        $category = $mydb->res ? $mydb->res->fetch_assoc() : null;

        if (!$category) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Category not found.'
            ]);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $name = trim($input['name'] ?? '');

        if ($name === '') {
            echo json_encode([
                'status' => 'error',
                'errors' => "Name is required."
            ]);
            exit;
        }

        // Nothing to check or update if the name didn't actually change.
        if ($name === $category['name']) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Category updated successfully.'
            ]);
            exit;
        }

        // to check all categories, not just active ones. myDB::select()
        // mydb select can't express "!=" so we have to manual query.
        $stmt = $mydb->conn->prepare(
            "SELECT id FROM categories
             WHERE name = ? AND user_id = ? AND id != ?"
        );
        $stmt->bind_param('sii', $name, $userId, $id);
        $stmt->execute();
        $conflict = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($conflict) {
            echo json_encode([
                'status' => 'error',
                'message' => 'This category already exists. Check your Archived list — it may be sitting there instead.'
            ]);
            exit;
        }

        $mydb->update('categories', ['name' => $name], ['id' => $id, 'user_id' => $userId]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Category updated successfully.'
        ]);
        break;


    // DELETE - Soft delete (archive) a category only
    case 'DELETE':
        $id = (int) ($_GET['id'] ?? 0);

        $mydb->select('categories', '*', ['id' => $id, 'user_id' => $userId, 'deleted_at' => null]);
        $category = $mydb->res ? $mydb->res->fetch_assoc() : null;

        if (!$category) {
            echo json_encode(['status' => 'error', 'message' => 'Category not found.']);
            exit;
        }

        $mydb->update(
            'categories',
            ['deleted_at' => date('Y-m-d H:i:s')],
            ['id' => $id, 'user_id' => $userId]
        );

        echo json_encode(['status' => 'success', 'message' => 'Category archived successfully.']);
        break;


    // PATCH - Restore an archived Category (clears deleted_at)
    case 'PATCH':
        $id = (int) ($_GET['id'] ?? 0);

        $stmt = $mydb->conn->prepare(
            "SELECT * FROM categories WHERE id = ? AND user_id = ? AND deleted_at IS NOT NULL"
        );
        $stmt->bind_param('ii', $id, $userId);
        $stmt->execute();
        $category = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$category) {
            echo json_encode(['status' => 'error', 'message' => 'Archived category not found.']);
            exit;
        }

        // $mydb->select('categories', 'id', ['name' => $category['name'], 'user_id' => $userId, 'deleted_at' => null]);
        // if ($mydb->res && $mydb->res->fetch_assoc()) {
        //     echo json_encode([
        //         'status' => 'error',
        //         'message' => 'An active category with this name already exists. Rename it before restoring.'
        //     ]);
        //     exit;
        // }

        $mydb->update('categories', ['deleted_at' => null], ['id' => $id, 'user_id' => $userId]);
        echo json_encode(['status' => 'success', 'message' => 'Category restored successfully.']);
        break;


    default:
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
}