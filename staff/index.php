<?php
require '../db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

// Get dashboard statistics
try {
    // Get total packages
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM catering_packages");
    $stmt->execute();
    $totalPackages = $stmt->fetchColumn();

    // Get active orders (non-pending bookings for simplicity)
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM event_bookings WHERE booking_status != 'pending'");
    $stmt->execute();
    $activeOrders = $stmt->fetchColumn() ?? 0;

    // Get total users
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM users");
    $stmt->execute();
    $totalUsers = $stmt->fetchColumn();

    // Fetch booking events for the calendar
    $stmt = $db->prepare("
        SELECT booking_id, event_date, event_time, booking_status, location, package_id 
        FROM event_bookings 
        WHERE event_date >= CURDATE()  -- Only show future bookings
        ORDER BY event_date, event_time
    ");
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMsg = "Error fetching dashboard data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Admin Dashboard - Catering System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/admin.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css' rel='stylesheet' />
    <style>
        .main-content { padding: 20px; }
        .dashboard-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .fc-event {
            cursor: pointer;
            margin: 2px 0;
            padding: 5px;
            border-radius: 4px;
        }
        .fc-event-pending { background-color: #ffc107; color: #000; }
        .fc-event-approved { background-color: #28a745; color: #fff; }
        .fc-event-rejected { background-color: #dc3545; color: #fff; }
        .fc-event-completed { background-color: #17a2b8; color: #fff; }
        .fc-event-cancelled { background-color: #dc3545; color: #fff; }
        #calendar { max-width: 100%; margin: 20px 0; }
    </style>
</head>

<body class="admin-dashboard">
    <?php include '../layout/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <h1 class="mb-4">Dashboard</h1>

            <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errorMsg); ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="dashboard-card p-3 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-box-open fa-2x me-3"></i>
                            <h5 class="mb-0">Total Packages</h5>
                        </div>
                        <p class="display-4 mb-3"><?php echo $totalPackages; ?></p>
                        <a href="packages.php" class="btn btn-primary btn-block">
                            <i class="fas fa-cog me-2"></i>Manage Packages
                        </a>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="dashboard-card p-3 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-shopping-cart fa-2x me-3"></i>
                            <h5 class="mb-0">Active Bookings</h5>
                        </div>
                        <p class="display-4 mb-3"><?php echo $activeOrders; ?></p>
                        <a href="orders.php" class="btn btn-primary btn-block">
                            <i class="fas fa-eye me-2"></i>View Bookings
                        </a>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="dashboard-card p-3 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-users fa-2x me-3"></i>
                            <h5 class="mb-0">Registered Users</h5>
                        </div>
                        <p class="display-4 mb-3"><?php echo $totalUsers; ?></p>
                        <a href="users.php" class="btn btn-primary btn-block">
                            <i class="fas fa-user-cog me-2"></i>Manage Users
                        </a>
                    </div>
                </div>
            </div>

            <!-- Calendar Section -->
            <div class="row">
                <div class="col-12">
                    <div class="dashboard-card p-3">
                        <h5 class="mb-3">Booking Calendar</h5>
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>
    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: <?php echo json_encode(array_map(function($booking) {
                    return [
                        'title' => "Booking #{$booking['booking_id']} - {$booking['location']} (Status: {$booking['booking_status']})",
                        'start' => $booking['event_date'] . 'T' . $booking['event_time'],
                        'className' => 'fc-event-' . strtolower($booking['booking_status']),
                        'url' => 'orders.php'  // Link to orders page for more details
                    ];
                }, $bookings)); ?>,
                eventClick: function(info) {
                    info.jsEvent.preventDefault(); // Prevent default link behavior
                    if (info.event.url) {
                        window.location.href = info.event.url;
                    }
                },
                height: 'auto'
            });
            calendar.render();
        });
    </script>
</body>

</html>