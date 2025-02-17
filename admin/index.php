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

    // Get active orders
    // $stmt = $db->prepare("SELECT COUNT(*) as total FROM orders WHERE status = 'active'");
    // $stmt->execute();
    // $activeOrders = $stmt->fetchColumn();

    // Get total users
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM users");
    $stmt->execute();
    $totalUsers = $stmt->fetchColumn();

    // Get total revenue
    // $stmt = $db->prepare("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'");
    // $stmt->execute();
    // $totalRevenue = $stmt->fetchColumn() ?? 0;
    $activeOrders = 1;
    $totalRevenue =1;
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
                        <a href="booking.php" class="btn btn-primary btn-block">
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
                <div class="col-md-3 mb-4">
                    <div class="dashboard-card p-3 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-chart-line fa-2x me-3"></i>
                            <h5 class="mb-0">Revenue</h5>
                        </div>
                        <p class="display-4 mb-3">₱<?php echo number_format($totalRevenue, 2); ?></p>
                        <a href="reports.php" class="btn btn-primary btn-block">
                            <i class="fas fa-file-alt me-2"></i>View Reports
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php
                        try {
                            $stmt = $db->prepare("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 5");
                            $stmt->execute();
                            $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($activities as $activity): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div><?php echo htmlspecialchars($activity['description']); ?></div>
                                <small><?php echo date('M j, Y g:i a', strtotime($activity['created_at'])); ?></small>
                            </div>
                        </div>
                        <?php endforeach;
                        } catch (PDOException $e) {
                            echo '<div class="alert alert-warning">Unable to load recent activity</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>
</body>

</html>