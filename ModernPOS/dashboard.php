<?php
require __DIR__ . '/templates/header.php';
require __DIR__ . '/templates/sidebar.php';

// --- Part 1: Fetch stats for the summary cards ---
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$sales_today_count = $pdo->query("SELECT COUNT(*) FROM sales WHERE DATE(sale_date) = CURDATE()")->fetchColumn();
$revenue_today = $pdo->query("SELECT SUM(total_amount) FROM sales WHERE DATE(sale_date) = CURDATE()")->fetchColumn() ?? 0;


// --- Part 2: Rebuilt Chart Data Preparation ---

// Step A: Get an associative array of sales data from the last 7 days.
// The PDO::FETCH_KEY_PAIR style is very efficient for this.
// It creates an array like: ['2025-06-14' => 11.50, '2025-06-13' => 18.00]
$sales_data = $pdo->query("
    SELECT 
        DATE(sale_date) as sale_day, 
        SUM(total_amount) as daily_total
    FROM sales
    WHERE sale_date >= CURDATE() - INTERVAL 6 DAY
    GROUP BY sale_day
    ORDER BY sale_day
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Step B: Create the final arrays for the chart using a single, clean loop.
$chart_labels = [];
$chart_values = [];
for ($i = 6; $i >= 0; $i--) {
    // Get the date for each of the last 7 days
    $date = date('Y-m-d', strtotime("-$i days"));
    
    // Add the formatted date (e.g., "Sat, Jun 14") to our labels array
    $chart_labels[] = date('D, M j', strtotime($date));
    
    // Check if a sale happened on that date. If yes, use the total. If no, use 0.
    // This guarantees we have exactly 7 data points in the correct order.
    $chart_values[] = $sales_data[$date] ?? 0;
}
?>

<h1><i class="fa-solid fa-chart-pie"></i> Dashboard</h1>

<div class="stats-grid">
    <div class="stat-card products">
        <div class="icon"><i class="fa-solid fa-box-archive"></i></div>
        <div class="info">
            <h3>Total Products</h3>
            <p><?php echo $total_products; ?></p>
        </div>
    </div>
    <div class="stat-card sales">
        <div class="icon"><i class="fa-solid fa-cash-register"></i></div>
        <div class="info">
            <h3>Sales Today</h3>
            <p><?php echo $sales_today_count; ?></p>
        </div>
    </div>
    <div class="stat-card revenue">
        <div class="icon"><i class="fa-solid fa-dollar-sign"></i></div>
        <div class="info">
            <h3>Revenue Today</h3>
            <p>$<?php echo number_format($revenue_today, 2); ?></p>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 30px; height: 400px;">
    <div class="card-header">
        <h2>Sales Trend (Last 7 Days)</h2>
    </div>
    <canvas id="salesChart"></canvas>
</div>

<div id="chartData"
     data-labels='<?php echo json_encode($chart_labels); ?>'
     data-values='<?php echo json_encode($chart_values); ?>'>
</div>

<?php 
// Tell the footer to load the dashboard's specific javascript file
$page_scripts = ['assets/js/dashboard.js'];
require __DIR__ . '/templates/footer.php'; 
?>