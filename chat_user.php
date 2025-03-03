<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['booking_id'])) {
    header("Location: auth/login.php");
    exit;
}

$booking_id = (int)$_GET['booking_id'];

try {
    $stmt = $db->prepare("
        SELECT eb.booking_id, cp.name as package_name, cp.category 
        FROM event_bookings eb 
        JOIN catering_packages cp ON eb.package_id = cp.package_id 
        WHERE eb.booking_id = :booking_id AND eb.user_id = :user_id
    ");
    $stmt->execute([':booking_id' => $booking_id, ':user_id' => $_SESSION['user_id']]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        header("Location: profile.php");
        exit;
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Ticket - Booking #<?php echo $booking_id; ?> - Pochie Catering</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Playball&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/owl.carousel.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/themes.css" rel="stylesheet">
    <link href="css/chat.css" rel="stylesheet">
    <style>
        .chat-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .chat-box {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background: #f9f9f9;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .chat-message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .chat-message.admin {
            text-align: right;
            background: #e9f7ff;
        }
        .chat-message.user {
            text-align: left;
            background: #f0f0f0;
        }
        .chat-message .sender {
            font-weight: bold;
            margin-right: 5px;
            color: #333;
        }
        .chat-message .time {
            font-size: 0.8rem;
            color: #777;
            display: block;
            margin-top: 5px;
        }
        .message-form {
            margin-top: 20px;
        }
        .chat-box::-webkit-scrollbar {
            width: 6px;
        }
        .chat-box::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .chat-box::-webkit-scrollbar-thumb {
            background: #007bff;
            border-radius: 10px;
        }
        .chat-box::-webkit-scrollbar-thumb:hover {
            background: #0056b3;
        }
    </style>
</head>
<body class="light-theme">
    <?php include 'layout/navbar.php'; ?>

    <div class="container-fluid chat-container">
        <h1 class="display-5 mb-4 text-center" style="font-family: 'Playball', cursive;">Chat Support Booking #<?php echo htmlspecialchars($booking['booking_id']); ?></h1>
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Ticket: <?php echo htmlspecialchars($booking['package_name']); ?> (<?php echo htmlspecialchars(str_replace('Catering', 'Party Catering', $booking['category'])); ?>)</h5>
            </div>
            <div class="card-body">
                <div id="chat-box" class="chat-box"></div>
                <form id="message-form" class="message-form">
                    <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                    <div class="mb-3">
                        <label for="message" class="form-label">Your Message</label>
                        <textarea class="form-control" id="message" name="message" rows="3" required placeholder="Type your concern or question here..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-2"></i>Send</button>
                    <a href="profile.php" class="btn btn-secondary">Back to Profile</a>
                </form>
            </div>
        </div>
    </div>

    <?php include 'layout/footer.php'; ?>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatBox = document.getElementById('chat-box');
            const messageForm = document.getElementById('message-form');
            let lastMessageId = 0;

            // Function to fetch messages
            function fetchMessages() {
                $.ajax({
                    url: 'chat_api.php',
                    method: 'GET',
                    data: { booking_id: <?php echo $booking_id; ?> },
                    success: function(response) {
                        if (response.status === 'success') {
                            chatBox.innerHTML = '';
                            response.data.messages.forEach(msg => {
                                const messageDiv = document.createElement('div');
                                messageDiv.className = `chat-message ${msg.sender === 'admin' ? 'admin' : 'user'}`;
                                messageDiv.innerHTML = `
                                    <span class="sender">${msg.sender === 'admin' ? 'Admin' : 'You'}:</span>
                                    <span>${msg.message}</span>
                                    <div class="time">${new Date(msg.created_at).toLocaleString()}</div>
                                `;
                                chatBox.appendChild(messageDiv);
                                if (msg.id > lastMessageId) lastMessageId = msg.id;
                            });
                            chatBox.scrollTop = chatBox.scrollHeight;
                        } else {
                            console.error('Error fetching messages:', response.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error);
                    }
                });
            }

            // Handle message sending
            messageForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(messageForm);
                
                $.ajax({
                    url: 'chat_api.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status === 'success') {
                            fetchMessages(); // Update chat without reload
                            document.getElementById('message').value = ''; // Clear textarea
                        } else {
                            alert('Error sending message: ' + response.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error);
                        alert('Failed to send message');
                    }
                });
            });

            // Initial fetch and periodic polling
            fetchMessages();
            setInterval(fetchMessages, 2000); // Check for new messages every 2 seconds
        });
    </script>
</body>
</html>