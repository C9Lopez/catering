<?php
// Require database connection
require '../db.php';

// Start or resume session for potential context checks (if needed later)
session_start();

// Log the incoming POST request for debugging
error_log("Received POST request: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Log and respond with error for invalid request method
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate and sanitize input data
$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
$sender = isset($_POST['sender']) ? htmlspecialchars(trim($_POST['sender'])) : '';
$message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';

if ($booking_id <= 0 || empty($sender) || empty($message)) {
    // Log invalid input for debugging
    error_log("Invalid input: booking_id=$booking_id, sender=$sender, message=$message");
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Validate sender value (ensure it’s either 'user' or 'admin')
if (!in_array($sender, ['user', 'admin'])) {
    error_log("Invalid sender value: $sender");
    echo json_encode(['success' => false, 'message' => 'Invalid sender']);
    exit;
}

try {
    // Prepare and execute INSERT query to save the chat message
    $stmt = $db->prepare("INSERT INTO chat_messages (order_id, sender, message) VALUES (:booking_id, :sender, :message)");
    $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $stmt->bindParam(':sender', $sender, PDO::PARAM_STR);
    $stmt->bindParam(':message', $message, PDO::PARAM_STR);
    $stmt->execute();

    // Log successful save for debugging
    error_log("Message saved successfully for booking_id=$booking_id by $sender");

    // Return success response for AJAX
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    // Log database error for debugging
    error_log("Database error in save_chat.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error saving message: ' . $e->getMessage()]);
    exit;
}