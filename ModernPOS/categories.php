<?php
require __DIR__ . '/templates/header.php';
require __DIR__ . '/templates/sidebar.php';

$message = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $image_name = 'default_category.png';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "uploads/categories/";
            $image_name = time() . '_' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];
            if (in_array($imageFileType, $allowed_types) && $_FILES["image"]["size"] < 5000000) {
                move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
            } else {
                $image_name = 'default_category.png';
            }
        }
        
        $name = trim($_POST['name']);
        if (!empty($name)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name, image) VALUES (?, ?)");
                $stmt->execute([$name, $image_name]);
                $message .= "<div class='alert-success'>Category added successfully!</div>";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                     $message .= "<div class='alert-danger'>Error: This category already exists.</div>";
                } else {
                    $message .= "<div class='alert-danger'>Error: " . $e->getMessage() . "</div>";
                }
            }
        }
    }

    if (isset($_POST['delete_category'])) {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $message = "<div class='alert-danger'>Category deleted.</div>";
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<h1><i class="fa-solid fa-tags"></i> Category Management</h1>
<?php echo $message; ?>

<div class="card">
    <div class="card-header">
        <h2>Add New Category</h2>
    </div>
    <form method="post" action="categories.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Category Name</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="image">Category Image</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*">
            <div class="image-preview-container">
                <img id="image-preview" src="#" alt="Image Preview"/>
                <span id="preview-text">Image Preview</span>
            </div>
        </div>
        <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2>Existing Categories</h2>
    </div>
    <table class="styled-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Category Name</th>
                <th style="width: 150px; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
            <tr>
                <td>
                    <img class="table-image" src="uploads/categories/<?php echo htmlspecialchars($cat['image']); ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>">
                </td>
                <td><?php echo htmlspecialchars($cat['name']); ?></td>
                <td style="text-align: right;">
                    <form method="post" action="categories.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this category?');">
                        <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                        <button type="submit" name="delete_category" class="btn btn-danger">Delete</button>
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