<?php
session_start();
require '../db.php';

// Fetch occupied dates for calendar
try {
    $stmt = $db->prepare("SELECT event_date FROM event_bookings WHERE booking_status = 'Approved'");
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $occupiedDates = array_column($bookings, 'event_date');
} catch (PDOException $e) {
    $occupiedDates = [];
}

// Fetch wedding packages
try {
    $stmt = $db->prepare("SELECT * FROM catering_packages WHERE category = 'Wedding Catering'");
    $stmt->execute();
    $wedding_packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $packages_json = json_encode($wedding_packages);
    
    $itemsPerPage = 2;
    $totalPackages = count($wedding_packages);
    $totalPages = ceil($totalPackages / $itemsPerPage);
    
    $currentPage = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= $totalPages 
                   ? (int)$_GET['page'] 
                   : 1;
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error loading packages: ' . htmlspecialchars($e->getMessage()) . '</div>';
    $packages_json = json_encode([]);
    $totalPackages = 0;
    $totalPages = 0;
    $currentPage = 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Wedding Catering - Pochie Catering</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="">
    <meta name="description" content="">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Playball&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="../lib/lightbox/css/lightbox.min.css" rel="stylesheet">
    <link href="../lib/owlcarousel/owl.carousel.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/themes.css" rel="stylesheet">
    <link href="../css/packages.css" rel="stylesheet">
    <link href="../css/booking.css" rel="stylesheet">
    <link href="../css/calendar.css" rel="stylesheet">
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
            <a href="./wedding_menu.php" class="btn btn-primary border-0 rounded-pill py-2 px-3 px-md-3 animated bounceInLeft">View Menu</a>
        </div>
    </div>

    <!-- About Wedding Catering -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-5 mb-4">Wedding Catering Excellence</h1>
                    <p class="mb-4">
                        At Pochie Catering, we understand that your wedding day is one of the most important days of your life.
                        Our wedding catering service is designed to provide an unforgettable culinary experience for you and your guests.
                    </p>
                    <div class="row g-4">
                        <div class="col-md-6"><div class="d-flex align-items-center mb-3"><i class="fas fa-check text-primary me-3"></i><span>Customized Menu Planning</span></div></div>
                        <div class="col-md-6"><div class="d-flex align-items-center mb-3"><i class="fas fa-check text-primary me-3"></i><span>Professional Service Staff</span></div></div>
                        <div class="col-md-6"><div class="d-flex align-items-center mb-3"><i class="fas fa-check text-primary me-3"></i><span>Elegant Table Settings</span></div></div>
                        <div class="col-md-6"><div class="d-flex align-items-center mb-3"><i class="fas fa-check text-primary me-3"></i><span>Flexible Packages</span></div></div>
                    </div>
                </div>
                <div class="col-lg-6 wow zoomIn" data-wow-delay="0.5s">
                    <img src="../img/event-2.jpg" class="img-fluid rounded" alt="Wedding Catering">
                </div>
            </div>
        </div>
    </div>

    <!-- Wedding Packages Section -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="text-center mb-5 wow fadeInUp" data-wow-delay="0.3s">
                <h1 class="display-4 text-title fw-bold">Wedding Packages</h1>
                <p class="fs-5 text-subtitle">Choose the perfect package for your special day</p>
            </div>
            <div id="packagesContainer" class="row g-4 justify-content-center">
                <!-- Packages will be loaded here via JavaScript -->
            </div>
            <div id="loadingIndicator" class="text-center my-3" style="display: none;">
                <span>Loading...</span>
            </div>
            <div class="pagination-buttons text-center mt-5">
                <button id="backButton" <?php echo $currentPage == 1 ? 'disabled' : ''; ?>>Back</button>
                <span id="pageInfo">Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?></span>
                <button id="nextButton" <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>>Next</button>
            </div>
        </div>
    </div>

    <!-- Booking Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel">Book Your Wedding Catering</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="process_booking.php" method="POST">
                        <input type="hidden" name="package_id" id="modalPackageId">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Package</label>
                                <input type="text" class="form-control" name="event_type" id="modalPackageName" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Total Amount (₱)</label>
                                <input type="text" class="form-control" id="modalTotalAmountDisplay" readonly>
                                <input type="hidden" name="total_amount" id="modalTotalAmount">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Number of Guests</label>
                                <input type="text" class="form-control" id="modalNumberOfGuests" name="number_of_guests" readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Event Date</label>
                                <div id="calendar"></div>
                                <input type="hidden" name="event_date" id="eventDate" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Event Time</label>
                                <input type="time" class="form-control" name="event_time" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Setup Time</label>
                                <input type="time" class="form-control" name="setup_time" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control" name="location" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Additional Requests (Optional)</label>
                                <textarea class="form-control" name="additional_requests" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Special Requirements (Optional)</label>
                                <textarea class="form-control" name="special_requirements" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Confirm Booking</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../layout/footer.php'; ?>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../lib/wow/wow.min.js"></script>
    <script src="../lib/easing/easing.min.js"></script>
    <script src="../lib/waypoints/waypoints.min.js"></script>
    <script src="../lib/counterup/counterup.min.js"></script>
    <script src="../lib/lightbox/js/lightbox.min.js"></script>
    <script src="../lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/theme-switcher.js"></script>
    <script>
        new WOW().init();

        // Booking Modal & Calendar Setup
        document.addEventListener("DOMContentLoaded", function () {
            let bookingModal = document.getElementById("bookingModal");
            let calendarInitialized = false;
            const occupiedDates = <?php echo json_encode($occupiedDates); ?>;

            bookingModal.addEventListener("shown.bs.modal", function (event) {
                let button = event.relatedTarget;
                let packageId = button.getAttribute("data-package-id");
                let packageName = button.getAttribute("data-package-name");
                let price = button.getAttribute("data-price");
                let numberOfGuests = button.getAttribute("data-number-of-guests");

                document.getElementById("modalPackageId").value = packageId;
                document.getElementById("modalPackageName").value = packageName;
                document.getElementById("modalTotalAmountDisplay").value = "₱" + parseFloat(price).toLocaleString('en-US');
                document.getElementById("modalTotalAmount").value = price;
                document.getElementById("modalNumberOfGuests").value = numberOfGuests;

                if (!calendarInitialized) {
                    var calendarEl = document.getElementById('calendar');
                    const now = new Date();
                    const philippineTimeOffset = 8 * 60;
                    const localOffset = now.getTimezoneOffset();
                    const philippineTime = new Date(now.getTime() + (philippineTimeOffset + localOffset) * 60 * 1000);
                    const today = new Date(philippineTime.toISOString().split('T')[0]);

                    var calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        initialDate: today,
                        headerToolbar: { left: 'prev,next', center: 'title', right: '' },
                        height: 'auto',
                        selectable: true,
                        validRange: { start: today },
                        events: occupiedDates.map(date => ({
                            title: 'Occupied',
                            start: date,
                            allDay: true,
                            backgroundColor: '#dc3545',
                            borderColor: '#dc3545',
                            editable: false,
                            selectable: false
                        })),
                        dateClick: function(info) {
                            if (new Date(info.dateStr) < today) {
                                alert('You cannot book a past date. Please select a future date.');
                                return;
                            }
                            if (occupiedDates.includes(info.dateStr)) {
                                alert('This date is already occupied. Please choose another date.');
                                return;
                            }
                            document.getElementById('eventDate').value = info.dateStr;
                            calendar.getEvents().forEach(event => {
                                if (event.title === 'Selected') event.remove();
                            });
                            calendar.addEvent({
                                title: 'Selected',
                                start: info.dateStr,
                                allDay: true,
                                color: '#28a745'
                            });
                        }
                    });
                    calendar.render();
                    calendarInitialized = true;
                }
            });
        });

        // Client-side Pagination for Wedding Packages
        let currentPage = <?php echo $currentPage; ?>;
        const itemsPerPage = <?php echo $itemsPerPage; ?>;
        const totalPackages = <?php echo $totalPackages; ?>;
        const totalPages = <?php echo $totalPages; ?>;
        const packages = <?php echo $packages_json; ?>;

        function loadPackages(page) {
            if (page < 1) page = 1;
            if (page > totalPages) page = totalPages;
            currentPage = page;
            $('#backButton').prop('disabled', currentPage === 1);
            $('#nextButton').prop('disabled', currentPage === totalPages);
            $('#loadingIndicator').show();
            $('#packagesContainer').empty();

            const start = (page - 1) * itemsPerPage;
            const end = Math.min(start + itemsPerPage, totalPackages);
            const pagePackages = packages.slice(start, end);

            pagePackages.forEach(pkg => {
                let stars = Math.min(5, Math.ceil(pkg.price / 20000));
                let starsHTML = Array(stars + 1).join('<small class="fa fa-star text-warning me-1"></small>');
                
                let featuresArr = pkg.description.split('\n').filter(f => f.trim() !== '');
                let featuresHTML = featuresArr.map(feature => `
                    <li class="mb-2 d-flex align-items-center">
                        <i class="fa fa-check-circle text-success me-2"></i>
                        ${feature.trim()}
                    </li>
                `).join('');

                const cardHTML = `
                    <div class="col-12 col-md-6 col-lg-6 mb-4 wow bounceInUp" data-wow-delay="0.1s">
                        <div class="package-card h-100">
                            <div class="package-header text-white text-center py-4">
                                <h3 class="mb-0 font-wedding">${pkg.name}</h3>
                                <div class="mt-2">${starsHTML}</div>
                            </div>
                            <div class="package-body p-4 text-content">
                                <div class="package-details">
                                    <h1 class="mb-3 text-center pricing-card-title">
                                        <small class="align-top text-muted" style="font-size: 1.5rem;">₱</small>
                                        ${parseFloat(pkg.price).toLocaleString('en-US')}
                                        <small class="align-bottom text-muted" style="font-size: 1rem;">/package</small>
                                    </h1>
                                    <p class="text-center mb-3 guests-count">
                                        <i class="fa fa-users text-success me-1"></i> 
                                        Up to ${pkg.number_of_guests} Guests
                                    </p>
                                </div>
                                <ul class="list-unstyled mb-0 text-left">
                                    ${featuresHTML}
                                </ul>
                            </div>
                            <div class="package-footer text-center py-3">
                                <button class="btn btn-primary rounded-pill py-2 px-4"
                                    data-bs-toggle="modal"
                                    data-bs-target="#bookingModal"
                                    data-package-id="${pkg.package_id}"
                                    data-package-name="${pkg.name}"
                                    data-price="${pkg.price}"
                                    data-number-of-guests="${pkg.number_of_guests}">
                                    <i class="fa fa-arrow-right me-2"></i>Book Now
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                $('#packagesContainer').append(cardHTML);
            });

            $('#pageInfo').text(`Page ${currentPage} of ${totalPages}`);
            $('#loadingIndicator').hide();
        }

        $('#backButton').on('click', function() {
            if (!$(this).prop('disabled') && currentPage > 1) {
                loadPackages(currentPage - 1);
            }
        });

        $('#nextButton').on('click', function() {
            if (!$(this).prop('disabled') && currentPage < totalPages) {
                loadPackages(currentPage + 1);
            }
        });

        loadPackages(currentPage);
    </script>
</body>
</html>