<aside class="sidebar">
    <ul class="sidebar-menu">

        <?php 
            // Get the current page filename to check which menu should be active
            $current_page = basename($_SERVER['PHP_SELF']); 
        ?>

        <?php if ($_SESSION['role'] === 'admin'): ?>
            <li class="menu-item"><a href="dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
            <li class="menu-item"><a href="pos_terminal.php"><i class="fa-solid fa-calculator"></i> New Sale</a></li>
            <li class="menu-item"><a href="sales_report.php"><i class="fa-solid fa-file-invoice-dollar"></i> Sales Report</a></li>

            <li class="menu-header">MANAGEMENT</li>
            
            <li class="menu-item has-submenu <?php if (in_array($current_page, ['products.php', 'categories.php', 'edit_product.php'])) echo 'open'; ?>">
                <a href="#"><i class="fa-solid fa-box-archive"></i> Catalog <i class="fa fa-angle-down float-right"></i></a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="products.php">Products</a></li>
                    <li class="submenu-item"><a href="categories.php">Categories</a></li>
                </ul>
            </li>
            
            <li class="menu-item has-submenu <?php if (in_array($current_page, ['inventory.php', 'stock_adjustment.php'])) echo 'open'; ?>">
                <a href="#"><i class="fa-solid fa-warehouse"></i> Inventory <i class="fa fa-angle-down float-right"></i></a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="inventory.php">Stock Count</a></li>
                    <li class="submenu-item"><a href="stock_adjustment.php">Stock Adjustment</a></li>
                </ul>
            </li>

            <li class="menu-header">ADMINISTRATION</li>
            <li class="menu-item"><a href="customers.php"><i class="fa-solid fa-users"></i> Customer Management</a></li>
            
            <li class="menu-item has-submenu <?php if (in_array($current_page, ['users.php'])) echo 'open'; ?>">
                <a href="#"><i class="fa-solid fa-users-cog"></i> Users <i class="fa fa-angle-down float-right"></i></a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="users.php">Manage Users</a></li>
                </ul>
            </li>

        <?php else: ?>
            <li class="menu-item"><a href="dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
            <li class="menu-item"><a href="pos_terminal.php"><i class="fa-solid fa-calculator"></i> New Sale</a></li>
            <li class="menu-item"><a href="register_customer.php"><i class="fa-solid fa-user-plus"></i> Register Customer</a></li>
            <li class="menu-item"><a href="inventory.php"><i class="fa-solid fa-warehouse"></i> View Stock</a></li>

        <?php endif; ?>

    </ul>
</aside>
<main class="main-content">