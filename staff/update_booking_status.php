<?php
require '../db.php';
session_start();

header('Content-Type: application/json');
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$booking_id = $_POST['booking_id'] ?? null;
$booking_status = trim($_POST['booking_status'] ?? '');
$valid_statuses = ['pending', 'approved', 'rejected', 'completed', 'cancelled'];

if (!$booking_id || !is_numeric($booking_id) || !in_array($booking_status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    $stmt = $db->prepare("UPDATE event_bookings SET booking_status = :status WHERE booking_id = :id");
    $stmt->execute([':status' => $booking_status, ':id' => $booking_id]);
    echo json_encode(['success' => $stmt->rowCount() > 0]);
} catch (PDOException $e) {
    error_log("Error updating status: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
exit;