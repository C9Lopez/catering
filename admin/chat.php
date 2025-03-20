<?php
// Require database connection
require '../db.php';

// Start or resume session for admin authentication
session_start();

// Check if admin is logged in; redirect if not
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit;
}

// Get booking ID from URL and validate it
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

if ($booking_id <= 0) {
    die("Invalid booking ID. Please provide a valid booking ID.");
}

$chatStmt = $db->prepare("UPDATE chat_messages SET is_unread = 0 WHERE order_id = :booking_id");
$chatStmt->execute([
    ':booking_id' => $booking_id
]);

try {
    // Fetch booking and user details for the chat, including user’s full name
    $stmt = $db->prepare("SELECT u.first_name, u.last_name, u.user_id, eb.event_type 
                         FROM event_bookings eb 
                         JOIN users u ON eb.user_id = u.user_id 
                         WHERE eb.booking_id = :booking_id");
    $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    $user_id = $booking['user_id'];
    if (!$booking) {
        die("Booking not found. Please check the booking ID or contact support.");
    }

    // Construct user’s full name for display in messages
    $userFullName = htmlspecialchars(trim($booking['first_name'] . ' ' . $booking['last_name']));

    // Fetch all chat messages for this booking, ordered chronologically
    $messageStmt = $db->prepare("SELECT * FROM chat_messages WHERE order_id = :booking_id ORDER BY created_at ASC");
    $messageStmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $messageStmt->execute();
    $messages = $messageStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Log database error for debugging and display a user-friendly message
    error_log("Database error in chat.php: " . $e->getMessage());
    die("An error occurred while loading the chat. Please try again later or contact support.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Booking #<?php echo $booking_id; ?> - Catering Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/admin.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Chat container styling for blue/black theme */
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

        /* Message styling */
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

        /* User (customer) message styling - Blue */
        .message.user {
            background: #007bff !important;
            color: white !important;
            margin-left: auto !important;
        }

        /* Admin message styling - Green */
        .message.admin {
            background: #28a745 !important;
            color: white !important;
        }

        /* Error message styling - Red */
        .message.error {
            background: #dc3545 !important;
            color: white !important;
            margin-bottom: 15px;
        }

        /* Hover effect for messages */
        .message:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        /* File attachment styling */
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

        /* Chat input styling */
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

        /* Timestamp styling */
        .text-muted {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Ensure sidebar and main content align with blue/black theme */
        .admin-dashboard {
            background-color: #f8f9fa;
            color: #333;
        }

        .main-content {
            padding: 20px;
            background-color: #ffffff;
        }

        h1 {
            font-family: 'Arial', sans-serif;
            color: #007bff;
            font-weight: 600;
            margin-bottom: 20px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .chat-container {
                height: 60vh;
                padding: 15px;
            }
            .message {
                max-width: 90%;
                font-size: 0.9rem;
                padding: 10px 12px;
            }
            .chat-input {
                gap: 10px;
                flex-wrap: wrap;
            }
            .chat-input input, .chat-input button {
                padding: 8px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body class="admin-dashboard">
    <?php include '../layout/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <h1>Chat - Booking #<?php echo $booking_id; ?> (<?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?> - <?php echo htmlspecialchars($booking['event_type']); ?>)</h1>

            <div class="card">
                <div class="card-body">
                    <div class="chat-container" id="chatMessages">
                        <?php foreach ($messages as $msg): ?>
                            <div class="message <?php echo $msg['sender'] === 'admin' ? 'admin' : 'user'; ?>">
                                <strong><?php echo $msg['sender'] === 'admin' ? 'Admin' : $userFullName; ?>:</strong> 
                                <?php echo htmlspecialchars($msg['message']); ?>
                                <?php if (!empty($msg['file_path'])): ?>
                                    <?php
                                    $fileExtension = strtolower(pathinfo($msg['file_path'], PATHINFO_EXTENSION));
                                    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                                    ?>
                                    <div class="file-attachment">
                                        <?php if (in_array($fileExtension, $imageExtensions)): ?>
                                            <a href="../<?php echo htmlspecialchars($msg['file_path']); ?>" target="_blank">
                                                <img src="../<?php echo htmlspecialchars($msg['file_path']); ?>" alt="Attachment">
                                            </a>
                                        <?php else: ?>
                                            <a href="../<?php echo htmlspecialchars($msg['file_path']); ?>" target="_blank">
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
                        <input type="hidden" name="sender" value="admin">
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>
    <script>
        // Function to fetch and update chat messages in real-time
        function fetchMessages() {
            $.ajax({
                url: 'fetch_chat.php',
                method: 'GET',
                data: { booking_id: <?php echo $booking_id; ?>, context: 'admin' },
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
                url: 'save_chat.php',
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