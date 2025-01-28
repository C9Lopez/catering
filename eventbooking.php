<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_type = $_POST['event_type'];
    $event_date = $_POST['event_date'];
    $number_of_guests = $_POST['number_of_guests'];
    $additional_requests = $_POST['additional_requests'];
    $client_name = $_POST['client_name'];
    $client_email = $_POST['client_email'];
    $client_phone = $_POST['client_phone'];

    try {
        $stmt = $db->prepare("INSERT INTO event_bookings (event_type, event_date, number_of_guests, additional_requests, client_name, client_email, client_phone) 
                               VALUES (:event_type, :event_date, :number_of_guests, :additional_requests, :client_name, :client_email, :client_phone)");
        $stmt->bindParam(':event_type', $event_type);
        $stmt->bindParam(':event_date', $event_date);
        $stmt->bindParam(':number_of_guests', $number_of_guests);
        $stmt->bindParam(':additional_requests', $additional_requests);
        $stmt->bindParam(':client_name', $client_name);
        $stmt->bindParam(':client_email', $client_email);
        $stmt->bindParam(':client_phone', $client_phone);
        $stmt->execute();
        $success_message = "Booking successful!";
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Booking</title>
</head>
<body>
    <h1>Event Booking</h1>
    <?php if (isset($success_message)) { echo "<div class='alert alert-success'>$success_message</div>"; } ?>
    <?php if (isset($error_message)) { echo "<div class='alert alert-danger'>$error_message</div>"; } ?>
    <form method="POST" action="">
        <label for="event_type">Event Type:</label>
        <input type="text" id="event_type" name="event_type" required><br>

        <label for="event_date">Event Date:</label>
        <input type="date" id="event_date" name="event_date" required><br>

        <label for="number_of_guests">Number of Guests:</label>
        <input type="number" id="number_of_guests" name="number_of_guests" required><br>

        <label for="additional_requests">Additional Requests:</label>
        <textarea id="additional_requests" name="additional_requests"></textarea><br>

        <label for="client_name">Client Name:</label>
        <input type="text" id="client_name" name="client_name" required><br>

        <label for="client_email">Client Email:</label>
        <input type="email" id="client_email" name="client_email" required><br>

        <label for="client_phone">Client Phone:</label>
        <input type="text" id="client_phone" name="client_phone" required><br>

        <input type="submit" value="Book Event">
    </form>
</body>
</html>
