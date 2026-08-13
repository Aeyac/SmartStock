<?php
require_once "../db.php";

session_start();
header('Content-Type: application/json; charset=utf-8');

$mydb = new myDB();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $type = $_GET['type'] ?? '';
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        if ($type === 'register') {

            $name = trim($data['name'] ?? '');
            $email = trim($data['email'] ?? '');
            $password = $data['password'] ?? '';

            if ($name === '' || $email === '' || $password === '') {
                echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
                exit;
            }

            if (strlen($password) < 8) {
                echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long.']);
                exit;
            }

            $mydb->select('users', 'id', ['email' => $email]);
            if ($mydb->res && $mydb->res->fetch_assoc()) {
                echo json_encode(['status' => 'error', 'message' => 'Email is already registered.']);
                exit;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $mydb->insert('users', [
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword
            ]);

            echo json_encode(['status' => 'success', 'message' => 'User registered successfully.']);
            exit;
        }

        if ($type === 'login') {
            $email = trim($data['email'] ?? '');
            $password = $data['password'] ?? '';

            if ($email === '' || $password === '') {
                echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
                exit;
            }

            $mydb->select('users', '*', ['email' => $email]);
            $user = $mydb->res ? $mydb->res->fetch_assoc() : null;

            if (!$user || !password_verify($password, $user['password'])) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
                exit;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            echo json_encode(['status' => 'success', 'message' => 'Logged in successfully.']);
            exit;
        }

        echo json_encode(['status' => 'error', 'message' => 'Missing or invalid type parameter.']);
        break;


    case 'DELETE':
        session_destroy();
        echo json_encode(['status' => 'success', 'message' => 'Logged out successfully.']);
        break;


    default:
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
}