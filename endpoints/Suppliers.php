<?php
session_start();

require_once "../db.php";
require_once "../utils/Utility.php";

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
// $userId = 1;
$mydb = new myDB();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // GET - List Suppliers

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



    //POST - Create Supplier
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $validation = validateSupplierInput($mydb, $userId, $input);

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

        echo json_encode([
            'status' => 'success',
            'message' => 'Supplier created successfully.',
            'id' => $id
        ]);
        break;



    // PUT - Update Supplier
    case 'PUT':
        $id = (int) ($_GET['id'] ?? 0);

        // Verify existence first
        $mydb->select('suppliers', 'id', [
            'id' => $id,
            'user_id' => $userId
        ]);

        if (!$mydb->res || !$mydb->res->fetch_assoc()) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Supplier not found.'
            ]);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        // Pass current $id so email uniqueness check ignores the record being updated
        $validation = validateSupplierInput($mydb, $userId, $input, $id);

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



    // DELETE - Delete Supplier

    case 'DELETE':

        $id = (int) ($_GET['id'] ?? 0);

        $mydb->select('suppliers', 'id', [
            'id' => $id,
            'user_id' => $userId
        ]);

        if (!$mydb->res || !$mydb->res->fetch_assoc()) {

            http_response_code(404);

            echo json_encode([
                'status' => 'error',
                'message' => 'Supplier not found.'
            ]);

            exit;
        }

        $mydb->delete('suppliers', [
            'id' => $id,
            'user_id' => $userId
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Supplier deleted successfully.'
        ]);

        break;



    default:

        http_response_code(405);

        echo json_encode([
            'status' => 'error',
            'message' => 'Method Not Allowed.'
        ]);

}