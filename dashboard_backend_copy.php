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

    // GET - Dashboard Summary

    case 'GET':

        $currentMonth = date('Y-m');

        $stmt = $mydb->conn->prepare(
            "SELECT COALESCE(SUM(total_amount), 0) AS total FROM purchases
             WHERE user_id = ? AND DATE_FORMAT(purchase_date, '%Y-%m') = ?"
        );
        $stmt->bind_param('is', $userId, $currentMonth);
        $stmt->execute();
        $monthlySpend = (float) $stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();

        $stmt = $mydb->conn->prepare(
            "SELECT COALESCE(SUM(total_amount), 0) AS total FROM sales
             WHERE user_id = ? AND DATE_FORMAT(sale_date, '%Y-%m') = ?"
        );
        $stmt->bind_param('is', $userId, $currentMonth);
        $stmt->execute();
        $monthlySale = (float) $stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();

        $stmt = $mydb->conn->prepare(
            "SELECT DATE_FORMAT(purchase_date, '%Y-%m') AS month, SUM(total_amount) AS total
             FROM purchases WHERE user_id = ? GROUP BY month ORDER BY month DESC LIMIT 6"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $spendTrend = array_reverse($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();

        $stmt = $mydb->conn->prepare(
            "SELECT DATE_FORMAT(sale_date, '%Y-%m') AS month, SUM(total_amount) AS total
             FROM sales WHERE user_id = ? GROUP BY month ORDER BY month DESC LIMIT 6"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $saleTrend = array_reverse($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $stmt->close();

        $stmt = $mydb->conn->prepare(
            "SELECT * FROM items WHERE user_id = ? AND stock <= safety_stock ORDER BY stock ASC"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $lowStockItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $stmt = $mydb->conn->prepare(
            "SELECT i.id, i.name, SUM(si.quantity) AS total_quantity, SUM(si.subtotal) AS total_revenue
             FROM sale_items si
             INNER JOIN sales s ON s.id = si.sale_id
             INNER JOIN items i ON i.id = si.item_id
             WHERE s.user_id = ?
             GROUP BY i.id, i.name
             ORDER BY total_quantity DESC
             LIMIT 5"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $bestByQuantity = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $stmt = $mydb->conn->prepare(
            "SELECT i.id, i.name, SUM(si.quantity) AS total_quantity, SUM(si.subtotal) AS total_revenue
             FROM sale_items si
             INNER JOIN sales s ON s.id = si.sale_id
             INNER JOIN items i ON i.id = si.item_id
             WHERE s.user_id = ?
             GROUP BY i.id, i.name
             ORDER BY total_revenue DESC
             LIMIT 5"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $bestByRevenue = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        echo json_encode([
            'status' => 'success',
            'dashboard' => [
                'monthly_spend' => $monthlySpend,
                'monthly_sale' => $monthlySale,
                'spend_trend' => $spendTrend,
                'sale_trend' => $saleTrend,
                'low_stock_items' => $lowStockItems,
                'best_selling_by_quantity' => $bestByQuantity,
                'best_selling_by_revenue' => $bestByRevenue,
            ]
        ]);
        break;



    default:

        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
}