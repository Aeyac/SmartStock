<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/SupplierController.php';

// Get which controller/action was requested from the URL
$controllerName = $_GET['controller'] ?? '';
$action = $_GET['action'] ?? '';

$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody, true);
if (!is_array($data)) {
    $data = [];
}

// Combine controller + action into one string so match() can check it in one place
$route = $controllerName . '.' . $action;

match ($route) {

    // AUTH
    'auth.register' => (new AuthController())->register($data),
    'auth.login' => (new AuthController())->login($data),
    'auth.logout' => (new AuthController())->logout($data),

    // SUPPLIER
    'supplier.create' => (new SupplierController())->createSupplier($data),


    // DEFAULT: no matching route found
    default => (function () {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Route not found.']);
        })(),
};