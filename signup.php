<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $contact_no = $_POST['contact_no'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password hashing

    try {
        $stmt = $db->prepare("INSERT INTO users (first_name, middle_name, last_name, birthdate, gender, address,contact_no, email, password) 
                                VALUES (:first_name,:middle_name, :last_name, :birthdate, :gender, :address, :contact_no, :email, :password)");
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':middle_name', $middle_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':birthdate', $birthdate);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':contact_no', $contact_no);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        echo "<div class='alert alert-success mt-3' role='alert'>User added successfully!</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger mt-3' role='alert'>Error: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Signup</title>
</head>

<body>

<?php include_once './layout/header.php'?>

    <h2>Signup</h2>
    <form method="post" action="">
        <input type="text" name="first_name" placeholder="first_name" required><br>
        <input type="text" name="middle_name" placeholder="middle_name" required><br>
        <input type="text" name="last_name" placeholder="last_name" required><br>
        <input type="date" name="birthdate" placeholder="birthdate" required><br>
        <input type="text" name="gender" placeholder="gender" required><br>
        <input type="text" name="address" placeholder="address" required><br>
        <input type="text" name="contact_no" placeholder="contact_no" required><br>
        <input type="email" name="email" placeholder="email" required><br>
        <input type="password" name="password" placeholder="password" required><br>
        <input type="submit" value="Signup">
    </form>
</body>

</html>