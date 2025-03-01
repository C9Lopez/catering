<?php
require '../db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

// Handle form submission for posting menus
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_type = $_POST['service_type'];
    $category = $_POST['category'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $image = $_FILES['image'];

    // Validate inputs
    if (empty($service_type) || empty($category) || empty($title) || empty($description) || empty($image['name'])) {
        die("Please fill in all fields.");
    }

    // Handle file upload
    $targetDir = "./uploads/";
    $targetFile = $targetDir . time() . '_' . str_replace(' ', '_', basename($image["name"]));
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check file size (limit to 5MB)
    if ($image["size"] > 5000000) {
        die("Sorry, your file is too large.");
    }

    // Allow certain file formats
    if ($fileType != "jpg" && $fileType != "png" && $fileType != "jpeg") {
        die("Sorry, only JPG, JPEG, and PNG files are allowed.");
    }

    // Attempt to move the uploaded file
    if (move_uploaded_file($image["tmp_name"], $targetFile)) {
        // Insert menu into the database
        $stmt = $db->prepare("INSERT INTO menus (service_type, category, title, description, image_path) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$service_type, $category, $title, $description, $targetFile]);
        echo "<script>alert('The menu item has been added successfully.');</script>";
    } else {
        die("Sorry, there was an error uploading your file.");
    }
}

// Handle delete action with confirmation
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $stmt = $db->prepare("DELETE FROM menus WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Menu item deleted successfully";
        header("Location: menus.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting menu item: " . $e->getMessage();
        header("Location: menus.php");
        exit();
    }
}

// Handle filter parameters
$service_filter = isset($_GET['service_type']) ? $_GET['service_type'] : 'all';
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';

// Build the base query
$query = "SELECT * FROM menus";
$params = [];

// Add filters if not 'all'
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

// Add ordering
$query .= " ORDER BY service_type, category";

// Execute the query
try {
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Unable to load menus: " . $e->getMessage();
}

// Display session messages
if (isset($_SESSION['success'])) {
    echo "<script>alert('" . htmlspecialchars($_SESSION['success']) . "');</script>";
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo "<script>alert('" . htmlspecialchars($_SESSION['error']) . "');</script>";
    unset($_SESSION['error']);
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
    <style>
        .delete-btn {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            padding: 0;
            font-size: 1rem;
        }
        .delete-btn:hover {
            text-decoration: underline;
        }
        .confirm-delete {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        .confirm-delete button {
            margin: 0 5px;
            padding: 8px 16px;
            border-radius: 4px;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        @media (max-width: 768px) {
            .confirm-delete {
                width: 80%;
            }
        }
    </style>
</head>
<body>
<?php include '../layout/sidebar.php'; ?>
    <div class="main-content">
        <div class="container-fluid mt-5">
            <h1>Add Menu Item</h1>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="service_type" class="form-label">Service Type</label>
                    <select class="form-select" name="service_type" required>
                        <option value="wedding">Wedding</option>
                        <option value="corporate">Corporate</option>
                        <option value="private">Private</option>
                        <option value="debut">Debut</option>
                        <option value="childrens">Children's Party</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" name="category" required>
                        <option value="Appetizers">Appetizers</option>
                        <option value="Soups">Soups</option>
                        <option value="Main Course">Main Course</option>
                        <option value="Desserts">Desserts</option>
                        <option value="Beverages">Beverages</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" name="title" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" name="description" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Image</label>
                    <input type="file" class="form-control" name="image" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Menu Item</button>
            </form>

            <h2 class="mt-5">Filter Menu Items</h2>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="service_type_filter" class="form-label">Service Type</label>
                    <select class="form-select" id="service_type_filter" onchange="applyFilters()">
                        <option value="all">All Services</option>
                        <option value="wedding">Wedding</option>
                        <option value="corporate">Corporate</option>
                        <option value="private">Private</option>
                        <option value="debut">Debut</option>
                        <option value="childrens">Children's Party</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="category_filter" class="form-label">Category</label>
                    <select class="form-select" id="category_filter" onchange="applyFilters()">
                        <option value="all">All Categories</option>
                        <option value="Appetizers">Appetizers</option>
                        <option value="Soups">Soups</option>
                        <option value="Main Course">Main Course</option>
                        <option value="Desserts">Desserts</option>
                        <option value="Beverages">Beverages</option>
                    </select>
                </div>
            </div>

            <h2 class="mt-5">Filtered Menu Items</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Service Type</th>
                        <th>Category</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menus as $menu): ?>
                        <tr>
                            <td><?php echo ucfirst($menu['service_type']); ?></td>
                            <td><?php echo $menu['category']; ?></td>
                            <td><?php echo htmlspecialchars($menu['title']); ?></td>
                            <td><?php echo htmlspecialchars($menu['description']); ?></td>
                            <td>
                                <button class="delete-btn" onclick="showConfirmDelete(<?php echo $menu['id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Confirmation Dialog -->
    <div class="overlay" id="overlay"></div>
    <div class="confirm-delete" id="confirmDelete">
        <p>Are you sure you want to delete this menu item?</p>
        <button class="btn btn-danger" onclick="confirmDelete()">OK</button>
        <button class="btn btn-secondary" onclick="cancelDelete()">Cancel</button>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let menuIdToDelete = null;

        function showConfirmDelete(id) {
            menuIdToDelete = id;
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('confirmDelete').style.display = 'block';
        }

        function confirmDelete() {
            if (menuIdToDelete) {
                window.location.href = `menus.php?action=delete&id=${menuIdToDelete}`;
            }
            cancelDelete();
        }

        function cancelDelete() {
            menuIdToDelete = null;
            document.getElementById('overlay').style.display = 'none';
            document.getElementById('confirmDelete').style.display = 'none';
        }

        function applyFilters() {
            const serviceType = document.getElementById('service_type_filter').value;
            const category = document.getElementById('category_filter').value;
            window.location.href = `menus.php?service_type=${serviceType}&category=${category}`;
        }
        
        // Set initial filter values from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const serviceFilter = urlParams.get('service_type') || 'all';
        const categoryFilter = urlParams.get('category') || 'all';
        
        document.getElementById('service_type_filter').value = serviceFilter;
        document.getElementById('category_filter').value = categoryFilter;
    </script>
    <script src="../js/admin.js"></script>
</body>
</html>