<?php
session_start();

// Destroy all user session data
$_SESSION = array();
session_destroy();

// Redirect to the user login page (adjust the path if needed)
header("Location: login.php");
exit();
?>
