<?php
require_once 'db.php';
session_start();

if (!isset($_POST['user_id'])) {
    die("Invalid request.");
}

$user_id = $_POST['user_id'];
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];
$contact_no = $_POST['contact_no'];
$address = $_POST['address'];

$profile_picture = null;

if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
    $targetDir = "img/profile/";
    $fileName = time() . "_" . basename($_FILES["profile_picture"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFilePath)) {
        $profile_picture = $fileName;
    }
}

try {
    if ($profile_picture) {
        $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, contact_no = ?, address = ?, profile_picture = ? WHERE user_id = ?");
        $stmt->execute([$first_name, $last_name, $email, $contact_no, $address, $profile_picture, $user_id]);
    } else {
        $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, contact_no = ?, address = ? WHERE user_id = ?");
        $stmt->execute([$first_name, $last_name, $email, $contact_no, $address, $user_id]);
    }

    $_SESSION['success_message'] = "Profile updated successfully!";
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error updating profile: " . $e->getMessage();
}

header("Location: profile.php");
exit;
?>
