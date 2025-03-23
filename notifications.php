<?php 
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<script>
        alert('You are not logged in. Please log in first.');
        window.location.href = 'auth/login.php';
    </script>";
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch user details
    $stmt = $db->prepare("SELECT first_name FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<script>
            alert('User not found.');
            window.location.href = 'auth/logout_user.php';
        </script>";
        exit;
    }

    // Fetch all notifications for the user
    $notifStmt = $db->prepare("SELECT notification_id, message, created_at, is_read FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC");
    $notifStmt->execute([':user_id' => $user_id]);
    $notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);

    // Mark all notifications as read when the page is loaded
    $updateStmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0");
    $updateStmt->execute([':user_id' => $user_id]);

} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Pochie Catering</title>
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
    <style>
        .notification-card {
            background-color: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .notification-card.unread {
            background-color: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body class="light-theme">
    <?php include 'layout/navbar.php'; ?>

    <!-- Notifications Section -->
    <div class="container-fluid py-6">
        <div class="container">
            <div class="text-center mb-4">
                <h1 class="display-5 mb-3" style="font-family: 'Playball', cursive;">Notifications</h1>
                <p class="fs-5 text-muted">View all your notifications</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <?php if (empty($notifications)): ?>
                        <p class="text-center text-muted">No notifications found.</p>
                    <?php else: ?>
                        <?php foreach ($notifications as $notif): ?>
                            <div class="notification-card <?php echo $notif['is_read'] == 0 ? 'unread' : ''; ?>">
                                <p><?php echo htmlspecialchars($notif['message']); ?></p>
                                <small class="text-muted"><?php echo date('F j, Y, h:i A', strtotime($notif['created_at'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'layout/footer.php'; ?>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    <script src="lib/lightbox/js/lightbox.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/theme-switcher.js"></script>
    <script>
        new WOW().init();

        // Real-Time Notifications (same as in profile.php)
        function fetchNotifications() {
            $.ajax({
                url: 'fetch_notifications.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.error) {
                        console.error(response.error);
                        return;
                    }

                    // Update notification count
                    $('.notification-count').text(response.unread_count);

                    // Update notification list
                    const notificationList = $('#notification-list');
                    notificationList.empty();
                    if (response.notifications.length === 0) {
                        notificationList.append('<li class="dropdown-item text-muted">No notifications</li>');
                    } else {
                        response.notifications.forEach(notif => {
                            const isUnread = notif.is_read == 0 ? 'unread' : '';
                            const date = new Date(notif.created_at).toLocaleString();
                            notificationList.append(`
                                <li class="notification-item ${isUnread}">
                                    <div>${notif.message}</div>
                                    <small class="text-muted">${date}</small>
                                </li>
                            `);
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching notifications:', error);
                }
            });
        }

        // Mark notifications as read when dropdown is opened
        $('#notificationBell').on('click', function() {
            $.ajax({
                url: 'fetch_notifications.php',
                method: 'GET',
                data: { mark_read: true },
                success: function() {
                    $('.notification-count').text('0');
                }
            });
        });

        // Fetch notifications initially and then every 10 seconds
        fetchNotifications();
        setInterval(fetchNotifications, 10000);
    </script>
</body>
</html>