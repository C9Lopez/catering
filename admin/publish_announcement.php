<?php
require '../db.php';
session_start();

// Set content type to JSON
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => ''];

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    $response['message'] = 'Unauthorized';
    echo json_encode($response);
    exit();
}

// Check for valid action and announcement ID
if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] === 'publish') {
    $id = $_GET['id'];

    try {
        // Check if the ID exists
        $stmt = $db->prepare("SELECT COUNT(*) FROM announcements WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() == 0) {
            $response['message'] = 'Invalid announcement ID';
            echo json_encode($response);
            exit();
        }

        // Update the status to 'live'
        $stmt = $db->prepare("UPDATE announcements SET status = 'live' WHERE id = ?");
        $stmt->execute([$id]);

        $response['status'] = 'success';
        $response['id'] = $id;
    } catch (PDOException $e) {
        $response['message'] = 'Error publishing announcement: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request';
}

echo json_encode($response);
exit();
?>