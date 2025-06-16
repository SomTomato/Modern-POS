<?php
require __DIR__ . '/templates/header.php';
require __DIR__ . '/templates/sidebar.php';

$message = ''; // To store success or error messages

// --- Handle ALL Form Submissions at the top ---

// 1. Handle Toggling Product Status (Enable/Disable)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $product_id_to_toggle = $_POST['id'];
    $new_status = $_POST['new_status'];
    $stmt = $pdo->prepare("UPDATE products SET is_active = ? WHERE id = ?");
    $stmt->execute([$new_status, $product_id_to_toggle]);
    $action = $new_status == 1 ? 'Enabled' : 'Disabled';
    $message = "<div class='alert-success'>Product status changed to $action.</div>";
}

// 2. Handle Adding a New Product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $image_name = 'default.png';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/products/";
        $image_name = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];
        if (in_array($imageFileType, $allowed_types) && $_FILES["image"]["size"] < 5000000) {
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
        } else {
            $image_name = 'default.png';
        }
    }

    try {
        $pdo->beginTransaction();
        $name = $_POST['name'];
        $category_id = $_POST['category_id'];
        $price = $_POST['price'];
        $quantity = $_POST['quantity'];

        $stmt = $pdo->prepare("INSERT INTO products (name, category_id, price, quantity, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category_id, $price, $quantity, $image_name]);

        $lastId = $pdo->lastInsertId();
        $sku = 'MPOS' . str_pad($lastId, 9, '0', STR_PAD_LEFT);

        $stmt = $pdo->prepare("UPDATE products SET sku = ? WHERE id = ?");
        $stmt->execute([$sku, $lastId]);

        $pdo->commit();
        $message = "<div class='alert-success'>Product added successfully with SKU: " . htmlspecialchars($sku) . "</div>";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "<div class='alert-danger'>Failed to add product: " . $e->getMessage() . "</div>";
    }
}

// --- Fetch data for display ---
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.name")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<h1><i class="fa-solid fa-box-archive"></i> Product Management</h1>
<?php echo $message; ?>

<div class="card">
    <div class="card-header">
        <h2>Add New Product</h2>
    </div>
    <form method="post" action="products.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="category_id">Category</label>
            <select name="category_id" id="category_id" class="form-control" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="price">Price</label>
            <input type="number" step="0.01" name="price" id="price" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" name="quantity" id="quantity" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="image">Product Image</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*">
            <div class="image-preview-container">
                <img id="image-preview" src="#" alt="Image Preview"/>
                <span id="preview-text">Image Preview</span>
            </div>
        </div>
        <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2>Existing Products</h2>
    </div>
    <table class="styled-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Barcode (SKU)</th>
                <th>Category</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Status</th>
                <th style="width: 220px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><img class="table-image" src="uploads/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"></td>
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td><?php echo htmlspecialchars($product['sku']); ?></td>
                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                <td>$<?php echo number_format($product['price'], 2); ?></td>
                <td><?php echo $product['quantity']; ?></td>
                <td>
                    <?php if ($product['is_active']): ?>
                        <span class="status-badge active">Active</span>
                    <?php else: ?>
                        <span class="status-badge disabled">Disabled</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">Edit</a>
                    <form method="post" action="products.php" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                        <?php if ($product['is_active']): ?>
                            <input type="hidden" name="new_status" value="0">
                            <button type="submit" name="toggle_status" class="btn btn-danger">Disable</button>
                        <?php else: ?>
                            <input type="hidden" name="new_status" value="1">
                            <button type="submit" name="toggle_status" class="btn btn-success">Enable</button>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('image-preview');
    const previewText = document.getElementById('preview-text');

    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            previewText.style.display = 'none';
            imagePreview.style.display = 'block';
            reader.onload = function(event) {
                imagePreview.setAttribute('src', event.target.result);
            }
            reader.readAsDataURL(file);
        } else {
            previewText.style.display = 'block';
            imagePreview.style.display = 'none';
            imagePreview.setAttribute('src', '#');
        }
    });
});
</script>

<?php require __DIR__ . '/templates/footer.php'; ?>