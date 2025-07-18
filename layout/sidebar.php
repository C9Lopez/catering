<?php
// Get the current URL path
$currentPath = $_SERVER['REQUEST_URI'];

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
<div class="mobile-header debug-header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-expanded="false" aria-controls="sidebar">
        <i class="fas fa-bars"></i>
    </button>
    <h3 class="mobile-header-title"><?php echo basename($_SERVER['PHP_SELF'], '.php') === 'index' ? 'Admin Dashboard' : ucfirst(basename($_SERVER['PHP_SELF'], '.php')); ?></h3>
</div>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3>Catering Admin</h3>
        <button class="sidebar-close d-lg-none" type="button" id="sidebarClose" aria-label="Close Sidebar">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <ul class="list-unstyled components">
        <li class="nav-item">
            <a href="../admin/index.php" class="nav-link<?= isActive('index.php') ?>">
                <i class="fas fa-tachometer-alt me-2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../admin/packages.php" class="nav-link<?= isActive('packages.php') ?>">
                <i class="fas fa-box-open me-2"></i>
                <span>Packages</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../admin/announcement.php" class="nav-link<?= isActive('announcement.php') ?>">
                <i class="fas fa-box"></i>
                <span>Announcement</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../admin/orders.php" class="nav-link<?= isActive('orders.php') ?>">
            <i class="fas fa-calendar-check me-2"></i>
                <span>Bookings</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../admin/menus.php" class="nav-link<?= isActive('menus.php') ?>">
            <i class="fas fa-utensils me-2"></i>
                <span>Menus</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../admin/users.php" class="nav-link<?= isActive('users.php') ?>">
                <i class="fas fa-users me-2"></i>
                <span>Users</span>
            </a>
        </li>
        <!-- <li class="nav-item">
            <a href="../admin/settings.php" class="nav-link<?= isActive('settings.php') ?>">
                <i class="fas fa-cog me-2"></i>
                <span>Settings</span>
            </a>
        </li> -->
        <li class="nav-item">
            <a href="../auth/logout_admin.php" class="nav-link">
                <i class="fas fa-sign-out-alt me-2"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>
<!-- Sidebar End -->