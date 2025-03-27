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
    <title>Admin Dashboard - Catering System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/admin.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css' rel='stylesheet' />
    <style>
        .main-content { padding: 20px; }
        .dashboard-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .dashboard-card:hover { transform: translateY(-5px); }
        .fc-event { 
            cursor: pointer; 
            margin: 2px 0; 
            padding: 5px; 
            border-radius: 4px; 
            font-size: 0.9rem; 
        }
        .fc-event-pending { background-color: #ffc107; color: #000; }
        .fc-event-approved { background-color: #28a745; color: #fff; }
        .fc-event-rejected { background-color: #dc3545; color: #fff; }
        .fc-event-completed { background-color: #17a2b8; color: #fff; }
        .fc-event-cancelled { background-color: #6c757d; color: #fff; }
        #calendar { max-width: 100%; margin: 20px 0; }
        .stat-icon { font-size: 2rem; color: #007bff; }
        .quick-links { margin-top: 20px; }
        .quick-links a { margin-right: 10px; }
        .status-overview { background: #007bff; color: white; padding: 10px; border-radius: 8px; margin-bottom: 20px; }
        .status-overview h5 { margin: 0; }
        .status-overview .badge { margin-right: 10px; }
        .modal-body p { margin-bottom: 10px; }
        @media (max-width: 991.98px) {
            .main-content {
                padding-top: 60px; /* Avoid overlap with toggle button */
            }
        }
    </style>
</head>
<body class="admin-dashboard">
    <?php include '../layout/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <h1 class="mb-4">Admin Dashboard</h1>

            <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($errorMsg); ?></div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="dashboard-card p-3 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-box-open stat-icon me-3"></i>
                            <h5 class="mb-0">Total Packages</h5>
                        </div>
                        <p class="display-4 mb-3"><?php echo $totalPackages; ?></p>
                        <a href="packages.php" class="btn btn-primary btn-block">
                            <i class="fas fa-cog me-2"></i>Manage Packages
                        </a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="dashboard-card p-3 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-shopping-cart stat-icon me-3"></i>
                            <h5 class="mb-0">Active Bookings</h5>
                        </div>
                        <p class="display-4 mb-3"><?php echo $activeOrders; ?></p>
                        <a href="orders.php" class="btn btn-primary btn-block">
                            <i class="fas fa-eye me-2"></i>View Bookings
                        </a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="dashboard-card p-3 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-users stat-icon me-3"></i>
                            <h5 class="mb-0">Registered Users</h5>
                        </div>
                        <p class="display-4 mb-3"><?php echo $totalUsers; ?></p>
                        <a href="users.php" class="btn btn-primary btn-block">
                            <i class="fas fa-user-cog me-2"></i>Manage Users
                        </a>
                    </div>
                </div>
            </div>

            <!-- Booking Status Summary -->
            <div class="status-overview">
                <h5>Booking Status Overview</h5>
                <div>
                    <span class="badge bg-warning text-dark status-badge">Pending: <?php echo $statusCounts['pending']; ?></span>
                    <span class="badge bg-success text-white status-badge">Approved: <?php echo $statusCounts['approved']; ?></span>
                    <span class="badge bg-danger text-white status-badge">Rejected: <?php echo $statusCounts['rejected']; ?></span>
                    <span class="badge bg-info text-white status-badge">Completed: <?php echo $statusCounts['completed']; ?></span>
                    <span class="badge bg-secondary text-white status-badge">Cancelled: <?php echo $statusCounts['cancelled']; ?></span>
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

            <!-- Quick Links -->
            <div class="quick-links">
                <h5>Quick Actions</h5>
                <a href="orders.php" class="btn btn-outline-primary btn-sm"><i class="fas fa-list"></i> View All Orders</a>
                <a href="add_package.php" class="btn btn-outline-success btn-sm"><i class="fas fa-plus"></i> Add New Package</a>
                <a href="announcements.php" class="btn btn-outline-info btn-sm"><i class="fas fa-bullhorn"></i> Manage Announcements</a>
            </div>
        </div>
    </div>

    <!-- Modal for Booking Details -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" style="display: none;" inert>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel">Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="bookingModalBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a id="goToOrdersBtn" href="orders.php" class="btn btn-primary">Go to Orders</a>
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