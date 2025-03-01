<?php
require '../db.php';
session_start();

// 1) Check if admin is logged in, otherwise return JSON error (instead of redirecting).
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// 2) Check for valid action and announcement ID
if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] === 'publish') {
    $id = $_GET['id'];

    // Check if the ID exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() == 0) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid announcement ID']);
        exit();
    }

    // 3) Update the status to 'live'
    $stmt = $db->prepare("UPDATE announcements SET status = 'live' WHERE id = ?");
    $stmt->execute([$id]);

    // 4) Return a JSON success response
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'id' => $id]);
    exit();
} else {
    // If the request is not valid, return a JSON error
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}
