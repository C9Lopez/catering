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
        $stmt->execute([$title, $description, $targetFile, $status]); // Include selected status
        echo "<script>alert('The announcement has been posted successfully.');</script>";
    } else {
        die("Sorry, there was an error uploading your file.");
    }
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];

    // Prepare the delete statement
    try {
        $stmt = $db->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->execute([$id]);

        // After deletion, redirect back to announcement page
        header("Location: announcement.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Fetch announcements based on the filter status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
try {
    if ($status_filter === 'all') {
        $stmt = $db->prepare("SELECT * FROM announcements");
    } else {
        $stmt = $db->prepare("SELECT * FROM announcements WHERE status = ?");
        $stmt->execute([$status_filter]);
    }
    $stmt->execute();
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
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($announcements as $announcement): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($announcement['title']); ?></td>
                        <td><?php echo htmlspecialchars($announcement['description']); ?></td>
                        <td>
                            <?php if ($announcement['status'] === 'preview'): ?>
                                <a href="publish_announcement.php?id=<?php echo $announcement['id']; ?>&action=publish" class="btn btn-success">Publish</a>
                            <?php elseif ($announcement['status'] === 'live'): ?>
                                <a href="unpublish_announcement.php?id=<?php echo $announcement['id']; ?>&action=unpublish" class="btn btn-warning">Unpublish</a>
                            <?php endif; ?>
                            <a href="?action=delete&id=<?php echo $announcement['id']; ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    
    
    

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>
</body>
</html>
