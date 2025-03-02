<?php
  session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Private Party Catering - Pochie Catering</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Playball&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <!-- <link href="../lib/animate/animate.min.css" rel="stylesheet"> -->
    <link href="../lib/lightbox/css/lightbox.min.css" rel="stylesheet">
    <link href="../lib/owlcarousel/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/themes.css" rel="stylesheet">
</head>
<body class="light-theme">

<!-- Loading Screen -->
<div id="loading-screen">
    <div class="loader"></div>
</div>

<?php include '../layout/navbar.php'; ?>

    <!-- Hero Section -->
    <div class="container-fluid hero-section py-6 my-6 text-center wow fadeInUp" data-wow-delay="0.3s">
        <div class="hero-overlay"></div>
        <div class="container position-relative text-white">
            <h1 class="display-1 mb-4">Private Party <span class="text-primary">Catering</span></h1>
            <p class="lead">Create intimate and memorable celebrations with our personalized private party catering services.</p>
            <a href="./private_menu.php" class="btn btn-primary border-0 rounded-pill py-2 px-3 px-md-3 animated bounceInLeft">View Menu</a>
            
        </div>
    </div>
    <!-- Hero End -->

    <!-- About Private Party Catering -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-5 mb-4">Personalized Party Catering</h1>
                    <p class="mb-4">
                        Transform your private gatherings into extraordinary experiences with our bespoke catering services. 
                        Whether it's an intimate dinner party, family reunion, anniversary celebration, or any special occasion, 
                        we create personalized menus and setups that perfectly match your vision.
                    </p>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check text-primary me-3"></i>
                                <span>Customized Menus</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check text-primary me-3"></i>
                                <span>Elegant Presentation</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check text-primary me-3"></i>
                                <span>Professional Service</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check text-primary me-3"></i>
                                <span>Flexible Venues</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 wow zoomIn" data-wow-delay="0.5s">
                    <img src="../img/event-6.jpg" class="img-fluid rounded" alt="Private Party Catering">
                </div>
            </div>
        </div>
    </div>

    <!-- Private Party Packages -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="text-center mb-5">
                <h1 class="display-5">Corporate Packages</h1>
                <p class="fs-5">Choose the perfect package for your special day</p>
            </div>
            <div class="row g-4">
                <?php
                require '../db.php';
                try {
                    // Fetch wedding packages from database
                    $stmt = $db->prepare("SELECT * FROM catering_packages WHERE category = 'Private Catering'");
                    $stmt->execute();
                    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($packages as $package):
                        // Generate star rating based on package price
                        $stars = min(5, ceil($package['price'] / 20000));
                ?>
                <div class="col-lg-4 col-md-6 wow bounceInUp" data-wow-delay="0.1s">
                    <div class="package-item rounded overflow-hidden">
                        <div class="text-center p-4">
                            <h3 class="mb-0"><?php echo htmlspecialchars($package['name']); ?></h3>
                            <div class="mb-3">
                                <?php for ($i = 0; $i < $stars; $i++): ?>
                                    <small class="fa fa-star text-primary"></small>
                                <?php endfor; ?>
                            </div>
                            <h1 class="mb-3">
                                <small class="align-top" style="font-size: 22px; line-height: 45px;">₱</small>
                                <?php echo number_format($package['price'], 0); ?>
                                <small class="align-bottom" style="font-size: 16px; line-height: 40px;">/ package</small>
                            </h1>
                        </div>
                        <div class="p-4">
                            <?php
                            // Convert description to list items
                            $features = explode("\n", $package['description']);
                            foreach ($features as $feature):
                                if (!empty(trim($feature))):
                            ?>
                                <p><i class="fa fa-check text-primary me-2"></i><?php echo htmlspecialchars(trim($feature)); ?></p>
                            <?php
                                endif;
                            endforeach;
                            ?>
                            <a class="btn-slide mt-2" href="../book.php?package_id=<?php echo $package['package_id']; ?>">
                                <i class="fa fa-arrow-right"></i><span>Book Now</span>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php } catch (PDOException $e) {
                    echo '<div class="alert alert-danger">Error loading packages: ' . htmlspecialchars($e->getMessage()) . '</div>';
                } ?>
            </div>
        </div>
    </div>
    <!-- Private Party Gallery -->
    <div class="container-fluid gallery py-6">
        <div class="container">
            <div class="text-center wow bounceInUp" data-wow-delay="0.1s">
                <h1 class="display-5 mb-5">Private Party Gallery</h1>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6 wow bounceInUp" data-wow-delay="0.1s">
                    <div class="gallery-item">
                        <img class="img-fluid rounded w-100" src="../img/event-6.jpg" alt="">
                        <div class="gallery-content">
                            <div class="gallery-info">
                                <h5 class="text-white text-uppercase mb-2">Intimate Gathering</h5>
                                <a href="../img/event-6.jpg" data-lightbox="gallery" class="btn-hover text-white">View Image</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 wow bounceInUp" data-wow-delay="0.3s">
                    <div class="gallery-item">
                        <img class="img-fluid rounded w-100" src="../img/event-7.jpg" alt="">
                        <div class="gallery-content">
                            <div class="gallery-info">
                                <h5 class="text-white text-uppercase mb-2">Family Celebration</h5>
                                <a href="../img/event-7.jpg" data-lightbox="gallery" class="btn-hover text-white">View Image</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 wow bounceInUp" data-wow-delay="0.5s">
                    <div class="gallery-item">
                        <img class="img-fluid rounded w-100" src="../img/event-8.jpg" alt="">
                        <div class="gallery-content">
                            <div class="gallery-info">
                                <h5 class="text-white text-uppercase mb-2">Special Occasion</h5>
                                <a href="../img/event-8.jpg" data-lightbox="gallery" class="btn-hover text-white">View Image</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../layout/footer.php'; ?>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../lib/wow/wow.min.js"></script>
    <script src="../lib/easing/easing.min.js"></script>
    <script src="../lib/waypoints/waypoints.min.js"></script>
    <script src="../lib/counterup/counterup.min.js"></script>
    <script src="../lib/lightbox/js/lightbox.min.js"></script>
    <script src="../lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="../js/main.js"></script>
    <script src="../js/theme-switcher.js"></script>
    <script>
        new WOW().init();
    </script>
</body>
</html>
