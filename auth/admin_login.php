<?php
require '../db.php';
session_start();

// Clear the signup_success flag if it exists
if (isset($_SESSION['signup_success'])) {
    unset($_SESSION['signup_success']);
}

// Rate limiting setup
$ip = $_SERVER['REMOTE_ADDR'];
$attempts_key = "admin_login_attempts_$ip";
$lockout_key = "admin_login_lockout_$ip";
$max_attempts = 5;
$lockout_time = 15 * 60; // 15 minutes

// Check for "Remember Me" cookie on page load
if (!isset($_SESSION['admin_id']) && isset($_COOKIE['admin_remember'])) {
    $token = $_COOKIE['admin_remember'];
    $stmt = $db->prepare("SELECT * FROM admin_user WHERE remember_token = :token AND status = 'active'");
    $stmt->bindParam(':token', $token);
    $stmt->execute();

    if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Valid token found, log the user in
        $_SESSION['admin_id'] = $result['admin_id'];
        $_SESSION['admin_email'] = $result['email'];
        $_SESSION['admin_name'] = $result['first_name'] . ' ' . $result['last_name'];
        $_SESSION['admin_role'] = $result['role'];
        header("Location: ../admin/");
        exit();
    } else {
        // Invalid token, clear the cookie
        setcookie('admin_remember', '', time() - 3600, '/');
    }
}

if (isset($_SESSION[$lockout_key]) && time() < $_SESSION[$lockout_key]) {
    $loginError = "Too many login attempts. Please try again later.";
} else {
    if (!isset($_SESSION[$attempts_key])) {
        $_SESSION[$attempts_key] = 0;
    }

    // CSRF Token
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $loginError = "";
    $loginSuccess = false;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $loginError = "Invalid request. Please try again.";
        } else {
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $remember = isset($_POST['remember']) ? true : false;

            $stmt = $db->prepare("SELECT * FROM admin_user WHERE email = :email AND status = 'active'");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (password_verify($password, $result['password'])) {
                    $_SESSION['admin_id'] = $result['admin_id'];
                    $_SESSION['admin_email'] = $result['email'];
                    $_SESSION['admin_name'] = $result['first_name'] . ' ' . $result['last_name'];
                    $_SESSION['admin_role'] = $result['role'];
                    $_SESSION[$attempts_key] = 0; // Reset attempts

                    // Handle "Remember Me" functionality
                    if ($remember) {
                        // Generate a secure token
                        $token = bin2hex(random_bytes(32));
                        // Store the token in the database
                        $stmt = $db->prepare("UPDATE admin_user SET remember_token = :token WHERE admin_id = :admin_id");
                        $stmt->bindParam(':token', $token);
                        $stmt->bindParam(':admin_id', $result['admin_id']);
                        $stmt->execute();
                        // Set the cookie (30 days expiry)
                        setcookie('admin_remember', $token, time() + (30 * 24 * 60 * 60), '/');
                    }

                    $loginSuccess = true;
                } else {
                    $loginError = "Invalid email or password.";
                }
            } else {
                $loginError = "Invalid email or password.";
            }

            if (!$loginSuccess) {
                $_SESSION[$attempts_key]++;
                if ($_SESSION[$attempts_key] >= $max_attempts) {
                    $_SESSION[$lockout_key] = time() + $lockout_time;
                    $loginError = "Too many attempts. Locked out for 15 minutes.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Login - Pochie Catering</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/admin.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .login-container { display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .login-card { max-width: 400px; width: 100%; padding: 2rem; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); border-radius: 12px; background: white; }
        .login-logo { max-width: 100px; }
        input.form-control { border-radius: 8px; }
        .btn { border-radius: 8px; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
        .login-card { animation: fadeIn 0.4s ease-out; }
        @keyframes bounce { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.2); } }
        .success-icon { color: #28a745; font-size: 50px; animation: bounce 0.8s ease infinite alternate; }
        .password-container { position: relative; }
        .password-container .form-control { padding-right: 40px; }
        .password-container .toggle-password { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666; }
    </style>
</head>
<body class="admin-login bg-light">
<div class="login-container">
    <div class="login-card text-center">
        <div class="text-center mb-4">
            <img src="../images/logo.png" alt="Catering Logo" class="login-logo">
            <h2 class="mt-3">Admin Login</h2>
        </div>

        <?php if (!empty($loginError)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($loginError); ?></div>
        <?php endif; ?>

        <?php if ($loginSuccess): ?>
            <div class="text-success text-center">
                <i class="fas fa-check-circle success-icon"></i>
                <p>Login Successful! Redirecting...</p>
            </div>
            <script>
                setTimeout(function() { window.location.href = '../admin/'; }, 2000);
            </script>
        <?php else: ?>
            <form method="post" action="admin_login.php">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="mb-3">
                    <div class="password-container">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember Me</label>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
            <p class="mt-3">Forgot your password? <a href="admin_forgot_password.php">Reset it here</a></p>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        // Toggle password visibility
        $('#togglePassword').on('click', function() {
            const passwordField = $('#password');
            const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
            passwordField.attr('type', type);
            $(this).toggleClass('fa-eye fa-eye-slash');
        });
    });
</script>
</body>
</html>