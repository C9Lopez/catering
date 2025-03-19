<?php
require '../db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

// Handle AJAX filter request
if (isset($_GET['action']) && $_GET['action'] === 'filter') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'data' => [], 'message' => ''];

    $filterType = isset($_GET['type']) ? trim($_GET['type']) : '';

    $sql = "SELECT * FROM catering_packages WHERE 1=1";
    $params = [];

    if ($filterType) {
        $sql .= " AND category = :type";
        $params[':type'] = $filterType;
    }

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response['success'] = true;
        $response['data'] = $packages;
    } catch (PDOException $e) {
        $response['message'] = "Error fetching packages: " . $e->getMessage();
    }

    echo json_encode($response);
    exit();
}

// Handle delete action via AJAX
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];

    if (!isset($_SESSION['admin_id'])) {
        $response['message'] = 'Unauthorized access';
        echo json_encode($response);
        exit();
    }

    $package_id = $_POST['id'];

    try {
        // Check if package is referenced in event_bookings
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM event_bookings WHERE package_id = :id");
        $checkStmt->bindParam(':id', $package_id, PDO::PARAM_INT);
        $checkStmt->execute();
        $bookingCount = $checkStmt->fetchColumn();

        if ($bookingCount > 0) {
            $response['message'] = 'Cannot delete package: It is referenced in existing bookings.';
            echo json_encode($response);
            exit();
        }

        // Delete package
        $stmt = $db->prepare("DELETE FROM catering_packages WHERE package_id = :id");
        $stmt->bindParam(':id', $package_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $response['success'] = true;
            $response['message'] = 'Package deleted successfully';
        } else {
            $response['message'] = 'Package not found or already deleted';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Error deleting package: ' . $e->getMessage();
    }
    echo json_encode($response);
    exit();
}

// Initial load: Fetch all packages
$filterType = isset($_GET['type']) ? trim($_GET['type']) : '';

$sql = "SELECT * FROM catering_packages WHERE 1=1";
$params = [];

if ($filterType) {
    $sql .= " AND category = :type";
    $params[':type'] = $filterType;
}

