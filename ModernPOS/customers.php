<?php
require __DIR__ . '/templates/header.php';
require __DIR__ . '/templates/sidebar.php';

// Check if a phone number was passed from the POS terminal to pre-fill the form
$prefilled_phone = isset($_GET['phone']) ? htmlspecialchars($_GET['phone']) : '';

$message = ''; // For success or error messages

// --- Handle Form Submissions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Logic to Add a new customer
    if (isset($_POST['add_customer'])) {
        $name = trim($_POST['name']);
        $phone_number = trim($_POST['phone_number']);

        if (!empty($name) && !empty($phone_number)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO customers (name, phone_number) VALUES (?, ?)");
                $stmt->execute([$name, $phone_number]);
                $message = "<div class='alert alert-success'>Customer added successfully!</div>";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { // Check for duplicate phone number
                    $message = "<div class='alert alert-danger'>Error: This phone number is already registered.</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
                }
            }
        }
    }

    // Logic to Delete a customer
    if (isset($_POST['delete_customer'])) {
        $id = $_POST['id'];
        // IMPORTANT: We prevent deleting customer ID 1, which is our "General Customer"
        if ($id != 1) {
            $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
            $stmt->execute([$id]);
            $message = "<div class='alert alert-danger'>Customer deleted.</div>";
        }
    }
}

// Fetch all customers to display in the table, except for the default 'General Customer'
$customers = $pdo->query("SELECT * FROM customers WHERE id != 1 ORDER BY name")->fetchAll();
?>

<h1><i class="fa-solid fa-users"></i> Customer Management</h1>
<?php echo $message; ?>

<div class="card">
    <div class="card-header">
        <h2>Add New Customer</h2>
    </div>
    <form method="post" action="customers.php">
        <div class="form-group">
            <label for="name">Customer Name</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="phone_number">Phone Number</label>
            <input type="text" name="phone_number" id="phone_number" class="form-control" value="<?php echo $prefilled_phone; ?>" required>
        </div>
        <button type="submit" name="add_customer" class="btn btn-primary">Add Customer</button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2>Registered Customers</h2>
    </div>
    <table class="styled-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Phone Number</th>
                <th>Date Registered</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($customers)): ?>
                <tr>
                    <td colspan="4">No customers have been registered yet.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($customers as $customer): ?>
                <tr>
                    <td><?php echo htmlspecialchars($customer['name']); ?></td>
                    <td><?php echo htmlspecialchars($customer['phone_number']); ?></td>
                    <td><?php echo date("Y-m-d", strtotime($customer['created_at'])); ?></td>
                    <td style="text-align: right;">
                        <form method="post" action="customers.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this customer? This cannot be undone.');">
                            <input type="hidden" name="id" value="<?php echo $customer['id']; ?>">
                            <button type="submit" name="delete_customer" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>