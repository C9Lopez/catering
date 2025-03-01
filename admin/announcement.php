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
    $title = $_POST['title'];
    $description = $_POST['description'];
    $media = $_FILES['media'];
    $status = isset($_POST['status']) ? $_POST['status'] : 'preview'; // Default to 'preview' if not set

    // Validate inputs
    if (empty($title) || empty($description) || empty($media['name'])) {
        die("Please fill in all fields.");
    }

    // Handle file upload
    $targetDir = "./uploads/"; // Ensure this directory exists and is writable
    $targetFile = $targetDir . time() . '_' . str_replace(' ', '_', basename($media["name"]));
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check file size (limit to 5MB)
    if ($media["size"] > 5000000) {
        die("Sorry, your file is too large.");
    }

    // Allow certain file formats
    if ($fileType != "jpg" && $fileType != "png" && $fileType != "jpeg" && $fileType != "gif" && $fileType != "mp4") {
        die("Sorry, only JPG, JPEG, PNG, GIF, and MP4 files are allowed.");
    }

    // Attempt to move the uploaded file
    if (move_uploaded_file($media["tmp_name"], $targetFile)) {
        // Insert announcement into the database with the selected status
        $stmt = $db->prepare("INSERT INTO announcements (title, description, media_path, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $description, $targetFile, $status]);
        echo "<script>alert('The announcement has been posted successfully.');</script>";
    } else {
        die("Sorry, there was an error uploading your file.");
    }
}

// Handle delete action via AJAX
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $db->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'id' => $id]);
        exit();
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit();
    }
}

// Fetch announcements based on the filter status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
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
    echo '<div class="alert alert-warning">Unable to load announcements</div>';
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
</head>
<body>
<?php include '../layout/sidebar.php'; ?>
<div class="main-content">
    <div class="container-fluid mt-5">
        <h1>Post Announcement</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" name="title" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description" required></textarea>
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

        <h2 class="mt-5">Filter Announcements</h2>
        <div class="btn-group" role="group" aria-label="Status Filter">
            <a href="announcement.php?status=all" class="btn btn-info">All</a>
            <a href="announcement.php?status=preview" class="btn btn-warning">Preview Only</a>
            <a href="announcement.php?status=live" class="btn btn-success">Live Only</a>
        </div>

        <h2 class="mt-5"><?php echo ucfirst($status_filter); ?> Announcements</h2>

        <div class="row">
            <?php foreach ($announcements as $announcement): ?>
                <div class="col-md-4 mb-4 announcement-card" id="announcement-<?php echo $announcement['id']; ?>">
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($announcement['media_path']); ?>" class="card-img-top" alt="Announcement Image">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($announcement['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($announcement['description']); ?></p>
                            <div class="d-flex justify-content-between">
                                <?php if ($announcement['status'] === 'preview'): ?>
                                    <!-- Publish action handled via AJAX -->
                                    <button type="button" class="btn btn-success publish-announcement" data-id="<?php echo $announcement['id']; ?>">Publish</button>
                                <?php elseif ($announcement['status'] === 'live'): ?>
                                    <!-- Unpublish action handled via AJAX -->
                                    <button type="button" class="btn btn-warning unpublish-announcement" data-id="<?php echo $announcement['id']; ?>">Unpublish</button>
                                <?php endif; ?>
                                <button class="btn btn-danger delete-announcement" data-id="<?php echo $announcement['id']; ?>">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/admin.js"></script>
<script>
    // Capture current filter from PHP to adjust behavior after AJAX actions
    let statusFilter = "<?php echo $status_filter; ?>";

    // Handle delete with AJAX
    $(document).on('click', '.delete-announcement', function() {
        var announcementId = $(this).data('id');
        var announcementCard = $('#announcement-' + announcementId);

        $.ajax({
            url: 'announcement.php?action=delete&id=' + announcementId,
            type: 'GET',
            success: function(response) {
                var data = JSON.parse(response);
                if (data.status === 'success') {
                    announcementCard.remove();
                } else {
                    alert('Error deleting announcement');
                }
            },
            error: function() {
                alert('Something went wrong.');
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
                    // If the current filter shows only live announcements, remove the card.
                    if (statusFilter === 'live') {
                        announcementCard.remove();
                    } else {
                        // Otherwise, replace the Unpublish button with a Publish button.
                        button.replaceWith('<button type="button" class="btn btn-success publish-announcement" data-id="'+announcementId+'">Publish</button>');
                    }
                } else {
                    alert('Error unpublishing announcement');
                }
            },
            error: function() {
                alert('Something went wrong.');
            }
        });
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
                    // If current filter shows only preview announcements, remove the card.
                    if (statusFilter === 'preview') {
                        announcementCard.remove();
                    } else {
                        // Otherwise, replace the Publish button with an Unpublish button.
                        button.replaceWith('<button type="button" class="btn btn-warning unpublish-announcement" data-id="'+announcementId+'">Unpublish</button>');
                    }
                } else {
                    alert('Error publishing announcement');
                }
            },
            error: function() {
                alert('Something went wrong.');
            }
        });
    });
</script>
</body>
</html>
