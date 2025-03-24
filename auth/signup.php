<?php
require '../db.php';
session_start();

// Rate limiting setup (max 5 attempts per IP in 15 minutes)
$ip = $_SERVER['REMOTE_ADDR'];
$attempts_key = "signup_attempts_$ip";
$lockout_key = "signup_lockout_$ip";
$max_attempts = 5;
$lockout_time = 15 * 60; // 15 minutes in seconds

// Check if locked out
if (isset($_SESSION[$lockout_key]) && time() < $_SESSION[$lockout_key]) {
    $errorMsg = "Too many signup attempts. Please try again later.";
} else {
    // Initialize signup attempts
    if (!isset($_SESSION[$attempts_key])) {
        $_SESSION[$attempts_key] = 0;
    }

    // CSRF Token
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $signupSuccess = false;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $errorMsg = "Invalid request. Please try again.";
        } else {
            $first_name = trim($_POST['first_name'] ?? '');
            $middle_name = trim($_POST['middle_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $birthdate = $_POST['birthdate'] ?? '';
            $gender = $_POST['gender'] ?? '';
            $address = trim($_POST['address'] ?? '');
            $contact_no = trim($_POST['contact_no'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

            // Password validation
            $password_regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{12,}$/';
            if (empty($password)) {
                $errorMsg = "Password is required.";
            } elseif (!preg_match($password_regex, $password)) {
                $errorMsg = "Password must be at least 12 characters long, include uppercase, lowercase, numbers, and special characters.";
            } elseif (empty($confirm_password) || $password !== $confirm_password) {
                $errorMsg = "Passwords do not match.";
            } elseif (empty($first_name) || empty($last_name) || empty($email)) {
                $errorMsg = "First name, last name, and email are required!";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMsg = "Invalid email format.";
            } else {
                try {
                    // Check if email exists
                    $stmt = $db->prepare("SELECT user_id FROM users WHERE email = :email");
                    $stmt->bindParam(':email', $email);
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                        $errorMsg = "Email already exists! Try logging in.";
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $db->prepare("INSERT INTO users (first_name, middle_name, last_name, birthdate, gender, address, contact_no, email, password) 
                                              VALUES (:first_name, :middle_name, :last_name, :birthdate, :gender, :address, :contact_no, :email, :password)");
                        $stmt->execute([
                            ':first_name' => $first_name,
                            ':middle_name' => $middle_name,
                            ':last_name' => $last_name,
                            ':birthdate' => $birthdate,
                            ':gender' => $gender,
                            ':address' => $address,
                            ':contact_no' => $contact_no,
                            ':email' => $email,
                            ':password' => $hashed_password
                        ]);

                        $_SESSION['user_id'] = $db->lastInsertId();
                        $signupSuccess = true;
                        $_SESSION[$attempts_key] = 0; // Reset attempts on success
                    }
                } catch (PDOException $e) {
                    $errorMsg = "Database error: " . $e->getMessage();
                }
            }

            // Rate limiting logic
            if (!$signupSuccess) {
                $_SESSION[$attempts_key]++;
                if ($_SESSION[$attempts_key] >= $max_attempts) {
                    $_SESSION[$lockout_key] = time() + $lockout_time;
                    $errorMsg = "Too many attempts. Locked out for 15 minutes.";
                }
            }
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .container { display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .card { max-width: 500px; width: 100%; padding: 2rem; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); border-radius: 12px; }
        .password-requirements { font-size: 0.9em; margin-top: 10px; }
        .password-requirements li { list-style: none; position: relative; padding-left: 20px; }
        .password-requirements li::before { content: "•"; position: absolute; left: 0; color: red; }
        .password-requirements li.valid::before { color: green; }
        .btn-primary { background-color: #0056b3; border: none; }
        .btn-primary:hover { background-color: #003d82; }
        .password-container { position: relative; }
        .password-container .form-control { padding-right: 40px; }
        .password-container .toggle-password { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666; }
        .modal-backdrop.show { opacity: 0.5; }
    </style>
</head>
<body class="bg-light">
<div class="container">
    <?php if (!$signupSuccess): ?>
        <div class="card">
            <h2 class="text-center mb-4">Account Setup</h2>
            <p class="text-center text-muted mb-4">Please set a password and agree to the terms.</p>

            <?php if (isset($errorMsg)): ?>
                <div class="alert alert-danger text-center"><?php echo htmlspecialchars($errorMsg); ?></div>
            <?php endif; ?>

            <form method="post" action="signup.php">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
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
                    <input type="text" name="contact_no" class="form-control" placeholder="Contact No" required>
                </div>
                <div class="mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <div class="password-container">
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                    <ul class="password-requirements" id="password-requirements">
                        <li id="length">minimum length 12</li>
                        <li id="lowercase">at least one lowercase letter (a-z)</li>
                        <li id="uppercase">at least one uppercase letter (A-Z)</li>
                        <li id="digit">at least one digit (0-9)</li>
                        <li id="special">at least one non-alphanumeric character</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <div class="password-container">
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="••••••••" required>
                        <i class="fas fa-eye toggle-password" id="toggleConfirmPassword"></i>
                    </div>
                </div>
                <div class="mb-3 text-muted">
                    By clicking "Start using Pochie Catering", you agree to the Web Site Terms of Use, Pochie Catering License Terms, and the Data Processing Addendum.
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">START USING POCHIE CATERING</button>
                </div>
            </form>
            <p class="mt-3 text-center">Already have an account? <a href="login.php">Sign In</a></p>
        </div>
    <?php endif; ?>
</div>

<?php if ($signupSuccess): ?>
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel">Account Created!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    🎉 Your account has been successfully created!<br>
                    Redirecting to login in 3 seconds...
                </div>
                <div class="modal-footer">
                    <a href="login.php" class="btn btn-success">Go to Login</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $("#successModal").modal('show');
            setTimeout(function() { window.location.href = 'login.php'; }, 3000);
        });
    </script>
<?php endif; ?>

<script>
    $(document).ready(function() {
        // Toggle password visibility for password field
        $('#togglePassword').on('click', function() {
            const passwordField = $('#password');
            const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
            passwordField.attr('type', type);
            $(this).toggleClass('fa-eye fa-eye-slash');
        });

        // Toggle password visibility for confirm password field
        $('#toggleConfirmPassword').on('click', function() {
            const confirmPasswordField = $('#confirm_password');
            const type = confirmPasswordField.attr('type') === 'password' ? 'text' : 'password';
            confirmPasswordField.attr('type', type);
            $(this).toggleClass('fa-eye fa-eye-slash');
        });

        // Real-time password validation
        $('#password').on('input', function() {
            const password = $(this).val();

            // Check length
            if (password.length >= 12) {
                $('#length').addClass('valid');
            } else {
                $('#length').removeClass('valid');
            }

            // Check lowercase
            if (/[a-z]/.test(password)) {
                $('#lowercase').addClass('valid');
            } else {
                $('#lowercase').removeClass('valid');
            }

            // Check uppercase
            if (/[A-Z]/.test(password)) {
                $('#uppercase').addClass('valid');
            } else {
                $('#uppercase').removeClass('valid');
            }

            // Check digit
            if (/[0-9]/.test(password)) {
                $('#digit').addClass('valid');
            } else {
                $('#digit').removeClass('valid');
            }

            // Check special character
            if (/[^A-Za-z0-9]/.test(password)) {
                $('#special').addClass('valid');
            } else {
                $('#special').removeClass('valid');
            }
        });
    });
</script>
</body>
</html>