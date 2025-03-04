<?php
// Require database connection
require 'db.php';

// Start or resume session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>
        alert('You are not logged in. Please log in first.');
        window.location.href = 'auth/login.php';
    </script>";
    exit;
}

// Get booking ID from URL and validate
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

if ($booking_id <= 0) {
    die("Invalid booking ID.");
}

try {
    // Fetch user's full name for later use on admin side
    $userStmt = $db->prepare("SELECT first_name, last_name FROM users WHERE user_id = :user_id");
    $userStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $userStmt->execute();
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    $userFullName = $user ? htmlspecialchars(trim($user['first_name'] . ' ' . $user['last_name'])) : 'User';

    // Fetch booking and admin details for the chat
    $bookingStmt = $db->prepare("SELECT eb.event_type, au.first_name AS admin_first_name, au.last_name AS admin_last_name 
                                FROM event_bookings eb 
                                LEFT JOIN admin_user au ON au.admin_id = (SELECT admin_id FROM notifications WHERE booking_id = eb.booking_id LIMIT 1)
                                WHERE eb.booking_id = :booking_id AND eb.user_id = :user_id");
    $bookingStmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $bookingStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $bookingStmt->execute();
    $booking = $bookingStmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        die("Booking not found or you do not have access.");
    }

    // Fetch all chat messages for this booking, ordered chronologically
    $messageStmt = $db->prepare("SELECT * FROM chat_messages WHERE order_id = :booking_id ORDER BY created_at ASC");
    $messageStmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $messageStmt->execute();
    $messages = $messageStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Log error for debugging and display a user-friendly message
    error_log("Database error in chat_user.php: " . $e->getMessage());
    die("An error occurred while loading the chat. Please try again later.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Booking #<?php echo $booking_id; ?> - Pochie Catering</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Playball&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/themes.css" rel="stylesheet">
    <style>
        .chat-container {
            height: 70vh;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa; /* Light gray background for contrast */
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border: 1px solid #e9ecef;
        }
        .message {
            margin-bottom: 15px;
            padding: 12px 15px;
            border-radius: 8px;
            max-width: 70%;
            font-size: 1rem;
            line-height: 1.5;
            transition: all 0.3s ease;
            word-wrap: break-word; /* Ensure long messages wrap properly */
        }
        .message.user {
            background: #007bff; /* Blue for user (you) messages */
            color: white;
            margin-left: auto;
        }
        .message.admin {
            background: #28a745; /* Green for admin messages */
            color: white;
        }
        .message.error {
            background: #dc3545; /* Red for error messages */
            color: white;
            margin-bottom: 15px;
        }
        .message:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        .chat-input {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }
        .chat-input input {
            flex-grow: 1;
            border-radius: 4px;
            border: 1px solid #ced4da;
            padding: 10px;
            font-size: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            background-color: #ffffff; /* White background for input */
        }
        .chat-input button {
            padding: 10px 20px;
            font-weight: 500;
            background-color: #007bff; /* Blue button to match theme */
            border: none;
            border-radius: 4px;
            color: white;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .chat-input button:hover {
            background-color: #0056b3; /* Darker blue on hover */
            transform: translateY(-2px);
            cursor: pointer;
        }
        .text-muted {
            font-size: 0.9rem;
            opacity: 0.8;
        }
    </style>
</head>
<body class="light-theme">
    <?php include 'layout/navbar.php'; ?>

    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container">
            <h1 class="display-5 text-center mb-4" style="font-family: 'Playball', cursive;">
                Chat - Booking #<?php echo $booking_id; ?> (<?php echo htmlspecialchars($booking['event_type']); ?>)
            </h1>
            
            <div class="card">
                <div class="card-body">
                    <div class="chat-container" id="chatMessages">
                        <?php foreach ($messages as $msg): ?>
                            <div class="message <?php echo $msg['sender'] === 'user' ? 'user' : 'admin'; ?>">
                                <strong><?php echo $msg['sender'] === 'user' ? 'You' : 'Admin'; ?>:</strong> 
                                <?php echo htmlspecialchars($msg['message']); ?>
                                <small class="text-muted d-block mt-2">Sent at <?php echo date('h:i A', strtotime($msg['created_at'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <form id="chatForm" method="POST" action="admin/save_chat.php">
                        <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                        <input type="hidden" name="sender" value="user">
                        <div class="chat-input">
                            <input type="text" id="message" name="message" class="form-control" placeholder="Type a message..." required>
                            <button type="submit" class="btn btn-primary">Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'layout/footer.php'; ?>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script>
        // Initialize WOW.js animations
        new WOW().init();

        // Function to fetch and update chat messages in real-time, ensuring styles persist
        function fetchMessages() {
            $.ajax({
                url: 'admin/fetch_chat.php',
                method: 'GET',
                data: { booking_id: <?php echo $booking_id; ?>, context: 'user' }, // Explicitly indicate user context
                success: function(response) {
                    if (typeof response === 'object' && response.error) {
                        console.error('Error fetching messages:', response.error);
                        $('#chatMessages').append('<div class="message error">Error loading messages: ' + response.error + '</div>');
                    } else if (typeof response === 'string') {
                        $('#chatMessages').html(response);
                        $('#chatMessages').scrollTop($('#chatMessages')[0].scrollHeight); // Auto-scroll to latest message
                    } else {
                        console.error('Unexpected response format:', response);
                        $('#chatMessages').append('<div class="message error">Unexpected response format. Please try again.</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error fetching messages:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText,
                        url: xhr.url
                    });
                    $('#chatMessages').append('<div class="message error">Network error loading messages: ' + error + '</div>');
                }
            });
        }

        // Poll for new messages every 2 seconds
        setInterval(fetchMessages, 2000);

        // Initial message load
        fetchMessages();

        // Handle form submission for sending messages
        $('#chatForm').submit(function(e) {
            e.preventDefault();
            const message = $('#message').val().trim();
            if (!message) {
                alert('Please enter a message.');
                return;
            }
            $.ajax({
                url: 'admin/save_chat.php',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        $('#message').val(''); // Clear input after successful send
                        fetchMessages(); // Refresh messages
                    } else {
                        alert('Error: ' + (data.message || 'Failed to send message'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error sending message:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText,
                        url: xhr.url
                    });
                    alert('Error sending message: ' + error + ' (Status: ' + status + ')');
                }
            });
        });
    </script>
</body>
</html>