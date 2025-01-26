<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name     = $_POST['first_name'];
    $middle_name    = $_POST['middle_name'];
    $last_name      = $_POST['last_name'];
    $birthdate      = $_POST['birthdate'];
    $gender         = $_POST['gender'];
    $address        = $_POST['address'];
    $contact_no     = $_POST['contact_no'];
    $email          = $_POST['email'];
    $password       = md5($_POST['password']); // MD5 encryption


    try {
        $stmt = $db->prepare("INSERT INTO users (first_name, middle_name, last_name, birthdate, gender, address, contact_no, email, password) 
                                VALUES (:first_name, :middle_name, :last_name, :birthdate, :gender, :address, :contact_no, :email, :password)");
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
    } catch(PDOException $e) {
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
    <h2>Signup</h2>
    <form method="post" action="">
        First Name: <input type="text" name="first_name" required><br>
        Middle Name: <input type="text" name="middle_name"><br>
        Last Name: <input type="text" name="last_name" required><br>
        Birthdate: <input type="date" name="birthdate" required><br>
        Gender: <select name="gender" required>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select><br>
        Address: <input type="text" name="address" required><br>
        Contact No: <input type="text" name="contact_no" required><br>
        Email: <input type="email" name="email" required><br>
        Password: <input type="password" name="password" required><br>
        <input type="submit" value="Signup">
    </form>
</body>

</html>