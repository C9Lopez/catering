<?php
// Get the current URL path
$currentPath = $_SERVER['REQUEST_URI'];

// Assuming admin name is stored in session after login
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$adminName = $_SESSION['admin_name'] ?? 'Super Admin';
$adminEmail = $_SESSION['admin_email'] ?? 'admin@example.com';

function isActive($pages) {
    global $currentPath;
    foreach ((array) $pages as $page) {
        if (strpos($currentPath, $page) !== false) {
            return ' active';
        }
    }
    return '';
}
?>
<!-- Sidebar Start -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="../admin/index.php" class="sidebar-brand">
            
            <span>ADMIN DASHBOARD</span>
        </a>
        <button class="sidebar-close d-lg-none" type="button" id="sidebarClose" aria-label="Close Sidebar">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <ul class="list-unstyled components">
            <li class="nav-item">
                <a href="../admin/index.php" class="nav-link<?= isActive('index.php') ?>">
                    <i class="fas fa-tachometer-alt fa-fw"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../admin/orders.php" class="nav-link<?= isActive('orders.php') ?>">
                    <i class="fas fa-calendar-check fa-fw"></i>
                    <span>Bookings</span>
                </a>
            </li>
            <!-- <li class="nav-item">
                <a href="../admin/reports.php" class="nav-link<?= isActive('reports.php') ?>">
                    <i class="fas fa-chart-line fa-fw"></i>
                    <span>Reports</span>
                </a>
            </li> -->
            <li class="nav-item">
                <a href="../admin/packages.php" class="nav-link<?= isActive(['packages.php', 'add_package.php', 'edit_package.php']) ?>">
                    <i class="fas fa-box-open fa-fw"></i>
                    <span>Packages</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../admin/menus.php" class="nav-link<?= isActive('menus.php') ?>">
                    <i class="fas fa-utensils fa-fw"></i>
                    <span>Menus</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../admin/customization_options.php" class="nav-link<?= isActive('customization_options.php') ?>">
                    <i class="fas fa-cogs fa-fw"></i>
                    <span>Customizations</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../admin/announcement.php" class="nav-link<?= isActive('announcement.php') ?>">
                    <i class="fas fa-bullhorn fa-fw"></i>
                    <span>Announcement</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../admin/users.php" class="nav-link<?= isActive('users.php') ?>">
                    <i class="fas fa-users fa-fw"></i>
                    <span>Users</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="https://i.pravatar.cc/40?u=<?php echo urlencode($adminEmail); ?>" alt="" width="32" height="32" class="rounded-circle me-2">
                <strong><?php echo htmlspecialchars($adminName); ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser">
                <li><a class="dropdown-item" href="../admin/settings.php">Settings</a></li>
                <li><a class="dropdown-item" href="../profile.php">Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="../auth/logout_admin.php">Sign out</a></li>
            </ul>
        </div>
    </div>
</aside>
<!-- Sidebar End -->