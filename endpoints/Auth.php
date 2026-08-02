<?php

require_once "../db.php";

session_start();
header('Content-Type: application/json; charset=utf-8');

$mydb = new myDB();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // POST - Register or Login (?type=register / ?type=login)
    case 'POST':

        $type = $_GET['type'] ?? '';
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        if ($type === 'register') {

            $name = trim($data['name'] ?? '');
            $email = trim($data['email'] ?? '');
            $password = $data['password'] ?? '';

            if ($name === '' || $email === '' || $password === '') {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(422);
                echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
                exit;
            }

            if (strlen($password) < 8) {
                http_response_code(422);
                echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long.']);
                exit;
            }

            $mydb->select('users', 'id', ['email' => $email]);
            if ($mydb->res && $mydb->res->fetch_assoc()) {
                http_response_code(409);
                echo json_encode(['status' => 'error', 'message' => 'Email is already registered.']);
                exit;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $mydb->insert('users', [
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword
            ]);

            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'User registered successfully.']);
            exit;
        }

        if ($type === 'login') {

            $email = trim($data['email'] ?? '');
            $password = $data['password'] ?? '';

            if ($email === '' || $password === '') {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
                exit;
            }

            $mydb->select('users', '*', ['email' => $email]);
            $user = $mydb->res ? $mydb->res->fetch_assoc() : null;

            if (!$user || !password_verify($password, $user['password'])) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
                exit;
            }

            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Logged in successfully.',
                // 'user' => ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email']]
            ]);
            exit;
        }

        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing or invalid type parameter.']);
        break;



    // DELETE - Logout (destroys the current session)

    case 'DELETE':

        $_SESSION = [];
        session_destroy();

        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Logged out successfully.']);
        break;



    default:

        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
}