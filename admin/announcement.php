<?php
require '../db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

// Handle form submission for posting announcements via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => ''];

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $media = $_FILES['media'];
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'preview';

    // Validate inputs
    $errors = [];
    if (empty($title)) $errors[] = "Title is required";
    if (empty($description)) $errors[] = "Description is required";
    if (empty($media['name'])) $errors[] = "Media is required";

    if (empty($errors)) {
        $targetDir = "./Uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        $targetFile = $targetDir . time() . '_' . str_replace(' ', '_', basename($media["name"]));
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        if ($media["size"] > 5000000) {
            $errors[] = "Sorry, your file is too large.";
        } elseif (!in_array($fileType, ["jpg", "png", "jpeg", "gif", "mp4"])) {
            $errors[] = "Sorry, only JPG, JPEG, PNG, GIF, and MP4 files are allowed.";
        } elseif (move_uploaded_file($media["tmp_name"], $targetFile)) {
            try {
                $stmt = $db->prepare("INSERT INTO announcements (title, description, media_path, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([$title, $description, $targetFile, $status]);
                $newAnnouncementId = $db->lastInsertId();
                $response['status'] = 'success';
                $response['message'] = $status === 'live' ? 'Announcement posted as Live successfully' : 'Announcement posted as Preview successfully';
                $response['announcement'] = [
                    'id' => $newAnnouncementId,
                    'title' => $title,
                    'description' => $description,
                    'media_path' => $targetFile,
                    'status' => $status
                ];
            } catch (PDOException $e) {
                $errors[] = "Error posting announcement: " . $e->getMessage();
                error_log("Database error: " . $e->getMessage());
            }
        } else {
            $errors[] = "Sorry, there was an error uploading your file.";
            error_log("File upload error: Unable to move uploaded file to $targetFile");
        }
    }

    if (!empty($errors)) {
        $response['message'] = implode(", ", $errors);
    }

    echo json_encode($response);
    exit();
}

// Handle delete action via AJAX
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => ''];

    $id = $_GET['id'];
    try {
        $db->beginTransaction();
        $selectStmt = $db->prepare("SELECT media_path FROM announcements WHERE id = ?");
        $selectStmt->execute([$id]);
        $announcement = $selectStmt->fetch(PDO::FETCH_ASSOC);

        if ($announcement) {
            if (file_exists($announcement['media_path'])) {
                unlink($announcement['media_path']) or $response['message'] = "Failed to delete media file.";
            }
            $deleteStmt = $db->prepare("DELETE FROM announcements WHERE id = ?");
            $deleteStmt->execute([$id]);
            if ($deleteStmt->rowCount() > 0) {
                $response['status'] = 'success';
                $response['message'] = 'Announcement deleted successfully';
            } else {
                $response['message'] = 'Announcement not found or already deleted';
            }
        } else {
            $response['message'] = 'Invalid announcement ID';
        }
        $db->commit();
    } catch (PDOException $e) {
        $db->rollBack();
        $response['message'] = 'Error deleting announcement: ' . $e->getMessage();
    }
    echo json_encode($response);
    exit();
}

