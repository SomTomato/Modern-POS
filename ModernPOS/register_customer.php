<?php
require __DIR__ . '/templates/header.php';
require __DIR__ . '/templates/sidebar.php';

$message = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_customer'])) {
    $name = trim($_POST['name']);
    $phone_number = trim($_POST['phone_number']);

    if (!empty($name) && !empty($phone_number)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO customers (name, phone_number) VALUES (?, ?)");
            $stmt->execute([$name, $phone_number]);
            $message = "<div class='alert-success'>Customer added successfully!</div>";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = "<div class='alert-danger'>Error: This phone number is already registered.</div>";
            } else {
                $message = "<div class='alert-danger'>Error: " . $e->getMessage() . "</div>";
            }
        }
    }
}

$customers = $pdo->query("SELECT * FROM customers WHERE id != 1 ORDER BY name")->fetchAll();
?>

<h1><i class="fa-solid fa-user-plus"></i> Register Customer</h1>
<?php echo $message; ?>

<div class="card">
    <div class="card-header">
        <h2>Add New Customer</h2>
    </div>
    <form method="post" action="register_customer.php">
        <div class="form-group">
            <label for="name">Customer Name</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="phone_number">Phone Number</label>
            <input type="text" name="phone_number" id="phone_number" class="form-control" required>
        </div>
        <button type="submit" name="add_customer" class="btn btn-primary">Add Customer</button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2>Registered Customers List (View Only)</h2>
    </div>
    <table class="styled-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Phone Number</th>
                <th>Date Registered</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($customers)): ?>
                <tr><td colspan="3">No customers have been registered yet.</td></tr>
            <?php else: ?>
                <?php foreach ($customers as $customer): ?>
                <tr>
                    <td><?php echo htmlspecialchars($customer['name']); ?></td>
                    <td><?php echo htmlspecialchars($customer['phone_number']); ?></td>
                    <td><?php echo date("Y-m-d", strtotime($customer['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>