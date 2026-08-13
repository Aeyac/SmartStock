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
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
    exit;
}

// Figure out "today" from the server's clock.
$currentYear = (int) date('Y');
$currentMonth = (int) date('n'); // 1 = January, 12 = December

// Figure out "last month" (handles January -> wraps back to December of last year)
$prevMonth = $currentMonth - 1;
$prevMonthYear = $currentYear;

if ($prevMonth === 0) {
    $prevMonth = 12;
    $prevMonthYear = $currentYear - 1;
}


// HELPER FUNCTIONS
// (small reusable pieces used more than once below)

// Adds up total_amount from either the "purchases" or "sales" table,
// but only for rows that fall inside one specific month/year.
function getMonthlyTotal($mydb, $tableName, $dateColumnName, $userId, $year, $month)
{
    $sql = "SELECT COALESCE(SUM(total_amount), 0) AS total 
            FROM {$tableName}
            WHERE user_id = ? 
              AND YEAR({$dateColumnName}) = ? 
              AND MONTH({$dateColumnName}) = ?";

    $stmt = $mydb->conn->prepare($sql);
    $stmt->bind_param('iii', $userId, $year, $month);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return (float) $row['total'];
}

// Works out the percentage change between this month and last month.
//
// Special case: if last month was 0, we CANNOT calculate a real
// percentage (that would be dividing by zero). Instead we return NULL,
// and the frontend shows a "New" label instead of a fake percentage.
function calculatePercentChange($currentValue, $previousValue)
{
    if ($previousValue == 0) {
        if ($currentValue == 0) {
            return 0.0; // nothing then, nothing now = 0% change
        }
        return null; // no baseline to compare against = "New"
    }

    $difference = $currentValue - $previousValue;
    $percent = ($difference / abs($previousValue)) * 100;

    return $percent;
}

// Rounds a number to 1 decimal place, UNLESS it's null (in which case
// we just leave it as null so it can still become "New" on the frontend).
function roundIfNotNull($value)
{
    if ($value === null) {
        return null;
    }
    return round($value, 1);
}


// =======================================================================
// STEP 1: STAT CARDS (Monthly Spend, Monthly Revenue, Net Profit)
// =======================================================================

$monthlySpend = getMonthlyTotal($mydb, 'purchases', 'purchase_date', $userId, $currentYear, $currentMonth);
$lastMonthSpend = getMonthlyTotal($mydb, 'purchases', 'purchase_date', $userId, $prevMonthYear, $prevMonth);

$monthlyRevenue = getMonthlyTotal($mydb, 'sales', 'sale_date', $userId, $currentYear, $currentMonth);
$lastMonthRevenue = getMonthlyTotal($mydb, 'sales', 'sale_date', $userId, $prevMonthYear, $prevMonth);

// Net Profit = money earned from sales MINUS money spent on purchases,
// for this month only. (This is a simple "cash flow" view, not full
$netProfit = $monthlyRevenue - $monthlySpend;
$lastMonthNetProfit = $lastMonthRevenue - $lastMonthSpend;

$spendChangePercent = calculatePercentChange($monthlySpend, $lastMonthSpend);
$revenueChangePercent = calculatePercentChange($monthlyRevenue, $lastMonthRevenue);
$netProfitChangePercent = calculatePercentChange($netProfit, $lastMonthNetProfit);

$stats = [
    'monthly_spend' => round($monthlySpend, 2),
    'monthly_spend_change_pct' => roundIfNotNull($spendChangePercent),

    'monthly_revenue' => round($monthlyRevenue, 2),
    'monthly_revenue_change_pct' => roundIfNotNull($revenueChangePercent),

    'net_profit' => round($netProfit, 2),
    'net_profit_change_pct' => roundIfNotNull($netProfitChangePercent),
];


// STEP 2: PERFORMANCE TRENDS CHART (Jan - Dec, this year)

$monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Start with all 12 months set to 0. We'll fill in real numbers next.
// (Using month number 1-12 as the array key, so $spendByMonth[3] = March.)
$spendByMonth = [];
$salesByMonth = [];

for ($month = 1; $month <= 12; $month++) {
    $spendByMonth[$month] = 0.0;
    $salesByMonth[$month] = 0.0;
}

