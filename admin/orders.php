<?php
require '../db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';
$filterCustomer = isset($_GET['customer']) ? trim($_GET['customer']) : '';
$filterPackage = isset($_GET['package']) ? trim($_GET['package']) : '';
$filterDate = isset($_GET['date']) ? trim($_GET['date']) : '';

$sql = "SELECT eb.*, u.first_name, u.last_name, p.name as package_name, p.category as package_category 
        FROM event_bookings eb 
        JOIN users u ON eb.user_id = u.user_id 
        JOIN catering_packages p ON eb.package_id = p.package_id 
        WHERE 1=1";
$params = [];

if ($filterStatus) {
    $sql .= " AND eb.booking_status = :status";
    $params[':status'] = $filterStatus;
}
if ($filterCustomer) {
    $sql .= " AND (u.first_name LIKE :customer OR u.last_name LIKE :customer)";
    $params[':customer'] = "%" . $filterCustomer . "%";
}
if ($filterPackage) {
    $sql .= " AND p.name LIKE :package";
    $params[':package'] = "%" . $filterPackage . "%";
}
if ($filterDate) {
    $sql .= " AND eb.event_date = :date";
    $params[':date'] = $filterDate;
}

try {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching bookings: " . $e->getMessage());
    $bookings = [];
}

function getStatusClass($status) {
    switch ($status) {
        case 'pending': return 'bg-warning text-dark';
        case 'approved': return 'bg-success text-white';
        case 'rejected': return 'bg-danger text-white';
        default: return 'bg-secondary text-white';
    }
}

function formatCategory($category) {
    return str_replace('Catering', 'Party Catering', $category);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Orders Management - Catering Admin</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/admin.css" rel="stylesheet">
    <link href="../css/booking.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .filter-container {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .filter-container .form-group {
            margin-right: 15px;
            margin-bottom: 10px;
        }
        .filter-container label {
            font-weight: 500;
            margin-right: 10px;
        }
        .filter-container input, .filter-container select {
            width: 200px;
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ced4da;
        }
        .filter-container button {
            margin-top: 5px;
        }
        .status-btn {
            margin-right: 5px;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .category-badge {
            font-size: 0.85rem;
            padding: 4px 8px;
            border-radius: 12px;
            background-color: #e9ecef;
        }
        .chat-btn {
            position: relative;
        }
        .chat-btn .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.7rem;
            padding: 2px 5px;
        }
        @media (max-width: 768px) {
            .filter-container .form-group {
                margin-right: 0;
                width: 100%;
            }
            .filter-container input, .filter-container select {
                width: 100%;
            }
            .filter-container button {
                width: 100%;
            }
            .status-btn, .btn-sm {
                padding: 3px 10px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body class="admin-dashboard">
    <?php include '../layout/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
            <h1 class="mb-4">Orders Management</h1>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Booking List</h5>
                </div>
                <div class="card-body">
                    <div class="filter-container">
                        <form method="GET" class="row g-2">
                            <div class="col-md-3 col-12 form-group">
                                <label for="status">Status:</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="">All</option>
                                    <option value="pending" <?php echo $filterStatus === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?php echo $filterStatus === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo $filterStatus === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-12 form-group">
                                <label for="customer">Customer:</label>
                                <input type="text" id="customer" name="customer" class="form-control" value="<?php echo htmlspecialchars($filterCustomer); ?>" placeholder="Filter by customer name...">
                            </div>
                            <div class="col-md-3 col-12 form-group">
                                <label for="package">Package:</label>
                                <input type="text" id="package" name="package" class="form-control" value="<?php echo htmlspecialchars($filterPackage); ?>" placeholder="Filter by package name...">
                            </div>
                            <div class="col-md-3 col-12 form-group">
                                <label for="date">Event Date:</label>
                                <input type="date" id="date" name="date" class="form-control" value="<?php echo htmlspecialchars($filterDate); ?>">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="orders.php" class="btn btn-secondary">Clear Filters</a>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Customer</th>
                                    <th>Package</th>
                                    <th>Category</th>
                                    <th>Number of Guests</th>
                                    <th>Status</th>
                                    <th>Chat</th>
                                    <th>Total Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (empty($bookings)) {
                                    echo "<tr><td colspan='9' class='text-center text-muted'>No bookings found matching the filters.</td></tr>";
                                } else {
                                    foreach ($bookings as $row):
                                        $chatStmt = $db->prepare("SELECT COUNT(*) as unread FROM chat_messages WHERE order_id = :booking_id AND sender = 'user' AND created_at > (SELECT IFNULL(MAX(created_at), '1970-01-01') FROM chat_messages WHERE order_id = :booking_id AND sender = 'admin')");
                                        $chatStmt->execute([':booking_id' => $row['booking_id']]);
                                        $unreadCount = $chatStmt->fetch(PDO::FETCH_ASSOC)['unread'];
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['booking_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['package_name']); ?></td>
                                            <td><span class="category-badge"><?php echo htmlspecialchars(formatCategory($row['package_category'])); ?></span></td>
                                            <td><?php echo htmlspecialchars($row['number_of_guests'] ?? 'Not set'); ?></td>
                                            <td>
                                                <form method="POST" action="update_booking_status.php" style="display: inline;">
                                                    <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                                    <select name="booking_status" class="status-btn <?php echo getStatusClass($row['booking_status']); ?>"
                                                        onchange="this.form.submit()">
                                                        <option value="pending" <?php echo $row['booking_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="approved" <?php echo $row['booking_status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                                        <option value="rejected" <?php echo $row['booking_status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td>
                                                <a href="chat.php?booking_id=<?php echo $row['booking_id']; ?>" class="btn btn-secondary btn-sm chat-btn">
                                                    <i class="fas fa-comments"></i> Chat
                                                    <?php if ($unreadCount > 0): ?>
                                                        <span class="badge bg-danger"><?php echo $unreadCount; ?></span>
                                                    <?php endif; ?>
                                                </a>
                                            </td>
                                            <td>₱<?php echo number_format($row['total_amount'], 2); ?></td>
                                            <td>
                                                <a href="view_order.php?id=<?php echo $row['booking_id']; ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <a href="update_booking.php?id=<?php echo $row['booking_id']; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i> Update
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach;
                                }
                                ?>
                            </tbody>
                        </table>
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