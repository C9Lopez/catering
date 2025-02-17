<?php
session_start();

// Log the logout activity
require '../db.php';
try {
    $stmt = $db->prepare("INSERT INTO activity_log (admin_id, description) VALUES (:admin_id, :description)");
    $stmt->execute([
        ':admin_id' => $_SESSION['admin_id'] ?? null,
        ':description' => 'Admin logged out'
    ]);
} catch (PDOException $e) {
    // Continue with logout even if logging fails
}

// Destroy all session data
$_SESSION = array();
session_destroy();

// Redirect to login page
header("Location: admin_login.php");
exit();
?>
