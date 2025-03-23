<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch unread messages count (messages from admin that are unread)
    $countStmt = $db->prepare("SELECT COUNT(*) as unread_count FROM chat_messages WHERE user_id = :user_id AND sender = 'admin' AND is_unread != 0");
    $countStmt->execute([':user_id' => $user_id]);
    $unreadCount = $countStmt->fetch(PDO::FETCH_ASSOC)['unread_count'];

    // Fetch unread messages with event type for display
    $stmt = $db->prepare("
        SELECT cm.id AS message_id, cm.order_id, cm.message, cm.created_at, cm.is_unread, eb.event_type
        FROM chat_messages cm
        JOIN event_bookings eb ON cm.order_id = eb.booking_id
        WHERE cm.user_id = :user_id AND cm.sender = 'admin' AND cm.is_unread != 0
        ORDER BY cm.created_at DESC
    ");
    $stmt->execute([':user_id' => $user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If mark_read parameter is set, mark messages as read
    if (isset($_GET['mark_read']) && $_GET['mark_read'] == 'true') {
        $updateStmt = $db->prepare("UPDATE chat_messages SET is_unread = 0 WHERE user_id = :user_id AND sender = 'admin' AND is_unread != 0");
        $updateStmt->execute([':user_id' => $user_id]);
        $unreadCount = 0; // Reset count after marking as read
        $messages = []; // Clear messages after marking as read
    }

    echo json_encode([
        'unread_count' => $unreadCount,
        'messages' => $messages
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>