<?php
require '../db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

// Handle form submission for posting announcements
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        // Handle file upload
        $targetDir = "./uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        $targetFile = $targetDir . time() . '_' . str_replace(' ', '_', basename($media["name"]));
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check file size (limit to 5MB)
        if ($media["size"] > 5000000) {
            $errors[] = "Sorry, your file is too large.";
        } elseif (!in_array($fileType, ["jpg", "png", "jpeg", "gif", "mp4"])) {
            $errors[] = "Sorry, only JPG, JPEG, PNG, GIF, and MP4 files are allowed.";
        } elseif (move_uploaded_file($media["tmp_name"], $targetFile)) {
            try {
                $stmt = $db->prepare("INSERT INTO announcements (title, description, media_path, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([$title, $description, $targetFile, $status]);
                header("Location: announcement.php?success=post");
                exit();
            } catch (PDOException $e) {
                $errors[] = "Error posting announcement: " . $e->getMessage();
            }
        } else {
            $errors[] = "Sorry, there was an error uploading your file.";
        }
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: announcement.php");
        exit();
    }
}

// Handle delete action via AJAX
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => ''];

    $id = $_GET['id'];
    try {
        // Start transaction
        $db->beginTransaction();

        // Fetch the announcement to get the media path
        $selectStmt = $db->prepare("SELECT media_path FROM announcements WHERE id = ?");
        $selectStmt->execute([$id]);
        $announcement = $selectStmt->fetch(PDO::FETCH_ASSOC);

        if ($announcement) {
            // Delete the media file if it exists
            if (file_exists($announcement['media_path'])) {
                unlink($announcement['media_path']) or $response['message'] = "Failed to delete media file.";
            }

            // Delete the announcement from the database
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

// Fetch announcements based on the filter status
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/admin.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
    </style>
</head>
<body>
<?php include '../layout/sidebar.php'; ?>
<div class="main-content">
    <div class="container-fluid mt-5">
        <h1>Post Announcement</h1>
        <div class="form-section">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" name="title" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="5" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="media" class="form-label">Media</label>
                    <input type="file" class="form-control" name="media" required>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="live">Live</option>
                        <option value="preview" selected>Preview</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Post Announcement</button>
            </form>
        </div>

        <h2 class="mt-5">Filter Announcements</h2>
        <div class="btn-group mb-4" role="group" aria-label="Status Filter">
            <a href="announcement.php?status=all" class="btn btn-info <?php echo $status_filter === 'all' ? 'active' : ''; ?>">All</a>
            <a href="announcement.php?status=preview" class="btn btn-warning <?php echo $status_filter === 'preview' ? 'active' : ''; ?>">Preview Only</a>
            <a href="announcement.php?status=live" class="btn btn-success <?php echo $status_filter === 'live' ? 'active' : ''; ?>">Live Only</a>
        </div>

        <h2 class="mt-5"><?php echo ucfirst($status_filter === 'all' ? 'All' : $status_filter); ?> Announcements</h2>

        <div class="row">
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="../js/admin.js"></script>
<script>
    // Display success/error messages from form submission using Toastify
    <?php
    if (isset($_SESSION['errors'])) {
        foreach ($_SESSION['errors'] as $error) {
            echo "Toastify({
                text: '" . addslashes($error) . "',
                duration: 3000,
                close: true,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#dc3545',
            }).showToast();";
        }
        unset($_SESSION['errors']);
    }
    if (isset($_GET['success']) && $_GET['success'] === 'post') {
        echo "Toastify({
            text: 'The announcement has been posted successfully',
            duration: 3000,
            close: true,
            gravity: 'top',
            position: 'right',
            backgroundColor: '#28a745',
        }).showToast();";
    }
    ?>

    // Clear success parameter from URL after displaying
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.pathname + window.location.search.replace(/success=post(&|$)/, ''));
    }

    // Capture current filter from PHP
    let statusFilter = "<?php echo $status_filter; ?>";

    // Handle delete with AJAX and confirmation
    $(document).on('click', '.delete-announcement', function() {
        var announcementId = $(this).data('id');
        var announcementCard = $('#announcement-' + announcementId);

        if (confirm('Are you sure you want to delete this announcement? This action cannot be undone.')) {
            $.ajax({
                url: 'announcement.php?action=delete&id=' + announcementId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        announcementCard.fadeOut(300, function() {
                            $(this).remove();
                            if ($('.announcement-card').length === 0) {
                                $('.row').html('<div class="col-12"><div class="alert alert-info text-center">No announcements found.</div></div>');
                            }
                        });
                        Toastify({
                            text: response.message,
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#28a745",
                        }).showToast();
                    } else {
                        Toastify({
                            text: response.message || "Error deleting announcement",
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#dc3545",
                        }).showToast();
                    }
                },
                error: function(xhr, status, error) {
                    Toastify({
                        text: "Error deleting announcement: " + (xhr.responseJSON?.message || error),
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#dc3545",
                    }).showToast();
                }
            });
        }
    });

    // Handle publish with AJAX
    $(document).on('click', '.publish-announcement', function(e) {
        e.preventDefault();
        var announcementId = $(this).data('id');
        var button = $(this);
        var announcementCard = $('#announcement-' + announcementId);

        $.ajax({
            url: 'publish_announcement.php?action=publish&id=' + announcementId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    if (statusFilter === 'preview') {
                        announcementCard.fadeOut(300, function() {
                            $(this).remove();
                            if ($('.announcement-card').length === 0) {
                                $('.row').html('<div class="col-12"><div class="alert alert-info text-center">No announcements found.</div></div>');
                            }
                        });
                    } else {
                        button.replaceWith('<button type="button" class="btn btn-warning btn-sm unpublish-announcement" data-id="'+announcementId+'">Unpublish</button>');
                    }
                    Toastify({
                        text: "Announcement published successfully",
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28a745",
                    }).showToast();
                } else {
                    Toastify({
                        text: response.message || "Error publishing announcement",
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#dc3545",
                    }).showToast();
                }
            },
            error: function() {
                Toastify({
                    text: "Something went wrong while publishing the announcement",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#dc3545",
                }).showToast();
            }
        });
    });

    // Handle unpublish with AJAX
    $(document).on('click', '.unpublish-announcement', function(e) {
        e.preventDefault();
        var announcementId = $(this).data('id');
        var button = $(this);
        var announcementCard = $('#announcement-' + announcementId);

        $.ajax({
            url: 'unpublish_announcement.php?action=unpublish&id=' + announcementId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    if (statusFilter === 'live') {
                        announcementCard.fadeOut(300, function() {
                            $(this).remove();
                            if ($('.announcement-card').length === 0) {
                                $('.row').html('<div class="col-12"><div class="alert alert-info text-center">No announcements found.</div></div>');
                            }
                        });
                    } else {
                        button.replaceWith('<button type="button" class="btn btn-success btn-sm publish-announcement" data-id="'+announcementId+'">Publish</button>');
                    }
                    Toastify({
                        text: "Announcement unpublished successfully",
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28a745",
                    }).showToast();
                } else {
                    Toastify({
                        text: response.message || "Error unpublishing announcement",
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#dc3545",
                    }).showToast();
                }
            },
            error: function() {
                Toastify({
                    text: "Something went wrong while unpublishing the announcement",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#dc3545",
                }).showToast();
            }
        });
    });
</script>
</body>
</html>