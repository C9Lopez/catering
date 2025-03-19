<?php
require '../db.php';
session_start();

$errorMsg = "";
$successMsg = "";

if (!isset($_SESSION['admin_reset_email'])) {
    header("Location: admin_forgot_password.php");
    exit();
}

$email = $_SESSION['admin_reset_email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST['code']);
    $new_password = trim($_POST['new_password']);

    $stmt = $db->prepare("SELECT reset_code, reset_expires FROM admin_user WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && $result['reset_code'] === $code && new DateTime() < new DateTime($result['reset_expires'])) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE admin_user SET password = :password, reset_code = NULL, reset_expires = NULL WHERE email = :email");
        $stmt->execute([':password' => $hashed_password, ':email' => $email]);
        $successMsg = "Password reset successfully! <a href='admin_login.php'>Login here</a>";
        unset($_SESSION['admin_reset_email']);
    } else {
        $errorMsg = "Invalid or expired reset code.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Reset Password - Catering System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/admin.css" rel="stylesheet">
</head>
<body class="admin-login">
<div class="login-container">
    <div class="login-card text-center">
        <h2 class="mb-4">Admin Reset Password</h2>

        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php endif; ?>
        <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success"><?php echo $successMsg; ?></div>
        <?php else: ?>
            <form method="post" action="admin_reset_password.php">
                <div class="mb-3">
                    <input type="text" name="code" class="form-control" placeholder="Enter reset code" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="new_password" class="form-control" placeholder="New Password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </div>
            </form>
            <p class="mt-3"><a href="admin_forgot_password.php">Resend Code</a></p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>