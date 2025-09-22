<?php
require '../db.php';
session_start();
// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

// Fetch categories for dropdown
$categories = $db->query("SELECT * FROM menu_categories WHERE status='active'")->fetchAll(PDO::FETCH_ASSOC);

// Handle AJAX form submission for adding menus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];

    $service_type = isset($_POST['service_type']) ? trim($_POST['service_type']) : '';
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $max_quantity = isset($_POST['max_quantity']) ? intval($_POST['max_quantity']) : 1;
    $status = isset($_POST['status']) ? $_POST['status'] : 'active';
    $image = isset($_FILES['image']) ? $_FILES['image'] : null;

    // Validate inputs
    $errors = [];
    if (empty($service_type)) $errors[] = "Service type is required";
    if ($category_id <= 0) $errors[] = "Category is required";
    if (empty($title)) $errors[] = "Title is required";
    if (empty($description)) $errors[] = "Description is required";
    if (!$image || empty($image['name'])) $errors[] = "Image is required";

    if (empty($errors)) {
        // Handle file upload
        $targetDir = "./uploads/";
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                $errors[] = "Failed to create uploads directory";
            }
        }

        if (empty($errors)) {
            $targetFile = $targetDir . time() . '_' . str_replace(' ', '_', basename($image["name"]));
            $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            // Check file size (limit to 5MB)
            if ($image["size"] > 5000000) {
                $errors[] = "Sorry, your file is too large.";
            } elseif (!in_array($fileType, ["jpg", "png", "jpeg"])) {
                $errors[] = "Sorry, only JPG, JPEG, and PNG files are allowed.";
            } elseif (move_uploaded_file($image["tmp_name"], $targetFile)) {
                try {
                    $stmt = $db->prepare("INSERT INTO menus (service_type, category_id, title, description, price, max_quantity, status, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$service_type, $category_id, $title, $description, $price, $max_quantity, $status, $targetFile]);
                    $response['success'] = true;
                    $response['message'] = "The menu item has been added successfully";
                } catch (PDOException $e) {
                    $errors[] = "Error adding menu item: " . $e->getMessage();
                }
            } else {
                $errors[] = "Sorry, there was an error uploading your file. Check directory permissions.";
            }
        }
    }

    if (!empty($errors)) {
        $response['message'] = implode('. ', $errors);
    }

    echo json_encode($response);
    exit();
}

// Handle AJAX form submission for editing menus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $service_type = isset($_POST['service_type']) ? trim($_POST['service_type']) : '';
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $max_quantity = isset($_POST['max_quantity']) ? intval($_POST['max_quantity']) : 1;
    $status = isset($_POST['status']) ? $_POST['status'] : 'active';
    $image = isset($_FILES['image']) ? $_FILES['image'] : null;

    // Validate inputs
    $errors = [];
    if ($id <= 0) $errors[] = "Invalid menu item ID";
    if (empty($service_type)) $errors[] = "Service type is required";
    if ($category_id <= 0) $errors[] = "Category is required";
    if (empty($title)) $errors[] = "Title is required";
    if (empty($description)) $errors[] = "Description is required";

    if (empty($errors)) {
        try {
            // Fetch existing image path
            $stmt = $db->prepare("SELECT image_path FROM menus WHERE id = ?");
            $stmt->execute([$id]);
            $existing_menu = $stmt->fetch(PDO::FETCH_ASSOC);

            $targetFile = $existing_menu['image_path'];

            // Handle file upload if a new image is provided
            if ($image && !empty($image['name'])) {
                $targetDir = "./uploads/";
                if (!is_dir($targetDir)) {
                    if (!mkdir($targetDir, 0755, true)) {
                        $errors[] = "Failed to create uploads directory";
                    }
                }

                if (empty($errors)) {
                    $targetFile = $targetDir . time() . '_' . str_replace(' ', '_', basename($image["name"]));
                    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

                    // Check file size (limit to 5MB)
                    if ($image["size"] > 5000000) {
                        $errors[] = "Sorry, your file is too large.";
                    } elseif (!in_array($fileType, ["jpg", "png", "jpeg"])) {
                        $errors[] = "Sorry, only JPG, JPEG, and PNG files are allowed.";
                    } elseif (move_uploaded_file($image["tmp_name"], $targetFile)) {
                        // Delete old image if it exists
                        if ($existing_menu && file_exists($existing_menu['image_path'])) {
                            unlink($existing_menu['image_path']);
                        }
                    } else {
                        $errors[] = "Sorry, there was an error uploading your file. Check directory permissions.";
                    }
                }
            }

            if (empty($errors)) {
                $stmt = $db->prepare("UPDATE menus SET service_type = ?, category_id = ?, title = ?, description = ?, price = ?, max_quantity = ?, status = ?, image_path = ? WHERE id = ?");
                $stmt->execute([$service_type, $category_id, $title, $description, $price, $max_quantity, $status, $targetFile, $id]);
                $response['success'] = true;
                $response['message'] = "The menu item has been updated successfully";
            }
        } catch (PDOException $e) {
            $errors[] = "Error updating menu item: " . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        $response['message'] = implode('. ', $errors);
    }

    echo json_encode($response);
    exit();
}

