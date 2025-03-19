<?php
require '../db.php';
session_start();

$loginError = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (password_verify($password, $result['password'])) {
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['user_email'] = $result['email'];
            $_SESSION['first_name'] = $result['first_name'];
            $_SESSION['last_name'] = $result['last_name'];
            $_SESSION['contact'] = $result['contact_no'];

            echo "<script>
                setTimeout(function() {
                    window.location.href = '../index.php';
                }, 2000);
            </script>";
            $loginSuccess = true;
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
    <title>Login - Pochie Catering</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .container { display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .login-card { max-width: 400px; width: 100%; padding: 2rem; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); border-radius: 12px; background: white; }
        input.form-control { border-radius: 8px; }
        .btn { border-radius: 8px; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
        .login-card { animation: fadeIn 0.4s ease-out; }
        @keyframes bounce { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.2); } }
        .success-icon { color: #28a745; font-size: 50px; animation: bounce 0.8s ease infinite alternate; }
    </style>
</head>
<body class="bg-light">
<div class="container">
    <div class="login-card text-center">
        <h2 class="mb-4">Login</h2>

        <?php if (!empty($loginError)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($loginError); ?></div>
        <?php endif; ?>

        <?php if (isset($loginSuccess)): ?>
            <div class="text-success text-center">
                <i class="fas fa-check-circle success-icon"></i>
                <p>Login Successful! Redirecting...</p>
            </div>
        <?php else: ?>
            <form method="post" action="login.php">
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
            <p class="mt-3">Forgot your password? <a href="forgot_password.php">Reset it here</a></p>
            <p>Don't have an account? <a href="./signup.php">Signup</a></p>
            <a href="../index.php" class="btn btn-outline-secondary btn-sm mt-2">Back to Homepage</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>