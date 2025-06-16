<?php
require __DIR__ . '/templates/header.php';
require __DIR__ . '/templates/sidebar.php';

$products = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.name ASC
")->fetchAll();

const LOW_STOCK_THRESHOLD = 10;
?>

<div class="no-print" style="display: flex; justify-content: space-between; align-items: center;">
    <h1><i class="fa-solid fa-warehouse"></i> Inventory Stock-take Sheet</h1>
    <button class="btn btn-primary" onclick="window.print()"><i class="fa-solid fa-print"></i> Print Sheet</button>
</div>

<div class="card">
    <div class="card-header no-print">
        <h2>Current Stock Levels</h2>
        <p>Print this sheet to perform a physical stock count.</p>
    </div>
    <table class="styled-table">
        <thead>
            <tr>
                <th>Product</th>
                <th style="text-align: center;">System Qty</th>
                
                <th class="print-only" style="width: 100px; text-align:center;">Physical Count</th>
                <th class="print-only" style="width: 80px; text-align:center;">Verified ✔</th>
                <th class="print-only" style="width: 80px; text-align:center;">Over</th>
                <th class="print-only" style="width: 80px; text-align:center;">Missing</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr><td colspan="5">No products found.</td></tr>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <img class="table-image" src="uploads/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <div>
                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                                    <small>SKU: <?php echo htmlspecialchars($product['sku']); ?></small>
                                </div>
                            </div>
                        </td>
                        <td style="text-align: center; font-weight: bold; font-size: 1.2em;">
                            <?php echo $product['quantity']; ?>
                        </td>
                        
                        <td class="print-only writable"></td>
                        <td class="print-only"><input type="checkbox"></td>
                        <td class="print-only writable"></td>
                        <td class="print-only writable"></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>