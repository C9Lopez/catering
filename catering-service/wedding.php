<?php
  session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Wedding Catering - Pochie Catering</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Playball&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
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
            <h1 class="display-1 mb-4">Wedding <span class="text-primary">Catering</span></h1>
            <p class="lead">Make your special day even more memorable with our exquisite wedding catering services.</p>
            
        </div>
    </div>
    <!-- Hero End -->

    <!-- About Wedding Catering -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-5 mb-4">Wedding Catering Excellence</h1>
                    <p class="mb-4">
                        At Pochie Catering, we understand that your wedding day is one of the most important days of
                        your life.
                        Our wedding catering service is designed to provide an unforgettable culinary experience for you
                        and your guests.
                    </p>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check text-primary me-3"></i>
                                <span>Customized Menu Planning</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check text-primary me-3"></i>
                                <span>Professional Service Staff</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check text-primary me-3"></i>
                                <span>Elegant Table Settings</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check text-primary me-3"></i>
                                <span>Flexible Packages</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 wow zoomIn" data-wow-delay="0.5s">
                    <img src="../img/event-2.jpg" class="img-fluid rounded" alt="Wedding Catering">
                </div>
            </div>
        </div>
    </div>

    <!-- Wedding Packages -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="text-center mb-5">
                <h1 class="display-5">Wedding Packages</h1>
                <p class="fs-5">Choose the perfect package for your special day</p>
            </div>
            <div class="row g-4">
                <?php
            require '../db.php';
            try {
                // Fetch wedding packages from database
                $stmt = $db->prepare("SELECT * FROM catering_packages WHERE category = 'Wedding Catering'");
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
                                <small class="align-bottom" style="font-size: 16px; line-height: 40px;">/
                                    package</small>
                            </h1>
                        </div>
                        <div class="p-4">
                            <?php
                        // Convert description to list items
                        $features = explode("\n", $package['description']);
                        foreach ($features as $feature):
                            if (!empty(trim($feature))):
                        ?>
                            <p><i
                                    class="fa fa-check text-primary me-2"></i><?php echo htmlspecialchars(trim($feature)); ?>
                            </p>
                            <?php
                            endif;
                        endforeach;
                        ?>
                            <button class="btn-slide mt-2" data-bs-toggle="modal" data-bs-target="#bookingModal"
                                data-package-id="<?php echo $package['package_id']; ?>"
                                data-package-name="<?php echo htmlspecialchars($package['name']); ?>"
                                data-price="<?php echo $package['price']; ?>">
                                <i class="fa fa-arrow-right"></i><span>Book Now</span>
                            </button>
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

    <!-- Bootstrap Modal for Booking -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel">Book Your Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="process_booking.php" method="POST">
                        <input type="text" name="package_id" id="modalPackageId" hidden>

                        <div class="mb-3">
                        <label class="form-label">Event Type</label>
                        <input type="text" class="form-control" name="event_type" readonly id="modalPackageName">
                    </div>

                        <div class="mb-3">
                            <label class="form-label">Event Date</label>
                            <input type="date" class="form-control" name="event_date" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Event Time</label>
                            <input type="time" class="form-control" name="event_time" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Setup Time</label>
                            <input type="time" class="form-control" name="setup_time" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Number of Guests</label>
                            <input type="number" class="form-control" name="number_of_guests" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Total Amount (Auto-filled)</label>
                            <input type="text" class="form-control" name="total_amount" id="modalTotalAmount"
                                 readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Additional Requests</label>
                            <textarea class="form-control" name="additional_requests"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Special Requirements</label>
                            <textarea class="form-control" name="special_requirements"></textarea>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Submit Booking</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Wedding Gallery -->
    <div class="container-fluid gallery py-6">
        <div class="container">
            <div class="text-center wow bounceInUp" data-wow-delay="0.1s">
                <h1 class="display-5 mb-5">Our Wedding Gallery</h1>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6 wow bounceInUp" data-wow-delay="0.1s">
                    <div class="gallery-item">
                        <img class="img-fluid rounded w-100" src="../img/event-1.jpg" alt="">
                        <div class="gallery-content">
                            <div class="gallery-info">
                                <h5 class="text-white text-uppercase mb-2">Wedding Reception</h5>
                                <a href="../img/event-1.jpg" data-lightbox="gallery" class="btn-hover text-white">View
                                    Image</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 wow bounceInUp" data-wow-delay="0.3s">
                    <div class="gallery-item">
                        <img class="img-fluid rounded w-100" src="../img/event-2.jpg" alt="">
                        <div class="gallery-content">
                            <div class="gallery-info">
                                <h5 class="text-white text-uppercase mb-2">Elegant Setup</h5>
                                <a href="../img/event-2.jpg" data-lightbox="gallery" class="btn-hover text-white">View
                                    Image</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 wow bounceInUp" data-wow-delay="0.5s">
                    <div class="gallery-item">
                        <img class="img-fluid rounded w-100" src="../img/event-3.jpg" alt="">
                        <div class="gallery-content">
                            <div class="gallery-info">
                                <h5 class="text-white text-uppercase mb-2">Wedding Feast</h5>
                                <a href="../img/event-3.jpg" data-lightbox="gallery" class="btn-hover text-white">View
                                    Image</a>
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




        document.addEventListener("DOMContentLoaded", function () {
            let bookingModal = document.getElementById("bookingModal");
            bookingModal.addEventListener("show.bs.modal", function (event) {
                let button = event.relatedTarget;
                let packageId = button.getAttribute("data-package-id");
                let packageName = button.getAttribute("data-package-name");
                let price = button.getAttribute("data-price");

                document.getElementById("modalPackageId").value = packageId;
                document.getElementById("modalPackageName").value = packageName;
                document.getElementById("modalTotalAmount").value = price;
            });
        });
    </script>
</body>

</html>

<!-- DOM MANIPULATION -->