// --- Get purchase totals grouped by month ---
$stmt = $mydb->conn->prepare(
    "SELECT MONTH(purchase_date) AS month_number, SUM(total_amount) AS total 
     FROM purchases
     WHERE user_id = ? AND YEAR(purchase_date) = ?
     GROUP BY MONTH(purchase_date)"
);
$stmt->bind_param('ii', $userId, $currentYear);
$stmt->execute();
$result = $stmt->get_result();

// Loop through each row the database gave us and slot it into the right month.
while ($row = $result->fetch_assoc()) {
    $monthNumber = (int) $row['month_number'];
    $spendByMonth[$monthNumber] = (float) $row['total'];
}
$stmt->close();

// --- Get sales totals grouped by month (same idea as above) ---
$stmt = $mydb->conn->prepare(
    "SELECT MONTH(sale_date) AS month_number, SUM(total_amount) AS total 
     FROM sales
     WHERE user_id = ? AND YEAR(sale_date) = ?
     GROUP BY MONTH(sale_date)"
);
$stmt->bind_param('ii', $userId, $currentYear);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $monthNumber = (int) $row['month_number'];
    $salesByMonth[$monthNumber] = (float) $row['total'];
}
$stmt->close();

// Turn our 1-12 indexed arrays into plain 0-11 indexed lists (what
// JavaScript/Chart.js expects), in Jan -> Dec order.
$spendList = [];
$salesList = [];

for ($month = 1; $month <= 12; $month++) {
    $spendList[] = round($spendByMonth[$month], 2);
    $salesList[] = round($salesByMonth[$month], 2);
}

$trends = [
    'months' => $monthLabels,
    'spend' => $spendList,
    'sales' => $salesList,
];


// LOW STOCK ALERTS

$stmt = $mydb->conn->prepare(
    "SELECT id, name, stock, safety_stock 
     FROM items
     WHERE user_id = ? AND deleted_at IS NULL AND stock <= safety_stock
     ORDER BY stock ASC
     LIMIT 10"
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$lowStockRows = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Build the final low_stock list one item at a time.
$lowStock = [];

foreach ($lowStockRows as $item) {
    $itemId = (int) $item['id'];
    $stock = (int) $item['stock'];
    $safetyStock = (int) $item['safety_stock'];

    
    $fakeSku = 'ITM-' . str_pad((string) $itemId, 2, '0', STR_PAD_LEFT);

    // Decide the status label: completely out, or just running low.
    if ($stock === 0) {
        $status = 'out';
    } else {
        $status = 'low';
    }

    $lowStock[] = [
        'id' => $itemId,
        'name' => $item['name'],
        'sku' => $fakeSku,
        'stock' => $stock,
        'safety_stock' => $safetyStock,
        'status' => $status,
    ];
}


// EST SELLING ITEMS (this month), ranked two ways
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
$result = $stmt->get_result();
$sellingRows = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Turn each raw database row into a clean array we can sort and reuse.
$sellingItems = [];

foreach ($sellingRows as $row) {
    $sellingItems[] = [
        'item_id' => (int) $row['item_id'],
        'name' => $row['name'],
        'quantity_sold' => (int) $row['qty'],
        'revenue' => round((float) $row['revenue'], 2),
    ];
}

// --- Make a copy sorted by QUANTITY SOLD, highest first ---
$byQuantity = $sellingItems; // copy the array so sorting one doesn't affect the other

usort($byQuantity, function ($itemA, $itemB) {
    // Returning a negative number means "itemA comes first"
    return $itemB['quantity_sold'] - $itemA['quantity_sold'];
});

// Only keep the top 5
$byQuantity = array_slice($byQuantity, 0, 5);

// --- Make a copy sorted by REVENUE, highest first ---
$byRevenue = $sellingItems;

usort($byRevenue, function ($itemA, $itemB) {
    if ($itemA['revenue'] == $itemB['revenue']) {
        return 0;
    }
    // Sort descending (biggest revenue first)
    return ($itemA['revenue'] < $itemB['revenue']) ? 1 : -1;
});

$byRevenue = array_slice($byRevenue, 0, 5);

$bestSelling = [
    'by_quantity' => $byQuantity,
    'by_revenue' => $byRevenue,
];


echo json_encode([
    'status' => 'success',
    'stats' => $stats,
    'trends' => $trends,
    'low_stock' => $lowStock,
    'best_selling' => $bestSelling,
]);