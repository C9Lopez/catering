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

$user_id = $_SESSION['user_id'];


try {
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

    // Fetch all bookings (no status exclusion)
    $bookingStmt = $db->prepare("
        SELECT eb.booking_id, eb.package_id, eb.event_type, eb.event_date, eb.event_time, eb.booking_status, eb.created_at, 
               cp.category, cp.price 
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

function formatCategory($category) {
    return str_replace('Catering', 'Party Catering', $category);
}

// Current date for comparison
$currentDate = new DateTime();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Pochie Catering</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Playball&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/owl.carousel.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/themes.css" rel="stylesheet">
    <link href="css/profile.css" rel="stylesheet">
    <link href="css/booking.css" rel="stylesheet">
    <style>
        .category-badge {
            font-size: 0.85rem;
            padding: 4px 8px;
            border-radius: 12px;
            background-color: #e9ecef;
            display: inline-block;
            margin-top: 5px;
        }
        .chat-btn {
            position: relative;
            margin-top: 10px;
        }
        .chat-btn .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.7rem;
            padding: 2px 5px;
        }
        .cancel-btn {
            margin-top: 10px;
        }
        .status-filter-btn {
            margin: 0 5px;
        }
        .status-filter-btn.active {
            font-weight: bold;
            border-bottom: 2px solid #007bff;
        }
    </style>
</head>
<body class="light-theme">
    <?php include 'layout/navbar.php'; ?>

    <!-- Profile Section -->
    <div class="container-fluid profile-container py-6 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container">
            <div class="text-center wow bounceInUp" data-wow-delay="0.1s">
                <h1 class="display-5 mb-5" style="font-family: 'Playball', cursive;">Welcome Back, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
                <button type="button" class="btn btn-primary mt-3 me-2" data-bs-toggle="modal" data-bs-target="#updateProfileModal">
                    <i class="fas fa-edit me-2"></i> Update Profile
                
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
            <div class="text-center mb-4">
                <button class="btn filter-btn me-2" data-filter="all">All Categories</button>
                <button class="btn filter-btn me-2" data-filter="Wedding Catering">Wedding</button>
                <button class="btn filter-btn me-2" data-filter="Debut Catering">Debut</button>
                <button class="btn filter-btn me-2" data-filter="Corporate Catering">Corporate</button>
                <button class="btn filter-btn me-2" data-filter="Childrens Party Catering">Children’s</button>
                <button class="btn filter-btn" data-filter="Private Catering">Private</button>
            </div>
            <div class="text-center mb-4">
                <button class="btn status-filter-btn me-2" data-status-filter="all">All Statuses</button>
                <button class="btn status-filter-btn me-2" data-status-filter="pending">Pending</button>
                <button class="btn status-filter-btn me-2" data-status-filter="approved">Approved</button>
                <button class="btn status-filter-btn me-2" data-status-filter="rejected">Rejected</button>
                <button class="btn status-filter-btn me-2" data-status-filter="cancelled">Cancelled</button>
                <button class="btn status-filter-btn me-2" data-status-filter="completed">Completed</button>
            </div>
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-8">
                    <div id="bookings-container">
                        <?php if (empty($allBookings)): ?>
                            <p class="text-center text-muted">No recent bookings found.</p>
                        <?php else: ?>
                            <?php foreach ($allBookings as $booking): 

                                // Fetch unread message count for this booking (from admin)
                                $chatStmt = $db->prepare("SELECT COUNT(*) as unread FROM chat_messages WHERE order_id =  :booking_id  AND user_id =  :user_id AND is_unread != 0");
                                $chatStmt->execute([
                                    ':booking_id' => $booking['booking_id'],
                                    ':user_id' => $user_id
                                ]);
                                $unreadCount = $chatStmt->fetch(PDO::FETCH_ASSOC)['unread'];

                                // Check if cancellation is allowed
                                $eventDate = new DateTime($booking['event_date']);
                                $daysUntilEvent = $currentDate->diff($eventDate)->days;
                                $canCancel = ($booking['booking_status'] === 'pending' && $daysUntilEvent >= 7);
                            ?>
                                <div class="booking-card <?php echo htmlspecialchars(str_replace(' ', '-', strtolower($booking['category']))); ?>" 
                                     data-type="<?php echo htmlspecialchars($booking['category']); ?>" 
                                     data-status="<?php echo htmlspecialchars($booking['booking_status']); ?>">
                                    <h5><?php echo htmlspecialchars($booking['event_type']); ?></h5>
                                    <div class="category-badge"><?php echo htmlspecialchars(formatCategory($booking['category'])); ?></div>
                                    <p><strong>Event Date:</strong> <?php echo date('F j, Y', strtotime($booking['event_date'])); ?></p>
                                    <p><strong>Event Time:</strong> <?php echo date('h:i A', strtotime($booking['event_time'])); ?></p>
                                    <p><strong>Booking Date:</strong> <?php echo date('F j, Y, h:i A', strtotime($booking['created_at'])); ?></p>
                                    <p><strong>Price:</strong> <?php echo $booking['price'] !== null ? '₱' . number_format($booking['price'], 2) : 'N/A'; ?></p>
                                    <p>
                                        <strong>Status:</strong> 
                                        <span class="status <?php 
                                            echo $booking['booking_status'] === 'approved' ? 'status-approved' : 
                                                ($booking['booking_status'] === 'pending' ? 'status-pending' : 
                                                ($booking['booking_status'] === 'completed' ? 'status-completed' : 
                                                ($booking['booking_status'] === 'cancelled' ? 'status-cancelled' : 'status-rejected'))); ?>">
                                            <?php echo ucfirst(htmlspecialchars($booking['booking_status'])); ?>
                                        </span>
                                    </p>
                                    <?php if ($booking['booking_status'] === 'approved'): ?>
                                        <a href="chat_user.php?booking_id=<?php echo $booking['booking_id']; ?>" class="btn btn-secondary btn-sm chat-btn">
                                            <i class="fas fa-comments"></i> Chat
                                            <?php if ($unreadCount != 0): ?>
                                                <span class="badge bg-danger"><?php echo $unreadCount; ?></span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($canCancel): ?>
                                        <a href="cancel_booking.php?booking_id=<?php echo $booking['booking_id']; ?>" class="btn btn-danger btn-sm cancel-btn" onclick="return confirm('Are you sure you want to cancel this booking?');">
                                            <i class="fas fa-times"></i> Cancel Booking
                                        </a>
                                    <?php endif; ?>
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

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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

        // Category filter
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                const categoryFilter = this.getAttribute('data-filter').toLowerCase().trim();
                applyFilters(categoryFilter, null);
                document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Status filter
        document.querySelectorAll('.status-filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                const statusFilter = this.getAttribute('data-status-filter').toLowerCase().trim();
                const activeCategory = document.querySelector('.filter-btn.active')?.getAttribute('data-filter').toLowerCase().trim() || 'all';
                applyFilters(activeCategory, statusFilter);
                document.querySelectorAll('.status-filter-btn').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Combined filter function
        function applyFilters(categoryFilter, statusFilter) {
            const cards = document.querySelectorAll('.booking-card');
            cards.forEach(card => {
                const cardCategory = card.getAttribute('data-type').toLowerCase().trim();
                const cardStatus = card.getAttribute('data-status').toLowerCase().trim();
                
                const categoryMatch = (categoryFilter === 'all' || cardCategory === categoryFilter);
                const statusMatch = (statusFilter === null || statusFilter === 'all' || cardStatus === statusFilter);
                
                card.style.display = (categoryMatch && statusMatch) ? 'block' : 'none';
            });
        }

        // Initialize with "All" category and "All" status
        document.querySelector('.filter-btn[data-filter="all"]').click();
        document.querySelector('.status-filter-btn[data-status-filter="all"]').click();
    </script>
</body>
</html>