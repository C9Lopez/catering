<?php
session_start();
require '../db.php';

// 1. Fetch occupied dates for calendar
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

// 2. Fetch Private packages
try {
    $stmt = $db->prepare("SELECT * FROM catering_packages WHERE category = 'Private Catering'");
    $stmt->execute();
    $private_packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $packages_json = json_encode($private_packages);
    
    $itemsPerPage = 2;
    $totalPackages = count($private_packages);
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

// 3. Fetch the package_id for Custom Private Package
$custom_package_id = null;
foreach ($private_packages as $pkg) {
    if (strpos(strtolower($pkg['name']), 'custom') !== false) {
        $custom_package_id = $pkg['package_id'];
        break;
    }
}

// 4. Fetch active private/social menu items for custom package
try {
    // Assuming service_type is 'private' or 'social'. Adjust if your DB uses a different tag.
    $menus_stmt = $db->prepare("SELECT m.*, c.category_name FROM menus m LEFT JOIN menu_categories c ON m.category_id = c.category_id WHERE m.service_type = 'private' AND m.status = 'active' ORDER BY c.category_name, m.title");
    $menus_stmt->execute();
    $private_menus = $menus_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fix image paths
    foreach ($private_menus as &$menu) {
        $image_path = $menu['image_path'];
        if (strpos($image_path, './uploads/') === 0) {
            $menu['image_path'] = '../admin/uploads/' . substr($image_path, 10);
        } elseif (strpos($image_path, 'uploads/') === 0) {
            $menu['image_path'] = '../admin/uploads/' . substr($image_path, 8);
        }
    }
    unset($menu); 
    $private_menus_json = json_encode($private_menus);
} catch (PDOException $e) {
    error_log("Error fetching private menus: " . $e->getMessage());
    $private_menus = [];
    $private_menus_json = json_encode([]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Private Party Catering - Pochie Catering</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Playball&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="../lib/lightbox/css/lightbox.min.css" rel="stylesheet">
    <link href="../lib/owlcarousel/owl.carousel.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/themes.css" rel="stylesheet">
    <link href="../css/packages.css" rel="stylesheet">
    <link href="../css/booking.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.min.css">

    <style>
        /* --- COPYING STYLES (With Private Party Colors) --- */
        
        /* Shopee-style custom package cards */
        .custom-menu-card {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: box-shadow 0.2s, transform 0.2s;
            border: 1px solid #f1f1f1;
            background: #fff;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .custom-menu-card:hover {
            box-shadow: 0 6px 18px rgba(0,0,0,0.13);
            transform: translateY(-2px) scale(1.01);
        }
        .custom-menu-img {
            border-radius: 12px 12px 0 0;
            object-fit: cover;
            width: 100%;
            height: 160px;
        }
        .custom-menu-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        .custom-menu-price {
            color: #198754; /* Green/Teal for Private Theme */
            font-weight: 700;
            font-size: 1.1rem;
        }
        .custom-qty-group {
            display: flex;
            align-items: center;
            margin-top: 0.5rem;
        }
        .custom-qty-btn {
            width: 32px;
            height: 32px;
            border: none;
            background: #f1f1f1;
            color: #333;
            font-size: 1.2rem;
            border-radius: 6px;
            transition: background 0.2s;
        }
        .custom-qty-btn:active {
            background: #e0e0e0;
        }
        .custom-qty-input {
            width: 48px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin: 0 6px;
            height: 32px;
        }
        .custom-cart-summary {
            position: sticky;
            top: 90px;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            border-radius: 12px;
            background: #fff;
            margin-bottom: 2rem;
        }
        @media (max-width: 991px) {
            .custom-cart-summary {
                position: static;
                margin-top: 1.5rem;
            }
        }
        
        /* Package Card Styles */
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
        
        /* PRIVATE PARTY THEME COLOR (Green/Teal Gradient) */
        .package-header {
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            padding: 20px;
        }
        
        .package-body {
            padding: 20px;
        }
        .package-footer {
            background-color: #f8f9fa;
            padding: 15px 20px;
        }
        .view-details-btn {
            color: #198754; /* Green Text */
            background: transparent;
            border: 1px solid #198754;
            margin-top: 10px;
        }
        .view-details-btn:hover {
            color: white;
            background-color: #198754;
        }
        .package-features {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease;
        }
        .package-features.show {
            max-height: 500px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #dee2e6;
        }
    </style>
</head>
<body class="light-theme">

<?php include '../layout/navbar.php'; ?>

    <div class="container-fluid hero-section py-6 my-6 text-center wow fadeInUp" data-wow-delay="0.3s">
        <div class="hero-overlay"></div>
        <div class="container position-relative text-white">
            <h1 class="display-1 mb-4">Private Party <span class="text-primary">Catering</span></h1>
            <p class="lead">Create intimate and memorable celebrations with our personalized private party catering services.</p>
            <a href="./private_menu.php" class="btn btn-primary border-0 rounded-pill py-2 px-3 px-md-3 animated bounceInLeft">View Menu</a>
        </div>
    </div>

    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-5 mb-4">Personalized Party Catering</h1>
                    <p class="mb-4">
                        Transform your private gatherings into extraordinary experiences. Whether it's an intimate dinner party, 
                        family reunion, or special anniversary, we create personalized menus that perfectly match your vision.
                    </p>
                    <div class="row g-4">
                        <div class="col-md-6"><div class="d-flex align-items-center mb-3"><i class="fas fa-check text-primary me-3"></i><span>Customized Menus</span></div></div>
                        <div class="col-md-6"><div class="d-flex align-items-center mb-3"><i class="fas fa-check text-primary me-3"></i><span>Elegant Presentation</span></div></div>
                        <div class="col-md-6"><div class="d-flex align-items-center mb-3"><i class="fas fa-check text-primary me-3"></i><span>Professional Service</span></div></div>
                        <div class="col-md-6"><div class="d-flex align-items-center mb-3"><i class="fas fa-check text-primary me-3"></i><span>Flexible Venues</span></div></div>
                    </div>
                </div>
                <div class="col-lg-6 wow zoomIn" data-wow-delay="0.5s">
                    <img src="../img/event-6.jpg" class="img-fluid rounded" alt="Private Party Catering">
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="text-center mb-5 wow fadeInUp" data-wow-delay="0.3s">
                <h1 class="display-4 text-title fw-bold">Private Party Packages</h1>
                <p class="fs-5 text-subtitle">Choose the perfect package for your Special Occasion</p>
                <p class="text-muted">Select a default package or customize it to fit your specific needs</p>
            </div>
            
            <div id="packagesContainer" class="row g-4 justify-content-center">
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

    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bold">Customize Your Package Now</h1>
                <p class="fs-5 text-muted">Build your own private package by selecting from our menu items below.</p>
            </div>
            <form id="customPrivateForm">
                <div class="row">
                    <div class="col-lg-8">
                        <div id="customMenuAccordion">
                            </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="custom-cart-summary p-3 mb-4">
                            <div class="card-header bg-primary text-white text-center rounded mb-3">
                                <h5 class="mb-0">Your Custom Package Cart</h5>
                            </div>
                            <div class="card-body p-0">
                                <div id="customSummaryItems"></div>
                                <div class="summary-total mt-3 d-flex justify-content-between align-items-center">
                                    <span>Total:</span>
                                    <span id="customTotalPriceDisplay">₱0.00</span>
                                </div>
                            </div>
                            <button type="button" id="customBookNowBtn" class="btn btn-success w-100 mt-3 py-2 fs-5">
                                <i class="fas fa-calendar-check me-2"></i>Book Now
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

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

    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel">Book Your Private Party</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height:70vh; overflow-y:auto;">
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
                                <input type="number" min="1" class="form-control" id="modalNumberOfGuests" name="number_of_guests">
                            </div>
                            <div class="col-12" id="pricePerHeadRow" style="display:none;">
                                <label class="form-label">Price Per Head (₱)</label>
                                <input type="number" min="0" step="0.01" class="form-control" id="modalPricePerHead" name="price_per_head" value="0" readonly>
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

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../lib/wow/wow.min.js"></script>
    <script src="../lib/easing/easing.min.js"></script>
    <script src="../lib/waypoints/waypoints.min.js"></script>
    <script src="../lib/counterup/counterup.min.js"></script>
    <script src="../lib/lightbox/js/lightbox.min.js"></script>
    <script src="../lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.min.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/theme-switcher.js"></script>

    <script>
        new WOW().init();

        // --- Global Variables ---
        let currentPage = <?php echo $currentPage; ?>;
        const itemsPerPage = <?php echo $itemsPerPage; ?>;
        const totalPackages = <?php echo $totalPackages; ?>;
        const totalPages = <?php echo $totalPages; ?>;
        const packages = <?php echo $packages_json; ?>;
        const privateMenus = <?php echo $private_menus_json; ?>;
        const occupiedDates = <?php echo json_encode($occupiedDates); ?>;
        const customPackageId = <?php echo json_encode($custom_package_id); ?>;
        
        let customSelections = {}; // Store custom items: {menu_id: {title, price, quantity}}

        // --- 1. AJAX Form Submission ---
        $('#bookingForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();

            $.ajax({
                url: 'process_booking.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    Toastify({
                        text: response.message,
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: response.success ? '#28a745' : '#dc3545',
                        stopOnFocus: true,
                        style: { borderRadius: "10px", fontSize: "16px", padding: "15px" }
                    }).showToast();

                    if (response.success) {
                        $('#bookingModal').modal('hide');
                        $('#bookingForm')[0].reset();
                        $('#eventDate').val('');
                    }
                },
                error: function(xhr, status, error) {
                    Toastify({
                        text: "An error occurred while processing your request.",
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: '#dc3545',
                        stopOnFocus: true,
                    }).showToast();
                }
            });
        });

        // --- 2. Booking Modal Logic & Calendar ---
        document.addEventListener("DOMContentLoaded", function () {
            let bookingModal = document.getElementById("bookingModal");
            let calendarInitialized = false;

            bookingModal.addEventListener("shown.bs.modal", function (event) {
                let button = event.relatedTarget;
                let packageId = button.getAttribute("data-package-id");
                let packageName = button.getAttribute("data-package-name");
                let price = button.getAttribute("data-price");
                let numberOfGuests = button.getAttribute("data-number-of-guests");
                let customizations = button.getAttribute("data-customizations") || "{}";
                let customizationTotal = button.getAttribute("data-customization-total") || "0";

                let totalPrice = parseFloat(price) + parseFloat(customizationTotal);

                document.getElementById("modalPackageId").value = packageId;
                document.getElementById("modalPackageName").value = packageName;
                document.getElementById("modalTotalAmountDisplay").value = "₱" + totalPrice.toLocaleString('en-US', {minimumFractionDigits: 2});
                document.getElementById("modalTotalAmount").value = totalPrice;
                document.getElementById("modalCustomizations").value = customizations;
                document.getElementById("modalCustomizationTotal").value = customizationTotal;

                // Guest Input Logic
                const guestsInput = document.getElementById("modalNumberOfGuests");
                const pricePerHeadRow = document.getElementById("pricePerHeadRow");
                const pricePerHeadInput = document.getElementById("modalPricePerHead");

                const isCustom = (packageId == customPackageId) || (packageName.toLowerCase().includes('custom'));

                if (isCustom) {
                    // Custom Package: Guests Editable
                    guestsInput.value = numberOfGuests && numberOfGuests !== 'null' ? numberOfGuests : '';
                    guestsInput.removeAttribute('readonly');
                    guestsInput.required = true;
                    guestsInput.placeholder = 'Enter number of guests';

                    pricePerHeadRow.style.display = '';
                    if (!pricePerHeadInput.value || pricePerHeadInput.value == '0') pricePerHeadInput.value = 100; // Default Head Charge
                    pricePerHeadInput.readOnly = true;
                } else {
                    // Standard Package: Guests Fixed
                    guestsInput.value = numberOfGuests;
                    guestsInput.readOnly = true;
                    guestsInput.required = false;
                    guestsInput.placeholder = '';
                    pricePerHeadRow.style.display = 'none';
                }

                // Initialize Calendar
                if (!calendarInitialized) {
                    var calendarEl = document.getElementById('calendar');
                    const now = new Date();
                    const philippineTimeOffset = 8 * 60;
                    const localOffset = now.getTimezoneOffset();
                    const philippineTime = new Date(now.getTime() + (philippineTimeOffset + localOffset) * 60 * 1000);
                    const today = philippineTime.toISOString().split('T')[0];

                    var calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
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
                                alert('You cannot book a past date.');
                                return;
                            }
                            if (occupiedDates.includes(selectedDate)) {
                                alert('This date is already occupied.');
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

        // --- 3. Package Rendering ---
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

        // --- 4. Custom Menu Logic (Shopee-style) ---
        function renderCustomMenuList() {
            const $accordion = $('#customMenuAccordion');
            $accordion.empty();
            if (!privateMenus.length) {
                $accordion.html('<div class="alert alert-info text-center">No menu items available for customization.</div>');
                return;
            }

            const groupedMenus = privateMenus.reduce((acc, menu) => {
                const category = menu.category_name || 'Uncategorized';
                if (!acc[category]) acc[category] = [];
                acc[category].push(menu);
                return acc;
            }, {});

            const sortedCategories = Object.keys(groupedMenus).sort();

            sortedCategories.forEach((category, index) => {
                const collapseId = `collapse-${category.replace(/\s+/g, '-')}`;
                const isFirst = index === 0;

                const categoryHtml = `
                    <div class="accordion-item mb-3">
                        <h2 class="accordion-header" id="heading-${collapseId}">
                            <button class="accordion-button ${isFirst ? '' : 'collapsed'}" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="${isFirst}" aria-controls="${collapseId}">
                                ${category}
                            </button>
                        </h2>
                        <div id="${collapseId}" class="accordion-collapse collapse ${isFirst ? 'show' : ''}" aria-labelledby="heading-${collapseId}" data-bs-parent="#customMenuAccordion">
                            <div class="accordion-body">
                                <div class="row">
                                    ${groupedMenus[category].map(menu => `
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="custom-menu-card h-100">
                                                ${menu.image_path ? `<img src="${menu.image_path}" class="custom-menu-img">` : ''}
                                                <div class="p-3 flex-grow-1 d-flex flex-column justify-content-between">
                                                    <div>
                                                        <div class="custom-menu-title">${menu.title}</div>
                                                        <div class="text-muted small mb-2">${menu.description}</div>
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-between mt-2">
                                                        <span class="custom-menu-price">₱${parseFloat(menu.price).toLocaleString('en-US', {minimumFractionDigits:2})}</span>
                                                        <span class="badge bg-success">Max: ${menu.max_quantity}</span>
                                                    </div>
                                                    <div class="custom-qty-group mt-2">
                                                        <button type="button" class="custom-qty-btn custom-qty-minus" data-menu-id="${menu.id}" data-max="${menu.max_quantity}">-</button>
                                                        <input type="text" readonly value="0" class="custom-qty-input" data-menu-id="${menu.id}" data-title="${menu.title}" data-price="${menu.price}" data-max="${menu.max_quantity}">
                                                        <button type="button" class="custom-qty-btn custom-qty-plus" data-menu-id="${menu.id}" data-max="${menu.max_quantity}">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $accordion.append(categoryHtml);
            });
        }

        // Update Cart Summary
        function updateCustomSummary() {
            let total = 0;
            let html = '';
            Object.values(customSelections).forEach(sel => {
                if (sel.quantity > 0) {
                    const itemTotal = sel.price * sel.quantity;
                    total += itemTotal;
                    html += `<div class="summary-item d-flex justify-content-between"><span>${sel.title} (x${sel.quantity})</span><span>₱${itemTotal.toLocaleString('en-US', {minimumFractionDigits:2})}</span></div>`;
                }
            });
            if (!html) html = '<div class="text-muted">No items selected.</div>';
            $('#customSummaryItems').html(html);
            $('#customTotalPriceDisplay').text('₱' + total.toLocaleString('en-US', {minimumFractionDigits:2}));
        }

        // Qty Controls
        $(document).on('click', '.custom-qty-plus', function() {
            const menuId = $(this).data('menu-id');
            const max = parseInt($(this).data('max'));
            const $input = $(`.custom-qty-input[data-menu-id='${menuId}']`);
            let qty = parseInt($input.val()) || 0;
            if (qty < max) qty++;
            $input.val(qty).trigger('change');
        });

        $(document).on('click', '.custom-qty-minus', function() {
            const menuId = $(this).data('menu-id');
            const $input = $(`.custom-qty-input[data-menu-id='${menuId}']`);
            let qty = parseInt($input.val()) || 0;
            if (qty > 0) qty--;
            $input.val(qty).trigger('change');
        });

        $(document).on('change', '.custom-qty-input', function() {
            const menuId = $(this).data('menu-id');
            const title = $(this).data('title');
            const price = parseFloat($(this).data('price'));
            const max = parseInt($(this).data('max'));
            let qty = parseInt($(this).val()) || 0;
            if (qty > max) qty = max;
            if (qty < 0) qty = 0;
            $(this).val(qty);
            
            if (qty > 0) {
                customSelections[menuId] = { title, price, quantity: qty };
            } else {
                delete customSelections[menuId];
            }
            updateCustomSummary();
        });

        // --- 5. Book Custom Package Button ---
        $('#customBookNowBtn').on('click', function() {
            const selectedItems = Object.values(customSelections).filter(sel => sel.quantity > 0);
            if (!selectedItems.length) {
                Toastify({
                    text: 'Please select at least one menu item.',
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#dc3545",
                }).showToast();
                return;
            }
            // Calculate total cost of selected menu items
            const menuTotalCost = selectedItems.reduce((sum, sel) => sum + sel.price * sel.quantity, 0);
            
            // Trigger Modal Open
            const bookNowButton = document.createElement('button');
            bookNowButton.setAttribute('data-bs-toggle', 'modal');
            bookNowButton.setAttribute('data-bs-target', '#bookingModal');
            bookNowButton.setAttribute('data-package-id', customPackageId ? customPackageId : '');
            bookNowButton.setAttribute('data-package-name', "Custom Private Package");
            bookNowButton.setAttribute('data-price', menuTotalCost); 
            bookNowButton.setAttribute('data-number-of-guests', ''); 
            bookNowButton.setAttribute('data-customizations', JSON.stringify(selectedItems));
            bookNowButton.setAttribute('data-customization-total', 0); 
            
            document.body.appendChild(bookNowButton);
            bookNowButton.click();
            document.body.removeChild(bookNowButton);
        });

        // --- 6. Live Total Calculation in Modal ---
        function updateCustomTotal() {
            const guests = parseInt($('#modalNumberOfGuests').val()) || 0;
            const pricePerHead = parseFloat($('#modalPricePerHead').val()) || 0;
            let menuTotal = 0;
            
            Object.values(customSelections).forEach(sel => {
                if (sel.quantity > 0) menuTotal += sel.price * sel.quantity;
            });

            const total = menuTotal + (guests * pricePerHead);
            
            $('#modalTotalAmountDisplay').val('₱' + total.toLocaleString('en-US', {minimumFractionDigits:2}));
            $('#modalTotalAmount').val(total);
        }

        $(document).on('input', '#modalNumberOfGuests', function() {
            if ($('#pricePerHeadRow').is(':visible')) {
                updateCustomTotal();
            }
        });
        
        $('#bookingModal').on('shown.bs.modal', function() {
            if ($('#pricePerHeadRow').is(':visible')) {
                updateCustomTotal();
            }
        });

        // Pagination buttons
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

        // Init
        loadPackages(currentPage);
        renderCustomMenuList();
        updateCustomSummary();
    </script>
</body>
</html>