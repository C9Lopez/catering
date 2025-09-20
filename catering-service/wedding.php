<?php
session_start();
require '../db.php';

// Fetch occupied dates for calendar
try {
    $stmt = $db->prepare("SELECT event_date FROM event_bookings WHERE booking_status = 'approved'");
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $occupiedDates = array_column($bookings, 'event_date');
    $occupiedDates = array_map(function($date) {
        return date('Y-m-d', strtotime($date));
    }, $occupiedDates);
} catch (PDOException $e) {
    error_log("Error fetching occupied dates: " . $e->getMessage());
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

// Fetch package add-ons for customization
try {
    $addons_stmt = $db->prepare("SELECT * FROM package_addons WHERE category = 'Wedding' AND status = 'active'");
    $addons_stmt->execute();
    $package_addons = $addons_stmt->fetchAll(PDO::FETCH_ASSOC);
    $addons_json = json_encode($package_addons);
} catch (PDOException $e) {
    error_log("Error fetching package addons: " . $e->getMessage());
    $package_addons = [];
    $addons_json = json_encode([]);
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

    <!-- Toastify CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.min.css">
    
    <style>
        /* Customization Modal Styles */
        .customization-section {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f8f9fa;
        }
        
        .customization-header {
            font-weight: 600;
            margin-bottom: 10px;
            color: #495057;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .addon-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .addon-item:last-child {
            border-bottom: none;
        }
        
        .addon-info {
            flex-grow: 1;
        }
        
        .addon-price {
            font-weight: 600;
            color: #28a745;
            margin-right: 15px;
        }
        
        .addon-quantity {
            width: 70px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #dee2e6;
        }
        
        .summary-total {
            font-weight: 700;
            font-size: 1.2em;
            color: #dc3545;
            border-top: 2px solid #495057;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .package-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
            border-radius: 8px 8px 0 0;
        }
        
        .package-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .package-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        
        .package-header {
            background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%);
            padding: 20px;
        }
        
        .package-body {
            padding: 20px;
        }
        
        .package-footer {
            background-color: #f8f9fa;
            padding: 15px 20px;
        }
        
        .customize-btn {
            background-color: #6c757d;
            border-color: #6c757d;
            margin-right: 10px;
        }
        
        .customize-btn:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
        
        .view-details-btn {
            color: #dc3545;
            background: transparent;
            border: 1px solid #dc3545;
            margin-top: 10px;
        }
        
        .view-details-btn:hover {
            color: white;
            background-color: #dc3545;
        }
        
        .package-features {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease;
        }
        
        .package-features.show {
            max-height: 500px;
        }
    </style>
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
                        At CONVERT Catering, we understand that your wedding day is one of the most important days of your life.
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
                <p class="text-muted">Select a default package or customize it to fit your specific needs</p>
            </div>
            <div id="packagesContainer" class="row g-4 justify-content-center">
                <!-- Packages will be loaded here via JavaScript -->
            </div>
            <div id="loadingIndicator" class="text-center my-3" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading packages...</p>
            </div>
            <div class="pagination-buttons text-center mt-5">
                <button id="backButton" class="btn btn-outline-primary me-2" <?php echo $currentPage == 1 ? 'disabled' : ''; ?>>
                    <i class="fas fa-arrow-left me-1"></i> Previous
                </button>
                <span id="pageInfo" class="mx-3 align-middle">Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?></span>
                <button id="nextButton" class="btn btn-outline-primary ms-2" <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>>
                    Next <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Package Details Modal -->
    <div class="modal fade" id="packageDetailsModal" tabindex="-1" aria-labelledby="packageDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="packageDetailsModalLabel">Package Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="packageDetailsContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Customization Modal -->
    <div class="modal fade" id="customizationModal" tabindex="-1" aria-labelledby="customizationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customizationModalLabel">Customize Your Package</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 id="customPackageName" class="mb-3"></h4>
                            <div class="base-price summary-item">
                                <span>Base Package Price:</span>
                                <span id="basePriceDisplay" class="fw-bold"></span>
                                <input type="hidden" id="basePrice">
                            </div>
                            
                            <div class="customization-options">
                                <h5 class="mt-4 mb-3">Customization Options</h5>
                                
                                <div class="customization-section">
                                    <div class="customization-header">
                                        <span>Food & Menu Options</span>
                                    </div>
                                    <div id="foodAddons">
                                        <!-- Food addons will be populated by JavaScript -->
                                    </div>
                                </div>
                                
                                <div class="customization-section">
                                    <div class="customization-header">
                                        <span>Service Upgrades</span>
                                    </div>
                                    <div id="serviceAddons">
                                        <!-- Service addons will be populated by JavaScript -->
                                    </div>
                                </div>
                                
                                <div class="customization-section">
                                    <div class="customization-header">
                                        <span>Decoration & Setup</span>
                                    </div>
                                    <div id="decorationAddons">
                                        <!-- Decoration addons will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="sticky-top" style="top: 20px;">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">Order Summary</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="summaryItems">
                                            <!-- Summary items will be populated by JavaScript -->
                                        </div>
                                        <div class="summary-total">
                                            <span>Total:</span>
                                            <span id="totalPriceDisplay">₱0.00</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <button id="proceedToBookingBtn" class="btn btn-success w-100 mt-3">
                                    <i class="fas fa-calendar-check me-2"></i>Proceed to Booking
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
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
                    <form id="bookingForm" method="POST">
                        <input type="hidden" name="package_id" id="modalPackageId">
                        <input type="hidden" name="customizations" id="modalCustomizations">
                        <input type="hidden" name="customization_total" id="modalCustomizationTotal">
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
    <!-- Toastify JS -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.min.js"></script>

    <script>
        new WOW().init();
        
        // Global variables
        const packageAddons = <?php echo $addons_json; ?>;
        let currentCustomizations = {
            packageId: null,
            packageName: null,
            basePrice: 0,
            addons: {},
            total: 0
        };

        // Handle form submission via AJAX
        $('#bookingForm').on('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            // Serialize form data
            const formData = $(this).serialize();

            // Send AJAX request
            $.ajax({
                url: 'process_booking.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    // Show toast notification
                    Toastify({
                        text: response.message,
                        duration: 3000, // 3 seconds
                        close: true,
                        gravity: "top", // Display at the top
                        position: "right", // Display on the right
                        backgroundColor: response.success ? '#28a745' : '#dc3545', // Green for success, red for error
                        stopOnFocus: true,
                        style: {
                            borderRadius: "10px",
                            fontSize: "16px",
                            padding: "15px"
                        }
                    }).showToast();

                    // If successful, close the modal
                    if (response.success) {
                        $('#bookingModal').modal('hide');
                        // Optionally reset the form
                        $('#bookingForm')[0].reset();
                        $('#eventDate').val(''); // Clear the hidden event date field
                    }
                },
                error: function(xhr, status, error) {
                    // Show error toast if AJAX request fails
                    Toastify({
                        text: "An error occurred while processing your request.",
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: '#dc3545',
                        stopOnFocus: true,
                        style: {
                            borderRadius: "10px",
                            fontSize: "16px",
                            padding: "15px"
                        }
                    }).showToast();
                }
            });
        });

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
                let customizations = button.getAttribute("data-customizations") || "{}";
                let customizationTotal = button.getAttribute("data-customization-total") || "0";

                // Calculate total price
                let totalPrice = parseFloat(price) + parseFloat(customizationTotal);

                document.getElementById("modalPackageId").value = packageId;
                document.getElementById("modalPackageName").value = packageName;
                document.getElementById("modalTotalAmountDisplay").value = "₱" + totalPrice.toLocaleString('en-US', {minimumFractionDigits: 2});
                document.getElementById("modalTotalAmount").value = totalPrice;
                document.getElementById("modalNumberOfGuests").value = numberOfGuests;
                document.getElementById("modalCustomizations").value = customizations;
                document.getElementById("modalCustomizationTotal").value = customizationTotal;

                if (!calendarInitialized) {
                    var calendarEl = document.getElementById('calendar');
                    const now = new Date();
                    const philippineTimeOffset = 8 * 60; // UTC+8 for Philippines
                    const localOffset = now.getTimezoneOffset();
                    const philippineTime = new Date(now.getTime() + (philippineTimeOffset + localOffset) * 60 * 1000);
                    const today = philippineTime.toISOString().split('T')[0]; // YYYY-MM-DD format

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
                            const selectedDate = info.dateStr;
                            const todayDate = new Date(today);
                            const clickedDate = new Date(selectedDate);

                            if (clickedDate < todayDate.setHours(0, 0, 0, 0)) {
                                alert('You cannot book a past date. Please select a future date.');
                                return;
                            }
                            if (occupiedDates.includes(selectedDate)) {
                                alert('This date is already occupied. Please choose another date.');
                                return;
                            }
                            document.getElementById('eventDate').value = selectedDate;
                            calendar.getEvents().forEach(event => {
                                if (event.title === 'Selected') event.remove();
                            });
                            calendar.addEvent({
                                title: 'Selected',
                                start: selectedDate,
                                allDay: true,
                                backgroundColor: '#28a745',
                                borderColor: '#28a745'
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
                
                // Get first few features for preview
                let previewFeatures = featuresArr.slice(0, 3).map(feature => `
                    <li class="mb-1 d-flex align-items-center">
                        <i class="fa fa-check-circle text-success me-2 small"></i>
                        <span class="small">${feature.trim()}</span>
                    </li>
                `).join('');

                const cardHTML = `
                    <div class="col-12 col-md-6 col-lg-6 mb-4 wow bounceInUp" data-wow-delay="0.1s">
                        <div class="package-card h-100">
                            ${pkg.image_url ? `<img src="${pkg.image_url}" class="package-image" alt="${pkg.name}">` : ''}
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
                                    ${previewFeatures}
                                </ul>
                                <div class="text-center mt-3">
                                    <button class="btn view-details-btn rounded-pill py-1 px-3" 
                                        onclick="showPackageDetails(${pkg.package_id})">
                                        View All Details
                                    </button>
                                </div>
                                <div id="features-${pkg.package_id}" class="package-features">
                                    <ul class="list-unstyled mt-3 text-left">
                                        ${featuresHTML}
                                    </ul>
                                </div>
                            </div>
                            <div class="package-footer text-center py-3">
                                <button class="btn customize-btn rounded-pill py-2 px-4"
                                    onclick="openCustomizationModal(${pkg.package_id}, '${pkg.name}', ${pkg.price}, ${pkg.number_of_guests})">
                                    <i class="fa fa-cog me-2"></i>Customize
                                </button>
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

        // Show package details
        function showPackageDetails(packageId) {
            const package = packages.find(p => p.package_id == packageId);
            if (!package) return;
            
            let featuresArr = package.description.split('\n').filter(f => f.trim() !== '');
            let featuresHTML = featuresArr.map(feature => `
                <li class="mb-2 d-flex align-items-center">
                    <i class="fa fa-check-circle text-success me-2"></i>
                    ${feature.trim()}
                </li>
            `).join('');
            
            const detailsHTML = `
                <h3>${package.name}</h3>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5>Package Details</h5>
                        <p><strong>Price:</strong> ₱${parseFloat(package.price).toLocaleString('en-US')}</p>
                        <p><strong>Maximum Guests:</strong> ${package.number_of_guests}</p>
                    </div>
                    <div class="col-md-6">
                        <h5>What's Included</h5>
                        <ul class="list-unstyled">
                            ${featuresHTML}
                        </ul>
                    </div>
                </div>
            `;
            
            $('#packageDetailsContent').html(detailsHTML);
            $('#packageDetailsModal').modal('show');
        }

        // Toggle package features visibility
        function toggleFeatures(packageId) {
            $(`#features-${packageId}`).toggleClass('show');
        }

        // Open customization modal
        function openCustomizationModal(packageId, packageName, basePrice, numberOfGuests) {
            // Reset current customizations
            currentCustomizations = {
                packageId: packageId,
                packageName: packageName,
                basePrice: basePrice,
                addons: {},
                total: 0
            };
            
            // Update modal title and base price
            $('#customPackageName').text(packageName);
            $('#basePriceDisplay').text('₱' + parseFloat(basePrice).toLocaleString('en-US'));
            $('#basePrice').val(basePrice);
            
            // Clear previous addons
            $('#foodAddons').empty();
            $('#serviceAddons').empty();
            $('#decorationAddons').empty();
            
            // Filter and display addons by category
            const foodAddons = packageAddons.filter(addon => addon.category === 'Food');
            const serviceAddons = packageAddons.filter(addon => addon.category === 'Service');
            const decorationAddons = packageAddons.filter(addon => addon.category === 'Decoration');
            
            // Populate food addons
            foodAddons.forEach(addon => {
                $('#foodAddons').append(createAddonItem(addon));
            });
            
            // Populate service addons
            serviceAddons.forEach(addon => {
                $('#serviceAddons').append(createAddonItem(addon));
            });
            
            // Populate decoration addons
            decorationAddons.forEach(addon => {
                $('#decorationAddons').append(createAddonItem(addon));
            });
            
            // Update summary
            updateSummary();
            
            // Show modal
            $('#customizationModal').modal('show');
        }
        
        // Create addon item HTML
        function createAddonItem(addon) {
            return `
                <div class="addon-item">
                    <div class="addon-info">
                        <h6 class="mb-1">${addon.name}</h6>
                        <p class="mb-0 small text-muted">${addon.description}</p>
                    </div>
                    <div class="addon-controls d-flex align-items-center">
                        <span class="addon-price">₱${parseFloat(addon.price).toLocaleString('en-US')}</span>
                        <input type="number" class="form-control form-control-sm addon-quantity" 
                            id="addon-${addon.addon_id}" 
                            min="0" 
                            max="${addon.max_quantity || 10}" 
                            value="0"
                            onchange="updateAddonQuantity(${addon.addon_id}, ${addon.price})">
                    </div>
                </div>
            `;
        }
        
        // Update addon quantity
        function updateAddonQuantity(addonId, price) {
            const quantity = parseInt($(`#addon-${addonId}`).val()) || 0;
            
            if (quantity > 0) {
                currentCustomizations.addons[addonId] = {
                    quantity: quantity,
                    price: price,
                    total: quantity * price
                };
            } else {
                delete currentCustomizations.addons[addonId];
            }
            
            updateSummary();
        }
        
        // Update order summary
        function updateSummary() {
            // Calculate total for addons
            let addonsTotal = 0;
            Object.keys(currentCustomizations.addons).forEach(addonId => {
                addonsTotal += currentCustomizations.addons[addonId].total;
            });
            
            // Update current customizations total
            currentCustomizations.total = addonsTotal;
            
            // Calculate grand total
            const grandTotal = parseFloat(currentCustomizations.basePrice) + addonsTotal;
            
            // Update summary items
            let summaryHTML = `
                <div class="summary-item">
                    <span>Base Package:</span>
                    <span>₱${parseFloat(currentCustomizations.basePrice).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                </div>
            `;
            
            // Add addons to summary
            Object.keys(currentCustomizations.addons).forEach(addonId => {
                const addon = packageAddons.find(a => a.addon_id == addonId);
                const addonData = currentCustomizations.addons[addonId];
                
                summaryHTML += `
                    <div class="summary-item">
                        <span>${addon.name} (x${addonData.quantity}):</span>
                        <span>₱${addonData.total.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                    </div>
                `;
            });
            
            // Add customization fee if there are any addons
            if (Object.keys(currentCustomizations.addons).length > 0) {
                summaryHTML += `
                    <div class="summary-item">
                        <span>Customization Fee:</span>
                        <span>₱${addonsTotal.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                    </div>
                `;
            }
            
            $('#summaryItems').html(summaryHTML);
            $('#totalPriceDisplay').text('₱' + grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2}));
        }
        
        // Proceed to booking from customization
        $('#proceedToBookingBtn').on('click', function() {
            // Close customization modal
            $('#customizationModal').modal('hide');
            
            // Prepare data for booking modal
            const customizationsJSON = JSON.stringify(currentCustomizations.addons);
            
            // Open booking modal with customized package
            const bookNowButton = document.createElement('button');
            bookNowButton.setAttribute('data-bs-toggle', 'modal');
            bookNowButton.setAttribute('data-bs-target', '#bookingModal');
            bookNowButton.setAttribute('data-package-id', currentCustomizations.packageId);
            bookNowButton.setAttribute('data-package-name', currentCustomizations.packageName);
            bookNowButton.setAttribute('data-price', currentCustomizations.basePrice);
            bookNowButton.setAttribute('data-number-of-guests', $('#modalNumberOfGuests').val());
            bookNowButton.setAttribute('data-customizations', customizationsJSON);
            bookNowButton.setAttribute('data-customization-total', currentCustomizations.total);
            
            // Trigger click on the hidden button to open modal
            document.body.appendChild(bookNowButton);
            bookNowButton.click();
            document.body.removeChild(bookNowButton);
        });

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

        // Initialize packages on page load
        loadPackages(currentPage);
    </script>
</body>
</html>