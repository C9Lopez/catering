<?php
require '../db.php';
session_start();

$loginError = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $db->prepare("SELECT * FROM admin_user WHERE email = :email AND status = 'active'");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (password_verify($password, $result['password'])) {
            $_SESSION['admin_id'] = $result['admin_id'];
            $_SESSION['admin_email'] = $result['email'];
            $_SESSION['admin_name'] = $result['first_name'] . ' ' . $result['last_name'];
            $_SESSION['admin_role'] = $result['role'];

            header("Location: ../admin/");
            exit();
        } else {
            $loginError = "Invalid email or password.";
        }
    } else {
        $loginError = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Login - Catering System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/admin.css" rel="stylesheet">
</head>
<body class="admin-login">
<div class="login-container">
    <div class="login-card">
        <div class="text-center mb-4">
            <img src="../images/logo.png" alt="Catering Logo" class="login-logo">
            <h2 class="mt-3">Admin Login</h2>
        </div>

        <?php if (!empty($loginError)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($loginError); ?></div>
        <?php endif; ?>

        <form method="post" action="admin_login.php">
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
        <p class="mt-3">Forgot your password? <a href="admin_forgot_password.php">Reset it here</a></p>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>