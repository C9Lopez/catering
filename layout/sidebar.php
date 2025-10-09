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
    <div class="sidebar-header d-flex justify-content-between align-items-center px-3 py-3 border-bottom">
        <a href="../admin/index.php" class="sidebar-brand d-flex align-items-center text-decoration-none">
            <i class="fas fa-shield-alt text-primary me-2"></i>
            <span class="fw-bold text-white">ADMIN DASHBOARD</span>
        </a>
        <button class="sidebar-close d-lg-none btn btn-sm btn-link text-white p-0" type="button" id="sidebarClose" aria-label="Close Sidebar">
            <i class="fas fa-times fs-5"></i>
        </button>
    </div>

    <nav class="sidebar-nav flex-grow-1">
        <ul class="nav flex-column px-2 pt-3">
            <li class="nav-item mb-2">
                <a href="../admin/index.php" class="nav-link d-flex align-items-center px-3 py-2 rounded-3 text-white <?= isActive('index.php') ?>" 
                   data-bs-toggle="tooltip" title="Dashboard">
                    <i class="fas fa-tachometer-alt me-3 opacity-75"></i>
                    <span class="flex-grow-1">Dashboard</span>
                    <i class="fas fa-chevron-right ms-auto opacity-50 d-none"></i>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="../admin/orders.php" class="nav-link d-flex align-items-center px-3 py-2 rounded-3 text-white <?= isActive('orders.php') ?>" 
                   data-bs-toggle="tooltip" title="Bookings">
                    <i class="fas fa-calendar-check me-3 opacity-75"></i>
                    <span class="flex-grow-1">Bookings</span>
                    <i class="fas fa-chevron-right ms-auto opacity-50 d-none"></i>
                </a>
            </li>
            <!-- <li class="nav-item mb-2">
                <a href="../admin/reports.php" class="nav-link d-flex align-items-center px-3 py-2 rounded-3 text-white <?= isActive('reports.php') ?>" 
                   data-bs-toggle="tooltip" title="Reports">
                    <i class="fas fa-chart-line me-3 opacity-75"></i>
                    <span class="flex-grow-1">Reports</span>
                    <i class="fas fa-chevron-right ms-auto opacity-50 d-none"></i>
                </a>
            </li> -->
            <li class="nav-item mb-2">
                <a href="../admin/packages.php" class="nav-link d-flex align-items-center px-3 py-2 rounded-3 text-white <?= isActive(['packages.php', 'add_package.php', 'edit_package.php']) ?>" 
                   data-bs-toggle="tooltip" title="Packages">
                    <i class="fas fa-box-open me-3 opacity-75"></i>
                    <span class="flex-grow-1">Packages</span>
                    <i class="fas fa-chevron-right ms-auto opacity-50 d-none"></i>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="../admin/menus.php" class="nav-link d-flex align-items-center px-3 py-2 rounded-3 text-white <?= isActive('menus.php') ?>" 
                   data-bs-toggle="tooltip" title="Menus">
                    <i class="fas fa-utensils me-3 opacity-75"></i>
                    <span class="flex-grow-1">Menus</span>
                    <i class="fas fa-chevron-right ms-auto opacity-50 d-none"></i>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="../admin/customization_options.php" class="nav-link d-flex align-items-center px-3 py-2 rounded-3 text-white <?= isActive('customization_options.php') ?>" 
                   data-bs-toggle="tooltip" title="Customizations">
                    <i class="fas fa-cogs me-3 opacity-75"></i>
                    <span class="flex-grow-1">Customizations</span>
                    <i class="fas fa-chevron-right ms-auto opacity-50 d-none"></i>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="../admin/announcement.php" class="nav-link d-flex align-items-center px-3 py-2 rounded-3 text-white <?= isActive('announcement.php') ?>" 
                   data-bs-toggle="tooltip" title="Announcement">
                    <i class="fas fa-bullhorn me-3 opacity-75"></i>
                    <span class="flex-grow-1">Announcement</span>
                    <i class="fas fa-chevron-right ms-auto opacity-50 d-none"></i>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="../admin/users.php" class="nav-link d-flex align-items-center px-3 py-2 rounded-3 text-white <?= isActive('users.php') ?>" 
                   data-bs-toggle="tooltip" title="Users">
                    <i class="fas fa-users me-3 opacity-75"></i>
                    <span class="flex-grow-1">Users</span>
                    <i class="fas fa-chevron-right ms-auto opacity-50 d-none"></i>
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer border-top px-3 py-3">
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="https://i.pravatar.cc/40?u=<?php echo urlencode($adminEmail); ?>" alt="<?php echo htmlspecialchars($adminName); ?>" width="32" height="32" class="rounded-circle me-2">
                <div class="flex-grow-1 text-start">
                    <strong class="d-block"><?php echo htmlspecialchars($adminName); ?></strong>
                    <small class="d-block"><?php echo htmlspecialchars($adminEmail); ?></small>
                </div>
                <i class="fas fa-chevron-down ms-2 opacity-75"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end text-small shadow-lg border-0 mt-2" aria-labelledby="dropdownUser" style="min-width: 200px;">
                <li><a class="dropdown-item px-3 py-2" href="../admin/settings.php">
                    <i class="fas fa-cog me-2"></i>Settings
                </a></li>
                <li><a class="dropdown-item px-3 py-2" href="../profile.php">
                    <i class="fas fa-user me-2"></i>Profile
                </a></li>
                <li><hr class="dropdown-divider my-0"></li>
                <li><a class="dropdown-item text-danger px-3 py-2" href="../auth/logout_admin.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Sign out
                </a></li>
            </ul>
        </div>
    </div>
</aside>
<!-- Sidebar End -->

<!-- Optional: Add this script for tooltips if not already included -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>