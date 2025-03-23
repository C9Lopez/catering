<?php


// Get the current URL path
$currentPath = $_SERVER['REQUEST_URI'];

// Check if the user is inside "catering-service" directory
$isInsideCateringService = strpos($currentPath, '/catering-service/') !== false;

// Define base paths dynamically
$basePath = $isInsideCateringService ? '../' : './';
$cateringBase = $isInsideCateringService ? './' : './catering-service/';

function isActive($pages) {
    global $currentPath;
    foreach ((array) $pages as $page) {
        if (strpos($currentPath, $page) !== false) {
            return ' active';
        }
    }
    return '';
}
?>
<!-- Navbar Start -->
<div class="container-fluid nav-bar wow fadeIn" data-wow-delay="0.1s">
    <div class="container">
        <nav class="navbar navbar-light navbar-expand-lg py-4">
            <a href="index.php" class="navbar-brand">
                <h1 class="text-primary fw-bold mb-0">Pochie<span class="text-dark">Catering</span></h1>
            </a>
            <button class="navbar-toggler py-2 px-3" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarCollapse">
                <span class="fa fa-bars text-primary"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav mx-auto">
                    <a href="<?= $basePath ?>index.php" class="nav-item nav-link <?= isActive('index.php') ?>">Home</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle <?= isActive(['wedding.php', 'debut.php', 'childrens.php', 'corporate.php', 'private.php']) ?>" data-bs-toggle="dropdown">Catering Services</a>
                        <div class="dropdown-menu">
                            <a href="<?= $cateringBase ?>wedding.php" class="dropdown-item">Wedding Catering Services</a>
                            <a href="<?= $cateringBase ?>debut.php" class="dropdown-item">Debut Catering Services</a>
                            <a href="<?= $cateringBase ?>childrens.php" class="dropdown-item">Children's Party Catering Services</a>
                            <a href="<?= $cateringBase ?>corporate.php" class="dropdown-item">Corporate Catering Services</a>
                            <a href="<?= $cateringBase ?>private.php" class="dropdown-item">Private Party Catering Services</a>
                        </div>
                    </div>
                    <a href="<?= $basePath ?>about.php" class="nav-item nav-link <?= isActive('about.php') ?>">About Us</a>
                    <a href="<?= $basePath ?>contact.php" class="nav-item nav-link <?= isActive('contact.php') ?>">Contact</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?= $basePath ?>profile.php" class="nav-item nav-link <?= isActive('profile.php') ?>">Profile</a>
                    <?php endif; ?>
                    <div class="d-flex align-items-center">
                    <div class="theme-switcher me-3">
                        <button id="theme-toggle" class="btn btn-sm btn-outline-secondary" title="Toggle theme">
                            <i class="fas fa-moon"></i>
                        </button>
                    </div>
                </div>
                
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Notification Bell -->
                        <div class="dropdown me-3">
                            <a href="#" class="nav-link dropdown-toggle" id="notificationBell" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                <span class="badge bg-danger notification-count">0</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationBell">
                                <li class="dropdown-header">Notifications</li>
                                <div id="notification-list"></div>
                                <li><hr class="dropdown-divider"></li>
                                
                            </ul>
                        </div>
                        <!-- Inbox Icon -->
                        <div class="dropdown me-3">
                            <a href="#" class="nav-link dropdown-toggle" id="inboxIcon" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-envelope"></i>
                                <span class="badge bg-primary message-count">0</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end message-dropdown" aria-labelledby="inboxIcon">
                                <li class="dropdown-header">Unread Messages</li>
                                <div id="message-list"></div>
                                <li><hr class="dropdown-divider"></li>
                                
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= $basePath ?>auth/login.php" class="btn btn-outline-primary me-2">Log In</a>
                        <a href="<?= $basePath ?>auth/signup.php" class="btn btn-primary">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </div>
</div>
<!-- Navbar End -->

<!-- JavaScript for Notifications and Messages -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    // Real-Time Notifications
    function fetchNotifications() {
        console.log('Fetching notifications...');
        $.ajax({
            url: '<?= $basePath ?>fetch_notifications.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Notifications response:', response);
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

    // Mark notifications as read when dropdown is fully opened
    $('#notificationBell').on('shown.bs.dropdown', function() {
        console.log('Marking notifications as read...');
        $.ajax({
            url: '<?= $basePath ?>fetch_notifications.php',
            method: 'GET',
            data: { mark_read: true },
            success: function() {
                console.log('Notifications marked as read, resetting count to 0');
                $('.notification-count').text('0');
            },
            error: function(xhr, status, error) {
                console.error('Error marking notifications as read:', error);
            }
        });
    });

    // Real-Time Unread Messages
    function fetchUnreadMessages() {
    console.log('Fetching unread messages...');
    console.log('Request URL:', '<?= $basePath ?>fetch_unread_messages.php');
    $.ajax({
        url: '<?= $basePath ?>fetch_unread_messages.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Unread messages response:', response);
            if (response.error) {
                console.error('Error in fetchUnreadMessages:', response.error);
                return;
            }

            // Update message count
            console.log('Updating message count to:', response.unread_count);
            $('.message-count').text(response.unread_count);

            // Update message list
            const messageList = $('#message-list');
            messageList.empty();
            if (response.messages.length === 0) {
                messageList.append('<li class="dropdown-item text-muted">No unread messages</li>');
            } else {
                response.messages.forEach(msg => {
                    const date = new Date(msg.created_at).toLocaleString();
                    messageList.append(`
                        <li class="message-item">
                            <div><strong>${msg.event_type}</strong>: ${msg.message}</div>
                            <small class="text-muted">${date}</small>
                        </li>
                    `);
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching messages:', error);
            console.error('Status:', status);
            console.error('Response Text:', xhr.responseText);
        }
    });
}

    // Mark messages as read when dropdown is fully opened
    $('#inboxIcon').on('shown.bs.dropdown', function() {
        console.log('Marking messages as read...');
        $.ajax({
            url: '<?= $basePath ?>fetch_unread_messages.php',
            method: 'GET',
            data: { mark_read: true },
            success: function() {
                console.log('Messages marked as read, resetting count to 0');
                $('.message-count').text('0');
            },
            error: function(xhr, status, error) {
                console.error('Error marking messages as read:', error);
            }
        });
    });

    // Fetch notifications and messages initially and then every 10 seconds
    fetchNotifications();
    fetchUnreadMessages();
    setInterval(fetchNotifications, 10000);
    setInterval(fetchUnreadMessages, 10000);
</script>