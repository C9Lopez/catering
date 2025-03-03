<?php
require_once 'db.php';
session_start();

header('Content-Type: application/json');

function sendResponse($status, $data = [], $error = null) {
    echo json_encode(['status' => $status, 'data' => $data, 'error' => $error]);
    exit;
}

// Check if user or admin is logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    sendResponse('error', [], 'Not authenticated');
}

$sender = isset($_SESSION['user_id']) ? 'user' : 'admin';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Send a message
    if (!isset($_POST['booking_id']) || !isset($_POST['message'])) {
        sendResponse('error', [], 'Missing booking_id or message');
    }

    $booking_id = (int)$_POST['booking_id'];
    $message = trim($_POST['message']);

    try {
        $stmt = $db->prepare("INSERT INTO chat_messages (order_id, sender, message) VALUES (:order_id, :sender, :message)");
        $stmt->execute([
            ':order_id' => $booking_id,
            ':sender' => $sender,
            ':message' => $message
        ]);
        sendResponse('success', ['message' => 'Message sent']);
    } catch (PDOException $e) {
        sendResponse('error', [], 'Database error: ' . $e->getMessage());
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch messages
    if (!isset($_GET['booking_id'])) {
        sendResponse('error', [], 'Missing booking_id');
    }

    $booking_id = (int)$_GET['booking_id'];

    // Validate booking access
    if ($sender === 'user') {
        $stmt = $db->prepare("SELECT COUNT(*) FROM event_bookings WHERE booking_id = :booking_id AND user_id = :user_id");
        $stmt->execute([':booking_id' => $booking_id, ':user_id' => $user_id]);
        if ($stmt->fetchColumn() == 0) {
            sendResponse('error', [], 'Unauthorized access to this booking');
        }
    }

    try {
        $chatStmt = $db->prepare("SELECT id, sender, message, created_at FROM chat_messages WHERE order_id = :booking_id ORDER BY created_at ASC");
        $chatStmt->execute([':booking_id' => $booking_id]);
        $messages = $chatStmt->fetchAll(PDO::FETCH_ASSOC);
        sendResponse('success', ['messages' => $messages]);
    } catch (PDOException $e) {
        sendResponse('error', [], 'Database error: ' . $e->getMessage());
    }
} else {
    sendResponse('error', [], 'Invalid request method');
}
?>