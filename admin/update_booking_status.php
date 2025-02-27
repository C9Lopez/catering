<?php
// Require the database connection (PDO)
require '../db.php';

// Start the session to check admin login
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "You must be logged in as an admin to perform this action.";
    header("Location: ../auth/admin_login.php");
    exit();
}

// Check if required POST data exists and is valid
if (!isset($_POST['booking_id']) || !isset($_POST['booking_status']) || !is_numeric($_POST['booking_id'])) {
    $_SESSION['error'] = "Invalid booking ID or status provided.";
    header("Location: orders.php");
    exit();
}

$booking_id = $_POST['booking_id'];
$booking_status = trim($_POST['booking_status']);

// Validate the booking status to prevent invalid values
$valid_statuses = ['pending', 'approved', 'rejected'];
if (!in_array($booking_status, $valid_statuses)) {
    $_SESSION['error'] = "Invalid booking status: " . htmlspecialchars($booking_status);
    header("Location: orders.php");
    exit();
}

try {
    // Start a transaction for data integrity
    $db->beginTransaction();

    // Prepare and execute the update query using PDO
    $stmt = $db->prepare("UPDATE event_bookings SET booking_status = :booking_status WHERE booking_id = :booking_id");
    $stmt->bindParam(':booking_status', $booking_status, PDO::PARAM_STR);
    $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $stmt->execute();

    // Check if any row was affected
    if ($stmt->rowCount() === 0) {
        throw new PDOException("No booking found with ID: " . $booking_id);
    }

    // Commit the transaction
    $db->commit();

    $_SESSION['success'] = "Booking status updated successfully to " . ucfirst($booking_status);
} catch (PDOException $e) {
    // Roll back the transaction on error
    $db->rollBack();
    $_SESSION['error'] = "Error updating booking status: " . $e->getMessage();
    error_log("PDO Error updating booking_status for booking_id {$booking_id}: " . $e->getMessage());
} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['error'] = "An unexpected error occurred: " . $e->getMessage();
    error_log("Unexpected error updating booking_status for booking_id {$booking_id}: " . $e->getMessage());
}

// Redirect back to orders.php
header("Location: orders.php");
exit();