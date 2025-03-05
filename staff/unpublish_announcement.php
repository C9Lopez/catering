<?php
require '../db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

// Ensure the action is valid and only happens on link click or AJAX call
if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] === 'unpublish') {
    $id = $_GET['id'];

    // Check if the ID exists in the database
    $stmt = $db->prepare("SELECT COUNT(*) FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() == 0) {
        $_SESSION['error'] = "Invalid announcement ID.";
        // For AJAX, return JSON
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
            echo json_encode(['status' => 'error', 'message' => 'Invalid announcement ID.']);
            exit();
        }
        header("Location: announcement.php");
        exit();
    }

    // Update the status to 'preview'
    $stmt = $db->prepare("UPDATE announcements SET status = 'preview' WHERE id = ?");
    $stmt->execute([$id]);

    // If the request is via AJAX, return JSON response instead of redirecting
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
        echo json_encode(['status' => 'success', 'id' => $id]);
        exit();
    } else {
        $_SESSION['success'] = "Announcement unpublished successfully.";
        header("Location: announcement.php");
        exit();
    }
} else {
    header("Location: announcement.php");
    exit();
}
?>
