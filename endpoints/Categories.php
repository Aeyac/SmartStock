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

    case 'GET':

        $mydb->select('categories', '*', ['user_id' => $userId]);
        $categories = $mydb->res ? $mydb->res->fetch_all(MYSQLI_ASSOC) : [];

        echo json_encode(['status' => 'success', 'categories' => $categories]);
        break;



    // POST - Create Category

    case 'POST':

        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $name = trim($data['name'] ?? '');

        if ($name === '') {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Category name is required.']);
            exit;
        }

        $mydb->select('categories', 'id', ['name' => $name, 'user_id' => $userId]);
        if ($mydb->res && $mydb->res->fetch_assoc()) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'This category already exists.']);
            exit;
        }

        $id = $mydb->insert('categories', ['user_id' => $userId, 'name' => $name]);

        http_response_code(201);
        echo json_encode(['status' => 'success', 'message' => 'Category created successfully.', 'id' => $id]);
        break;



    default:

        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
}