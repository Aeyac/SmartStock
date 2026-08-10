<?php
session_start();

require_once "../db.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated.']);
    exit;
}

$userId = $_SESSION['user_id'];
$mydb = new myDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
    exit;
}

$currentYear = (int) date('Y');
$currentMonth = (int) date('n');

$prevMonth = $currentMonth - 1;
$prevMonthYear = $currentYear;
if ($prevMonth === 0) {
    $prevMonth = 12;
    $prevMonthYear = $currentYear - 1;
}

// Sums total_amount from purchases/sales for a single calendar month.
function monthTotal($mydb, $table, $dateCol, $userId, $year, $month)
{
    $stmt = $mydb->conn->prepare(
        "SELECT COALESCE(SUM(total_amount), 0) AS total FROM {$table}
         WHERE user_id = ? AND YEAR({$dateCol}) = ? AND MONTH({$dateCol}) = ?"
    );
    $stmt->bind_param('iii', $userId, $year, $month);
    $stmt->execute();
    $total = (float) $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    return $total;
}

// Percent change vs previous month. Returns null when there's no baseline
// to compare against (previous month was zero) — a hardcoded "+100%" in
// that case would be misleading, especially for a value like Net Profit
// that can be negative. The frontend shows a neutral "New" tag instead.
function pctChange($current, $previous)
{
    if ($previous == 0) {
        return $current == 0 ? 0.0 : null;
    }
    return (($current - $previous) / abs($previous)) * 100;
}

function roundOrNull($value, $precision = 1)
{
    return $value === null ? null : round($value, $precision);
}

// ======================
// Stat Cards
// ======================
$monthlySpend = monthTotal($mydb, 'purchases', 'purchase_date', $userId, $currentYear, $currentMonth);
$prevSpend = monthTotal($mydb, 'purchases', 'purchase_date', $userId, $prevMonthYear, $prevMonth);

$monthlyRevenue = monthTotal($mydb, 'sales', 'sale_date', $userId, $currentYear, $currentMonth);
$prevRevenue = monthTotal($mydb, 'sales', 'sale_date', $userId, $prevMonthYear, $prevMonth);

$netProfit = $monthlyRevenue - $monthlySpend;
$prevNetProfit = $prevRevenue - $prevSpend;

$stats = [
    'monthly_spend' => round($monthlySpend, 2),
    'monthly_spend_change_pct' => roundOrNull(pctChange($monthlySpend, $prevSpend)),
    'monthly_revenue' => round($monthlyRevenue, 2),
    'monthly_revenue_change_pct' => roundOrNull(pctChange($monthlyRevenue, $prevRevenue)),
    'net_profit' => round($netProfit, 2),
    'net_profit_change_pct' => roundOrNull(pctChange($netProfit, $prevNetProfit)),
];

// ======================
// Performance Trends (Jan - Dec, current year)
// ======================
$monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

$spendByMonth = array_fill(1, 12, 0.0);
$salesByMonth = array_fill(1, 12, 0.0);

$stmt = $mydb->conn->prepare(
    "SELECT MONTH(purchase_date) AS m, SUM(total_amount) AS total FROM purchases
     WHERE user_id = ? AND YEAR(purchase_date) = ?
     GROUP BY MONTH(purchase_date)"
);
$stmt->bind_param('ii', $userId, $currentYear);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $spendByMonth[(int) $row['m']] = (float) $row['total'];
}
$stmt->close();

$stmt = $mydb->conn->prepare(
    "SELECT MONTH(sale_date) AS m, SUM(total_amount) AS total FROM sales
     WHERE user_id = ? AND YEAR(sale_date) = ?
     GROUP BY MONTH(sale_date)"
);
$stmt->bind_param('ii', $userId, $currentYear);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $salesByMonth[(int) $row['m']] = (float) $row['total'];
}
$stmt->close();

$trends = [
    'months' => $monthLabels,
    'spend' => array_values(array_map(fn($v) => round($v, 2), $spendByMonth)),
    'sales' => array_values(array_map(fn($v) => round($v, 2), $salesByMonth)),
];

// ======================
// Low Stock Alerts (stock <= safety_stock, includes fully out-of-stock items)
// ======================
$stmt = $mydb->conn->prepare(
    "SELECT id, name, stock, safety_stock FROM items
     WHERE user_id = ? AND deleted_at IS NULL AND stock <= safety_stock
     ORDER BY stock ASC
     LIMIT 10"
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$lowStockRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$lowStock = array_map(function ($item) {
    return [
        'id' => (int) $item['id'],
        'name' => $item['name'],
        // NOTE: items has no real SKU column. This is a synthetic
        // placeholder derived from the id purely so the UI has something to
        // show where the mockup expects one. Replace this if a real `sku`
        // column gets added to the items table later.
        'sku' => 'SKU-' . str_pad((string) $item['id'], 4, '0', STR_PAD_LEFT),
        'stock' => (int) $item['stock'],
        'safety_stock' => (int) $item['safety_stock'],
        'status' => ((int) $item['stock'] === 0) ? 'out' : 'low',
    ];
}, $lowStockRows);

// ======================
// Best Selling (this month, ranked two ways from the same underlying data)
// ======================
$stmt = $mydb->conn->prepare(
    "SELECT si.item_id, i.name, SUM(si.quantity) AS qty, SUM(si.subtotal) AS revenue
     FROM sale_items si
     INNER JOIN sales s ON s.id = si.sale_id
     INNER JOIN items i ON i.id = si.item_id
     WHERE i.user_id = ? AND YEAR(s.sale_date) = ? AND MONTH(s.sale_date) = ?
     GROUP BY si.item_id, i.name"
);
$stmt->bind_param('iii', $userId, $currentYear, $currentMonth);
$stmt->execute();
$sellingRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);    
$stmt->close();

$byQuantity = $sellingRows;
usort($byQuantity, fn($a, $b) => $b['qty'] <=> $a['qty']);
$byQuantity = array_slice($byQuantity, 0, 5);

$byRevenue = $sellingRows;
usort($byRevenue, fn($a, $b) => $b['revenue'] <=> $a['revenue']);
$byRevenue = array_slice($byRevenue, 0, 5);

$formatSelling = fn($rows) => array_values(array_map(fn($r) => [
    'item_id' => (int) $r['item_id'],
    'name' => $r['name'],
    'quantity_sold' => (int) $r['qty'],
    'revenue' => round((float) $r['revenue'], 2),
], $rows));

$bestSelling = [
    'by_quantity' => $formatSelling($byQuantity),
    'by_revenue' => $formatSelling($byRevenue),
];

echo json_encode([
    'status' => 'success',
    'stats' => $stats,
    'trends' => $trends,
    'low_stock' => $lowStock,
    'best_selling' => $bestSelling,
]);