<?php
require '../db.php';
session_start();

$signupSuccess = false; // Track signup status

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
            $stmt = $db->prepare("SELECT user_id FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $errorMsg = "Email already exists! Try logging in.";
            } else {
                // Secure password hashing
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user into the database
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
                $stmt->bindParam(':password', $hashed_password);

                if ($stmt->execute()) {
                    $_SESSION['user_id'] = $db->lastInsertId();
                    $signupSuccess = true; // Success flag for modal display
                } else {
                    $errorMsg = "Error: Could not create account.";
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
    <title>Signup - Pochie Catering</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card p-4 shadow-lg" style="max-width: 450px; width: 100%;">
        <h2 class="text-center mb-4">Create Account</h2>

        <?php if (isset($errorMsg)): ?>
            <div class="alert alert-danger text-center">
                <?php echo htmlspecialchars($errorMsg); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="signup.php">
            <div class="mb-3">
                <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
            </div>
            <div class="mb-3">
                <input type="text" name="middle_name" class="form-control" placeholder="Middle Name">
            </div>
            <div class="mb-3">
                <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
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
                <input type="text" name="contact_no" class="form-control" placeholder="Contact No" required>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Sign Up</button>
            </div>
        </form>

        <p class="mt-3 text-center">Already have an account? <a href="./login.php">Sign In</a></p>
    </div>
</div>

<!-- Success Modal -->
<?php if ($signupSuccess): ?>
    <script>
        $(document).ready(function(){
            $("#successModal").modal('show');
        });
    </script>
<?php endif; ?>

<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="successModalLabel">Account Created!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                🎉 Your account has been successfully created!<br>
                You will be redirected to your profile shortly.
            </div>
            <div class="modal-footer">
                <a href="../profile.php" class="btn btn-success">Go to Profile</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
