<?php 
require_once 'db.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    echo "<script>
        alert('You are not logged in. Please log in first.');
        window.location.href = 'login.php';
    </script>";
    exit;
}
// Fetch user details
$stmt = $db->prepare("SELECT first_name, last_name, email, profile_picture, address, contact_no FROM users WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Dummy data if user details are missing
if (!$user) {
    $user = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'johndoe@example.com',
        'profile_picture' => 'default-profile.png',
        'address' => '123 Main Street, City, Country',
        'contact_no' => '+1234567890'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pochie Catering Service - Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="./styles/style.css">

</head>
<body>
<?php include_once './layout/header.php'; ?>

<div class="container mt-5">
    <h1 class="mb-4 text-center">Profile</h1>
    
    <div class="card p-4 shadow-sm mx-auto" style="max-width: 500px;">
        <div class="text-center">
            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                 class="rounded-circle border border-secondary p-1" width="150" height="150" alt="Profile Picture">
        </div>
        <h3 class="mt-3 text-center"><?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?></h3>
        <p class="text-center text-muted"><?php echo htmlspecialchars($user['email']); ?></p>

        <hr>
        
        <p><i class="fas fa-home"></i> <strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
        <p><i class="fas fa-phone"></i> <strong>Contact No:</strong> <?php echo htmlspecialchars($user['contact_no']); ?></p>

        <div class="d-flex justify-content-center mt-4">
            <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</div>

</body>
</html>
