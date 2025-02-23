<?php
require '../db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

// Ensure the action is valid and only happens on link click
if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] === 'publish') {
    $id = $_GET['id'];

// Check if the ID exists in the database
$stmt = $db->prepare("SELECT COUNT(*) FROM announcements WHERE id = ?");
$stmt->execute([$id]);
if ($stmt->fetchColumn() == 0) {
    $_SESSION['error'] = "Invalid announcement ID.";
    header("Location: announcement.php");
    exit();
}

// Update the status to 'live'
$stmt = $db->prepare("UPDATE announcements SET status = 'live' WHERE id = ?");
$stmt->execute([$id]);

// Set success message
$_SESSION['success'] = "Announcement published successfully.";

// After publishing, redirect to avoid re-triggering on page refresh
header("Location: announcement.php");
exit();
} else {
    // If the request does not include the publish action, redirect back
    header("Location: announcement.php");
    exit();
}
?>
