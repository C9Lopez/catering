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
<div class="sidebar">
    <div class="sidebar-header">
        <h3>Catering Admin</h3>
        <button class="sidebar-toggle d-lg-none" type="button">
            <i class="fas fa-bars"></i>
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
            <a href="../admin/booking.php" class="nav-link<?= isActive('orders.php') ?>">
                <i class="fas fa-shopping-cart me-2"></i>
                <span>booking</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../admin/users.php" class="nav-link<?= isActive('users.php') ?>">
                <i class="fas fa-users me-2"></i>
                <span>Users</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../admin/settings.php" class="nav-link<?= isActive('settings.php') ?>">
                <i class="fas fa-cog me-2"></i>
                <span>Settings</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../auth/logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt me-2"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>
<!-- Sidebar End -->
