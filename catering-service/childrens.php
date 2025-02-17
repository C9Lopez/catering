<?php
  session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Children's Party Catering - Pochie Catering</title>
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
            <h1 class="display-1 mb-4">Children's Party <span class="text-primary">Catering</span></h1>
            <p class="lead">Create magical moments with our fun and delicious children's party catering services.</p>
            <a href="../book.php" class="btn btn-primary py-3 px-5 rounded-pill wow bounceInLeft" data-wow-delay="0.5s">Book Now</a>
        </div>
    </div>
    <!-- Hero End -->

    <!-- About Children's Party Catering -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-5 mb-4">Fun & Delicious Party Catering</h1>
                    <p class="mb-4">
                        Make your child's special day extraordinary with our specialized children's party catering services. 
                        We combine fun, colorful presentations with kid-friendly menus that parents will appreciate too. 
                        Every detail is carefully planned to ensure a memorable celebration for children of all ages.
                    </p>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check text-primary me-3"></i>
                                <span>Kid-Friendly Menu Options</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check text-primary me-3"></i>
                                <span>Fun Food Presentations</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check text-primary me-3"></i>
                                <span>Themed Decorations</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check text-primary me-3"></i>
                                <span>Special Dietary Options</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 wow zoomIn" data-wow-delay="0.5s">
                    <img src="../img/event-7.jpg" class="img-fluid rounded" alt="Children's Party Catering">
                </div>
            </div>
        </div>
    </div>

    <!-- Children's Party Packages -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="text-center mb-5">
                <h1 class="display-5">Party Packages</h1>
                <p class="fs-5">Choose the perfect package for your child's special day</p>
            </div>
            <div class="row g-4">
                <!-- Fun Package -->
                <div class="col-lg-4 col-md-6 wow bounceInUp" data-wow-delay="0.1s">
                    <div class="package-item rounded overflow-hidden">
                        <div class="text-center p-4">
                            <h3 class="mb-0">Fun Package</h3>
                            <div class="mb-3">
                                <small class="fa fa-star text-primary"></small>
                                <small class="fa fa-star text-primary"></small>
                                <small class="fa fa-star text-primary"></small>
                            </div>
                            <h1 class="mb-3">
                                <small class="align-top" style="font-size: 22px; line-height: 45px;">₱</small>15,000<small class="align-bottom" style="font-size: 16px; line-height: 40px;">/ package</small>
                            </h1>
                        </div>
                        <div class="p-4">
                            <p><i class="fa fa-check text-primary me-2"></i>Basic Party Setup</p>
                            <p><i class="fa fa-check text-primary me-2"></i>30 Kids + 20 Adults</p>
                            <p><i class="fa fa-check text-primary me-2"></i>Kid's Menu Favorites</p>
                            <p><i class="fa fa-check text-primary me-2"></i>Basic Decorations</p>
                            <p><i class="fa fa-check text-primary me-2"></i>Party Snacks</p>
                            <a class="btn-slide mt-2" href="../book.php"><i class="fa fa-arrow-right"></i><span>Book Now</span></a>
                        </div>
                    </div>
                </div>
                <!-- Super Fun Package -->
                <div class="col-lg-4 col-md-6 wow bounceInUp" data-wow-delay="0.3s">
                    <div class="package-item rounded overflow-hidden">
                        <div class="text-center p-4">
                            <h3 class="mb-0">Super Fun Package</h3>
                            <div class="mb-3">
                                <small class="fa fa-star text-primary"></small>
                                <small class="fa fa-star text-primary"></small>
                                <small class="fa fa-star text-primary"></small>
                                <small class="fa fa-star text-primary"></small>
                            </div>
                            <h1 class="mb-3">
                                <small class="align-top" style="font-size: 22px; line-height: 45px;">₱</small>25,000<small class="align-bottom" style="font-size: 16px; line-height: 40px;">/ package</small>
                            </h1>
                        </div>
                        <div class="p-4">
                            <p><i class="fa fa-check text-primary me-2"></i>Enhanced Party Setup</p>
                            <p><i class="fa fa-check text-primary me-2"></i>50 Kids + 30 Adults</p>
                            <p><i class="fa fa-check text-primary me-2"></i>Extended Menu Selection</p>
                            <p><i class="fa fa-check text-primary me-2"></i>Themed Decorations</p>
                            <p><i class="fa fa-check text-primary me-2"></i>Dessert Station</p>
                            <a class="btn-slide mt-2" href="../book.php"><i class="fa fa-arrow-right"></i><span>Book Now</span></a>
                        </div>
                    </div>
                </div>
                <!-- Ultimate Fun Package -->
                <div class="col-lg-4 col-md-6 wow bounceInUp" data-wow-delay="0.5s">
                    <div class="package-item rounded overflow-hidden">
                        <div class="text-center p-4">
                            <h3 class="mb-0">Ultimate Fun Package</h3>
                            <div class="mb-3">
                                <small class="fa fa-star text-primary"></small>
                                <small class="fa fa-star text-primary"></small>
                                <small class="fa fa-star text-primary"></small>
                                <small class="fa fa-star text-primary"></small>
                                <small class="fa fa-star text-primary"></small>
                            </div>
                            <h1 class="mb-3">
                                <small class="align-top" style="font-size: 22px; line-height: 45px;">₱</small>35,000<small class="align-bottom" style="font-size: 16px; line-height: 40px;">/ package</small>
                            </h1>
                        </div>
                        <div class="p-4">
                            <p><i class="fa fa-check text-primary me-2"></i>Premium Party Setup</p>
                            <p><i class="fa fa-check text-primary me-2"></i>80 Kids + 40 Adults</p>
                            <p><i class="fa fa-check text-primary me-2"></i>Premium Menu Selection</p>
                            <p><i class="fa fa-check text-primary me-2"></i>Custom Theme Decorations</p>
                            <p><i class="fa fa-check text-primary me-2"></i>Candy & Dessert Buffet</p>
                            <a class="btn-slide mt-2" href="../book.php"><i class="fa fa-arrow-right"></i><span>Book Now</span></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Children's Party Gallery -->
    <div class="container-fluid gallery py-6">
        <div class="container">
            <div class="text-center wow bounceInUp" data-wow-delay="0.1s">
                <h1 class="display-5 mb-5">Our Party Gallery</h1>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6 wow bounceInUp" data-wow-delay="0.1s">
                    <div class="gallery-item">
                        <img class="img-fluid rounded w-100" src="../img/event-7.jpg" alt="">
                        <div class="gallery-content">
                            <div class="gallery-info">
                                <h5 class="text-white text-uppercase mb-2">Birthday Setup</h5>
                                <a href="../img/event-7.jpg" data-lightbox="gallery" class="btn-hover text-white">View Image</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 wow bounceInUp" data-wow-delay="0.3s">
                    <div class="gallery-item">
                        <img class="img-fluid rounded w-100" src="../img/event-8.jpg" alt="">
                        <div class="gallery-content">
                            <div class="gallery-info">
                                <h5 class="text-white text-uppercase mb-2">Party Food</h5>
                                <a href="../img/event-8.jpg" data-lightbox="gallery" class="btn-hover text-white">View Image</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 wow bounceInUp" data-wow-delay="0.5s">
                    <div class="gallery-item">
                        <img class="img-fluid rounded w-100" src="../img/event-1.jpg" alt="">
                        <div class="gallery-content">
                            <div class="gallery-info">
                                <h5 class="text-white text-uppercase mb-2">Dessert Station</h5>
                                <a href="../img/event-1.jpg" data-lightbox="gallery" class="btn-hover text-white">View Image</a>
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
