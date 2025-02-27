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
    $number_of_guests = trim($_POST['number_of_guests']) ?? null; // New field

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

            $_SESSION['success'] = "Package added successfully";
            header("Location: packages.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Error adding package: " . $e->getMessage();
        }
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
</head>
<body class="admin-dashboard">
    <?php include '../layout/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
            <h1 class="mb-4">Add New Package</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
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
                        <button type="submit" class="btn btn-primary">Add Package</button>
                        <a href="packages.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>
</body>
</html>