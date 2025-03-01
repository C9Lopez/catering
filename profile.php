<?php 
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<script>
        alert('You are not logged in. Please log in first.');
        window.location.href = 'auth/login.php';
    </script>";
    exit;
}

try {
    // Fetch user profile
    $stmt = $db->prepare("SELECT user_id, first_name, middle_name, last_name, email, profile_picture, address, contact_no, birthdate, gender FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $age = null;
    if (!empty($user['birthdate'])) {
        $birthdate = new DateTime($user['birthdate']);
        $today = new DateTime();
        $age = $today->diff($birthdate)->y;
    }

    // Fetch all bookings for filtering, including category from catering_packages
    $bookingStmt = $db->prepare("
        SELECT eb.booking_id, eb.package_id, eb.event_type, eb.event_date, eb.event_time, eb.booking_status, eb.created_at, cp.category 
        FROM event_bookings eb 
        LEFT JOIN catering_packages cp ON eb.package_id = cp.package_id 
        WHERE eb.user_id = :user_id 
        ORDER BY eb.created_at DESC
    ");
    $bookingStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $bookingStmt->execute();
    $allBookings = $bookingStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Pochie Catering</title>

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Playball&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/themes.css" rel="stylesheet">
    <link href="css/profile.css" rel="stylesheet">
    <link href="css/booking.css" rel="stylesheet">
</head>

<body class="light-theme">

    <?php include 'layout/navbar.php'; ?>

    <!-- Profile Section -->
    <div class="container-fluid profile-container py-6 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container">
            <div class="text-center wow bounceInUp" data-wow-delay="0.1s">
                <h1 class="display-5 mb-5" style="font-family: 'Playball', cursive;">Welcome Back, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
                <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#updateProfileModal">
                    <i class="fas fa-edit me-2"></i> Update Profile
                </button>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="profile-card">
                        <div class="profile-image text-center">
                            <img src="<?php echo './img/profile/' . htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="img-fluid">
                        </div>
                        <div class="profile-info text-center">
                            <h2 class="text-primary mb-2" style="font-family: 'Playball', cursive;"><?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?></h2>
                            <p class="text-muted mb-4"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>

                        <div class="profile-details">
                            <div class="profile-detail-item text-center">
                                <i class="fas fa-birthday-cake"></i>
                                <span><?php echo $age !== null ? $age . ' years old' : 'Birthdate not provided'; ?></span>
                            </div>
                            <div class="profile-detail-item text-center">
                                <i class="fas fa-venus-mars"></i>
                                <span><?php echo htmlspecialchars($user['gender']); ?></span>
                            </div>
                            <div class="profile-detail-item text-center">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($user['address']); ?></span>
                            </div>
                            <div class="profile-detail-item text-center">
                                <i class="fas fa-phone"></i>
                                <span><?php echo htmlspecialchars($user['contact_no']); ?></span>
                            </div>
                        </div>

                        <div class="logout-container text-center mt-4">
                            <a href="./auth/logout_user.php" class="btn btn-danger logout-btn">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings Section -->
    <div class="container-fluid bookings-section wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="text-center mb-4">
                <h2 class="display-5 mb-3" style="font-family: 'Playball', cursive;">Recent Bookings</h2>
                <p class="fs-5 text-muted">Check the status of your recent catering bookings</p>
            </div>

            <!-- Filter Buttons (Reinstated "All" button) -->
            <div class="text-center mb-4">
                <button class="btn filter-btn me-2" data-filter="all">All</button>
                <button class="btn filter-btn me-2" data-filter="Wedding Catering">Wedding</button>
                <button class="btn filter-btn me-2" data-filter="Debut Catering">Debut</button>
                <button class="btn filter-btn me-2" data-filter="Corporate Catering">Corporate</button>
                <button class="btn filter-btn me-2" data-filter="Childrens Party Catering">Children’s</button>
                <button class="btn filter-btn" data-filter="Private Catering">Private</button>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-8">
                    <div id="bookings-container">
                        <?php if (empty($allBookings)): ?>
                            <p class="text-center text-muted">No recent bookings found.</p>
                        <?php else: ?>
                            <?php foreach ($allBookings as $booking): ?>
                                <div class="booking-card <?php echo htmlspecialchars(str_replace(' ', '-', strtolower($booking['category']))); ?>" data-type="<?php echo htmlspecialchars($booking['category']); ?>">
                                    <h5><?php echo htmlspecialchars($booking['event_type']); ?></h5>
                                    <p><strong>Event Date:</strong> <?php echo date('F j, Y', strtotime($booking['event_date'])); ?></p>
                                    <p><strong>Event Time:</strong> <?php echo date('h:i A', strtotime($booking['event_time'])); ?></p>
                                    <p><strong>Booking Date:</strong> <?php echo date('F j, Y, h:i A', strtotime($booking['created_at'])); ?></p>
                                    <p>
                                        <strong>Status:</strong> 
                                        <span class="status <?php 
                                            echo $booking['booking_status'] === 'approved' ? 'status-approved' : 
                                                ($booking['booking_status'] === 'pending' ? 'status-pending' : 'status-rejected'); ?>">
                                            <?php echo ucfirst(htmlspecialchars($booking['booking_status'])); ?>
                                        </span>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Profile Modal -->
    <div class="modal fade" id="updateProfileModal" tabindex="-1" aria-labelledby="updateProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateProfileModalLabel">Update Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateProfileForm" enctype="multipart/form-data" method="POST" action="update_profile.php">
                        <div class="mb-3 text-center">
                            <label for="profilePicture" class="form-label">Profile Picture</label>
                            <div class="profile-image">
                                <img id="profilePreview" src="<?php echo './img/profile/' . htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="img-fluid rounded-circle" width="150" height="150">
                            </div>
                            <input type="file" class="form-control mt-2" id="profilePicture" name="profile_picture" accept="image/*" onchange="previewImage(event)">
                        </div>
                        <input type="text" class="form-control" id="user_id" name="user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>" required hidden>
                        <div class="mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="middleName" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middleName" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="birthdate" class="form-label">Birthdate</label>
                            <input type="date" class="form-control" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($user['birthdate']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <input type="text" class="form-control" id="gender" name="gender" value="<?php echo htmlspecialchars($user['gender']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="contactNo" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="contactNo" name="contact_no" value="<?php echo htmlspecialchars($user['contact_no']); ?>">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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

        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function () {
                var output = document.getElementById('profilePreview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        // Booking Filter Functionality (Updated for exact matches)
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter').toLowerCase().trim(); // Case-insensitive and trim whitespace
                const cards = document.querySelectorAll('.booking-card');
                
                cards.forEach(card => {
                    const cardType = card.getAttribute('data-type').toLowerCase().trim();
                    if (filter === 'all' || cardType === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Update button styles to indicate active filter
                document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Set default filter to "All" on page load
        document.querySelector('.filter-btn[data-filter="all"]').click();
    </script>

</body>
</html>