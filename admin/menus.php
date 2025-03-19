<?php
require '../db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

// Handle AJAX form submission for posting menus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];

    // Debug: Log incoming request data
    error_log("POST Data: " . print_r($_POST, true));
    error_log("FILES Data: " . print_r($_FILES, true));

    $service_type = isset($_POST['service_type']) ? trim($_POST['service_type']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $image = isset($_FILES['image']) ? $_FILES['image'] : null;

    // Validate inputs
    $errors = [];
    if (empty($service_type)) $errors[] = "Service type is required";
    if (empty($category)) $errors[] = "Category is required";
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
                    $stmt = $db->prepare("INSERT INTO menus (service_type, category, title, description, image_path) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$service_type, $category, $title, $description, $targetFile]);
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
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : 'all';

// Build the base query
$query = "SELECT * FROM menus";
$params = [];

if ($service_filter !== 'all') {
    $query .= " WHERE service_type = ?";
    $params[] = $service_filter;
    if ($category_filter !== 'all') {
        $query .= " AND category = ?";
        $params[] = $category_filter;
    }
} elseif ($category_filter !== 'all') {
    $query .= " WHERE category = ?";
    $params[] = $category_filter;
}

$query .= " ORDER BY service_type, category";

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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Menus - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/admin.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        .form-section {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .filter-container {
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .filter-container .form-group {
            flex: 1;
            min-width: 200px;
            margin-bottom: 0;
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
        .filter-container .filter-actions {
            display: flex;
            gap: 10px;
        }

        .menu-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 20px;
        }
        .menu-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
        .menu-card .card-header {
            background: #f8f9fa;
            border-radius: 10px 10px 0 0;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .menu-card .card-body {
            padding: 20px;
        }
        .menu-card .card-title {
            color: #2c3e50;
            font-size: 1.25rem;
            margin-bottom: 15px;
        }
        .menu-card .card-text {
            font-size: 0.9rem;
            color: #555;
            max-height: 100px;
            overflow-y: auto;
        }
        .menu-card .card-img-top {
            border-radius: 10px 10px 0 0;
            object-fit: cover;
            height: 200px;
            width: 100%;
        }
        .menu-card .card-footer {
            background: #fff;
            border-top: none;
            padding: 15px 20px;
            display: flex;
            justify-content: flex-end;
        }

        @media (max-width: 768px) {
            .filter-container {
                flex-direction: column;
                align-items: stretch;
            }
            .filter-container .form-group {
                min-width: 100%;
            }
            .menu-card {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
<?php include '../layout/sidebar.php'; ?>
<div class="main-content">
    <div class="container-fluid mt-5">
        <h1>Add Menu Item</h1>
        <div class="form-section">
            <form id="addMenuForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div class="mb-3">
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
                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" name="category" id="category" required>
                        <option value="" disabled selected>Select Category</option>
                        <option value="Appetizers">Appetizers</option>
                        <option value="Soups">Soups</option>
                        <option value="Main Course">Main Course</option>
                        <option value="Desserts">Desserts</option>
                        <option value="Beverages">Beverages</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" name="title" id="title" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" name="description" id="description" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Image</label>
                    <input type="file" class="form-control" name="image" id="image" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Menu Item</button>
            </form>
        </div>

        <h2 class="mt-5">Filter Menu Items</h2>
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
                    <option value="all" <?php echo $category_filter === 'all' ? 'selected' : ''; ?>>All Categories</option>
                    <option value="Appetizers" <?php echo $category_filter === 'Appetizers' ? 'selected' : ''; ?>>Appetizers</option>
                    <option value="Soups" <?php echo $category_filter === 'Soups' ? 'selected' : ''; ?>>Soups</option>
                    <option value="Main Course" <?php echo $category_filter === 'Main Course' ? 'selected' : ''; ?>>Main Course</option>
                    <option value="Desserts" <?php echo $category_filter === 'Desserts' ? 'selected' : ''; ?>>Desserts</option>
                    <option value="Beverages" <?php echo $category_filter === 'Beverages' ? 'selected' : ''; ?>>Beverages</option>
                </select>
            </div>
            <div class="filter-actions">
                <button type="button" class="btn btn-primary" onclick="applyFilters()">Apply Filters</button>
                <a href="menus.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>

        <h2 class="mt-5">Filtered Menu Items</h2>
        <div class="row" id="menuList">
            <?php if (empty($menus)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">No menu items found.</div>
                </div>
            <?php else: ?>
                <?php foreach ($menus as $menu): ?>
                    <div class="col-md-4 col-sm-6 mb-4 menu-item" data-id="<?php echo $menu['id']; ?>">
                        <div class="card menu-card">
                            <img src="<?php echo htmlspecialchars($menu['image_path']); ?>" class="card-img-top" alt="Menu Image">
                            <div class="card-header">
                                <span class="badge bg-primary"><?php echo ucfirst($menu['service_type']); ?></span>
                                <span class="badge bg-secondary"><?php echo $menu['category']; ?></span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($menu['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($menu['description']); ?></p>
                            </div>
                            <div class="card-footer">
                                <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $menu['id']; ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="../js/admin.js"></script>
<script>
    // Handle form submission with AJAX
    $('#addMenuForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        console.log("Submitting form...");
        console.log("FormData:", formData);

        $.ajax({
            url: 'menus.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                console.log("Success response:", response);
                if (response.success) {
                    $('#addMenuForm')[0].reset(); // Clear the form
                    applyFilters(); // Refresh the menu list
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
                console.error("Error response:", xhr.responseText);
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

    // Handle delete with AJAX and confirmation
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        const menuId = $(this).data('id');
        const $card = $(this).closest('.menu-item');

        if (confirm('Are you sure you want to delete this menu item? This action cannot be undone.')) {
            $.ajax({
                url: 'menus.php?action=delete&id=' + menuId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log("Delete response:", response);
                    if (response.success) {
                        $card.fadeOut(300, function() {
                            $(this).remove();
                            if ($('#menuList').children().length === 0) {
                                $('#menuList').html('<div class="col-12"><div class="alert alert-info text-center">No menu items found.</div></div>');
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
                            text: response.message || 'Error deleting menu item',
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#dc3545",
                        }).showToast();
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Delete error:", xhr.responseText);
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

    // Apply filters with AJAX
    function applyFilters() {
        const serviceType = $('#service_type_filter').val();
        const category = $('#category_filter').val();

        $.ajax({
            url: 'menus.php',
            type: 'GET',
            data: { service_type: serviceType, category: category },
            dataType: 'html',
            success: function(response) {
                $('#menuList').html($(response).find('#menuList').html());
                // Update URL without reloading
                if (history.pushState) {
                    const newUrl = `menus.php?service_type=${serviceType}&category=${category}`;
                    window.history.pushState({ path: newUrl }, '', newUrl);
                }
            },
            error: function() {
                Toastify({
                    text: 'Error applying filters',
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#dc3545",
                }).showToast();
            }
        });
    }
</script>
</body>
</html>