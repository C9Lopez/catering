<!-- Footer Start -->
<?php
// Get the current URL path
$currentPath = $_SERVER['REQUEST_URI'];

// Check if the user is inside "catering-service" directory
$isInsideCateringService = strpos($currentPath, '/catering-service/') !== false;

// Define base paths dynamically
$basePath = $isInsideCateringService ? '../' : './';
$cateringBase = $isInsideCateringService ? './' : './catering-service/';

?>

<div class="container-fluid footer py-6 bg-dark text-white wow fadeInUp" data-wow-delay="0.1s">
    <div class="container">
        <div class="row g-4">
            <!-- About Section -->
            <div class="col-lg-3 col-md-6">
                <h1 class="text-primary">Pochie<span class="text-light">Catering</span></h1>
                <p class="lh-lg">Providing top-quality catering services for your special occasions with elegance and perfection.</p>
                <div class="flex">
                    <a class="btn btn-outline-light rounded-circle me-2" href="https://www.facebook.com/yourpage"><i class="fab fa-facebook-f"></i></a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="col-lg-3 col-md-6">
                <h4 class="mb-4">Quick Links</h4>
                <ul class="list-unstyled">
                    <li><a class="text-light text-decoration-none" href="../index.php">Home</a></li>
                    <li><a class="text-light text-decoration-none" href="../about.php">About Us</a></li>
                </ul>
            </div>

            <!-- Contact Information -->
            <div class="col-lg-3 col-md-6">
                <h4 class="mb-4">Contact Us</h4>
                <p><i class="fa fa-map-marker-alt text-primary me-2"></i>123 Street, Philippines</p>
                <p><i class="fa fa-phone-alt text-primary me-2"></i> (+63) 123 123 123</p>
                <p><i class="fas fa-envelope text-primary me-2"></i> test@example.com</p>
            </div>

            <!-- Gallery Section -->
            <div class="col-lg-3 col-md-6">
                <h4 class="mb-4">Social Gallery</h4>
                <div class="row g-2">
                    <div class="col-4"><img src="<?= $basePath ?>img/menu-01.jpg" class="img-fluid rounded-circle border border-primary p-1" alt=""></div>
                    <div class="col-4"><img src="<?= $basePath ?>/img/menu-02.jpg" class="img-fluid rounded-circle border border-primary p-1" alt=""></div>
                    <div class="col-4"><img src="<?= $basePath ?>/img/menu-03.jpg" class="img-fluid rounded-circle border border-primary p-1" alt=""></div>
                    <div class="col-4"><img src="<?= $basePath ?>/img/menu-04.jpg" class="img-fluid rounded-circle border border-primary p-1" alt=""></div>
                    <div class="col-4"><img src="<?= $basePath ?>/img/menu-05.jpg" class="img-fluid rounded-circle border border-primary p-1" alt=""></div>
                    <div class="col-4"><img src="<?= $basePath ?>/img/menu-06.jpg" class="img-fluid rounded-circle border border-primary p-1" alt=""></div>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="text-center mt-5">
            <p class="mb-0">&copy; 2024 Pochie Catering. All Rights Reserved. Designed and Developed By <a class="text-primary border-bottom" href="https://www.example.com">DonLopez and CapstoneTeam</a></p>
        </div>
    </div>
</div>
<!-- Footer End -->