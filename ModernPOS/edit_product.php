<?php
require __DIR__ . '/templates/header.php';
require __DIR__ . '/templates/sidebar.php';

$message = '';
$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    header("Location: products.php");
    exit();
}

// Handle form submission for updating the product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    // Get form data
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $current_image = $_POST['current_image'];
    $image_name = $current_image;

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // ... (Image upload logic is the same as in add product) ...
        $target_dir = "uploads/products/";
        $image_name = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        // ... (move_uploaded_file logic) ...
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }

    // Update database
    $stmt = $pdo->prepare("UPDATE products SET name = ?, category_id = ?, price = ?, quantity = ?, image = ? WHERE id = ?");
    $stmt->execute([$name, $category_id, $price, $quantity, $image_name, $product_id]);
    $message = "<div class='alert-success'>Product updated successfully!</div>";
}

// Fetch the product's current data to pre-fill the form
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    echo "Product not found.";
    exit();
}

// Fetch all categories for the dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<h1><i class="fa-solid fa-edit"></i> Edit Product</h1>
<?php echo $message; ?>

<div class="card">
    <div class="card-header">
        <h2>Editing "<?php echo htmlspecialchars($product['name']); ?>"</h2>
    </div>
    <form method="post" action="edit_product.php?id=<?php echo $product_id; ?>" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
        <input type="hidden" name="current_image" value="<?php echo $product['image']; ?>">

        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="category_id">Category</label>
            <select name="category_id" id="category_id" class="form-control" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $product['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="price">Price</label>
            <input type="number" step="0.01" name="price" id="price" class="form-control" value="<?php echo $product['price']; ?>" required>
        </div>
        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" name="quantity" id="quantity" class="form-control" value="<?php echo $product['quantity']; ?>" required>
        </div>
        <div class="form-group">
            <label for="image">Product Image (leave empty to keep current image)</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*">
            <div class="image-preview-container" style="margin-top: 15px;">
                <p>Current Image:</p>
                <img id="image-preview" src="uploads/products/<?php echo htmlspecialchars($product['image']); ?>" alt="Current Image" style="max-width: 200px; display: block;" />
            </div>
        </div>
        <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
        <a href="products.php" class="btn" style="background-color: #777;">Cancel</a>
    </form>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>