try {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching packages: " . $e->getMessage();
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        .filter-container {
            margin-bottom: 25px;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .filter-container .form-group {
            margin-bottom: 15px;
        }
        .filter-container label {
            font-weight: 600;
            margin-bottom: 5px;
            display: block;
        }
        .filter-container select {
            width: 100%;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ddd;
            transition: border-color 0.3s ease;
        }
        .filter-container select:focus {
            border-color: #007bff;
            outline: none;
        }
        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .package-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .package-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }
        .package-card .card-header {
            background: #f8f9fa;
            border-radius: 10px 10px 0 0;
            padding: 15px;
        }
        .package-card .card-body {
            padding: 20px;
        }
        .package-card .card-title {
            color: #2c3e50;
            font-size: 1.25rem;
            margin-bottom: 15px;
        }
        .package-card .description {
            max-height: 100px;
            overflow-y: auto;
            font-size: 0.9rem;
            color: #555;
        }
        .package-card .card-footer {
            background: #fff;
            border-top: none;
            padding: 15px 20px;
        }
        .btn-delete {
            position: relative;
        }
        .btn-delete i {
            transition: transform 0.2s ease;
        }
        .btn-delete:hover i {
            transform: scale(1.2);
        }

        @media (max-width: 768px) {
            .filter-container {
                padding: 15px;
            }
            .filter-buttons {
                flex-direction: column;
            }
            .filter-buttons button, .filter-buttons a {
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
                        <i class="fas fa-plus"></i> Add Package
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filter Container -->
                    <div class="filter-container">
                        <div class="row g-3">
                            <div class="col-md-3 col-sm-6 form-group">
                                <label for="type">Category</label>
                                <select id="type" class="form-control">
                                    <option value="">All Categories</option>
                                    <option value="Wedding Catering" <?php echo $filterType === 'Wedding Catering' ? 'selected' : ''; ?>>Wedding Catering</option>
                                    <option value="Debut Catering" <?php echo $filterType === 'Debut Catering' ? 'selected' : ''; ?>>Debut Catering</option>
                                    <option value="Corporate Catering" <?php echo $filterType === 'Corporate Catering' ? 'selected' : ''; ?>>Corporate Catering</option>
                                    <option value="Private Catering" <?php echo $filterType === 'Private Catering' ? 'selected' : ''; ?>>Private Catering</option>
                                    <option value="Childrens Party Catering" <?php echo $filterType === 'Childrens Party Catering' ? 'selected' : ''; ?>>Childrens Party Catering</option>
                                    <option value="Special Event Catering" <?php echo $filterType === 'Special Event Catering' ? 'selected' : ''; ?>>Special Event Catering</option>
                                </select>
                            </div>
                            <div class="col-12 filter-buttons">
                                <button type="button" id="applyFilter" class="btn btn-primary">Apply Filter</button>
                                <button type="button" id="resetFilter" class="btn btn-outline-secondary">Reset</button>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="packageList">
                        <?php if (isset($error)): ?>
                            <div class="col-12">
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            </div>
                        <?php elseif (empty($packages)): ?>
                            <div class="col-12">
                                <div class="alert alert-info text-center">No packages found.</div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($packages as $row): ?>
                                <div class="col-md-4 col-sm-6 mb-4 package-item" data-id="<?php echo $row['package_id']; ?>">
                                    <div class="card h-100 package-card">
                                        <div class="card-header">
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($row['category']); ?></span>
                                            <?php if (!$row['is_available']): ?>
                                                <span class="badge bg-danger ms-2">Unavailable</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                                            <div class="description"><?php echo htmlspecialchars($row['description']); ?></div>
                                            <ul class="list-group list-group-flush mt-3">
                                                <li class="list-group-item">
                                                    <strong>Price:</strong> ₱<?php echo number_format($row['price'], 2); ?>
                                                </li>
                                                <li class="list-group-item">
                                                    <strong>Guests:</strong> <?php echo htmlspecialchars($row['number_of_guests'] ?? 'Not set'); ?>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="card-footer">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="edit_package.php?id=<?php echo $row['package_id']; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <button class="btn btn-danger btn-sm btn-delete" data-id="<?php echo $row['package_id']; ?>">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="../js/admin.js"></script>
    <script>
        // Display error messages from PHP using Toastify
        <?php if (isset($error)): ?>
            Toastify({
                text: "<?php echo $error; ?>",
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                backgroundColor: "#dc3545",
            }).showToast();
        <?php endif; ?>

        $(document).ready(function() {
            // Function to apply filter via AJAX
            function applyFilter() {
                const filterType = $('#type').val();

                $.ajax({
                    url: 'packages.php',
                    type: 'GET',
                    data: { action: 'filter', type: filterType },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            updatePackageList(response.data);
                        } else {
                            Toastify({
                                text: response.message || 'Error fetching packages',
                                duration: 3000,
                                close: true,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#dc3545",
                            }).showToast();
                        }
                    },
                    error: function(xhr, status, error) {
                        Toastify({
                            text: 'Error fetching packages: ' + (xhr.responseJSON?.message || error),
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#dc3545",
                        }).showToast();
                    }
                });
            }

            // Function to update the package list
            function updatePackageList(packages) {
                const $packageList = $('#packageList');
                $packageList.empty();

                if (packages.length === 0) {
                    $packageList.html('<div class="col-12"><div class="alert alert-info text-center">No packages found.</div></div>');
                    return;
                }

                packages.forEach(function(row) {
                    const isAvailable = row.is_available ? '' : '<span class="badge bg-danger ms-2">Unavailable</span>';
                    const html = `
                        <div class="col-md-4 col-sm-6 mb-4 package-item" data-id="${row.package_id}">
                            <div class="card h-100 package-card">
                                <div class="card-header">
                                    <span class="badge bg-primary">${row.category}</span>
                                    ${isAvailable}
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">${row.name}</h5>
                                    <div class="description">${row.description}</div>
                                    <ul class="list-group list-group-flush mt-3">
                                        <li class="list-group-item">
                                            <strong>Price:</strong> ₱${parseFloat(row.price).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')}
                                        </li>
                                        <li class="list-group-item">
                                            <strong>Guests:</strong> ${row.number_of_guests || 'Not set'}
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="edit_package.php?id=${row.package_id}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button class="btn btn-danger btn-sm btn-delete" data-id="${row.package_id}">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    $packageList.append(html);
                });
            }

            // Apply filter on button click
            $('#applyFilter').on('click', function(e) {
                e.preventDefault();
                applyFilter();
            });

            // Reset filter on button click
            $('#resetFilter').on('click', function(e) {
                e.preventDefault();
                $('#type').val('');
                applyFilter();
            });

            // Delete package via AJAX
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                const packageId = $(this).data('id');
                const $card = $(this).closest('.package-item');

                if (confirm('Are you sure you want to delete this package? This action cannot be undone.')) {
                    $.ajax({
                        url: 'packages.php',
                        type: 'POST',
                        data: { action: 'delete', id: packageId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                $card.fadeOut(300, function() {
                                    $(this).remove();
                                    if ($('#packageList').children().length === 0) {
                                        $('#packageList').html('<div class="col-12"><div class="alert alert-info text-center">No packages found.</div></div>');
                                    }
                                });
                                Toastify({
                                    text: response.message,
                                    duration: 3000,
                                    close: true,
                                    gravity: "top",
                                    position: "right",
                                    backgroundColor: "#28a745",
                                }).showToast();
                            } else {
                                Toastify({
                                    text: response.message || 'Failed to delete package',
                                    duration: 3000,
                                    close: true,
                                    gravity: "top",
                                    position: "right",
                                    backgroundColor: "#dc3545",
                                }).showToast();
                            }
                        },
                        error: function(xhr, status, error) {
                            Toastify({
                                text: 'Error deleting package: ' + (xhr.responseJSON?.message || error),
                                duration: 3000,
                                close: true,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#dc3545",
                            }).showToast();
                        }
                    });
                }
            });

            // Display success message from edit_package.php
            <?php if (isset($_GET['success']) && $_GET['success'] === 'update'): ?>
                Toastify({
                    text: "Package updated successfully",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28a745",
                }).showToast();
            <?php endif; ?>

            // Clear success parameter from URL after displaying
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.pathname + window.location.search.replace(/success=update(&|$)/, ''));
            }
        });
    </script>
</body>
</html>