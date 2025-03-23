<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // Number of bookings per page
$offset = ($page - 1) * $limit;

$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build the query with filters
$query = "
    SELECT eb.booking_id, eb.package_id, eb.event_type, eb.event_date, eb.event_time, eb.booking_status, eb.created_at, 
           cp.category, cp.price 
    FROM event_bookings eb 
    LEFT JOIN catering_packages cp ON eb.package_id = cp.package_id 
    WHERE eb.user_id = :user_id
";
$params = [':user_id' => $user_id];

if ($category_filter !== 'all') {
    $query .= " AND cp.category = :category";
    $params[':category'] = $category_filter;
}

if ($status_filter !== 'all') {
    $query .= " AND eb.booking_status = :status";
    $params[':status'] = $status_filter;
}

$query .= " ORDER BY eb.created_at DESC LIMIT :limit OFFSET :offset";

// Count total bookings for pagination
$countQuery = "SELECT COUNT(*) as total FROM event_bookings eb LEFT JOIN catering_packages cp ON eb.package_id = cp.package_id WHERE eb.user_id = :user_id";
$countParams = [':user_id' => $user_id];

if ($category_filter !== 'all') {
    $countQuery .= " AND cp.category = :category";
    $countParams[':category'] = $category_filter;
}

if ($status_filter !== 'all') {
    $countQuery .= " AND eb.booking_status = :status";
    $countParams[':status'] = $status_filter;
}

try {
    // Fetch bookings
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    if ($category_filter !== 'all') {
        $stmt->bindParam(':category', $category_filter);
    }
    if ($status_filter !== 'all') {
        $stmt->bindParam(':status', $status_filter);
    }
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch total count
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute($countParams);
    $totalBookings = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalBookings / $limit);

    // Fetch unread message counts for each booking
    $bookingsWithUnread = [];
    foreach ($bookings as $booking) {
        $chatStmt = $db->prepare("SELECT COUNT(*) as unread FROM chat_messages WHERE order_id = :booking_id AND user_id = :user_id AND sender = 'admin' AND is_unread != 0");
        $chatStmt->execute([
            ':booking_id' => $booking['booking_id'],
            ':user_id' => $user_id
        ]);
        $unreadCount = $chatStmt->fetch(PDO::FETCH_ASSOC)['unread'];

        $booking['unread_count'] = $unreadCount;
        $bookingsWithUnread[] = $booking;
    }

    $response = [
        'bookings' => $bookingsWithUnread,
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_bookings' => $totalBookings
    ];

} catch (PDOException $e) {
    $response = ['error' => 'Database error: ' . $e->getMessage()];
}

header('Content-Type: application/json');
echo json_encode($response);
?>