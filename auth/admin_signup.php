<?php
require '../db.php';
session_start();

$signupSuccess = false;
$errorMsg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $address = trim($_POST['address']);
    $contact_no = trim($_POST['contact_no']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate input
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $errorMsg = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Invalid email format.";
    } else {
        try {
            // Check if email already exists
            $stmt = $db->prepare("SELECT admin_id FROM admin_user WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $errorMsg = "Email already exists!";
            } else {
                // Secure password hashing
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert admin into the database
                $stmt = $db->prepare("INSERT INTO admin_user 
                    (first_name, middle_name, last_name, birthdate, gender, address, contact_no, email, password, role, status) 
                    VALUES 
                    (:first_name, :middle_name, :last_name, :birthdate, :gender, :address, :contact_no, :email, :password, 'admin', 'active')");

                $stmt->bindParam(':first_name', $first_name);
                $stmt->bindParam(':middle_name', $middle_name);
                $stmt->bindParam(':last_name', $last_name);
                $stmt->bindParam(':birthdate', $birthdate);
                $stmt->bindParam(':gender', $gender);
                $stmt->bindParam(':address', $address);
                $stmt->bindParam(':contact_no', $contact_no);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);

                if ($stmt->execute()) {
                    $signupSuccess = true;
                } else {
                    $errorMsg = "Error: Could not create admin account.";
                }
            }
        } catch (PDOException $e) {
            $errorMsg = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Signup - Catering System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/admin.css" rel="stylesheet">
</head>
<body class="admin-signup">
    <div class="signup-container">
        <div class="signup-card">
            <div class="text-center mb-4">
                <img src="../images/logo.png" alt="Catering Logo" class="signup-logo">
                <h2 class="mt-3">Admin Registration</h2>
            </div>

            <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($errorMsg); ?></div>
            <?php endif; ?>

            <?php if ($signupSuccess): ?>
                <div class="alert alert-success">
                    Admin account created successfully! <a href="admin_login.php">Login here</a>
                </div>
            <?php else: ?>
                <form method="post" action="admin_signup.php">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="text" name="middle_name" class="form-control" placeholder="Middle Name">
                    </div>
                    <div class="mb-3">
                        <input type="date" name="birthdate" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <select name="gender" class="form-control" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <input type="text" name="address" class="form-control" placeholder="Address" required>
                    </div>
                    <div class="mb-3">
                        <input type="text" name="contact_no" class="form-control" placeholder="Contact Number" required>
                    </div>
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
