<?php
require '../db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $number_of_guests = trim($_POST['number_of_guests']) ?? null;

    // Validate inputs
    $errors = [];
    if (empty($name)) $errors[] = "Package name is required";
    if (empty($description)) $errors[] = "Package description is required";
    if (!is_numeric($price) || $price <= 0) $errors[] = "Invalid price";
    if (!is_numeric($number_of_guests) || $number_of_guests <= 0) $errors[] = "Number of guests must be a positive number";

    if (empty($errors)) {
        try {
            // Insert new package with number_of_guests
            $stmt = $db->prepare("INSERT INTO catering_packages (category, name, description, price, number_of_guests) VALUES (:category, :name, :description, :price, :number_of_guests)");
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':number_of_guests', $number_of_guests, PDO::PARAM_INT);
            $stmt->execute();

            // Redirect with success parameter instead of session
            header("Location: add_package.php?success=1");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Error adding package: " . $e->getMessage();
        }
    } else {
        // Store errors in session for display
        $_SESSION['errors'] = $errors;
        header("Location: add_package.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Package - Catering Admin</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
        .form-label {
            font-weight: 600;
        }
        .form-text {
            font-size: 0.875rem;
        }
        .btn-group {
            gap: 10px;
        }
        @media (max-width: 768px) {
            .btn-group {
                flex-direction: column;
                gap: 10px;
            }
            .btn-group .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body class="admin-dashboard">
    <?php include '../layout/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
            <h1 class="mb-4">Add New Package</h1>
            
            <div class="form-section">
                <form method="POST">
                    <div class="mb-3">
                        <label for="category" class="form-label">Package Type</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select Package Type</option>
                            <option value="Wedding Catering">Wedding Catering</option>
                            <option value="Debut Catering">Debut Catering</option>
                            <option value="Corporate Catering">Corporate Catering</option>
                            <option value="Private Catering">Private Catering</option>
                            <option value="Childrens Party Catering">Childrens Party Catering</option>
                            <option value="Special Event Catering">Special Event Catering</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Package Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <small class="form-text text-muted">Example: Silver Package, Gold Package, Platinum Package</small>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="number_of_guests" class="form-label">Number of Guests</label>
                        <input type="number" class="form-control" id="number_of_guests" name="number_of_guests" min="1" required>
                        <small class="form-text text-muted">Set the fixed number of guests for this package</small>
                    </div>
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">Add Package</button>
                        <a href="packages.php" class="btn btn-secondary">Cancel</a>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addOptionModal">Add New Item/Option</button>
                    </div>
                </form>
            </div>

            <!-- Add Option Modal -->
            <div class="modal fade" id="addOptionModal" tabindex="-1" aria-labelledby="addOptionModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post" action="customization_options.php" enctype="multipart/form-data">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addOptionModalLabel">Add New Item/Option</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="action" value="add">
                                <!-- Category is now automatic and hidden from the modal -->
                                <?php
                                // Map package type to category name (adjust as needed)
                                $packageTypeToCategory = [
                                    'Wedding Catering' => 'Wedding',
                                    'Debut Catering' => 'Debut',
                                    'Corporate Catering' => 'Corporate',
                                    'Private Catering' => 'Private',
                                    'Childrens Party Catering' => 'Children',
                                    'Special Event Catering' => 'Special Event',
                                ];
                                $currentType = $_POST['category'] ?? '';
                                $categories = $db->query('SELECT * FROM customization_categories WHERE status="active"')->fetchAll(PDO::FETCH_ASSOC);
                                $selectedCategoryId = '';
                                foreach ($categories as $cat) {
                                    if (strcasecmp($cat['category_name'], $packageTypeToCategory[$currentType] ?? $currentType) === 0) {
                                        $selectedCategoryId = $cat['category_id'];
                                        break;
                                    }
                                }
                                ?>
                                <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($selectedCategoryId); ?>">
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="option_name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="description" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Price</label>
                                    <input type="number" name="price" class="form-control" min="0" step="0.01" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Max Qty</label>
                                    <input type="number" name="max_quantity" class="form-control" min="1" value="1" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Image</label>
                                    <input type="file" name="image" class="form-control">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Add Item/Option</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="../js/admin.js"></script>
    <script>
        // Display success or error messages using Toastify
        <?php
        if (isset($_SESSION['errors'])) {
            foreach ($_SESSION['errors'] as $error) {
                echo "Toastify({
                    text: '" . addslashes($error) . "',
                    duration: 3000,
                    close: true,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: '#dc3545',
                }).showToast();";
            }
            unset($_SESSION['errors']);
        }
        if (isset($_GET['success']) && $_GET['success'] == 1) {
            echo "Toastify({
                text: 'Package added successfully',
                duration: 3000,
                close: true,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#28a745',
            }).showToast();";
        }
        ?>

        // Optional: Clear success parameter from URL after displaying
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.pathname);
        }
    </script>
</body>
</html>