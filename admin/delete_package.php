<?php
require '../db.php';
session_start();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['admin_id'])) {
    $response['message'] = 'Unauthorized access';
    echo json_encode($response);
    exit();
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    $response['message'] = 'Invalid package ID';
    echo json_encode($response);
    exit();
}

$package_id = $_POST['id'];

try {
    // Start a transaction to ensure atomicity
    $db->beginTransaction();

    // Delete related bookings
    $deleteBookingsStmt = $db->prepare("DELETE FROM event_bookings WHERE package_id = :id");
    $deleteBookingsStmt->bindParam(':id', $package_id, PDO::PARAM_INT);
    $deleteBookingsStmt->execute();

    // Delete the package
    $deletePackageStmt = $db->prepare("DELETE FROM catering_packages WHERE package_id = :id");
    $deletePackageStmt->bindParam(':id', $package_id, PDO::PARAM_INT);
    $deletePackageStmt->execute();

    if ($deletePackageStmt->rowCount() > 0) {
        $response['success'] = true;
        $response['message'] = 'Package and related bookings deleted successfully';
    } else {
        $response['message'] = 'Package not found or already deleted';
    }

    // Commit the transaction
    $db->commit();
} catch (PDOException $e) {
    // Roll back the transaction on error
    $db->rollBack();
    $response['message'] = 'Error deleting package: ' . $e->getMessage();
}

echo json_encode($response);
exit();
?>