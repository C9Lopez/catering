<?php
// Require database connection
require '../db.php';

// Start or resume session for context determination
session_start();

// Get booking ID from URL and validate it
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

if ($booking_id <= 0) {
    echo json_encode(['error' => 'Invalid booking ID. Please provide a valid booking ID.']);
    exit;
}

// Get context from URL (user or admin) to determine message display
$context = isset($_GET['context']) ? htmlspecialchars(trim($_GET['context'])) : '';

// Default to user context if not specified (for backward compatibility)
if (empty($context)) {
    $context = 'user';
}

try {
    // Fetch booking and user details for the chat, including user’s full name
    $bookingStmt = $db->prepare("SELECT u.user_id, u.first_name, u.last_name 
                                FROM event_bookings eb 
                                JOIN users u ON eb.user_id = u.user_id 
                                WHERE eb.booking_id = :booking_id");
    $bookingStmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $bookingStmt->execute();
    $booking = $bookingStmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        echo json_encode(['error' => 'Booking not found. Please check the booking ID or contact support.']);
        exit;
    }

    $userId = $booking['user_id'];
    $userFullName = htmlspecialchars(trim($booking['first_name'] . ' ' . $booking['last_name']));

    // Determine if the request is from a user or admin based on context and session
    $isUser = ($context === 'user' && isset($_SESSION['user_id'])) || (!$context || $context === 'user');
    $isAdmin = $context === 'admin' && isset($_SESSION['admin_id']);

    // Allow user access if they are logged in and the booking belongs to them, or admin access if logged in as admin
    if ($isUser && isset($_SESSION['user_id']) && $_SESSION['user_id'] != $userId) {
        echo json_encode(['error' => 'Unauthorized access. This booking does not belong to you.']);
        exit;
    }
    if (!$isUser && !$isAdmin) {
        echo json_encode(['error' => 'Unauthorized access. Please log in or check your permissions.']);
        exit;
    }

    // Fetch all chat messages for this booking, ordered chronologically
    $messageStmt = $db->prepare("SELECT * FROM chat_messages WHERE order_id = :booking_id ORDER BY created_at ASC");
    $messageStmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $messageStmt->execute();
    $messages = $messageStmt->fetchAll(PDO::FETCH_ASSOC);

    // Output messages with appropriate sender titles and classes
    foreach ($messages as $msg) {
        $sender = $msg['sender'];
        $senderTitle = '';
        $senderClass = '';

        if ($sender === 'user') {
            $senderTitle = $isUser ? 'You' : $userFullName;
            $senderClass = 'user'; // Use 'user' class for consistency with CSS
        } elseif ($sender === 'admin') {
            $senderTitle = 'Admin';
            $senderClass = 'admin';
        }

        echo "<div class='message $senderClass'>";
        echo "<strong>" . htmlspecialchars($senderTitle) . ":</strong> ";
        echo htmlspecialchars($msg['message']);
        echo "<small class='text-muted d-block mt-2'>Sent at " . date('h:i A', strtotime($msg['created_at'])) . "</small>";
        echo "</div>";
    }

} catch (PDOException $e) {
    // Log database error for debugging and return a JSON error response for AJAX handling
    error_log("Database error in fetch_chat.php: " . $e->getMessage());
    echo json_encode(['error' => 'An error occurred while fetching messages. Please try again later or contact support.']);
    exit;
}