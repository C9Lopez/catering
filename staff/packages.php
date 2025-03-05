<?php
require '../db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

// Handle filtering via GET parameters
$filterType = isset($_GET['type']) ? trim($_GET['type']) : '';
$filterName = isset($_GET['name']) ? trim($_GET['name']) : '';
$filterPrice = isset($_GET['price']) ? floatval($_GET['price']) : '';
$filterGuests = isset($_GET['guests']) ? intval($_GET['guests']) : '';

// Build the SQL query with filters
$sql = "SELECT * FROM catering_packages WHERE 1=1";
$params = [];

if ($filterType) {
    $sql .= " AND category = :type";
    $params[':type'] = $filterType;
}
if ($filterName) {
    $sql .= " AND name LIKE :name";
    $params[':name'] = "%" . $filterName . "%";
}
if ($filterPrice > 0) {
    $sql .= " AND price = :price";
    $params[':price'] = $filterPrice;
}
if ($filterGuests > 0) {
    $sql .= " AND number_of_guests = :guests";
    $params[':guests'] = $filterGuests;
}

try {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching packages: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Packages Management - Catering Admin</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/admin.css" rel="stylesheet">
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
        }
    </style>
</head>
<body class="admin-dashboard">
    <?php include '../layout/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
            <h1 class="mb-4">Packages Management</h1>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Package List</h5>
                    <a href="add_package.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New Package
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filter Container -->
                    <div class="filter-container">
                        <form method="GET" class="row g-2">
                            <div class="col-md-3 col-12 form-group">
                                <label for="type">Type:</label>
                                <select id="type" name="type" class="form-control">
                                    <option value="">All</option>
                                    <option value="Wedding Catering" <?php echo $filterType === 'Wedding Catering' ? 'selected' : ''; ?>>Wedding Catering</option>
                                    <option value="Debut Catering" <?php echo $filterType === 'Debut Catering' ? 'selected' : ''; ?>>Debut Catering</option>
                                    <option value="Corporate Catering" <?php echo $filterType === 'Corporate Catering' ? 'selected' : ''; ?>>Corporate Catering</option>
                                    <option value="Private Catering" <?php echo $filterType === 'Private Catering' ? 'selected' : ''; ?>>Private Catering</option>
                                    <option value="Childrens Party Catering" <?php echo $filterType === 'Childrens Party Catering' ? 'selected' : ''; ?>>Childrens Party Catering</option>
                                    <option value="Special Event Catering" <?php echo $filterType === 'Special Event Catering' ? 'selected' : ''; ?>>Special Event Catering</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-12 form-group">
                                <label for="name">Name:</label>
                                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($filterName); ?>" placeholder="Filter by name...">
                            </div>
                            <div class="col-md-3 col-12 form-group">
                                <label for="price">Price:</label>
                                <input type="number" id="price" name="price" class="form-control" value="<?php echo htmlspecialchars($filterPrice); ?>" placeholder="Filter by price..." step="0.01">
                            </div>
                            <div class="col-md-3 col-12 form-group">
                                <label for="guests">Guests:</label>
                                <input type="number" id="guests" name="guests" class="form-control" value="<?php echo htmlspecialchars($filterGuests); ?>" placeholder="Filter by guests..." min="1">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="packages.php" class="btn btn-secondary">Clear Filters</a>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Number of Guests</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Display filtered packages
                                if (empty($packages)) {
                                    echo "<tr><td colspan='6' class='text-center text-muted'>No packages found matching the filters.</td></tr>";
                                } else {
                                    foreach ($packages as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                                            <td>₱<?php echo number_format($row['price'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($row['number_of_guests'] ?? 'Not set'); ?></td>
                                            <td>
                                                <a href="edit_package.php?id=<?php echo $row['package_id']; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="delete_package.php?id=<?php echo $row['package_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this package?')">
                                                    <i class="fas fa-trash"></i> Delete
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