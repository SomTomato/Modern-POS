<?php
require __DIR__ . '/templates/header.php';
require __DIR__ . '/templates/sidebar.php';

// --- Security Check: Only allow admins to access this page ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // You can redirect them or show an error message
    echo "<h1>Access Denied</h1>";
    echo "<p>You do not have permission to view this page.</p>";
    require __DIR__ . '/templates/footer.php';
    exit(); // Stop the script immediately
}

$message = ''; // To store success or error messages

// --- Handle Form Submissions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Logic to Add a new user
    if (isset($_POST['add_user'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $role = $_POST['role'];

        if (!empty($username) && !empty($password) && !empty($role)) {
            // Hash the password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $stmt->execute([$username, $hashed_password, $role]);
                $message = "<div class='alert-success'>User created successfully!</div>";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { // Check for duplicate username
                     $message = "<div class='alert-danger'>Error: This username is already taken.</div>";
                } else {
                    $message = "<div class='alert-danger'>Error: " . $e->getMessage() . "</div>";
                }
            }
        } else {
            $message = "<div class='alert-danger'>Please fill in all fields.</div>";
        }
    }
}

// Fetch all users to display in the table
$users = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY username")->fetchAll();
?>

<h1><i class="fa-solid fa-users-cog"></i> User Management</h1>
<?php echo $message; ?>

<div class="card">
    <div class="card-header">
        <h2>Create New User</h2>
    </div>
    <form method="post" action="users.php">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select name="role" id="role" class="form-control" required>
                <option value="cashier">Cashier</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <button type="submit" name="add_user" class="btn btn-primary">Create User</button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2>Existing Users</h2>
    </div>
    <table class="styled-table">
        <thead>
            <tr>
                <th>Username</th>
                <th>Role</th>
                <th>Created On</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo ucfirst($user['role']); // Capitalize first letter ?></td>
                    <td><?php echo date("Y-m-d", strtotime($user['created_at'])); ?></td>
                    <td>
                        <!-- Prevent the primary admin user from being deleted -->
                        <?php if ($user['id'] != 1): ?>
                            <button class="btn btn-danger">Delete</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>