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