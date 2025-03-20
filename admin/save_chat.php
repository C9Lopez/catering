<?php
// Require database connection
require '../db.php';

// Start or resume session for potential context checks (if needed later)
session_start();

// Log the incoming POST request for debugging
error_log("Received POST request: " . print_r($_POST, true));
error_log("Received FILES: " . print_r($_FILES, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Log and respond with error for invalid request method
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate and sanitize input data
$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$sender = isset($_POST['sender']) ? htmlspecialchars(trim($_POST['sender'])) : '';
$message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';

if ($booking_id <= 0 || empty($sender)) {
    // Log invalid input for debugging
    error_log("Invalid input: booking_id=$booking_id, sender=$sender");
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Validate sender value (ensure it’s either 'user' or 'admin')
if (!in_array($sender, ['user', 'admin'])) {
    error_log("Invalid sender value: $sender");
    echo json_encode(['success' => false, 'message' => 'Invalid sender']);
    exit;
}

// Check if a file is uploaded
$filePath = null;
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/chat/';
    // Create the directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = 'booking_' . $booking_id . '_' . time() . '_' . basename($_FILES['file']['name']);
    $filePath = $uploadDir . $fileName;

    // Validate file type and size (e.g., max 5MB, allow images and PDFs)
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    $fileType = mime_content_type($_FILES['file']['tmp_name']);
    $fileSize = $_FILES['file']['size'];

    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only images (JPEG, PNG, GIF) and PDFs are allowed.']);
        exit;
    }

    if ($fileSize > $maxFileSize) {
        echo json_encode(['success' => false, 'message' => 'File size exceeds the 5MB limit.']);
        exit;
    }

    // Move the uploaded file to the uploads directory
    if (!move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        error_log("Failed to move uploaded file to $filePath");
        echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
        exit;
    }

    // Adjust file path for database storage (relative to project root)
    $filePath = 'uploads/chat/' . $fileName;
}

// Validate that either a message or a file is provided
if (empty($message) && empty($filePath)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a message or a file']);
    exit;
}

try {
    // Prepare and execute INSERT query to save the chat message
    $stmt = $db->prepare("INSERT INTO chat_messages (order_id, user_id, sender, message, file_path) VALUES (:booking_id, :user_id, :sender, :message, :file_path)");
    $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':sender', $sender, PDO::PARAM_STR);
    $stmt->bindParam(':message', $message, PDO::PARAM_STR);
    $stmt->bindParam(':file_path', $filePath, PDO::PARAM_STR);
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