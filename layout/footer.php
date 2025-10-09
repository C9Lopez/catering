<?php
// Get the current URL path
$currentPath = $_SERVER['REQUEST_URI'];

// Check if the user is inside "catering-service" directory
$isInsideCateringService = strpos($currentPath, '/catering-service/') !== false;

// Define base paths dynamically
$basePath = $isInsideCateringService ? '../' : './';
$cateringBase = $isInsideCateringService ? './' : './catering-service/';
?>

<!-- Footer Start -->
<footer class="footer bg-dark text-white py-5 wow fadeInUp" data-wow-delay="0.1s">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <!-- About Section -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center mb-4">
                    <i class="fas fa-utensils text-primary fs-2 me-3"></i>
                    <h2 class="text-primary mb-0 fw-bold">Pochie<span class="text-light">Catering</span></h2>
                </div>
                <p class="lh-lg opacity-75">Providing top-quality catering services for your special occasions with elegance and perfection. We bring culinary excellence to every event.</p>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-light rounded-circle p-2" href="https://www.facebook.com/yourpage" aria-label="Facebook" target="_blank" rel="noopener">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a class="btn btn-outline-light rounded-circle p-2" href="https://www.instagram.com/yourpage" aria-label="Instagram" target="_blank" rel="noopener">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a class="btn btn-outline-light rounded-circle p-2" href="https://www.twitter.com/yourpage" aria-label="Twitter" target="_blank" rel="noopener">
                        <i class="fab fa-twitter"></i>
                    </a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
                <h4 class="mb-4 fw-semibold text-light">Quick Links</h4>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a class="text-light text-decoration-none opacity-75 hover-primary" href="<?= $basePath ?>index.php">
                            <i class="fas fa-chevron-right me-2 opacity-50"></i>Home
                        </a>
                    </li>
                    <li class="mb-2">
                        <a class="text-light text-decoration-none opacity-75 hover-primary" href="<?= $basePath ?>about.php">
                            <i class="fas fa-chevron-right me-2 opacity-50"></i>About Us
                        </a>
                    </li>
                    <li class="mb-2">
                        <a class="text-light text-decoration-none opacity-75 hover-primary" href="<?= $cateringBase ?>wedding.php">
                            <i class="fas fa-chevron-right me-2 opacity-50"></i>Catering Services
                        </a>
                    </li>
                    <li class="mb-2">
                        <a class="text-light text-decoration-none opacity-75 hover-primary" href="<?= $basePath ?>contact.php">
                            <i class="fas fa-chevron-right me-2 opacity-50"></i>Contact
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Contact Information -->
            <div class="col-lg-3 col-md-6">
                <h4 class="mb-4 fw-semibold text-light">Contact Us</h4>
                <ul class="list-unstyled">
                    <li class="mb-3">
                        <a class="text-light text-decoration-none opacity-75 d-flex align-items-center" href="https://maps.google.com/?q=123+Street,+Philippines" target="_blank" rel="noopener">
                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                            123 Street, Philippines
                        </a>
                    </li>
                    <li class="mb-3">
                        <a class="text-light text-decoration-none opacity-75 d-flex align-items-center" href="tel:+63123123123">
                            <i class="fas fa-phone-alt text-primary me-2"></i>
                            (+63) 123 123 123
                        </a>
                    </li>
                    <li class="mb-3">
                        <a class="text-light text-decoration-none opacity-75 d-flex align-items-center" href="mailto:test@example.com">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            test@example.com
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Gallery Section -->
            <div class="col-lg-3 col-md-6">
                <h4 class="mb-4 fw-semibold text-light">Social Gallery</h4>
                <div class="row g-2">
                    <div class="col-4">
                        <a href="<?= $basePath ?>img/menu-01.jpg" class="d-block" data-bs-toggle="modal" data-bs-target="#galleryModal">
                            <img src="<?= $basePath ?>img/menu-01.jpg" class="img-fluid rounded-circle border border-primary border-2 p-1 w-100" alt="Gallery Image 1" loading="lazy">
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="<?= $basePath ?>img/menu-02.jpg" class="d-block" data-bs-toggle="modal" data-bs-target="#galleryModal">
                            <img src="<?= $basePath ?>img/menu-02.jpg" class="img-fluid rounded-circle border border-primary border-2 p-1 w-100" alt="Gallery Image 2" loading="lazy">
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="<?= $basePath ?>img/menu-03.jpg" class="d-block" data-bs-toggle="modal" data-bs-target="#galleryModal">
                            <img src="<?= $basePath ?>img/menu-03.jpg" class="img-fluid rounded-circle border border-primary border-2 p-1 w-100" alt="Gallery Image 3" loading="lazy">
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="<?= $basePath ?>img/menu-04.jpg" class="d-block" data-bs-toggle="modal" data-bs-target="#galleryModal">
                            <img src="<?= $basePath ?>img/menu-04.jpg" class="img-fluid rounded-circle border border-primary border-2 p-1 w-100" alt="Gallery Image 4" loading="lazy">
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="<?= $basePath ?>img/menu-05.jpg" class="d-block" data-bs-toggle="modal" data-bs-target="#galleryModal">
                            <img src="<?= $basePath ?>img/menu-05.jpg" class="img-fluid rounded-circle border border-primary border-2 p-1 w-100" alt="Gallery Image 5" loading="lazy">
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="<?= $basePath ?>img/menu-06.jpg" class="d-block" data-bs-toggle="modal" data-bs-target="#galleryModal">
                            <img src="<?= $basePath ?>img/menu-06.jpg" class="img-fluid rounded-circle border border-primary border-2 p-1 w-100" alt="Gallery Image 6" loading="lazy">
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <hr class="my-4 border-secondary">
        <div class="text-center">
            <p class="mb-0 opacity-75">&copy; <?php echo date('Y'); ?> Pochie Catering. All Rights Reserved. 
                Designed and Developed By 
                <a class="text-primary text-decoration-none border-bottom border-primary" href="https://www.example.com">DonLopez and CapstoneTeam</a>
            </p>
        </div>
    </div>
</footer>

<!-- Gallery Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white" id="galleryModalLabel">Gallery Image</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <img id="modalImage" src="" class="img-fluid w-100" alt="Gallery Image">
            </div>
        </div>
    </div>
</div>

<style>
.hover-primary:hover {
    color: #0d6efd !important;
    opacity: 1 !important;
    transition: color 0.2s ease;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle gallery modal image update
    const galleryLinks = document.querySelectorAll('[data-bs-toggle="modal"]');
    galleryLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const imgSrc = this.querySelector('img').src;
            document.getElementById('modalImage').src = imgSrc;
            document.getElementById('galleryModalLabel').textContent = this.querySelector('img').alt;
        });
    });
});
</script>
<!-- Footer End -->