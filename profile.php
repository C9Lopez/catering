<?php 
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<p>Not logged in.</p>";
    exit;
}

// Fetch user details
$stmt = $db->prepare("SELECT first_name, last_name, email, profile_picture, address, contact_no FROM users WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pochie Catering Service - Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<?php include_once './layout/header.php'?>

<h1 class="mb-3">Profile</h1>

<?php if ($user): ?>
    <div class="card p-4 shadow-sm">
        <div class="text-center">
            <img src="<?php echo $user['profile_picture'] ? $user['profile_picture'] : 'default-profile.png'; ?>" 
                 class="rounded-circle" width="150" height="150" alt="Profile Picture">
        </div>
        <h3 class="mt-3"><?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?></h3>
        <p>Email: <strong><?php echo htmlspecialchars($user['email']); ?></strong></p>
        <p>Address: <strong><?php echo htmlspecialchars($user['address']); ?></strong></p>
        <p>Contact No: <strong><?php echo htmlspecialchars($user['contact_no']); ?></strong></p>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
<?php else: ?>
    <p class="text-danger">User not found.</p>
<?php endif; ?>

</body>
</html>
