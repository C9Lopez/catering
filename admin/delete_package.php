<?php
require '../db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

// Check if package ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid package ID";
    header("Location: packages.php");
    exit();
}

$package_id = $_GET['id'];

try {
    // Delete package from database
    $stmt = $db->prepare("DELETE FROM catering_packages WHERE package_id = :id");
    $stmt->bindParam(':id', $package_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $_SESSION['success'] = "Package deleted successfully";
    } else {
        $_SESSION['error'] = "Package not found or already deleted";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error deleting package: " . $e->getMessage();
}

header("Location: packages.php");
exit();
?>
