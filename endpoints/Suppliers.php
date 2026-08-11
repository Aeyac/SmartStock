<?php

session_start();

require_once "../db.php";
require_once "../utils/Utility.php";

header('Content-Type: application/json; charset=utf-8');

// ==================================================
// Authentication
// ==================================================

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


// ==================================================
// Request Handler
// ==================================================

switch ($method) {

    // ==================================================
    // GET - List Suppliers (active + archived together)
    // Filtering by archive status is handled client-side, the same
    // pattern already used for the stock ledger — one fetch, filtered
    // in JS, instead of separate active/archived endpoints.
    // ==================================================

    case 'GET':

        $mydb->select('suppliers', '*', [
            'user_id' => $userId
        ]);

        $suppliers = $mydb->res
            ? $mydb->res->fetch_all(MYSQLI_ASSOC)
            : [];

        echo json_encode([
            'status' => 'success',
            'suppliers' => $suppliers
        ]);

        break;


    // ==================================================
    // POST - Create Supplier
    // ==================================================

    case 'POST':

        $input = json_decode(
            file_get_contents('php://input'),
            true
        ) ?? [];

        // NOTE: duplicate checking (e.g. email uniqueness) happens inside
        // validateSupplierInput() in Utility.php. That check needs to be
        // widened to also match archived suppliers (deleted_at IS NOT
        // NULL), the same way Categories.php checks both active AND
        // archived categories — otherwise re-creating a supplier whose
        // name/email was already archived will crash at the database's
        // unique constraint instead of returning a clean validation error.
        $validation = validateSupplierInput(
            $mydb,
            $userId,
            $input
        );

        if (!$validation['isValid']) {
            echo json_encode([
                'status' => 'error',
                'errors' => $validation['errors']
            ]);

            exit;
        }

        $validData = $validation['data'];

        $id = $mydb->insert('suppliers', [
            'user_id' => $userId,
            'name' => $validData['name'],
            'contact_number' => $validData['contact_number'],
            'email' => $validData['email'],
            'active' => $validData['status']
        ]);

        if (!$id) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to create supplier.'
            ]);

            exit;
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Supplier created successfully.',
            'id' => $id
        ]);

        break;


    // ==================================================
    // PUT - Update Supplier (active suppliers only)
    // ==================================================

    case 'PUT':

        $id = (int) ($_GET['id'] ?? 0);

        // Verify supplier exists first
        $mydb->select('suppliers', '*', [
            'id' => $id,
            'user_id' => $userId
        ]);

        $supplier = $mydb->res ? $mydb->res->fetch_assoc() : null;

        if (!$supplier) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Supplier not found.'
            ]);

            exit;
        }

        if ($supplier['deleted_at'] !== null) {
            echo json_encode([
                'status' => 'error',
                'message' => 'This supplier is archived. Restore it before editing.'
            ]);

            exit;
        }

        $input = json_decode(
            file_get_contents('php://input'),
            true
        ) ?? [];

        // Pass current ID so the email uniqueness
        // check ignores the supplier being updated
        $validation = validateSupplierInput(
            $mydb,
            $userId,
            $input,
            $id
        );

        if (!$validation['isValid']) {
            echo json_encode([
                'status' => 'error',
                'errors' => $validation['errors']
            ]);

            exit;
        }

        $validData = $validation['data'];

        $mydb->update(
            'suppliers',
            [
                'name' => $validData['name'],
                'contact_number' => $validData['contact_number'],
                'email' => $validData['email'],
                'active' => $validData['status']
            ],
            [
                'id' => $id,
                'user_id' => $userId
            ]
        );

        echo json_encode([
            'status' => 'success',
            'message' => 'Supplier updated successfully.'
        ]);

        break;


    // ==================================================
    // DELETE - Archive Supplier (soft delete)
    // ==================================================
    // No more "is this supplier used by any items" check here — that
    // check only mattered for a PERMANENT delete, which could break the
    // foreign key on items.supplier_id. Archiving never removes the row,
    // so there's nothing for that foreign key to lose.
    // ==================================================

    case 'DELETE':

        $id = (int) ($_GET['id'] ?? 0);

        // Verify supplier exists and isn't already archived
        $mydb->select('suppliers', 'id', [
            'id' => $id,
            'user_id' => $userId,
            'deleted_at' => null
        ]);

        if (!$mydb->res || !$mydb->res->fetch_assoc()) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Supplier not found.'
            ]);

            exit;
        }

        $mydb->update(
            'suppliers',
            ['deleted_at' => date('Y-m-d H:i:s')],
            ['id' => $id, 'user_id' => $userId]
        );

        echo json_encode([
            'status' => 'success',
            'message' => 'Supplier archived successfully.'
        ]);

        break;


    // ==================================================
    // PATCH - Restore an archived Supplier (clears deleted_at)
    // ==================================================

    case 'PATCH':

        $id = (int) ($_GET['id'] ?? 0);

        $stmt = $mydb->conn->prepare("
            SELECT * FROM suppliers
            WHERE id = ? AND user_id = ? AND deleted_at IS NOT NULL
        ");
        $stmt->bind_param('ii', $id, $userId);
        $stmt->execute();
        $supplier = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$supplier) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Archived supplier not found.'
            ]);

            exit;
        }

        // Restoring shouldn't silently create a duplicate — if a new
        // active supplier has since taken this email, block the restore.
        // NOTE: adjust the matched column(s) here to mirror whatever
        // validateSupplierInput() actually treats as the unique field.
        $mydb->select('suppliers', 'id', [
            'email' => $supplier['email'],
            'user_id' => $userId,
            'deleted_at' => null
        ]);

        if ($mydb->res && $mydb->res->fetch_assoc()) {
            echo json_encode([
                'status' => 'error',
                'message' => 'An active supplier with this email already exists. Resolve that first before restoring.'
            ]);

            exit;
        }

        $mydb->update(
            'suppliers',
            ['deleted_at' => null],
            ['id' => $id, 'user_id' => $userId]
        );

        echo json_encode([
            'status' => 'success',
            'message' => 'Supplier restored successfully.'
        ]);

        break;


    default:

        http_response_code(405);

        echo json_encode([
            'status' => 'error',
            'message' => 'Method Not Allowed.'
        ]);

        break;
}