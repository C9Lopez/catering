<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You must be logged in to cancel a booking.'); window.location.href='auth/login.php';</script>";
    exit;
}

if (!isset($_GET['booking_id'])) {
    echo "<script>alert('No booking ID provided.'); window.location.href='profile.php';</script>";
    exit;
}

$bookingId = intval($_GET['booking_id']);
$userId = $_SESSION['user_id'];

try {
    // Fetch the booking details to verify ownership and conditions
    $stmt = $db->prepare("
        SELECT booking_status, event_date 
        FROM event_bookings 
        WHERE booking_id = :booking_id AND user_id = :user_id
    ");
    $stmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        echo "<script>alert('Booking not found or you do not have permission to cancel it.'); window.location.href='profile.php';</script>";
        exit;
    }

    $currentDate = new DateTime();
    $eventDate = new DateTime($booking['event_date']);
    $daysUntilEvent = $currentDate->diff($eventDate)->days;

    // Check cancellation conditions
    if ($booking['booking_status'] !== 'pending') {
        echo "<script>alert('Only pending bookings can be cancelled.'); window.location.href='profile.php';</script>";
        exit;
    }

    if ($daysUntilEvent < 7) {
        echo "<script>alert('Cancellation is only allowed if the event is at least 7 days away.'); window.location.href='profile.php';</script>";
        exit;
    }

    // Update booking status to 'cancelled' (double 'l')
    $updateStmt = $db->prepare("
        UPDATE event_bookings 
        SET booking_status = 'cancelled', updated_at = NOW() 
        WHERE booking_id = :booking_id AND user_id = :user_id
    ");
    $updateStmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
    $updateStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $updateStmt->execute();

    echo "<script>alert('Booking successfully cancelled.'); window.location.href='profile.php';</script>";

} catch (PDOException $e) {
    echo "<script>alert('Error cancelling booking: " . $e->getMessage() . "'); window.location.href='profile.php';</script>";
}
?>