<?php

require_once "../db.php";

session_start();
header('Content-Type: application/json; charset=utf-8');

// if (!isset($_SESSION['user_id'])) {
//     http_response_code(401);
//     echo json_encode([
//         'status' => 'error',
//         'message' => 'Not authenticated.'
//     ]);
//     exit;
// }

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
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $name = trim($data['name'] ?? '');
        $contact = trim($data['contact_number'] ?? '');
        $email = trim($data['email'] ?? '');
        $status = trim($data['status'] ?? 1);

        $errors = [];

        if ($name === '')
            $errors['name'] = 'Supplier name is required.';

        if ($contact === '') {
            $errors['contact_number'] = 'Contact number is required.';
        } elseif (strlen($contact) > 15) {
            $errors['contact_number'] = 'Maximum of 15 digits.';
        } elseif (!preg_match('/^[0-9]+$/', $contact)) {
            $errors['contact_number'] = 'Contact number must contain digits only.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email address.';
        } else {

            $mydb->select('suppliers', 'id', [
                'email' => $email,
                'user_id' => $userId
            ]);

            if ($mydb->res && $mydb->res->fetch_assoc()) {
                $errors['email'] = 'Supplier already exists.';
            }
        }

        if (!empty($errors)) {
            // http_response_code(422);
            echo json_encode(['status' => 'error', 'errors' => $errors]);
            exit;
        }

        $id = $mydb->insert('suppliers', [
            'user_id' => $userId,
            'name' => $name,
            'contact_number' => $contact,
            'email' => $email,
            'active' => $status
        ]);

        http_response_code(201);

        echo json_encode([
            'status' => 'success',
            'message' => 'Supplier created successfully.',
            'id' => $id
        ]);

        break;



    // PUT - Update Supplier

    case 'PUT':

        $id = (int) ($_GET['id'] ?? 0);

        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $name = trim($data['name'] ?? '');
        $contact = trim($data['contact_number'] ?? '');
        $email = trim($data['email'] ?? '');
        $status = trim($data['status'] ?? 1);


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

        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Supplier name is required.';
        }

        if ($contact === '') {
            $errors['contact_number'] = 'Contact number is required.';
        } elseif (strlen($contact) > 15) {
            $errors['contact_number'] = 'Maximum of 15 digits.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email address.';
        } else {

            $mydb->select('suppliers', '*', [
                'email' => $email,
                'user_id' => $userId
            ]);

            $existing = $mydb->res
                ? $mydb->res->fetch_assoc()
                : null;

            if ($existing && (int) $existing['id'] !== $id) {
                $errors['email'] = 'Supplier already exists.';
            }

        }

        if (!empty($errors)) {

            http_response_code(422);

            echo json_encode([
                'status' => 'error',
                'errors' => $errors
            ]);

            exit;
        }

        $mydb->update(
            'suppliers',

            [
                'name' => $name,
                'contact_number' => $contact,
                'email' => $email,
                'active' => $status
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