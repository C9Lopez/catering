<?php
session_start();

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = md5($_POST['password']); // MD5 encryption

    // check if user exist
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND password = :password");

    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);

    // Execute the statement
    $stmt->execute();

    // Fetch the user data
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if($result) {
        // Store user data in session
        $_SESSION['user_id'] = $result['id']; 
        $_SESSION['user_email'] = $result['email']; 
        // Redirect to index.php
        header("Location: index.php");
        exit(); 
    } else {
        echo "Email or password does not match";
    }
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