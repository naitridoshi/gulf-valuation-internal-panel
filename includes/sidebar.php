<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="dashboard.php" class="brand-link d-flex align-items-center">
        <img src="<?php echo htmlspecialchars($settings['logo'] ?? 'https://via.placeholder.com/40'); ?>" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-light" style="white-space:normal; word-break:break-word; max-width:140px; display:inline-block;"><?php echo htmlspecialchars($settings['company_name'] ?? 'Final Val'); ?></span>
    </a>
    <div class="sidebar">
        <nav class="mt-3">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="valuations.php?action=add" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'valuations.php' && isset($_GET['action']) && $_GET['action'] == 'add') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-plus-circle"></i>
                        <p>Add New Valuation</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Manage Users</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="valuations.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'valuations.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-car"></i>
                        <p>Valuations</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_banks.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_banks.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-bank"></i>
                        <p>Manage Banks</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_car_models.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_car_models.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-car-side"></i>
                        <p>Manage Car Models</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="config_page.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'config_page.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-cog"></i>
                        <p>Configuration</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>