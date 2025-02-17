<?php
  session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Premium Package - Pochie Catering</title>
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
    <link href="../lib/animate/animate.min.css" rel="stylesheet">
    <link href="../lib/lightbox/css/lightbox.min.css" rel="stylesheet">
    <link href="../lib/owlcarousel/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>

<!-- Loading Screen -->
<div id="loading-screen">
    <div class="loader"></div>
</div>

<?php include '../layout/navbar.php'; ?>

    <!-- Hero Section -->
    <div class="container-fluid hero-section py-6 my-6 text-center wow fadeInUp" data-wow-delay="0.3s">
        <div class="hero-overlay"></div>
        <div class="container position-relative text-white">
            <h1 class="display-1 mb-4">Premium <span class="text-primary">Package</span></h1>
            <p class="lead">Indulge in our premium catering package for an unforgettable culinary experience.</p>
            <a href="../book.php" class="btn btn-primary py-3 px-5 rounded-pill wow bounceInLeft" data-wow-delay="0.5s">Book Now</a>
        </div>
    </div>
    <!-- Hero End -->

    <!-- About Premium Package -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-5 mb-4">Our Premium Package Features</h1>
                    <p class="mb-4">
                        Our Premium Package is designed for those who seek the finest catering experience. 
                        With an extensive menu and exceptional service, this package is perfect for weddings, 
                        corporate events, and special celebrations where quality is paramount.
                    </p>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check text-primary me-3"></i>
                                <span>Complete Table Setup</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check text-primary me-3"></i>
                                <span>Professional Staff</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check text-primary me-3"></i>
                                <span>Extensive Menu Selection</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check text-primary me-3"></i>
                                <span>Elegant Decorations</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 wow zoomIn" data-wow-delay="0.5s">
                    <img src="../img/event-5.jpg" class="img-fluid rounded" alt="Premium Package">
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Selection -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="text-center mb-5">
                <h1 class="display-5">Premium Package Menu</h1>
                <p class="fs-5">Choose from our exquisite menu options</p>
            </div>
            <div class="row g-4">
                <!-- Main Courses -->
                <div class="col-lg-6 wow bounceInUp" data-wow-delay="0.1s">
                    <div class="menu-category rounded p-4">
                        <h3 class="mb-4">Main Courses</h3>
                        <div class="menu-item d-flex align-items-center mb-4">
                            <img class="flex-shrink-0 img-fluid rounded-circle" src="../img/menu-07.jpg" alt="" style="width: 80px;">
                            <div class="w-100 d-flex flex-column ps-4">
                                <h5 class="mb-2">Grilled Salmon</h5>
                                <p class="mb-0">Served with lemon butter sauce</p>
                            </div>
                        </div>
                        <div class="menu-item d-flex align-items-center mb-4">
                            <img class="flex-shrink-0 img-fluid rounded-circle" src="../img/menu-08.jpg" alt="" style="width: 80px;">
                            <div class="w-100 d-flex flex-column ps-4">
                                <h5 class="mb-2">Beef Wellington</h5>
                                <p class="mb-0">Tender beef wrapped in pastry</p>
                            </div>
                        </div>
                        <div class="menu-item d-flex align-items-center mb-4">
                            <img class="flex-shrink-0 img-fluid rounded-circle" src="../img/menu-09.jpg" alt="" style="width: 80px;">
                            <div class="w-100 d-flex flex-column ps-4">
                                <h5 class="mb-2">Vegetable Lasagna</h5>
                                <p class="mb-0">Layers of pasta with fresh vegetables</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Side Dishes & Desserts -->
                <div class="col-lg-6 wow bounceInUp" data-wow-delay="0.3s">
                    <div class="menu-category rounded p-4">
                        <h3 class="mb-4">Sides & Desserts</h3>
                        <div class="menu-item d-flex align-items-center mb-4">
                            <img class="flex-shrink-0 img-fluid rounded-circle" src="../img/menu-10.jpg" alt="" style="width: 80px;">
                            <div class="w-100 d-flex flex-column ps-4">
                                <h5 class="mb-2">Garlic Mashed Potatoes</h5>
                                <p class="mb-0">Creamy mashed potatoes with garlic</p>
                            </div>
                        </div>
                        <div class="menu-item d-flex align-items-center mb-4">
                            <img class="flex-shrink-0 img-fluid rounded-circle" src="../img/menu-11.jpg" alt="" style="width: 80px;">
                            <div class="w-100 d-flex flex-column ps-4">
                                <h5 class="mb-2">Caesar Salad</h5>
                                <p class="mb-0">Crisp romaine with Caesar dressing</p>
                            </div>
                        </div>
                        <div class="menu-item d-flex align-items-center mb-4">
                            <img class="flex-shrink-0 img-fluid rounded-circle" src="../img/menu-12.jpg" alt="" style="width: 80px;">
                            <div class="w-100 d-flex flex-column ps-4">
                                <h5 class="mb-2">Chocolate Mousse</h5>
                                <p class="mb-0">Rich and creamy chocolate dessert</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Package Details -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="text-center mb-5">
                <h1 class="display-5">Package Inclusions</h1>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6 wow bounceInUp" data-wow-delay="0.1s">
                    <div class="service-item rounded p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="service-icon flex-shrink-0">
                                <i class="fas fa-utensils fa-2x text-primary"></i>
                            </div>
                            <h4 class="mb-0 ms-4">Food Service</h4>
                        </div>
                        <ul class="mb-0">
                            <li>5 Main Courses</li>
                            <li>3 Side Dishes</li>
                            <li>2 Desserts</li>
                            <li>Unlimited Rice</li>
                            <li>Drinking Water</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 wow bounceInUp" data-wow-delay="0.3s">
                    <div class="service-item rounded p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="service-icon flex-shrink-0">
                                <i class="fas fa-chair fa-2x text-primary"></i>
                            </div>
                            <h4 class="mb-0 ms-4">Setup & Equipment</h4>
                        </div>
                        <ul class="mb-0">
                            <li>Complete Table Setup</li>
                            <li>Elegant Decorations</li>
                            <li>Tables and Chairs</li>
                            <li>Complete Dinnerware</li>
                            <li>Service Equipment</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 wow bounceInUp" data-wow-delay="0.5s">
                    <div class="service-item rounded p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="service-icon flex-shrink-0">
                                <i class="fas fa-user-tie fa-2x text-primary"></i>
                            </div>
                            <h4 class="mb-0 ms-4">Service Staff</h4>
                        </div>
                        <ul class="mb-0">
                            <li>Professional Servers</li>
                            <li>Kitchen Staff</li>
                            <li>Service Supervisor</li>
                            <li>Uniformed Personnel</li>
                            <li>On-site Support</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing Section -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="text-center mb-5">
                <h1 class="display-5">Premium Package Pricing</h1>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-6 wow bounceInUp" data-wow-delay="0.3s">
                    <div class="price-item rounded text-center">
                        <div class="border-bottom p-4">
                            <h4 class="text-primary mb-0">Premium Package Rate</h4>
                            <div class="text-center mb-3">
                                <span class="fs-5">Starting at</span>
                                <h1 class="display-4 mb-0">
                                    <small class="align-top" style="font-size: 22px; line-height: 45px;">₱</small>750<small class="align-bottom" style="font-size: 16px; line-height: 40px;">/ person</small>
                                </h1>
                            </div>
                            <p class="mb-0">Minimum of 50 persons</p>
                        </div>
                        <div class="p-4">
                            <p><i class="fa fa-check text-primary me-2"></i>Complete Food Service</p>
                            <p><i class="fa fa-check text-primary me-2"></i>Full Setup & Equipment</p>
                            <p><i class="fa fa-check text-primary me-2"></i>Professional Staff</p>
                            <p><i class="fa fa-check text-primary me-2"></i>Elegant Decorations</p>
                            <p><i class="fa fa-check text-primary me-2"></i>4 Hours Service</p>
                            <a class="btn btn-primary rounded-pill py-3 px-5 mt-3" href="../book.php">Book Now</a>
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
    <script>
        new WOW().init();
    </script>
</body>
</html>
