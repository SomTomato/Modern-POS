<?php
require __DIR__ . '/templates/header.php';
require __DIR__ . '/templates/sidebar.php';

// --- Pagination & Filter Variables ---
// Get current filter values from URL, with defaults
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// --- Database Queries ---

// 1. Get TOTAL count of sales that match the date filter for pagination calculations
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM sales WHERE DATE(sale_date) BETWEEN ? AND ?");
$count_stmt->execute([$start_date, $end_date]);
$total_sales = $count_stmt->fetchColumn();
$total_pages = $limit > 0 ? ceil($total_sales / $limit) : 1;

// 2. Calculate the OFFSET for the main query
$offset = ($page - 1) * $limit;

// 3. Get the sales for the CURRENT PAGE
$sql = "
    SELECT s.id, s.total_amount, s.sale_date, u.username as cashier_name
    FROM sales s
    JOIN users u ON s.user_id = u.id
    WHERE DATE(s.sale_date) BETWEEN ? AND ?
    ORDER BY s.sale_date DESC
";

// If "All" is not selected, apply LIMIT and OFFSET
if ($limit > 0) {
    $sql .= " LIMIT ? OFFSET ?";
}

$stmt = $pdo->prepare($sql);

// Bind parameters
$stmt->bindValue(1, $start_date);
$stmt->bindValue(2, $end_date);
if ($limit > 0) {
    $stmt->bindValue(3, $limit, PDO::PARAM_INT);
    $stmt->bindValue(4, $offset, PDO::PARAM_INT);
}
$stmt->execute();
$sales = $stmt->fetchAll();

// Calculate total revenue for the selected period (all pages)
$rev_stmt = $pdo->prepare("SELECT SUM(total_amount) FROM sales WHERE DATE(sale_date) BETWEEN ? AND ?");
$rev_stmt->execute([$start_date, $end_date]);
$total_revenue = $rev_stmt->fetchColumn() ?? 0;
?>

<h1><i class="fa-solid fa-file-invoice-dollar"></i> Sales Report</h1>

<div class="card">
    <div class="card-header">
        <h2>Filter Sales</h2>
    </div>
    <form method="get" action="sales_report.php" class="filter-form">
        <input type="hidden" name="limit" value="<?php echo $limit; ?>">
        <div style="display: flex; gap: 20px; align-items: flex-end;">
            <div class="form-group" style="flex: 1;">
                <label for="start_date">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="end_date">End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Sales from <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?></h2>
        <div style="text-align: right;">
            <h3 style="margin: 0;">Total Revenue: <span style="color: var(--success-color);">$<?php echo number_format($total_revenue, 2); ?></span></h3>
        </div>
    </div>
    <table class="styled-table">
        <thead>
            <tr>
                <th>Sale ID</th><th>Date & Time</th><th>Cashier</th><th style="text-align: right;">Total Amount</th><th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($sales)): ?>
                <tr><td colspan="5">No sales found for the selected period.</td></tr>
            <?php else: ?>
                <?php foreach ($sales as $sale): ?>
                <tr>
                    <td>#<?php echo $sale['id']; ?></td>
                    <td><?php echo date("Y-m-d h:i A", strtotime($sale['sale_date'])); ?></td>
                    <td><?php echo htmlspecialchars($sale['cashier_name']); ?></td>
                    <td style="text-align: right;">$<?php echo number_format($sale['total_amount'], 2); ?></td>
                    <td style="text-align: center;"><a href="invoice.php?sale_id=<?php echo $sale['id']; ?>" class="btn btn-primary" target="_blank">View Invoice</a></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination-container">
        <div class="show-per-page">
            <form method="get" action="sales_report.php">
                <input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
                <input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
                <label for="limit">Show:</label>
                <select name="limit" id="limit" onchange="this.form.submit()" class="form-control" style="width: auto; display: inline-block;">
                    <option value="10" <?php if ($limit == 10) echo 'selected'; ?>>10</option>
                    <option value="20" <?php if ($limit == 20) echo 'selected'; ?>>20</option>
                    <option value="50" <?php if ($limit == 50) echo 'selected'; ?>>50</option>
                    <option value="0" <?php if ($limit == 0) echo 'selected'; ?>>All</option>
                </select>
                <span> per page</span>
            </form>
        </div>
        <nav>
            <ul class="pagination">
                <li class="page-item <?php if($page <= 1){ echo 'disabled'; } ?>">
                    <a class="page-link" href="?page=<?php echo $page-1; ?>&limit=<?php echo $limit; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>">Previous</a>
                </li>
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php if($page == $i) {echo 'active'; } ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?php if($page >= $total_pages){ echo 'disabled'; } ?>">
                    <a class="page-link" href="?page=<?php echo $page+1; ?>&limit=<?php echo $limit; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>