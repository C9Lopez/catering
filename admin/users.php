<?php
require '../db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Management - Catering Admin</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/admin.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* General Body and Main Content */
        body {
            background-color: #f0f2f5;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        .main-content {
            padding-top: 2rem;
            padding-bottom: 2rem;
            margin-left: 250px;
            transition: margin-left 0.3s ease;
        }
        /* Mobile Header Styles */
        .mobile-header {
            display: none;
            background: #fff;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            align-items: center;
            gap: 1rem;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 100;
        }
        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #333;
            cursor: pointer;
            padding: 0.5rem;
        }
        .mobile-header-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
        }
        /* Sidebar Overlay */
        .sidebar-overlay {
            display: none;
        }
        @media (max-width: 991.98px) {
            .mobile-header {
                display: flex;
            }
            .main-content {
                padding-top: 4rem;
                margin-left: 0;
            }
            .sidebar {
                left: -250px !important;
            }
            .sidebar.active {
                left: 0 !important;
            }
            .sidebar-overlay {
                display: block;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
                z-index: 1000;
            }
            .sidebar-overlay.active {
                opacity: 1;
                visibility: visible;
            }
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            .card {
                padding: 1rem;
            }
            .table {
                font-size: 0.9rem;
            }
        }
        /* Desktop Sidebar */
        @media (min-width: 992px) {
            .sidebar {
                left: 0 !important;
            }
            .sidebar-overlay {
                display: none !important;
            }
        }
    </style>
</head>
<body class="admin-dashboard">
    <?php include '../layout/sidebar.php'; ?>
    
    <div class="main-content">
        <header class="mobile-header">
            <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <h2 class="mobile-header-title">User Management</h2>
        </header>
        <div class="container-fluid">
            <h1 class="mb-4">   </h1>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">User List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch users from database
                                $stmt = $db->prepare("SELECT * FROM users");
                                $stmt->execute();
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['contact_no']); ?></td>
                                        <td>
                                            <span class="badge 
                                                <?php echo $row['status'] === 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        
                                        
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>
    <script>
        $(document).ready(function() {
            // Sidebar Toggle Functionality for Mobile
            $('#sidebarToggle').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#sidebar').addClass('active');
                $('#sidebarOverlay').addClass('active');
            });

            $('#sidebarOverlay, #sidebarClose').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#sidebar').removeClass('active');
                $('#sidebarOverlay').removeClass('active');
            });

            // Prevent clicks inside sidebar from closing it
            $('#sidebar').on('click', function(e) {
                e.stopPropagation();
            });
        });
    </script>
</body>
</html>