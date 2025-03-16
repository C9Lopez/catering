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

try {
    // Fetch booking and user details for the chat, including user’s full name
    $stmt = $db->prepare("SELECT u.first_name, u.last_name, u.user_id, eb.event_type 
                         FROM event_bookings eb 
                         JOIN users u ON eb.user_id = u.user_id 
                         WHERE eb.booking_id = :booking_id");
    $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

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
            background: #f8f9fa; /* Light gray background for contrast with dark theme */
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
            word-wrap: break-word; /* Ensure long messages wrap properly */
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

        /* Chat input styling */
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
                                <small class="text-muted d-block mt-2">Sent at <?php echo date('h:i A', strtotime($msg['created_at'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <form id="chatForm" method="POST" action="save_chat.php">
                        <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                        <input type="hidden" name="sender" value="admin">
                        <div class="chat-input">
                            <input type="text" id="message" name="message" class="form-control" placeholder="Type a message..." required>
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
        // Function to fetch and update chat messages in real-time, ensuring styles persist
        function fetchMessages() {
            $.ajax({
                url: 'fetch_chat.php',
                method: 'GET',
                data: { booking_id: <?php echo $booking_id; ?>, context: 'admin' }, // Explicitly indicate admin context
                success: function(response) {
                    if (typeof response === 'object' && response.error) {
                        console.error('Error fetching messages:', response.error);
                        $('#chatMessages').append('<div class="message error">Error loading messages: ' + response.error + '</div>');
                    } else if (typeof response === 'string') {
                        // Ensure styles are applied by wrapping response in a div with chat-container class
                        const styledResponse = '<div class="chat-container">' + response + '</div>';
                        $('#chatMessages').html(response); // Use raw response for simplicity, but ensure CSS is loaded
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
                url: 'save_chat.php',
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