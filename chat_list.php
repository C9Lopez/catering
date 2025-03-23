<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch all bookings with associated messages for the user
    $stmt = $db->prepare("
        SELECT eb.booking_id, eb.event_type, 
               (SELECT message FROM chat_messages cm WHERE cm.order_id = eb.booking_id ORDER BY cm.created_at DESC LIMIT 1) as last_message,
               (SELECT created_at FROM chat_messages cm WHERE cm.order_id = eb.booking_id ORDER BY cm.created_at DESC LIMIT 1) as last_message_time
        FROM event_bookings eb
        WHERE eb.user_id = :user_id
        ORDER BY last_message_time DESC
    ");
    $stmt->execute([':user_id' => $user_id]);
    $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'layout/navbar.php'; ?>
    <div class="container mt-5">
        <h2>Your Chats</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php else: ?>
            <?php if (empty($chats)): ?>
                <p>No chats available.</p>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($chats as $chat): ?>
                        <li class="list-group-item">
                            <a href="chat_user.php?booking_id=<?php echo $chat['booking_id']; ?>">
                                <strong><?php echo htmlspecialchars($chat['event_type']); ?></strong>
                                <p><?php echo htmlspecialchars($chat['last_message'] ?? 'No messages yet'); ?></p>
                                <small class="text-muted"><?php echo $chat['last_message_time'] ? date('M d, Y H:i', strtotime($chat['last_message_time'])) : ''; ?></small>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php include 'layout/footer.php'; ?>
</body>
</html>