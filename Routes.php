<?php
/**
 * Centralized API router (front controller).
 *
 * Every backend request goes through THIS one file, e.g.:
 *   /api/routes.php?controller=auth&action=register
 *   /api/routes.php?controller=auth&action=login
 *
 * How it works, step by step:
 *   1. Read "controller" and "action" from the URL (?controller=auth&action=login)
 *   2. Read the JSON body sent by fetch() (name, email, password, etc.)
 *   3. Use match() to decide which class + which method should handle it
 *   4. Call that method and let it print the JSON response
 *
 * To add a new module (items, sales, etc.):
 *   1. require_once its controller file below
 *   2. Add a new match() case for it
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/controllers/Auth.php';
// require_once __DIR__ . '/controllers/Item.php';
// require_once __DIR__ . '/controllers/Supplier.php';
// require_once __DIR__ . '/controllers/Purchase.php';
// require_once __DIR__ . '/controllers/Sale.php';

// Get which controller/action was requested from the URL
$controllerName = $_GET['controller'] ?? '';
$action = $_GET['action'] ?? '';

// Get the JSON body sent by fetch() and turn it into a normal PHP array
$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody, true);
if (!is_array($data)) {
    $data = [];
}

// Combine controller + action into one string so match() can check it in one place
// e.g. "auth" + "login" becomes "auth.login"
$route = $controllerName . '.' . $action;

match ($route) {

    // ---------------- AUTH ROUTES ----------------
    'auth.register' => (new Auth())->register($data),
    'auth.login' => (new Auth())->login($data),
    'auth.logout' => (new Auth())->logout($data),

    // ---------------- ITEM ROUTES (example, uncomment when ready) ----------------
    // 'items.list'   => (new Item())->list($data),
    // 'items.create' => (new Item())->create($data),
    // 'items.update' => (new Item())->update($data),
    // 'items.delete' => (new Item())->delete($data),

    // ---------------- DEFAULT: no matching route found ----------------
    default => (function () {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Route not found.']);
        })(),
};