// Handle AJAX delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];

    $id = $_GET['id'];

    try {
        $stmt = $db->prepare("SELECT image_path FROM menus WHERE id = ?");
        $stmt->execute([$id]);
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($menu && file_exists($menu['image_path'])) {
            unlink($menu['image_path']);
        }

        $stmt = $db->prepare("DELETE FROM menus WHERE id = ?");
        $stmt->execute([$id]);
        $response['success'] = true;
        $response['message'] = "Menu item deleted successfully";
    } catch (PDOException $e) {
        $response['message'] = "Error deleting menu item: " . $e->getMessage();
    }

    echo json_encode($response);
    exit();
}

// Handle filter parameters
$service_filter = isset($_GET['service_type']) ? trim($_GET['service_type']) : 'all';
$category_filter = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

// Build the base query
$query = "SELECT m.*, c.category_name FROM menus m LEFT JOIN menu_categories c ON m.category_id = c.category_id";
$params = [];
$where = [];
if ($service_filter !== 'all') {
    $where[] = "m.service_type = ?";
    $params[] = $service_filter;
}
if ($category_filter) {
    $where[] = "m.category_id = ?";
    $params[] = $category_filter;
}
if ($where) {
    $query .= " WHERE " . implode(' AND ', $where);
}
$query .= " ORDER BY m.service_type, c.category_name";
try {
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['errors'] = ["Unable to load menus: " . $e->getMessage()];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menus - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/admin.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        /* General Body and Main Content */
        body {
            background-color: #f0f2f5;
            font-family: 'Poppins', sans-serif;
        }

        .main-content {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }

        /* Container for Sections */
        .card-section {
            background-color: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .card-section h1, .card-section h2 {
            font-weight: 700;
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 1.75rem;
        }

        /* Form Styling */
        .form-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .form-control, .form-select {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border: 1px solid #ddd;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
        }

        .input-group-text {
            background-color: #e9ecef;
            border-radius: 8px 0 0 8px;
            border: 1px solid #ddd;
            font-size: 1rem;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: background-color 0.3s, border-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }

        /* Filter Section */
        .filter-container {
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 1rem;
        }

        .filter-container .form-group {
            flex: 1;
            min-width: 200px;
            margin-bottom: 0;
        }

        .filter-actions {
            display: flex;
            gap: 0.75rem;
        }

        /* Menu Cards */
        .menu-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .menu-card .card-img-top {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 12px 12px 0 0;
        }

        .menu-card .card-body {
            padding: 1.5rem;
        }

        .menu-card .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .menu-card .card-text {
            font-size: 0.95rem;
            color: #666;
            min-height: 48px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .menu-card .card-footer {
            background: #fafafa;
            border-top: 1px solid #eee;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .menu-card .badge {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.5em 0.75em;
            border-radius: 50rem;
        }

        .menu-card .price-tag {
            font-size: 1.1rem;
            font-weight: 700;
            color: #28a745;
        }
        
        .btn-edit, .btn-delete {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        .btn-delete {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-delete:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        
        /* Modals */
        .modal-content {
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            border-bottom: none;
            padding: 1.5rem;
            background-color: #f8f9fa;
            border-radius: 12px 12px 0 0;
        }

        .modal-title {
            font-weight: 700;
            color: #333;
        }

        .modal-body {
            padding: 1.5rem;
        }

        /* Responsive adjustments for elderly-friendly design */
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .card-section, .filter-container {
                padding: 1.5rem;
            }

            .card-section h1, .card-section h2 {
                font-size: 1.5rem;
            }
            
            .form-control, .form-select, .btn {
                font-size: 1rem;
                padding: 0.8rem 1rem;
            }

            .filter-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-container .form-group {
                min-width: 100%;
            }

            .menu-card .card-img-top {
                height: 180px;
            }

            .menu-card .card-title {
                font-size: 1.25rem;
            }
            
            .menu-card .card-text, .menu-card .badge {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
<?php include '../layout/sidebar.php'; ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="card-section">
                <h1>Add Menu Item</h1>
                <form id="addMenuForm" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="service_type" class="form-label">Service Type</label>
                            <select class="form-select" name="service_type" id="service_type" required>
                                <option value="" disabled selected>Select Service Type</option>
                                <option value="wedding">Wedding</option>
                                <option value="corporate">Corporate</option>
                                <option value="private">Private</option>
                                <option value="debut">Debut</option>
                                <option value="childrens">Children's Party</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" name="category_id" id="category_id" required>
                                <option value="" disabled selected>Select Category</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['category_id']; ?>">
                                            <?php echo htmlspecialchars($cat['category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No categories available</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" id="title" required>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="description" rows="3" required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label for="price" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" name="price" id="price" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="max_quantity" class="form-label">Max Quantity</label>
                            <input type="number" class="form-control" name="max_quantity" id="max_quantity" min="1" value="1" required>
                        </div>
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" name="status" id="status" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control" name="image" id="image" required>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">Add Menu Item</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="filter-container">
                <div class="form-group">
                    <label for="service_type_filter">Service Type</label>
                    <select class="form-select" id="service_type_filter">
                        <option value="all" <?php echo $service_filter === 'all' ? 'selected' : ''; ?>>All Services</option>
                        <option value="wedding" <?php echo $service_filter === 'wedding' ? 'selected' : ''; ?>>Wedding</option>
                        <option value="corporate" <?php echo $service_filter === 'corporate' ? 'selected' : ''; ?>>Corporate</option>
                        <option value="private" <?php echo $service_filter === 'private' ? 'selected' : ''; ?>>Private</option>
                        <option value="debut" <?php echo $service_filter === 'debut' ? 'selected' : ''; ?>>Debut</option>
                        <option value="childrens" <?php echo $service_filter === 'childrens' ? 'selected' : ''; ?>>Children's Party</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="category_filter">Category</label>
                    <select class="form-select" id="category_filter">
                        <option value="0" <?php echo !$category_filter ? 'selected' : ''; ?>>All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php echo $category_filter == $cat['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="button" class="btn btn-primary" onclick="applyFilters()">Apply Filters</button>
                    <a href="menus.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>

            <div class="card-section">
                <h2>Filtered Menu Items</h2>
                <div class="row g-4" id="menuList">
                    <?php if (empty($menus)): ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center">No menu items found.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($menus as $menu): ?>
                            <div class="col-md-6 col-lg-4 d-flex">
                                <div class="card menu-card flex-grow-1" data-id="<?php echo $menu['id']; ?>">
                                    <img src="<?php echo htmlspecialchars($menu['image_path']); ?>" class="card-img-top" alt="Menu Image">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?php echo htmlspecialchars($menu['title']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($menu['description']); ?></p>
                                        <div class="d-flex flex-wrap gap-2 mb-3 mt-auto">
                                            <span class="badge bg-primary"><?php echo ucfirst($menu['service_type']); ?></span>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($menu['category_name'] ?: 'N/A'); ?></span>
                                            <span class="badge bg-success">Max: <?php echo $menu['max_quantity']; ?></span>
                                            <span class="badge bg-<?php echo $menu['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($menu['status']); ?></span>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <span class="price-tag">₱<?php echo number_format($menu['price'], 2); ?></span>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-primary edit-btn" data-id="<?php echo $menu['id']; ?>" data-service_type="<?php echo htmlspecialchars($menu['service_type']); ?>" data-category_id="<?php echo $menu['category_id']; ?>" data-title="<?php echo htmlspecialchars($menu['title']); ?>" data-description="<?php echo htmlspecialchars($menu['description']); ?>" data-price="<?php echo $menu['price']; ?>" data-max_quantity="<?php echo $menu['max_quantity']; ?>" data-status="<?php echo $menu['status']; ?>" data-image_path="<?php echo htmlspecialchars($menu['image_path']); ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $menu['id']; ?>">
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

    <div class="modal fade" id="editMenuModal" tabindex="-1" aria-labelledby="editMenuModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMenuModalLabel">Edit Menu Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editMenuForm" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_service_type" class="form-label">Service Type</label>
                                <select class="form-select" name="service_type" id="edit_service_type" required>
                                    <option value="" disabled>Select Service Type</option>
                                    <option value="wedding">Wedding</option>
                                    <option value="corporate">Corporate</option>
                                    <option value="private">Private</option>
                                    <option value="debut">Debut</option>
                                    <option value="childrens">Children's Party</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_category_id" class="form-label">Category</label>
                                <select class="form-select" name="category_id" id="edit_category_id" required>
                                    <option value="" disabled>Select Category</option>
                                    <?php if (!empty($categories)): ?>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['category_id']; ?>">
                                                <?php echo htmlspecialchars($cat['category_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>No categories available</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="edit_title" class="form-label">Title</label>
                                <input type="text" class="form-control" name="title" id="edit_title" required>
                            </div>
                            <div class="col-12">
                                <label for="edit_description" class="form-label">Description</label>
                                <textarea class="form-control" name="description" id="edit_description" rows="3" required></textarea>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_price" class="form-label">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" name="price" id="edit_price" min="0" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_max_quantity" class="form-label">Max Quantity</label>
                                <input type="number" class="form-control" name="max_quantity" id="edit_max_quantity" min="1" required>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_status" class="form-label">Status</label>
                                <select class="form-select" name="status" id="edit_status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="edit_image" class="form-label">Image</label>
                                <input type="file" class="form-control" name="image" id="edit_image">
                                <img id="edit_image_preview" src="" class="img-fluid mt-2 rounded" style="max-height: 150px;" alt="Current Image">
                            </div>
                        </div>
                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary">Update Menu Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        // Updated JavaScript to handle the UI interactions
        // Handle add form submission with AJAX
        $('#addMenuForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                url: 'menus.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#addMenuForm')[0].reset();
                        applyFilters();
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
                            text: response.message,
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
                        text: 'Error adding menu item: ' + (xhr.responseJSON?.message || error),
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#dc3545",
                    }).showToast();
                }
            });
        });

        // Handle edit button click
        $(document).on('click', '.edit-btn', function() {
            const menuId = $(this).data('id');
            const serviceType = $(this).data('service_type');
            const categoryId = $(this).data('category_id');
            const title = $(this).data('title');
            const description = $(this).data('description');
            const price = $(this).data('price');
            const maxQuantity = $(this).data('max_quantity');
            const status = $(this).data('status');
            const imagePath = $(this).data('image_path');

            // Populate the edit form
            $('#edit_id').val(menuId);
            $('#edit_service_type').val(serviceType);
            $('#edit_category_id').val(categoryId);
            $('#edit_title').val(title);
            $('#edit_description').val(description);
            $('#edit_price').val(price);
            $('#edit_max_quantity').val(maxQuantity);
            $('#edit_status').val(status);
            $('#edit_image_preview').attr('src', imagePath);

            // Show the modal
            $('#editMenuModal').modal('show');
        });

        // Handle edit form submission with AJAX
        $('#editMenuForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                url: 'menus.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#editMenuModal').modal('hide');
                        $('#editMenuForm')[0].reset();
                        applyFilters();
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
                            text: response.message,
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
                        text: 'Error updating menu item: ' + (xhr.responseJSON?.message || error),
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#dc3545",
                    }).showToast();
                }
            });
        });

        // Handle delete with AJAX and confirmation
        $(document).on('click', '.delete-btn', function() {
            const menuId = $(this).data('id');
            if (confirm('Are you sure you want to delete this menu item?')) {
                $.ajax({
                    url: 'menus.php?action=delete&id=' + menuId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            applyFilters();
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
                                text: response.message,
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
                            text: 'Error deleting menu item: ' + (xhr.responseJSON?.message || error),
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

        // Function to apply filters and refresh the menu list
        function applyFilters() {
            const serviceType = $('#service_type_filter').val();
            const categoryId = $('#category_filter').val();
            window.location.href = `menus.php?service_type=${serviceType}&category_id=${categoryId}`;
        }
    </script>
</body>
</html>