<?php
require '../db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
$new_status = isset($_POST['booking_status']) ? htmlspecialchars(trim($_POST['booking_status'])) : '';

$valid_statuses = ['pending', 'on_process', 'approved', 'rejected', 'completed', 'cancelled'];

if ($booking_id <= 0 || !in_array($new_status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid booking ID or status']);
    exit;
}

try {
    $stmt = $db->prepare("UPDATE event_bookings SET booking_status = :status WHERE booking_id = :booking_id");
    $stmt->execute([
        ':status' => $new_status,
        ':booking_id' => $booking_id
    ]);

    // Add a notification for the user
    $notificationStmt = $db->prepare("INSERT INTO notifications (booking_id, user_id, admin_id, message, created_at) VALUES (:booking_id, (SELECT user_id FROM event_bookings WHERE booking_id = :booking_id), :admin_id, :message, NOW())");
    $notificationStmt->execute([
        ':booking_id' => $booking_id,
        ':admin_id' => $_SESSION['admin_id'],
        ':message' => "Your booking status has been updated to " . ucfirst(str_replace('_', ' ', $new_status)) . "."
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log("Error updating booking status: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}