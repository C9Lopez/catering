<?php
require '../db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid package ID";
    header("Location: packages.php");
    exit();
}

$package_id = $_GET['id'];

try {
    $stmt = $db->prepare("SELECT * FROM catering_packages WHERE package_id = :package_id");
    $stmt->bindParam(':package_id', $package_id, PDO::PARAM_INT);
    $stmt->execute();
    $package = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$package) {
        $_SESSION['error'] = "Package not found";
        header("Location: packages.php");
        exit();
    }

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
            $stmt = $db->prepare("UPDATE catering_packages SET category = :category, name = :name, description = :description, price = :price, number_of_guests = :number_of_guests WHERE package_id = :package_id");
            $stmt->execute([
                ':category' => $category,
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':number_of_guests' => $number_of_guests,
                ':package_id' => $package_id
            ]);

            // Handle customization option assignments
            if (isset($_POST['customization_options']) && is_array($_POST['customization_options'])) {
                $selectedOptions = array_map('intval', $_POST['customization_options']);
            } else {
                $selectedOptions = [];
            }
            // Remove all current assignments for this package
            $db->prepare('DELETE FROM package_customization_options WHERE package_id = ?')->execute([$package_id]);
            // Insert new assignments
            if (!empty($selectedOptions)) {
                $ins = $db->prepare('INSERT INTO package_customization_options (package_id, option_id) VALUES (?, ?)');
                foreach ($selectedOptions as $oid) {
                    $ins->execute([$package_id, $oid]);
                }
            }

            header("Location: packages.php?success=update");
            exit();
        } else {
            $_SESSION['errors'] = $errors;
            header("Location: edit_package.php?id=$package_id");
            exit();
        }
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: edit_package.php?id=$package_id");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Package - Catering Admin</title>
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
            <h1 class="mb-4">Edit Package</h1>
            
            <div class="form-section">
                <form method="POST" action="edit_package.php?id=<?php echo $package_id; ?>">
                    <div class="mb-3">
                        <label for="category" class="form-label">Package Type</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select Package Type</option>
                            <option value="Wedding Catering" <?php echo $package['category'] === 'Wedding Catering' ? 'selected' : ''; ?>>Wedding Catering</option>
                            <option value="Debut Catering" <?php echo $package['category'] === 'Debut Catering' ? 'selected' : ''; ?>>Debut Catering</option>
                            <option value="Corporate Catering" <?php echo $package['category'] === 'Corporate Catering' ? 'selected' : ''; ?>>Corporate Catering</option>
                            <option value="Private Catering" <?php echo $package['category'] === 'Private Catering' ? 'selected' : ''; ?>>Private Catering</option>
                            <option value="Childrens Party Catering" <?php echo $package['category'] === 'Childrens Party Catering' ? 'selected' : ''; ?>>Childrens Party Catering</option>
                            <option value="Special Event Catering" <?php echo $package['category'] === 'Special Event Catering' ? 'selected' : ''; ?>>Special Event Catering</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Package Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($package['name']); ?>" required>
                        <small class="form-text text-muted">Example: Silver Package, Gold Package, Platinum Package</small>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($package['description']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($package['price']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="number_of_guests" class="form-label">Number of Guests</label>
                        <input type="number" class="form-control" id="number_of_guests" name="number_of_guests" min="1" value="<?php echo htmlspecialchars($package['number_of_guests'] ?? ''); ?>" required>
                        <small class="form-text text-muted">Set the fixed number of guests for this package</small>
                    </div>
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">Update Package</button>
                        <a href="packages.php" class="btn btn-secondary">Cancel</a>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addOptionModal">Add New Item/Option</button>
                    </div>
                    <!-- Customization Option Assignment UI -->
                    <div class="mt-4">
                        <h4>Assign Customization Options to this Package</h4>
                        <div class="row">
                        <?php
                        // Fetch all options, grouped by category
                        $allOptions = $db->query('SELECT o.*, c.category_name, c.category_type FROM customization_options o JOIN customization_categories c ON o.category_id = c.category_id WHERE o.status="active" AND c.status="active" ORDER BY c.category_type, o.option_name')->fetchAll(PDO::FETCH_ASSOC);
                        $assigned = $db->query('SELECT option_id FROM package_customization_options WHERE package_id = '.intval($package_id))->fetchAll(PDO::FETCH_COLUMN);
                        $grouped = ['food'=>[], 'service'=>[], 'decoration'=>[]];
                        foreach ($allOptions as $opt) {
                            $type = strtolower($opt['category_type']);
                            if (isset($grouped[$type])) $grouped[$type][] = $opt;
                        }
                        ?>
                        <?php foreach ($grouped as $type => $opts): ?>
                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card-header bg-light fw-bold text-capitalize"><?php echo ucfirst($type); ?> Options</div>
                                    <div class="card-body" style="max-height:300px;overflow-y:auto;">
                                        <?php if (empty($opts)): ?>
                                            <div class="text-muted">No options available.</div>
                                        <?php else: ?>
                                            <?php foreach ($opts as $opt): ?>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" name="customization_options[]" value="<?php echo $opt['option_id']; ?>" id="opt_<?php echo $opt['option_id']; ?>" <?php echo in_array($opt['option_id'], $assigned) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="opt_<?php echo $opt['option_id']; ?>">
                                                        <strong><?php echo htmlspecialchars($opt['option_name']); ?></strong>
                                                        <?php if ($opt['description']): ?><br><span class="small text-muted"><?php echo htmlspecialchars($opt['description']); ?></span><?php endif; ?>
                                                        <br><span class="badge bg-secondary">₱<?php echo number_format($opt['price'],2); ?></span>
                                                        <!-- Image removed -->
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
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
                                <?php
                                $categories = $db->query('SELECT * FROM customization_categories WHERE status="active"')->fetchAll(PDO::FETCH_ASSOC);
                                // Find the Food category id
                                $foodCatId = '';
                                foreach ($categories as $cat) {
                                    if (strcasecmp($cat['category_name'], 'Food') === 0) {
                                        $foodCatId = $cat['category_id'];
                                        break;
                                    }
                                }
                                ?>
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select name="category_id" class="form-control" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['category_id']; ?>" <?php echo (strcasecmp($cat['category_name'], 'Food') === 0) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
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
                                <!-- Image field removed -->
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
        // Display error messages using Toastify
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
        if (isset($_SESSION['error'])) {
            echo "Toastify({
                text: '" . addslashes($_SESSION['error']) . "',
                duration: 3000,
                close: true,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#dc3545',
            }).showToast();";
            unset($_SESSION['error']);
        }
        ?>
    </script>
</body>
</html>