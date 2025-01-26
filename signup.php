<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $contact_no = $_POST['contact_no'];
    $email = $_POST['email'];
    $password = md5($_POST['password']); // MD5 encryption

    // Insert user into the database
    $sql = "INSERT INTO users (first_name, middle_name, last_name, birthdate, gender, address, contact_no, email, password) 
            VALUES ('$first_name', '$middle_name', '$last_name', '$birthdate', '$gender', '$address', '$contact_no', '$email', '$password')";

    if (mysqli_query($conn, $sql)) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
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