// Handle publish/unpublish action via AJAX
if (isset($_GET['action']) && in_array($_GET['action'], ['publish', 'unpublish']) && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => ''];

    $id = $_GET['id'];
    $newStatus = $_GET['action'] === 'publish' ? 'live' : 'preview';

    try {
        $stmt = $db->prepare("UPDATE announcements SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);

        if ($stmt->rowCount() > 0) {
            $response['status'] = 'success';
            $response['message'] = $_GET['action'] === 'publish' ? 'Announcement published successfully' : 'Announcement unpublished successfully';
            $response['new_status'] = $newStatus;
        } else {
            $response['message'] = 'Announcement not found or already in this status';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Error updating announcement status: ' . $e->getMessage();
    }

    echo json_encode($response);
    exit();
}

// Handle fetching announcements via AJAX
if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    header('Content-Type: application/json');
    $status_filter = isset($_GET['status']) ? trim($_GET['status']) : 'all';
    $response = ['status' => 'error', 'html' => ''];

    try {
        if ($status_filter === 'all') {
            $stmt = $db->prepare("SELECT * FROM announcements");
            $stmt->execute();
        } else {
            $stmt = $db->prepare("SELECT * FROM announcements WHERE status = ?");
            $stmt->execute([$status_filter]);
        }
        $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        ob_start();
        if (empty($announcements)) {
            echo '<div class="col-12"><div class="alert alert-info text-center">No announcements found.</div></div>';
        } else {
            foreach ($announcements as $announcement) {
                ?>
                <div class="col-md-4 mb-4 announcement-card" id="announcement-<?php echo $announcement['id']; ?>">
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($announcement['media_path']); ?>" class="card-img-top" alt="Announcement Image">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($announcement['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($announcement['description']); ?></p>
                            <div class="d-flex justify-content-between">
                                <?php if ($announcement['status'] === 'preview'): ?>
                                    <button type="button" class="btn btn-success btn-sm publish-announcement" data-id="<?php echo $announcement['id']; ?>">Publish</button>
                                <?php elseif ($announcement['status'] === 'live'): ?>
                                    <button type="button" class="btn btn-warning btn-sm unpublish-announcement" data-id="<?php echo $announcement['id']; ?>">Unpublish</button>
                                <?php endif; ?>
                                <button class="btn btn-danger btn-sm delete-announcement" data-id="<?php echo $announcement['id']; ?>">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        $response['html'] = ob_get_clean();
        $response['status'] = 'success';
    } catch (PDOException $e) {
        $response['html'] = '<div class="col-12"><div class="alert alert-danger text-center">Error loading announcements: ' . htmlspecialchars($e->getMessage()) . '</div></div>';
    }

    echo json_encode($response);
    exit();
}

// Initial page load: Fetch announcements
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : 'all';
try {
    if ($status_filter === 'all') {
        $stmt = $db->prepare("SELECT * FROM announcements");
        $stmt->execute();
    } else {
        $stmt = $db->prepare("SELECT * FROM announcements WHERE status = ?");
        $stmt->execute([$status_filter]);
    }
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['errors'] = ["Unable to load announcements: " . $e->getMessage()];
    header("Location: announcement.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Announcements - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../css/admin.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        .announcement-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .announcement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .announcement-card .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }
        .announcement-card .card-img-top {
            border-radius: 10px 10px 0 0;
            object-fit: cover;
            height: 200px;
        }
        .announcement-card .card-body {
            padding: 20px;
        }
        .announcement-card .card-title {
            color: #2c3e50;
            font-size: 1.25rem;
            margin-bottom: 10px;
        }
        .announcement-card .card-text {
            font-size: 0.9rem;
            color: #555;
            max-height: 80px;
            overflow-y: auto;
        }
        .btn-group .btn {
            border-radius: 5px;
            margin-right: 5px;
        }
        .form-section {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        @media (max-width: 768px) {
            .btn-group {
                flex-direction: column;
                gap: 10px;
            }
            .btn-group .btn {
                width: 100%;
                margin-right: 0;
            }
        }
        .toastify {
            z-index: 9999 !important;
            position: fixed !important;
        }
        /* Mobile Header Styles */
        .mobile-header {
            display: none;
            background: #fff;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            align-items: center;
            gap: 1rem;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #333;
            cursor: pointer;
            padding: 0.5rem;
        }
        .mobile-header-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
        }
        @media (max-width: 991.98px) {
            .mobile-header {
                display: flex;
            }
            .main-content {
                padding-top: 4rem;
            }
        }
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: -250px;
            width: 250px;
            height: 100%;
            background: #343a40;
            transition: all 0.3s;
            z-index: 1050;
        }
        .sidebar.active {
            left: 0;
        }
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            z-index: 1000;
        }
        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }
    </style>
</head>
<body>
    <?php include '../layout/sidebar.php'; ?>
    <div class="main-content">
        <header class="mobile-header">
            <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <h2 class="mobile-header-title">Announcements</h2>
        </header>
        <div class="container-fluid mt-5">
            <h1>Post Announcement</h1>
            <div class="form-section">
                <form method="POST" enctype="multipart/form-data" id="announcementForm">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" id="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="description" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="media" class="form-label">Media</label>
                        <input type="file" class="form-control" name="media" id="media" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="status">
                            <option value="live">Live</option>
                            <option value="preview" selected>Preview</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" id="postAnnouncementBtn">Post Announcement</button>
                </form>
            </div>

            <h2 class="mt-5">Filter Announcements</h2>
            <div class="btn-group mb-4" role="group" aria-label="Status Filter">
                <button type="button" class="btn btn-info filter-btn <?php echo $status_filter === 'all' ? 'active' : ''; ?>" data-status="all">All</button>
                <button type="button" class="btn btn-warning filter-btn <?php echo $status_filter === 'preview' ? 'active' : ''; ?>" data-status="preview">Preview Only</button>
                <button type="button" class="btn btn-success filter-btn <?php echo $status_filter === 'live' ? 'active' : ''; ?>" data-status="live">Live Only</button>
            </div>

            <h2 class="mt-5" id="announcementsTitle"><?php echo ucfirst($status_filter === 'all' ? 'All' : $status_filter); ?> Announcements</h2>

            <div class="row" id="announcementsList">
                <?php if (empty($announcements)): ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">No announcements found.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="col-md-4 mb-4 announcement-card" id="announcement-<?php echo $announcement['id']; ?>">
                            <div class="card">
                                <img src="<?php echo htmlspecialchars($announcement['media_path']); ?>" class="card-img-top" alt="Announcement Image">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($announcement['title']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($announcement['description']); ?></p>
                                    <div class="d-flex justify-content-between">
                                        <?php if ($announcement['status'] === 'preview'): ?>
                                            <button type="button" class="btn btn-success btn-sm publish-announcement" data-id="<?php echo $announcement['id']; ?>">Publish</button>
                                        <?php elseif ($announcement['status'] === 'live'): ?>
                                            <button type="button" class="btn btn-warning btn-sm unpublish-announcement" data-id="<?php echo $announcement['id']; ?>">Unpublish</button>
                                        <?php endif; ?>
                                        <button class="btn btn-danger btn-sm delete-announcement" data-id="<?php echo $announcement['id']; ?>">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        $(document).ready(function() {
            // Sidebar Toggle Functionality
            $('#sidebarToggle').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Sidebar toggle clicked');
                $('#sidebar').toggleClass('active');
                $('#sidebarOverlay').toggleClass('active');
            });

            $('#sidebarOverlay, #sidebarClose').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Overlay or close button clicked');
                $('#sidebar').removeClass('active');
                $('#sidebarOverlay').removeClass('active');
            });

            // Current filter state
            let currentFilter = "<?php echo $status_filter; ?>";

            // Function to show Toastify notification
            function showToast(message, backgroundColor) {
                console.log("Showing Toastify notification: " + message);
                Toastify({
                    text: message,
                    duration: 8000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: backgroundColor,
                }).showToast();
            }

            // Handle form submission via AJAX
            $('#announcementForm').on('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log("Form submission prevented");

                var formData = new FormData(this);

                $.ajax({
                    url: 'announcement.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(response) {
                        console.log("Post AJAX success response:", response);
                        if (response.status === 'success') {
                            if (response.announcement) {
                                var announcement = response.announcement;
                                if (currentFilter === 'all' || currentFilter === announcement.status) {
                                    var announcementHtml = `
                                        <div class="col-md-4 mb-4 announcement-card" id="announcement-${announcement.id}">
                                            <div class="card">
                                                <img src="${announcement.media_path}" class="card-img-top" alt="Announcement Image">
                                                <div class="card-body">
                                                    <h5 class="card-title">${announcement.title}</h5>
                                                    <p class="card-text">${announcement.description}</p>
                                                    <div class="d-flex justify-content-between">
                                                        ${announcement.status === 'preview' ? 
                                                            `<button type="button" class="btn btn-success btn-sm publish-announcement" data-id="${announcement.id}">Publish</button>` : 
                                                            `<button type="button" class="btn btn-warning btn-sm unpublish-announcement" data-id="${announcement.id}">Unpublish</button>`
                                                        }
                                                        <button class="btn btn-danger btn-sm delete-announcement" data-id="${announcement.id}">Delete</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                    if ($('#announcementsList').find('.alert-info').length > 0) {
                                        $('#announcementsList').empty();
                                    }
                                    $('#announcementsList').prepend(announcementHtml);
                                    console.log("New announcement added to the list");
                                }
                            }
                            $('#announcementForm')[0].reset();
                            console.log("Form reset");
                            showToast(response.message, "#28a745");
                        } else {
                            showToast(response.message || "Error posting announcement", "#dc3545");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("Post AJAX error:", status, error);
                        showToast("Error posting announcement: " + (xhr.responseJSON?.message || error), "#dc3545");
                    }
                });
            });

            // Handle filter button clicks
            $('.filter-btn').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log("Filter button clicked");

                const status = $(this).data('status');
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
                $('#announcementsTitle').text(status === 'all' ? 'All Announcements' : (status.charAt(0).toUpperCase() + status.slice(1) + ' Announcements'));
                currentFilter = status;

                $.ajax({
                    url: 'announcement.php',
                    type: 'GET',
                    data: { action: 'fetch', status: status },
                    dataType: 'json',
                    success: function(response) {
                        console.log("Filter AJAX success response:", response);
                        if (response.status === 'success') {
                            $('#announcementsList').html(response.html);
                        } else {
                            $('#announcementsList').html('<div class="col-12"><div class="alert alert-danger text-center">Error loading announcements.</div></div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("Filter AJAX error:", status, error);
                        $('#announcementsList').html('<div class="col-12"><div class="alert alert-danger text-center">Error loading announcements: ' + error + '</div></div>');
                    }
                });
            });

            // Handle delete with AJAX and confirmation
            $(document).on('click', '.delete-announcement', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log("Delete button clicked");

                var announcementId = $(this).data('id');
                var announcementCard = $('#announcement-' + announcementId);

                if (confirm('Are you sure you want to delete this announcement? This action cannot be undone.')) {
                    $.ajax({
                        url: 'announcement.php?action=delete&id=' + announcementId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            console.log("Delete AJAX success response:", response);
                            if (response.status === 'success') {
                                announcementCard.fadeOut(300, function() {
                                    $(this).remove();
                                    if ($('.announcement-card').length === 0) {
                                        $('#announcementsList').html('<div class="col-12"><div class="alert alert-info text-center">No announcements found.</div></div>');
                                    }
                                });
                                showToast(response.message, "#28a745");
                            } else {
                                showToast(response.message || "Error deleting announcement", "#dc3545");
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log("Delete AJAX error:", status, error);
                            showToast("Error deleting announcement: " + (xhr.responseJSON?.message || error), "#dc3545");
                        }
                    });
                }
            });

            // Handle publish with AJAX
            $(document).on('click', '.publish-announcement', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log("Publish button clicked");

                var announcementId = $(this).data('id');
                var announcementCard = $('#announcement-' + announcementId);

                $.ajax({
                    url: 'announcement.php?action=publish&id=' + announcementId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("Publish AJAX success response:", response);
                        if (response.status === 'success') {
                            if (currentFilter === 'preview') {
                                announcementCard.fadeOut(300, function() {
                                    $(this).remove();
                                    if ($('.announcement-card').length === 0) {
                                        $('#announcementsList').html('<div class="col-12"><div class="alert alert-info text-center">No announcements found.</div></div>');
                                    }
                                });
                            } else {
                                announcementCard.find('.publish-announcement').replaceWith(
                                    `<button type="button" class="btn btn-warning btn-sm unpublish-announcement" data-id="${announcementId}">Unpublish</button>`
                                );
                            }
                            showToast(response.message, "#28a745");
                        } else {
                            showToast(response.message || "Error publishing announcement", "#dc3545");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("Publish AJAX error:", status, error);
                        showToast("Error publishing announcement: " + (xhr.responseJSON?.message || error), "#dc3545");
                    }
                });
            });

            // Handle unpublish with AJAX
            $(document).on('click', '.unpublish-announcement', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log("Unpublish button clicked");

                var announcementId = $(this).data('id');
                var announcementCard = $('#announcement-' + announcementId);

                $.ajax({
                    url: 'announcement.php?action=unpublish&id=' + announcementId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("Unpublish AJAX success response:", response);
                        if (response.status === 'success') {
                            if (currentFilter === 'live') {
                                announcementCard.fadeOut(300, function() {
                                    $(this).remove();
                                    if ($('.announcement-card').length === 0) {
                                        $('#announcementsList').html('<div class="col-12"><div class="alert alert-info text-center">No announcements found.</div></div>');
                                    }
                                });
                            } else {
                                announcementCard.find('.unpublish-announcement').replaceWith(
                                    `<button type="button" class="btn btn-success btn-sm publish-announcement" data-id="${announcementId}">Publish</button>`
                                );
                            }
                            showToast(response.message, "#28a745");
                        } else {
                            showToast(response.message || "Error unpublishing announcement", "#dc3545");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("Unpublish AJAX error:", status, error);
                        showToast("Error unpublishing announcement: " + (xhr.responseJSON?.message || error), "#dc3545");
                    }
                });
            });
        });
    </script>
</body>
</html>