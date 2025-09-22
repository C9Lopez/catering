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
    // Total Packages
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM catering_packages");
    $stmt->execute();
    $totalPackages = $stmt->fetchColumn();

    // Active Orders (non-pending bookings)
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM event_bookings WHERE booking_status != 'pending'");
    $stmt->execute();
    $activeOrders = $stmt->fetchColumn() ?? 0;

    // Total Users
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM users");
    $stmt->execute();
    $totalUsers = $stmt->fetchColumn();

    // Fetch counts for all booking statuses
    $stmt = $db->query("SELECT booking_status, COUNT(*) as count FROM event_bookings GROUP BY booking_status");
    $statusCounts = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'count', 'booking_status');
    $statusCounts = array_merge([
        'pending' => 0, 
        'approved' => 0, 
        'rejected' => 0, 
        'completed' => 0, 
        'cancelled' => 0
    ], $statusCounts);

    // Fetch all booking events for the calendar with event type (category)
    $stmt = $db->prepare("
        SELECT eb.booking_id, eb.event_date, eb.event_time, eb.booking_status, eb.location, eb.package_id, 
               eb.user_id, cp.name AS package_name, cp.category AS event_type, 
               u.first_name, u.middle_name, u.last_name, u.birthdate, u.gender, u.address, u.contact_no, u.email
        FROM event_bookings eb
        LEFT JOIN catering_packages cp ON eb.package_id = cp.package_id
        LEFT JOIN users u ON eb.user_id = u.user_id
        ORDER BY eb.event_date, eb.event_time
    ");
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug: Log fetched bookings
    error_log("Fetched bookings: " . print_r($bookings, true));
} catch (PDOException $e) {
    $errorMsg = "Error fetching dashboard data: " . $e->getMessage();
    error_log($errorMsg);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard - CONVERT</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../css/admin.css" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css' rel='stylesheet' />
    <style>
        .fc-event { cursor: pointer; }
        .fc-event-pending { background-color: #f59e0b; border-color: #f59e0b; }
        .fc-event-approved { background-color: #10b981; border-color: #10b981; }
        .fc-event-on_process { background-color: #3b82f6; border-color: #3b82f6; }
        .fc-event-rejected, .fc-event-cancelled { background-color: #ef4444; border-color: #ef4444; }
        .fc-event-completed { background-color: #6b7280; border-color: #6b7280; }
    </style>
</head>
<body class="admin-dashboard">
    <?php include '../layout/sidebar.php'; ?>

    <div class="main-content">
        <header class="mobile-header">
            <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <h2 class="mobile-header-title">Dashboard</h2>
        </header>

        <div class="container-fluid">
            <h1 class="mb-4">Dashboard</h1>

            <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($errorMsg); ?></div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="dashboard-card h-100">
                        <div class="icon bg-primary-light"><i class="fas fa-box-open"></i></div>
                        <div class="text">
                            <h5>Total Packages</h5>
                            <p class="display-4"><?php echo $totalPackages; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="dashboard-card h-100">
                        <div class="icon bg-success-light"><i class="fas fa-calendar-check"></i></div>
                        <div class="text">
                            <h5>Active Bookings</h5>
                            <p class="display-4"><?php echo $activeOrders; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="dashboard-card h-100">
                        <div class="icon bg-warning-light"><i class="fas fa-users"></i></div>
                        <div class="text">
                            <h5>Registered Users</h5>
                            <p class="display-4"><?php echo $totalUsers; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="dashboard-card h-100">
                        <div class="icon bg-danger-light"><i class="fas fa-hourglass-half"></i></div>
                        <div class="text">
                            <h5>Pending Bookings</h5>
                            <p class="display-4"><?php echo $statusCounts['pending']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar and Quick Actions -->
            <div class="row">
                <div class="col-lg-9">
                    <div class="card">
                        <div class="card-header">
                            Booking Calendar
                        </div>
                        <div class="card-body">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-header">
                            Quick Actions
                        </div>
                        <div class="card-body">
                            <a href="orders.php" class="btn btn-primary w-100 mb-2"><i class="fas fa-list me-2"></i>View All Bookings</a>
                            <a href="add_package.php" class="btn btn-outline-secondary w-100 mb-2"><i class="fas fa-plus me-2"></i>Add New Package</a>
                            <a href="announcement.php" class="btn btn-outline-secondary w-100"><i class="fas fa-bullhorn me-2"></i>Manage Announcements</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Booking Details -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel">Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="bookingModalBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a id="goToOrdersBtn" href="orders.php" class="btn btn-primary">Go to Bookings</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js'></script>
    <script src="../js/admin.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: <?php echo json_encode(array_map(function($booking) {
                    return [
                        'id' => $booking['booking_id'],
                        'title' => "Booking #{$booking['booking_id']} - {$booking['package_name']}",
                        'start' => $booking['event_date'] . 'T' . $booking['event_time'],
                        'className' => 'fc-event-' . strtolower($booking['booking_status']),
                        'extendedProps' => [
                            'customer' => $booking['first_name'] . ' ' . ($booking['middle_name'] ? $booking['middle_name'] . ' ' : '') . $booking['last_name'],
                            'birthdate' => $booking['birthdate'],
                            'gender' => $booking['gender'],
                            'address' => $booking['address'],
                            'contact_no' => $booking['contact_no'],
                            'email' => $booking['email'],
                            'location' => $booking['location'],
                            'status' => $booking['booking_status'],
                            'package' => $booking['package_name'],
                            'event_type' => str_replace(' Catering', '', $booking['event_type'])
                        ]
                    ];
                }, $bookings)); ?>,
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    const props = info.event.extendedProps;
                    const modalBody = `
                        <h6>Booking Details</h6>
                        
                        <p><strong>Event Type:</strong> ${props.event_type || 'N/A'}</p>
                        <p><strong>Package:</strong> ${props.package}</p>
                        <p><strong>Location:</strong> ${props.location}</p>
                        <p><strong>Event Date:</strong> ${info.event.start.toLocaleString()}</p>
                        <p><strong>Status:</strong> ${props.status.charAt(0).toUpperCase() + props.status.slice(1)}</p>

                        <h6>User Details</h6>
                        <p><strong>Full Name:</strong> ${props.customer}</p>
                        <p><strong>Birthdate:</strong> ${props.birthdate}</p>
                        <p><strong>Gender:</strong> ${props.gender}</p>
                        <p><strong>Address:</strong> ${props.address}</p>
                        <p><strong>Contact No:</strong> ${props.contact_no}</p>
                        <p><strong>Email:</strong> ${props.email}</p>
                    `;
                    $('#bookingModalBody').html(modalBody);
                    // Update the "Go to Orders" button href with the booking_id
                    $('#goToOrdersBtn').attr('href', `orders.php?highlight=${info.event.id}`);
                    $('#bookingModal').removeAttr('inert').modal('show');
                },
                eventDidMount: function(info) {
                    $(info.el).tooltip({
                        title: `${info.event.title} (${info.event.extendedProps.status})`,
                        placement: 'top',
                        trigger: 'hover',
                        container: 'body'
                    });
                },
                height: 'auto',
                eventTimeFormat: { 
                    hour: '2-digit', 
                    minute: '2-digit', 
                    hour12: true 
                }
            });
            calendar.render();
        });

        // Hide modal with inert when closed
        $('#bookingModal').on('hidden.bs.modal', function() {
            $(this).attr('inert', '');
        });
    </script>
</body>
</html>