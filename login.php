<?php
require 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password']; // Use password_verify

    // check if user exists
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    // Fetch the user data
    if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (password_verify($password, $result['password'])) {
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['user_email'] = $result['email'];
            $_SESSION['first_name'] = $result['first_name'];
            $_SESSION['last_name'] = $result['last_name'];
            // $_SESSION['user_email'] = $result['email'];
            header("Location: index.php");
            exit();
        }
        echo "<p>Logged in as: " . $_SESSION['user_email'] . "</p>";

    }
    // Generic error message for security
    echo "Invalid login credentials.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form method="post" action="">
        Email: <input type="email" name="email" required><br>
        Password: <input type="password" name="password" required><br>
        <input type="submit" value="Login">
    </form>
</body>
</html>
