<?php
require 'db.php'; // Include database connection
session_start();
// phpinfo();
// Fetch all live announcements for AJAX (store in JSON for client-side use)
try {
    $stmt = $db->prepare("SELECT * FROM announcements WHERE status = 'live' ORDER BY created_at DESC");
    $stmt->execute();
    $live_announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $announcements_json = json_encode($live_announcements);
    
    // Calculate pagination variables in PHP for JavaScript
    $itemsPerPage = 4;
    $totalAnnouncements = count($live_announcements);
    $totalPages = ceil($totalAnnouncements / $itemsPerPage);
    // Get current page from URL, default to 1
    $currentPage = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= $totalPages ? (int)$_GET['page'] : 1;
} catch (PDOException $e) {
    echo '<div class="alert alert-warning">Unable to load announcements</div>';
    $announcements_json = json_encode([]);
    $totalAnnouncements = 0;
    $totalPages = 0;
    $currentPage = 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pochie Catering</title>
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
    <!-- <link href="lib/animate/animate.min.css" rel="stylesheet">   REMOVED CAUSE WE HAD TO!  JUST REMOVE COMMENT IF YOU WANT TO HAVE ANIMATIONS--> 
    <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/themes.css" rel="stylesheet">
    <link href="css/ann_button.css" rel="stylesheet">
    
    
</head>
<body class="light-theme">

<!-- Loading Screen -->
<div id="loading-screen">
    <div class="loader"></div>
</div>

<?php include 'layout/navbar.php'; ?>

<!-- Hero Section -->
<div class="container-fluid hero-section py-6 my-6 text-center wow fadeInUp" data-wow-delay="0.3s">
    <div class="hero-overlay"></div>
    <div class="container position-relative text-white">
        <small class="d-inline-block fw-bold text-uppercase bg-light border border-primary rounded-pill px-4 py-1 mb-4 animated bounceInDown text-dark">Welcome to Pochie Catering Services</small>
        <h1 class="display-1 mb-4">Welcome to <span class="text-primary">Pochie Catering</span></h1>
        <p class="lead">Providing the best catering services for every occasion.</p>
        <a href="./about.php" class="btn btn-primary border-0 rounded-pill py-2 px-3 px-md-3 animated bounceInLeft">Know More</a>
    </div>
</div>
<!-- End Hero Section -->

<!-- Our Services Section -->
<div class="container services-section wow fadeInUp" data-wow-delay="0.3s">
    <h2 class="display-4 mb-5 text-center">Our Catering Services</h2>
    <div class="row justify-content-center g-4">
        <!-- Top Row (3 Services) -->
        <div class="col-lg-4 col-md-6">
            <div class="service-box p-4 wow bounceInUp" data-wow-delay="0.2s">
                <i class="fas fa-utensils"></i>
                <h4 class="mt-3">Wedding Catering</h4>
                <p>Exquisite dining experience tailored for your special day.</p>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="service-box p-4 wow bounceInUp" data-wow-delay="0.4s">
                <i class="fas fa-birthday-cake"></i>
                <h4 class="mt-3">Party Catering</h4>
                <p>Delicious meals and beverages to make your party unforgettable.</p>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="service-box p-4 wow bounceInUp" data-wow-delay="0.6s">
                <i class="fas fa-briefcase"></i>
                <h4 class="mt-3">Corporate Catering</h4>
                <p>Perfectly curated meals for corporate events and meetings.</p>
            </div>
        </div>
    </div>
    <!-- Centered Bottom Row (2 Services) -->
    <div class="row justify-content-center g-4 mt-3">
        <div class="col-lg-4 col-md-6">
            <div class="service-box p-4 wow bounceInUp" data-wow-delay="0.8s">
                <i class="fas fa-star"></i>
                <h4 class="mt-3">Debut Catering</h4>
                <p>Elegant catering services for your special debut celebration.</p>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="service-box p-4 wow bounceInUp" data-wow-delay="1.0s">
                <i class="fas fa-child"></i>
                <h4 class="mt-3">Children's Party Catering</h4>
                <p>Fun and delicious catering for kids' parties and celebrations.</p>
            </div>
        </div>
    </div>
</div>
<!-- End Our Services Section -->

<!-- Announcement Section Start -->
<div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
    <div class="container">
        <h2 class="display-4 mb-5 text-center">Latest Announcements</h2>
        <!-- Announcement Cards Container -->
        <div class="announcement-container" id="announcementContainer">
            <div class="row">
                <!-- Announcements will be loaded here via AJAX -->
            </div>
        </div>
        <!-- Loading Indicator -->
        <div class="loading" id="loadingIndicator">
            <span>Loading...</span>
        </div>
        <!-- Pagination Buttons -->
        <div class="pagination-buttons">
            <button id="backButton" <?php echo $currentPage == 1 ? 'disabled' : ''; ?>>Back</button>
            <span id="pageInfo">Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?></span>
            <button id="nextButton" <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>>Next</button>
        </div>
    </div>
</div>
<!-- Announcement Section End -->

 <!-- Testimonial Start -->
 <!-- <div class="container-fluid py-6">
            <div class="container">
                <div class="text-center wow bounceInUp" data-wow-delay="0.1s">
                    <small class="d-inline-block fw-bold text-dark text-uppercase bg-light border border-primary rounded-pill px-4 py-1 mb-3">Testimonial</small>
                    <h1 class="display-5 mb-5">What Our Customers says!</h1>
                </div>
                <div class="owl-carousel owl-theme testimonial-carousel testimonial-carousel-1 mb-4 wow bounceInUp" data-wow-delay="0.1s">
                    <div class="testimonial-item rounded bg-light">
                        <div class="d-flex mb-3">
                            <img src="img/testimonial-1.jpg" class="img-fluid rounded-circle flex-shrink-0" alt="">
                            <div class="position-absolute" style="top: 15px; right: 20px;">
                                <i class="fa fa-quote-right fa-2x"></i>
                            </div>
                            <div class="ps-3 my-auto">
                                <h4 class="mb-0">Person Name</h4>
                                <p class="m-0">Profession</p>
                            </div>
                        </div>
                        <div class="testimonial-content">
                            <div class="d-flex">
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                            </div>
                            <p class="fs-5 m-0 pt-3">Lorem ipsum dolor sit amet elit, sed do eiusmod tempor ut labore et dolore magna aliqua.</p>
                        </div>
                    </div>
                    <div class="testimonial-item rounded bg-light">
                        <div class="d-flex mb-3">
                            <img src="img/testimonial-2.jpg" class="img-fluid rounded-circle flex-shrink-0" alt="">
                            <div class="position-absolute" style="top: 15px; right: 20px;">
                                <i class="fa fa-quote-right fa-2x"></i>
                            </div>
                            <div class="ps-3 my-auto">
                                <h4 class="mb-0">Person Name</h4>
                                <p class="m-0">Profession</p>
                            </div>
                        </div>
                        <div class="testimonial-content">
                            <div class="d-flex">
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                            </div>
                            <p class="fs-5 m-0 pt-3">Lorem ipsum dolor sit amet elit, sed do eiusmod tempor ut labore et dolore magna aliqua.</p>
                        </div>
                    </div>
                    <div class="testimonial-item rounded bg-light">
                        <div class="d-flex mb-3">
                            <img src="img/testimonial-3.jpg" class="img-fluid rounded-circle flex-shrink-0" alt="">
                            <div class="position-absolute" style="top: 15px; right: 20px;">
                                <i class="fa fa-quote-right fa-2x"></i>
                            </div>
                            <div class="ps-3 my-auto">
                                <h4 class="mb-0">Person Name</h4>
                                <p class="m-0">Profession</p>
                            </div>
                        </div>
                        <div class="testimonial-content">
                            <div class="d-flex">
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                            </div>
                            <p class="fs-5 m-0 pt-3">Lorem ipsum dolor sit amet elit, sed do eiusmod tempor ut labore et dolore magna aliqua.</p>
                        </div>
                    </div>
                    <div class="testimonial-item rounded bg-light">
                        <div class="d-flex mb-3">
                            <img src="img/testimonial-4.jpg" class="img-fluid rounded-circle flex-shrink-0" alt="">
                            <div class="position-absolute" style="top: 15px; right: 20px;">
                                <i class="fa fa-quote-right fa-2x"></i>
                            </div>
                            <div class="ps-3 my-auto">
                                <h4 class="mb-0">Person Name</h4>
                                <p class="m-0">Profession</p>
                            </div>
                        </div>
                        <div class="testimonial-content">
                            <div class="d-flex">
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                            </div>
                            <p class="fs-5 m-0 pt-3">Lorem ipsum dolor sit amet elit, sed do eiusmod tempor ut labore et dolore magna aliqua.</p>
                        </div>
                    </div>
                </div>
                <div class="owl-carousel testimonial-carousel testimonial-carousel-2 wow bounceInUp" data-wow-delay="0.3s">
                    <div class="testimonial-item rounded bg-light">
                        <div class="d-flex mb-3">
                            <img src="img/testimonial-1.jpg" class="img-fluid rounded-circle flex-shrink-0" alt="">
                            <div class="position-absolute" style="top: 15px; right: 20px;">
                                <i class="fa fa-quote-right fa-2x"></i>
                            </div>
                            <div class="ps-3 my-auto">
                                <h4 class="mb-0">Person Name</h4>
                                <p class="m-0">Profession</p>
                            </div>
                        </div>
                        <div class="testimonial-content">
                            <div class="d-flex">
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                            </div>
                            <p class="fs-5 m-0 pt-3">Lorem ipsum dolor sit amet elit, sed do eiusmod tempor ut labore et dolore magna aliqua.</p>
                        </div>
                    </div>
                    <div class="testimonial-item rounded bg-light">
                        <div class="d-flex mb-3">
                            <img src="img/testimonial-2.jpg" class="img-fluid rounded-circle flex-shrink-0" alt="">
                            <div class="position-absolute" style="top: 15px; right: 20px;">
                                <i class="fa fa-quote-right fa-2x"></i>
                            </div>
                            <div class="ps-3 my-auto">
                                <h4 class="mb-0">Person Name</h4>
                                <p class="m-0">Profession</p>
                            </div>
                        </div>
                        <div class="testimonial-content">
                            <div class="d-flex">
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                            </div>
                            <p class="fs-5 m-0 pt-3">Lorem ipsum dolor sit amet elit, sed do eiusmod tempor ut labore et dolore magna aliqua.</p>
                        </div>
                    </div>
                    <div class="testimonial-item rounded bg-light">
                        <div class="d-flex mb-3">
                            <img src="img/testimonial-3.jpg" class="img-fluid rounded-circle flex-shrink-0" alt="">
                            <div class="position-absolute" style="top: 15px; right: 20px;">
                                <i class="fa fa-quote-right fa-2x"></i>
                            </div>
                            <div class="ps-3 my-auto">
                                <h4 class="mb-0">Person Name</h4>
                                <p class="m-0">Profession</p>
                            </div>
                        </div>
                        <div class="testimonial-content">
                            <div class="d-flex">
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                            </div>
                            <p class="fs-5 m-0 pt-3">Lorem ipsum dolor sit amet elit, sed do eiusmod tempor ut labore et dolore magna aliqua.</p>
                        </div>
                    </div>
                    <div class="testimonial-item rounded bg-light">
                        <div class="d-flex mb-3">
                            <img src="img/testimonial-4.jpg" class="img-fluid rounded-circle flex-shrink-0" alt="">
                            <div class="position-absolute" style="top: 15px; right: 20px;">
                                <i class="fa fa-quote-right fa-2x"></i>
                            </div>
                            <div class="ps-3 my-auto">
                                <h4 class="mb-0">Person Name</h4>
                                <p class="m-0">Profession</p>
                            </div>
                        </div>
                        <div class="testimonial-content">
                            <div class="d-flex">
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                                <i class="fas fa-star text-primary"></i>
                            </div>
                            <p class="fs-5 m-0 pt-3">Lorem ipsum dolor sit amet elit, sed do eiusmod tempor ut labore et dolore magna aliqua.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
        <!-- Testimonial End -->

<?php include 'layout/footer.php'; ?>

<!-- JavaScript Libraries -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/wow/wow.min.js"></script>
<script src="lib/easing/easing.min.js"></script>
<script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/counterup/counterup.min.js"></script>
<script src="lib/lightbox/js/lightbox.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>
<script src="js/main.js"></script>
<script src="js/theme-switcher.js"></script>
<script>
    new WOW().init();

    // Load initial announcements (default to page 1)
    let currentPage = <?php echo $currentPage; ?>;
    const itemsPerPage = 4;
    const totalAnnouncements = <?php echo $totalAnnouncements; ?>;
    const totalPages = <?php echo $totalPages; ?>;
    const announcements = <?php echo $announcements_json; ?>;

    // Function to load announcements based on current page
    function loadAnnouncements(page) {
        if (page < 1) page = 1; // Prevent going below page 1
        if (page > totalPages) page = totalPages; // Prevent going beyond total pages

        currentPage = page;
        $('#backButton').prop('disabled', currentPage === 1);
        $('#nextButton').prop('disabled', currentPage === totalPages);
        $('#loadingIndicator').show();
        $('#announcementContainer .row').empty();

        const start = (page - 1) * itemsPerPage;
        const end = Math.min(start + itemsPerPage, totalAnnouncements);
        const pageAnnouncements = announcements.slice(start, end);

        pageAnnouncements.forEach(announcement => {
            // Inilalagay natin ang "glass-card" kasama ng "card" para masunod ang theme.css
            const card = `
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card glass-card">
                        <img src="admin/${announcement.media_path}" class="card-img-top" alt="${announcement.title}">
                        <div class="card-body">
                            <h5 class="card-title">${announcement.title}</h5>
                            <p class="card-text">${announcement.description}</p>
                            
                        </div>
                    </div>
                </div>`;
            $('#announcementContainer .row').append(card);
        });

        // Update page info and hide loading indicator
        $('#pageInfo').text(`Page ${currentPage} of ${totalPages}`);
        $('#loadingIndicator').hide();
    }

    // Event listeners for pagination buttons
    $('#backButton').on('click', function() {
        if (!$(this).prop('disabled') && currentPage > 1) {
            loadAnnouncements(currentPage - 1);
        }
    });

    $('#nextButton').on('click', function() {
        if (!$(this).prop('disabled') && currentPage < totalPages) {
            loadAnnouncements(currentPage + 1);
        }
    });

    // Initial load
    loadAnnouncements(currentPage);
</script>
</body>
</html>
