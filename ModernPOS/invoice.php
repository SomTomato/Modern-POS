<?php
session_start();
require __DIR__ . '/includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$sale_id = $_GET['sale_id'] ?? die("Error: Sale ID not specified.");

$stmt = $pdo->prepare("
    SELECT s.*, u.username as cashier_name, c.name as customer_name, c.phone_number as customer_phone
    FROM sales s 
    LEFT JOIN users u ON s.user_id = u.id
    LEFT JOIN customers c ON s.customer_id = c.id
    WHERE s.id = ?
");
$stmt->execute([$sale_id]);
$sale = $stmt->fetch();

if (!$sale) { die("Error: Sale not found."); }

$stmt = $pdo->prepare("
    SELECT si.*, p.name as product_name, p.sku as product_sku
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    WHERE si.sale_id = ?
");
$stmt->execute([$sale_id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?php echo $sale['id']; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        @media print {
            body { background-color: #fff; }
            .no-print { display: none; }
            .invoice-box { box-shadow: none; border: none; margin: 0; padding: 0; }
        }
    </style>
</head>
<body style="background-color: #eee; padding: 20px;">
    <div class="invoice-box">
        <table>
            <tr class="top">
                <td colspan="4">
                    <table>
                        <tr>
                            <td class="title"><h2>Modern POS</h2></td>
                            <td>
                                <strong>Invoice #:</strong> <?php echo $sale['id']; ?><br>
                                <strong>Date:</strong> <?php echo date("Y-m-d h:i A", strtotime($sale['sale_date'])); ?><br>
                                <strong>Cashier:</strong> <?php echo htmlspecialchars($sale['cashier_name']); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="information">
                <td colspan="4">
                    <table>
                        <tr>
                            <td>
                                <strong>BILLED TO:</strong><br>
                                <?php echo htmlspecialchars($sale['customer_name']); ?><br>
                                <?php if($sale['customer_phone'] !== 'N/A') echo htmlspecialchars($sale['customer_phone']); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="heading">
                <td>Item</td>
                <td style="text-align: center;">Quantity</td>
                <td style="text-align: right;">Price/Unit</td>
                <td style="text-align: right;">Total</td>
            </tr>
            <?php foreach ($items as $item): ?>
            <tr class="item">
                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
                <td style="text-align: right;">$<?php echo number_format($item['price_per_unit'], 2); ?></td>
                <td style="text-align: right;">$<?php echo number_format($item['quantity'] * $item['price_per_unit'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total">
                <td colspan="3" style="text-align: right; font-weight: bold;">Grand Total:</td>
                <td style="text-align: right; font-weight: bold;">$<?php echo number_format($sale['total_amount'], 2); ?></td>
            </tr>
            <tr class="heading">
                <td>Payment Method</td>
                <td colspan="3" style="text-align: right;">Details</td>
            </tr>
            <tr class="details">
                <td><?php echo ucfirst($sale['payment_method']); ?></td>
                <td colspan="3" style="text-align: right;"><?php echo htmlspecialchars($sale['payment_provider'] ?? ''); ?></td>
            </tr>
        </table>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" class="btn btn-primary"><i class="fa fa-print"></i> Print Invoice</button>
        <a href="pos_terminal.php" class="btn" style="background-color: #555;">New Sale</a>
    </div>
</body>
</html>