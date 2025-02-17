<?php 
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<script>
        alert('You are not logged in. Please log in first.');
        window.location.href = 'auth/login.php';
    </script>";
    exit;
}

try {
    $stmt = $db->prepare("SELECT user_id, first_name,middle_name, last_name, email, profile_picture, address, contact_no, birthdate, gender FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $age = null;
    if (!empty($user['birthdate'])) {
        $birthdate = new DateTime($user['birthdate']);
        $today = new DateTime();
        $age = $today->diff($birthdate)->y;
    }
    

    // if (!$user) {
    //     $user = [
    //         'first_name' => 'John',
    //         'last_name' => 'Doe',
    //         'email' => 'johndoe@example.com',
    //         'profile_picture' => 'default-profile.png',
    //         'address' => '123 Main Street, City, Country',
    //         'contact_no' => '+1234567890'
    //     ];
    // }
} catch (PDOException $e) {
    die("Error fetching profile: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Pochie Catering</title>

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Playball&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/themes.css" rel="stylesheet">

    <style>
        .profile-section {
            padding: 120px 0 60px;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .profile-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .light-theme .profile-card {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid var(--light-border);
        }

        .dark-theme .profile-card {
            background: var(--dark-card);
            border: 1px solid var(--dark-border);
        }

        .dark-grey-theme .profile-card {
            background: var(--dark-grey-card);
            border: 1px solid var(--dark-grey-border);
        }

        .dark-blue-theme .profile-card {
            background: var(--dark-blue-card);
            border: 1px solid var(--dark-blue-border);
        }

        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            padding: 5px;
            margin: 0 auto 2rem;
            overflow: hidden;
            position: relative;
        }

        .light-theme .profile-image {
            border: 3px solid var(--light-primary);
            background: linear-gradient(45deg, var(--light-primary), #e91e63);
        }

        .dark-theme .profile-image {
            border: 3px solid var(--dark-primary);
            background: linear-gradient(45deg, var(--dark-primary), #e91e63);
        }

        .dark-grey-theme .profile-image {
            border: 3px solid var(--dark-grey-primary);
            background: linear-gradient(45deg, var(--dark-grey-primary), #e91e63);
        }

        .dark-blue-theme .profile-image {
            border: 3px solid var(--dark-blue-primary);
            background: linear-gradient(45deg, var(--dark-blue-primary), #e91e63);
        }

        .profile-image img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .profile-details {
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .light-theme .profile-details {
            background: rgba(0, 0, 0, 0.05);
        }

        .dark-theme .profile-details {
            background: rgba(255, 255, 255, 0.05);
        }

        .dark-grey-theme .profile-details {
            background: rgba(255, 255, 255, 0.05);
        }

        .dark-blue-theme .profile-details {
            background: rgba(255, 255, 255, 0.05);
        }

        .profile-detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .profile-detail-item i {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: white;
        }

        .light-theme .profile-detail-item i {
            background: var(--light-primary);
        }

        .dark-theme .profile-detail-item i {
            background: var(--dark-primary);
        }

        .dark-grey-theme .profile-detail-item i {
            background: var(--dark-grey-primary);
        }

        .dark-blue-theme .profile-detail-item i {
            background: var(--dark-blue-primary);
        }

        .logout-btn {
            background: linear-gradient(45deg, #ff416c, #ff4b2b);
            border: none;
            border-radius: 50px;
            padding: 0.8rem 2rem;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 2rem;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 65, 108, 0.4);
            color: white;
        }
    </style>
</head>

<body class="light-theme">

    <?php include 'layout/navbar.php'; ?>

    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container">
            <div class="text-center wow bounceInUp" data-wow-delay="0.1s">
                <!-- <small class="d-inline-block fw-bold text-dark text-uppercase bg-light border border-primary rounded-pill px-4 py-1 mb-3">User Profile</small> -->
                <h1 class="display-5 mb-5">Welcome Back!</h1>
                <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal"
                    data-bs-target="#updateProfileModal">
                    <i class="fas fa-edit me-2"></i> Update Profile
                </button>
            </div>


            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="profile-card wow fadeInUp" data-wow-delay="0.2s">
                        <div class="profile-image">
                            <img src="<?php echo './img/profile/' . htmlspecialchars($user['profile_picture']); ?>"
                                alt="Profile Picture">
                        </div>

                        <div class="text-center">
                            <h2 class="text-primary mb-2">
                                <?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?></h2>
                            <p class="text-theme mb-4"><?php echo htmlspecialchars($user['email']); ?></p>


                            <div class="profile-details">
                                <div class="profile-detail-item">
                                    <i class="fas fa-birthday-cake"></i>
                                    <span
                                        class="text-theme"><?php echo $age !== null ? $age . ' years old' : 'Birthdate not provided'; ?></span>
                                </div>

                                <div class="profile-detail-item">
                                    <i class="fas fa-venus-mars"></i>
                                    <span class="text-theme"><?php echo htmlspecialchars($user['gender']); ?></span>
                                </div>

                                <div class="profile-detail-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span class="text-theme"><?php echo htmlspecialchars($user['address']); ?></span>
                                </div>
                                <div class="profile-detail-item">
                                    <i class="fas fa-phone"></i>
                                    <span class="text-theme"><?php echo htmlspecialchars($user['contact_no']); ?></span>
                                </div>
                            </div>

                            <a href="./auth/logout.php" class="btn logout-btn">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Update Profile Modal -->
    <div class="modal fade" id="updateProfileModal" tabindex="-1" aria-labelledby="updateProfileModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateProfileModalLabel">Update Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateProfileForm" enctype="multipart/form-data" method="POST"
                        action="update_profile.php">
                        <div class="mb-3 text-center">
                            <label for="profilePicture" class="form-label">Profile Picture</label>
                            <div class="profile-image">
                                <img id="profilePreview"
                                    src="<?php echo './img/profile/' . htmlspecialchars($user['profile_picture']); ?>"
                                    alt="Profile Picture" class="img-fluid rounded-circle" width="150" height="150">
                            </div>
                            <input type="file" class="form-control mt-2" id="profilePicture" name="profile_picture"
                                accept="image/*" onchange="previewImage(event)">
                        </div>
                        <input type="text" class="form-control" id="user_id" name="user_id"
                            value="<?php echo htmlspecialchars($user['user_id']); ?>" required hidden>
                        <div class="mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="first_name"
                                value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="middle_name" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middleName" name="middle_name"
                                value="<?php echo htmlspecialchars($user['middle_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="last_name"
                                value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="birthdate" class="form-label">Birthdate</label>
                            <input type="date" class="form-control" id="birthdate" name="birthdate"
                                value="<?php echo htmlspecialchars($user['birthdate']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <input type="text" class="form-control" id="gender" name="gender"
                                value="<?php echo htmlspecialchars($user['gender']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address"
                                value="<?php echo htmlspecialchars($user['address']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="contactNo" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="contactNo" name="contact_no"
                                value="<?php echo htmlspecialchars($user['contact_no']); ?>">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'layout/footer.php'; ?>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    <script src="lib/lightbox/js/lightbox.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/theme-switcher.js"></script>

    <script>
        new WOW().init();

        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function () {
                var output = document.getElementById('profilePreview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>

</body>

</html>