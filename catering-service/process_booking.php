<?php
session_start();
require '../db.php';

// Set the content type to JSON since this will be an AJAX request
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'User not logged in.'
        ]);
        exit();
    }
    $user_id = $_SESSION['user_id'];

    // Retrieve and sanitize form data
    $package_id = $_POST['package_id'] ?? null;
    $event_type = $_POST['event_type'] ?? null;
    $event_date = $_POST['event_date'] ?? null;
    $event_time = $_POST['event_time'] ?? null;
    $number_of_guests = $_POST['number_of_guests'] ?? null;
    $location = $_POST['location'] ?? null;
    $total_amount = isset($_POST['total_amount']) ? floatval(str_replace(',', '', $_POST['total_amount'])) : null;
    $additional_requests = isset($_POST['additional_requests']) ? $_POST['additional_requests'] : null;
    $special_requirements = isset($_POST['special_requirements']) ? $_POST['special_requirements'] : null;
        $customizations = isset($_POST['customizations']) ? $_POST['customizations'] : null;
        $price_per_head = isset($_POST['price_per_head']) ? floatval($_POST['price_per_head']) : null;

    // Basic validation
    if (!$package_id || !$event_type || !$event_date || !$event_time || !$number_of_guests || !$location || !$total_amount) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill in all required fields.'
        ]);
        exit();
    }

    try {
        if (!empty($customizations)) {
            // Custom booking: save customizations and price_per_head
            $stmt = $db->prepare("
                INSERT INTO event_bookings (
                    user_id, package_id, location, event_type, event_date, event_time,
                    number_of_guests, total_amount, payment_status, booking_status,
                    additional_requests, special_requirements, customizations, price_per_head
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id, $package_id, $location, $event_type, $event_date, $event_time,
                $number_of_guests, $total_amount, $additional_requests, $special_requirements,
                $customizations, $price_per_head
            ]);
        } else {
            // Regular booking
            $stmt = $db->prepare("
                INSERT INTO event_bookings (
                    user_id, package_id, location, event_type, event_date, event_time,
                    number_of_guests, total_amount, payment_status, booking_status,
                    additional_requests, special_requirements
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', ?, ?)
            ");
            $stmt->execute([
                $user_id, $package_id, $location, $event_type, $event_date, $event_time,
                $number_of_guests, $total_amount, $additional_requests, $special_requirements
            ]);
        }
        echo json_encode([
            'success' => true,
            'message' => 'Your booking has been successfully submitted!'
        ]);
        exit();
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error processing booking: ' . htmlspecialchars($e->getMessage())
        ]);
        exit();
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit();
}
?>