<?php
// Get the current URL path
$currentPath = $_SERVER['REQUEST_URI'];

// Check if the user is inside "catering-service" directory
$isInsideCateringService = strpos($currentPath, '/catering-service/') !== false;
$isInsideCateringPackages = strpos($currentPath, '/catering-packages/') !== false;

// Define base paths dynamically
$basePath = $isInsideCateringService || $isInsideCateringPackages ? '../' : './';
$cateringBase = $isInsideCateringService ? './' : ($isInsideCateringPackages ? '../catering-service/' : './catering-service/');
$packageBase = $isInsideCateringPackages ? './' : ($isInsideCateringService ? '../catering-packages/' : './catering-packages/');

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
<!-- Navbar Start -->
<div class="container-fluid nav-bar wow fadeIn" data-wow-delay="0.1s">
    <div class="container">
        <nav class="navbar navbar-light navbar-expand-lg py-4">
            <a href="index.php" class="navbar-brand">
                <h1 class="text-primary fw-bold mb-0">Pochie<span class="text-dark">Catering</span></h1>
            </a>
            <button class="navbar-toggler py-2 px-3" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarCollapse">
                <span class="fa fa-bars text-primary"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav mx-auto">
                <a href="<?= $basePath ?>index.php" class="nav-item nav-link <?= isActive('index.php') ?>" >Home</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle <?= isActive(['wedding.php', 'debut.php', 'childrens.php', 'corporate.php', 'private.php']) ?>" data-bs-toggle="dropdown">Catering Services</a>
                        <div class="dropdown-menu">
                            <a href="<?= $cateringBase  ?>wedding.php" class="dropdown-item">Wedding Catering Services</a>
                            <a href="<?= $cateringBase  ?>debut.php" class="dropdown-item">Debut Catering Services</a>
                            <a href="<?= $cateringBase  ?>childrens.php" class="dropdown-item">Children's Party Catering
                                Services</a>
                            <a href="<?= $cateringBase  ?>corporate.php" class="dropdown-item">Corporate Catering
                                Services</a>
                            <a href="<?= $cateringBase  ?>private.php" class="dropdown-item">Private Party Catering
                                Services</a>
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle <?= isActive(['basic-package.php', 'standard-package.php', 'premium-package.php']) ?>" data-bs-toggle="dropdown">Catering Packages</a>
                        <div class="dropdown-menu">
                            <a href="<?=$packageBase  ?>basic-package.php" class="dropdown-item">Basic Package</a>
                            <a href="<?=$packageBase  ?>standard-package.php" class="dropdown-item">Standard
                                Package</a>
                            <a href="<?=$packageBase  ?>premium-package.php" class="dropdown-item">Premium Package</a>
                        </div>
                    </div>
                    <a href="<?= $basePath ?>about.php" class="nav-item nav-link  <?= isActive('about.php') ?>">About Us</a>
                    <a href="<?= $basePath ?>contact.php" class="nav-item nav-link <?= isActive('contact.php') ?>">Contact</a>
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="<?= $basePath ?>profile.php" class="nav-item nav-link <?= isActive('profile.php') ?>">Profile</a>
                    <?php endif; ?>
                </div>
                <div class="d-flex align-items-center">
                    <div class="theme-switcher me-3">
                        <button id="theme-toggle" class="btn btn-sm btn-outline-secondary" title="Toggle theme">
                            <i class="fas fa-moon"></i>
                        </button>
                    </div>
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <!-- <a href="<?= $basePath ?>auth/logout.php" class="btn btn-outline-danger">Logout</a> -->
                    <?php else: ?>
                    <a href="<?= $basePath ?>auth/login.php" class="btn btn-outline-primary me-2">Log In</a>
                    <a href="<?= $basePath ?>auth/signup.php" class="btn btn-primary">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </div>
</div>
<!-- Navbar End -->