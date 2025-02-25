<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    if (!isset($_SESSION['user_id'])) {
        echo '<div class="alert alert-danger">User not logged in.</div>';
        exit();
    }
    $user_id = $_SESSION['user_id'];

    $package_id = $_POST['package_id'];

    $event_type = $_POST['event_type'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $setup_time = $_POST['setup_time'];
    $number_of_guests = $_POST['number_of_guests'];
    $total_amount = $_POST['total_amount'];
    $location = $_POST['location'];
    $additional_requests = $_POST['additional_requests'];
    $special_requirements = $_POST['special_requirements'];

    try {
        // Insert booking details into the database
        $stmt = $db->prepare("INSERT INTO event_bookings (user_id, package_id, location, event_type, event_date, event_time, setup_time, number_of_guests, total_amount, payment_status, booking_status, additional_requests, special_requirements) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', ?, ?)");





        $stmt->execute([$user_id, $package_id, $location, $event_type, $event_date, $event_time, $setup_time, $number_of_guests, $total_amount, $additional_requests, $special_requirements]);



        // Remove the order logging as it's no longer needed



        // Redirect to a confirmation page or display a success message
        echo "<script>alert('Your booking has been successfully submitted!'); window.location.href = 'wedding.php';</script>";
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">Error processing booking: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
} else {
    header("Location: wedding.php");
    exit();
}
