<?php
require __DIR__ . '/templates/header.php';
require __DIR__ . '/templates/sidebar.php';

$message = '';

// --- Handle Stock Adjustment Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adjust_stock'])) {
    $product_id = $_POST['product_id'];
    $adjustment_type = $_POST['adjustment_type'];
    $quantity = (int)$_POST['quantity'];
    $reason = trim($_POST['reason']);
    $user_id = $_SESSION['user_id'];

    if ($product_id && $quantity > 0) {
        try {
            $pdo->beginTransaction();

            $stmt_check = $pdo->prepare("SELECT quantity FROM products WHERE id = ?");
            $stmt_check->execute([$product_id]);
            $current_quantity = $stmt_check->fetchColumn();

            if ($adjustment_type === 'remove' && $quantity > $current_quantity) {
                throw new Exception("Cannot remove more stock than is available. Current stock: $current_quantity");
            }

            $sql_update = ($adjustment_type === 'add') ? "UPDATE products SET quantity = quantity + ? WHERE id = ?" : "UPDATE products SET quantity = quantity - ? WHERE id = ?";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([$quantity, $product_id]);

            $stmt_log = $pdo->prepare("INSERT INTO stock_adjustments (product_id, user_id, adjustment_type, quantity_changed, reason) VALUES (?, ?, ?, ?, ?)");
            $stmt_log->execute([$product_id, $user_id, $adjustment_type, $quantity, $reason]);
            
            $pdo->commit();
            $message = "<div class='alert-success'>Stock adjusted successfully.</div>";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "<div class='alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert-danger'>Please select a product and enter a valid quantity.</div>";
    }
}

// --- Fetch Data for Display ---
// For the product dropdown
$products = $pdo->query("SELECT id, name, quantity, image FROM products ORDER BY name")->fetchAll();
// For the adjustments log table
$adjustments = $pdo->query("
    SELECT sa.*, p.name as product_name, u.username as user_name
    FROM stock_adjustments sa
    JOIN products p ON sa.product_id = p.id
    JOIN users u ON sa.user_id = u.id
    ORDER BY sa.created_at DESC
    LIMIT 25
")->fetchAll();
?>

<h1><i class="fa-solid fa-right-left"></i> Stock Adjustment</h1>
<?php echo $message; ?>

<div class="card">
    <div class="card-header">
        <h2>Adjust Product Stock</h2>
    </div>
    <form method="post" action="stock_adjustment.php">
        <div class="form-group">
            <label for="product_id">Select Product</label>
            <select name="product_id" id="product_id" class="form-control" required>
                <option value="">-- Choose a product --</option>
                <?php foreach($products as $product): ?>
                    <option value="<?php echo $product['id']; ?>" data-image="<?php echo $product['image']; ?>">
                        <?php echo htmlspecialchars($product['name']); ?> (Current Stock: <?php echo $product['quantity']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="image-preview-container" id="product-image-preview-container" style="display: none; justify-content: flex-start; height: auto; margin-bottom: 20px;">
             <img id="product-image-preview" src="#" alt="Product Image" class="table-image"/>
        </div>

        <div class="form-group">
            <label for="adjustment_type">Adjustment Type</label>
            <select name="adjustment_type" id="adjustment_type" class="form-control" required>
                <option value="add">Add to Stock</option>
                <option value="remove">Remove from Stock</option>
            </select>
        </div>
        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
        </div>
        <div class="form-group">
            <label for="reason">Reason for Adjustment</label>
            <input type="text" name="reason" id="reason" class="form-control" placeholder="e.g., New shipment, Damaged goods">
        </div>
        <button type="submit" name="adjust_stock" class="btn btn-primary">Submit Adjustment</button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2>Recent Adjustment Log</h2>
    </div>
    <table class="styled-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Product</th>
                <th>Type</th>
                <th>Quantity</th>
                <th>Reason</th>
                <th>Adjusted By</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($adjustments)): ?>
                <tr><td colspan="6">No stock adjustments have been recorded yet.</td></tr>
            <?php else: ?>
                <?php foreach ($adjustments as $adj): ?>
                <tr>
                    <td><?php echo date("Y-m-d H:i", strtotime($adj['created_at'])); ?></td>
                    <td><?php echo htmlspecialchars($adj['product_name']); ?></td>
                    <td>
                        <?php if ($adj['adjustment_type'] == 'add'): ?>
                            <span class="status-badge active" style="background-color: #3498db;">Added</span>
                        <?php else: ?>
                            <span class="status-badge disabled" style="background-color: var(--warning-color);">Removed</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $adj['quantity_changed']; ?></td>
                    <td><?php echo htmlspecialchars($adj['reason']); ?></td>
                    <td><?php echo htmlspecialchars($adj['user_name']); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const imagePreviewContainer = document.getElementById('product-image-preview-container');
    const imagePreview = document.getElementById('product-image-preview');

    productSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const imageUrl = selectedOption.dataset.image;

        if (imageUrl && imageUrl !== 'default.png') {
            imagePreview.src = 'uploads/products/' + imageUrl;
            imagePreviewContainer.style.display = 'flex';
        } else {
            imagePreviewContainer.style.display = 'none';
        }
    });
});
</script>

<?php require __DIR__ . '/templates/footer.php'; ?>