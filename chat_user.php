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

$user_id = $_SESSION['user_id'];
// Get booking ID from URL and validate
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

if ($booking_id <= 0) {
    die("Invalid booking ID.");
}

$chatStmt = $db->prepare("UPDATE chat_messages SET is_unread = 0 WHERE order_id = :booking_id AND user_id = :user_id");
$chatStmt->execute([
    ':booking_id' => $booking_id,
    ':user_id' => $user_id
]);

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
            background: #f8f9fa;
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
            word-wrap: break-word;
        }
        .message.user {
            background: #007bff;
            color: white;
            margin-left: auto;
        }
        .message.admin {
            background: #28a745;
            color: white;
        }
        .message.error {
            background: #dc3545;
            color: white;
            margin-bottom: 15px;
        }
        .message:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        .file-attachment {
            margin-top: 10px;
            display: block;
        }
        .file-attachment img {
            max-width: 200px;
            border-radius: 5px;
            margin-top: 5px;
        }
        .file-attachment a {
            color: #007bff;
            text-decoration: none;
        }
        .file-attachment a:hover {
            text-decoration: underline;
        }
        .chat-input {
            display: flex;
            gap: 15px;
            margin-top: 15px;
            align-items: center;
        }
        .chat-input input[type="text"] {
            flex-grow: 1;
            border-radius: 4px;
            border: 1px solid #ced4da;
            padding: 10px;
            font-size: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            background-color: #ffffff;
        }
        .chat-input input[type="file"] {
            border: none;
            padding: 5px 0;
        }
        .chat-input button {
            padding: 10px 20px;
            font-weight: 500;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            color: white;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .chat-input button:hover {
            background-color: #0056b3;
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
                                <?php if (!empty($msg['file_path'])): ?>
                                    <?php
                                    $fileExtension = strtolower(pathinfo($msg['file_path'], PATHINFO_EXTENSION));
                                    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                                    ?>
                                    <div class="file-attachment">
                                        <?php if (in_array($fileExtension, $imageExtensions)): ?>
                                            <a href="<?php echo htmlspecialchars($msg['file_path']); ?>" target="_blank">
                                                <img src="<?php echo htmlspecialchars($msg['file_path']); ?>" alt="Attachment">
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo htmlspecialchars($msg['file_path']); ?>" target="_blank">
                                                <i class="fas fa-file"></i> Download Attachment
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <small class="text-muted d-block mt-2">Sent at <?php echo date('h:i A', strtotime($msg['created_at'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <form id="chatForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        <input type="hidden" name="sender" value="user">
                        <div class="chat-input">
                            <input type="text" id="message" name="message" class="form-control" placeholder="Type a message...">
                            <input type="file" id="fileInput" name="file" accept="image/*,application/pdf">
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

        // Function to fetch and update chat messages in real-time
        function fetchMessages() {
            $.ajax({
                url: 'admin/fetch_chat.php',
                method: 'GET',
                data: { booking_id: <?php echo $booking_id; ?>, context: 'user' },
                success: function(response) {
                    if (typeof response === 'object' && response.error) {
                        console.error('Error fetching messages:', response.error);
                        $('#chatMessages').append('<div class="message error">Error loading messages: ' + response.error + '</div>');
                    } else if (typeof response === 'string') {
                        $('#chatMessages').html(response);
                        $('#chatMessages').scrollTop($('#chatMessages')[0].scrollHeight);
                    } else {
                        console.error('Unexpected response format:', response);
                        $('#chatMessages').append('<div class="message error">Unexpected response format. Please try again.</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error fetching messages:', { status: status, error: error, responseText: xhr.responseText });
                    $('#chatMessages').append('<div class="message error">Network error loading messages: ' + error + '</div>');
                }
            });
        }

        // Poll for new messages every 2 seconds
        setInterval(fetchMessages, 2000);

        // Initial message load
        fetchMessages();

        // Handle form submission for sending messages with file
        $('#chatForm').submit(function(e) {
            e.preventDefault();

            const message = $('#message').val().trim();
            const fileInput = $('#fileInput')[0].files[0];

            if (!message && !fileInput) {
                alert('Please enter a message or select a file to send.');
                return;
            }

            const formData = new FormData(this);
            formData.append('message', message);

            $.ajax({
                url: 'admin/save_chat.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        $('#message').val('');
                        $('#fileInput').val('');
                        fetchMessages();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to send message'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error sending message:', { status: status, error: error, responseText: xhr.responseText });
                    alert('Error sending message: ' + error + ' (Status: ' + status + ')');
                }
            });
        });
    </script>
</body>
</html>