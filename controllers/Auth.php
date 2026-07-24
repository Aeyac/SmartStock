<?php
require_once __DIR__ . '/../models/User.php';

class Auth
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }


    public function register($data)
    {
        header('Content-Type: application/json; charset=utf-8');

        $name = isset($data['name']) ? trim($data['name']) : '';
        $email = isset($data['email']) ? trim($data['email']) : '';
        $password = isset($data['password']) ? $data['password'] : '';

        if (!$this->validateRegister($name, $email, $password)) {
            return;
        }

        // Check if email is already registered
        $existing = $this->userModel->findByEmail($email);

        if ($existing) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Email is already registered.']);
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $this->userModel->create($name, $email, $hashedPassword);

        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'message' => 'User registered successfully.',
            'user' => [
                'name' => $name,
                'email' => $email
            ]
        ]);
    }


    public function login($data)
    {
        header('Content-Type: application/json; charset=utf-8');

        $email = isset($data['email']) ? trim($data['email']) : '';
        $password = isset($data['password']) ? $data['password'] : '';

        if (!$this->validateLogin($email, $password)) {
            return;
        }

        $user = $this->userModel->findByEmail($email);

        // Same generic message whether the email doesn't exist or the
        // password is wrong — don't reveal which one it was.
        if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
            return;
        }

        // Regenerate the session id on login to prevent session fixation.
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'Logged in successfully.',
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ]
        ]);
    }


    public function logout($data)
    {
        header('Content-Type: application/json; charset=utf-8');

        session_destroy();
        header("/SmartStock");

        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Logged out successfully.']);
        exit;
    }


    private function validateRegister($name, $email, $password)
    {
        if (empty($name) || empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'All fields (name, email, password) are required.']);
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
            return false;
        }

        if (strlen($password) < 8) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long.']);
            return false;
        }

        return true;
    }


    private function validateLogin($email, $password)
    {
        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
            return false;
        }

        return true;
    }

}