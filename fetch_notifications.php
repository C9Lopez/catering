<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$response = [];

try {
    // Fetch unread notifications count
    $countStmt = $db->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = :user_id AND is_read = 0");
    $countStmt->execute([':user_id' => $user_id]);
    $unreadCount = $countStmt->fetch(PDO::FETCH_ASSOC)['unread_count'];

    // Fetch recent notifications (limit to 5 for dropdown)
    $notifStmt = $db->prepare("SELECT notification_id, message, created_at, is_read FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5");
    $notifStmt->execute([':user_id' => $user_id]);
    $notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'unread_count' => $unreadCount,
        'notifications' => $notifications
    ];

    // If the request includes a "mark_read" parameter, mark notifications as read
    if (isset($_GET['mark_read']) && $_GET['mark_read'] == 'true') {
        $updateStmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0");
        $updateStmt->execute([':user_id' => $user_id]);
    }

} catch (PDOException $e) {
    $response = ['error' => 'Database error: ' . $e->getMessage()];
}

header('Content-Type: application/json');
echo json_encode($response);
?>