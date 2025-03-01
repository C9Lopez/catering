<?php
session_start();
require '../db.php'; // Adjust the path if necessary

// Log the admin logout activity
try {
    $stmt = $db->prepare("INSERT INTO activity_log (admin_id, description) VALUES (:admin_id, :description)");
    $stmt->execute([
        ':admin_id' => $_SESSION['admin_id'] ?? null,
        ':description' => 'Admin logged out'
    ]);
} catch (PDOException $e) {
    // Continue with logout even if logging fails
}

// Destroy all admin session data
$_SESSION = array();
session_destroy();

// Redirect to the admin login page (adjust the path if needed)
header("Location: ../auth/admin_login.php");
exit();
?>
