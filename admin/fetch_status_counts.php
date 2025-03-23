<?php
require '../db.php';

try {
    $stmt = $db->query("SELECT booking_status, COUNT(*) as count FROM event_bookings GROUP BY booking_status");
    $counts = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'count', 'booking_status');
    $counts = array_merge(['pending' => 0, 'on_process' => 0, 'approved' => 0, 'rejected' => 0, 'completed' => 0, 'cancelled' => 0], $counts);
    echo json_encode($counts);
} catch (PDOException $e) {
    error_log("Error fetching status counts: " . $e->getMessage());
    echo json_encode(['error' => 'Error fetching status counts']);
}