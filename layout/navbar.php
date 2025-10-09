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
                <h1 class="text-primary fw-bold mb-0 d-flex align-items-center">
                    <i class="fas fa-utensils me-2 text-primary"></i>
                    Pochie<span class="text-dark">Catering</span>
                </h1>
            </a>
            <button class="navbar-toggler py-2 px-3" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarCollapse">
                <span class="fa fa-bars text-primary"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a href="<?= $basePath ?>index.php" class="nav-link <?= isActive('index.php') ?>">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle <?= isActive(['wedding.php', 'debut.php', 'childrens.php', 'corporate.php', 'private.php']) ?>" 
                           data-bs-toggle="dropdown" role="button" aria-expanded="false">
                            <i class="fas fa-concierge-bell me-1"></i>Catering Services
                        </a>
                        <ul class="dropdown-menu shadow-sm border-0 rounded-3">
                            <li><a href="<?= $cateringBase ?>wedding.php" class="dropdown-item py-2">
                                <i class="fas fa-ring me-2"></i>Wedding Catering Services
                            </a></li>
                            <li><a href="<?= $cateringBase ?>debut.php" class="dropdown-item py-2">
                                <i class="fas fa-star me-2"></i>Debut Catering Services
                            </a></li>
                            <li><a href="<?= $cateringBase ?>childrens.php" class="dropdown-item py-2">
                                <i class="fas fa-child me-2"></i>Children's Party Catering Services
                            </a></li>
                            <li><a href="<?= $cateringBase ?>corporate.php" class="dropdown-item py-2">
                                <i class="fas fa-briefcase me-2"></i>Corporate Catering Services
                            </a></li>
                            <li><a href="<?= $cateringBase ?>private.php" class="dropdown-item py-2">
                                <i class="fas fa-users me-2"></i>Private Party Catering Services
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $basePath ?>about.php" class="nav-link <?= isActive('about.php') ?>">
                            <i class="fas fa-info-circle me-1"></i>About Us
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $basePath ?>contact.php" class="nav-link <?= isActive('contact.php') ?>">
                            <i class="fas fa-envelope me-1"></i>Contact
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a href="<?= $basePath ?>profile.php" class="nav-link <?= isActive('profile.php') ?>">
                                <i class="fas fa-user me-1"></i>Profile
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <div class="theme-switcher">
                        <button id="theme-toggle" class="btn btn-sm btn-outline-secondary rounded-circle p-2" title="Toggle theme" style="width: 38px; height: 38px;">
                            <i class="fas fa-moon"></i>
                        </button>
                    </div>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Notification Bell -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary rounded-circle p-2 position-relative" type="button" id="notificationBell" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications" style="width: 38px; height: 38px;">
                                <i class="fas fa-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-count" style="font-size: 0.65em;">0</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end notification-dropdown shadow border-0 rounded-3 p-0" aria-labelledby="notificationBell" style="min-width: 300px; max-height: 400px; overflow-y: auto;">
                                <li class="dropdown-header bg-light px-3 py-2 border-bottom">Notifications</li>
                                <div id="notification-list" class="p-0"></div>
                                <li class="dropdown-item text-center text-muted py-2 border-top">
                                    <small>No more notifications</small>
                                </li>
                            </ul>
                        </div>
                        <!-- Inbox Icon -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary rounded-circle p-2 position-relative" type="button" id="inboxIcon" data-bs-toggle="dropdown" aria-expanded="false" title="Messages" style="width: 38px; height: 38px;">
                                <i class="fas fa-envelope"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary message-count" style="font-size: 0.65em;">0</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end message-dropdown shadow border-0 rounded-3 p-0" aria-labelledby="inboxIcon" style="min-width: 300px; max-height: 400px; overflow-y: auto;">
                                <li class="dropdown-header bg-light px-3 py-2 border-bottom">Unread Messages</li>
                                <div id="message-list" class="p-0"></div>
                                <li class="dropdown-item text-center text-muted py-2 border-top">
                                    <small>No more messages</small>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= $basePath ?>auth/login.php" class="btn btn-outline-primary btn-sm me-2 px-3">Log In</a>
                        <a href="<?= $basePath ?>auth/signup.php" class="btn btn-primary btn-sm px-3">Sign Up</a>
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
                $('.notification-count').text(response.unread_count || 0);

                // Update notification list
                const notificationList = $('#notification-list');
                notificationList.empty();
                if (response.notifications.length === 0) {
                    notificationList.append('<li class="dropdown-item text-center text-muted py-3">No notifications</li>');
                } else {
                    response.notifications.forEach(notif => {
                        const isUnread = notif.is_read == 0 ? 'bg-light' : '';
                        const date = new Date(notif.created_at).toLocaleString();
                        notificationList.append(`
                            <li class="dropdown-item ${isUnread} p-0">
                                <a href="#" class="d-block px-3 py-3 border-bottom">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-info-circle text-primary me-2 mt-1"></i>
                                        <div class="flex-grow-1">
                                            <div>${notif.message}</div>
                                            <small class="text-muted">${date}</small>
                                        </div>
                                    </div>
                                </a>
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
                $('.notification-count').text('0').hide().fadeIn();
                fetchNotifications(); // Refresh list
            },
            error: function(xhr, status, error) {
                console.error('Error marking notifications as read:', error);
            }
        });
    });

    // Real-Time Unread Messages
    function fetchUnreadMessages() {
        console.log('Fetching unread messages...');
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
                $('.message-count').text(response.unread_count || 0);

                // Update message list
                const messageList = $('#message-list');
                messageList.empty();
                if (response.messages.length === 0) {
                    messageList.append('<li class="dropdown-item text-center text-muted py-3">No unread messages</li>');
                } else {
                    response.messages.forEach(msg => {
                        const date = new Date(msg.created_at).toLocaleString();
                        messageList.append(`
                            <li class="dropdown-item p-0">
                                <a href="#" class="d-block px-3 py-3 border-bottom">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-envelope-open-text text-info me-2 mt-1"></i>
                                        <div class="flex-grow-1">
                                            <div><strong>${msg.event_type}</strong>: ${msg.message}</div>
                                            <small class="text-muted">${date}</small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        `);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching messages:', error);
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
                $('.message-count').text('0').hide().fadeIn();
                fetchUnreadMessages(); // Refresh list
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