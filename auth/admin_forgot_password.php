<?php
require '../db.php';

// Include PHPMailer files with error handling
$phpMailerPath = '../phpmailer/src/';
$files = ['Exception.php', 'PHPMailer.php', 'SMTP.php'];

foreach ($files as $file) {
    $filePath = $phpMailerPath . $file;
    if (!file_exists($filePath)) {
        die("Error: PHPMailer file not found at $filePath. Please ensure the phpmailer folder is correctly placed in the project directory.");
    }
    require $filePath;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

$errorMsg = "";
$successMsg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Please enter a valid email.";
    } else {
        $stmt = $db->prepare("SELECT admin_id FROM admin_user WHERE email = :email AND status = 'active'");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $resetCode = sprintf("%06d", mt_rand(1, 999999));
            $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $stmt = $db->prepare("UPDATE admin_user SET reset_code = :code, reset_expires = :expires WHERE email = :email");
            $stmt->execute([':code' => $resetCode, ':expires' => $expires, ':email' => $email]);

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'bongbongcastro19@gmail.com'; // Replace with your email
                $mail->Password = 'rkdr iwjj hmyz gxhq'; // Replace with your app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('myemail@gmail.com', 'Pochie Catering Admin');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Admin Password Reset Code';
                $mail->Body = "Your password reset code is: <b>$resetCode</b>. It expires in 15 minutes.";

                $mail->send();
                $successMsg = "A reset code has been sent to your email.";
                $_SESSION['admin_reset_email'] = $email;
            } catch (Exception $e) {
                $errorMsg = "Failed to send reset code. Error: {$mail->ErrorInfo}";
            }
        } else {
            $errorMsg = "Email not found or account is inactive.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Forgot Password - Catering System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/admin.css" rel="stylesheet">
</head>
<body class="admin-login">
<div class="login-container">
    <div class="login-card text-center">
        <h2 class="mb-4">Admin Forgot Password</h2>

        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php endif; ?>
        <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMsg); ?></div>
            <a href="admin_reset_password.php" class="btn btn-primary">Enter Reset Code</a>
        <?php else: ?>
            <form method="post" action="admin_forgot_password.php">
                <div class="mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Send Reset Code</button>
                </div>
            </form>
            <p class="mt-3"><a href="admin_login.php">Back to Login</a></p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>