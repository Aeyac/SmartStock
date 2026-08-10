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

    // GET - List Categories
    // Default: active (non-archived) categories only.
    // ?archived=1 : list archived (soft-deleted) categories instead, for
    // a "restore from archive" screen.
    case 'GET':

        if (isset($_GET['archived']) && $_GET['archived'] == '1') {
            // $mydb->select() only knows how to build "IS NULL" checks, not
            // "IS NOT NULL", so archived listing needs a raw prepared query.
            $stmt = $mydb->conn->prepare(
                "SELECT * FROM categories
                 WHERE user_id = ? AND deleted_at IS NOT NULL
                 ORDER BY deleted_at DESC"
            );
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            $mydb->select('categories', '*', ['user_id' => $userId, 'deleted_at' => null]);
            $categories = $mydb->res ? $mydb->res->fetch_all(MYSQLI_ASSOC) : [];
        }

        echo json_encode(['status' => 'success', 'categories' => $categories]);
        break;


    // POST - Create Category
    case 'POST':

        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $name = trim($data['name'] ?? '');

        if ($name === '') {
            echo json_encode(['status' => 'error', 'message' => 'Category name is required.']);
            exit;
        }

        // Check ALL categories, active or archived — the database has a
        // unique constraint on name that doesn't distinguish archived rows,
        // so an archived "Shoes" still blocks a new "Shoes" at the DB level.
        // Checking here first means the user gets a clear message instead
        // of a raw SQL error.
        $mydb->select('categories', 'id', ['name' => $name, 'user_id' => $userId]);
        if ($mydb->res && $mydb->res->fetch_assoc()) {
            echo json_encode([
                'status' => 'error',
                'message' => 'This category already exists. Please check your Archived list. It may be sitting there instead.'
            ]);
            exit;
        }

        $id = $mydb->insert('categories', ['user_id' => $userId, 'name' => $name]);

        echo json_encode(['status' => 'success', 'message' => 'Category created successfully.', 'id' => $id]);
        break;


    // PUT - Rename a Category (active categories only)
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

        if ($category['deleted_at'] !== null) {
            echo json_encode([
                'status' => 'error',
                'message' => 'This category is archived. Restore it before renaming.'
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

        // Same reasoning as the create check above — the unique constraint
        // doesn't care whether a matching row is archived, so this needs
        // to check all categories, not just active ones.
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


    // DELETE - Soft delete (archive) a Category
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

        // Restoring shouldn't silently create a duplicate — if a new
        // active category has since taken this name, block the restore
        // and ask the user to rename one of them first.
        $mydb->select('categories', 'id', ['name' => $category['name'], 'user_id' => $userId, 'deleted_at' => null]);
        if ($mydb->res && $mydb->res->fetch_assoc()) {
            echo json_encode([
                'status' => 'error',
                'message' => 'An active category with this name already exists. Rename it before restoring.'
            ]);
            exit;
        }

        $mydb->update('categories', ['deleted_at' => null], ['id' => $id, 'user_id' => $userId]);

        echo json_encode(['status' => 'success', 'message' => 'Category restored successfully.']);
        break;


    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
}