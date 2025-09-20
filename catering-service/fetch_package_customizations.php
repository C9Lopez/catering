<?php
require '../db.php';
header('Content-Type: application/json');

$package_id = isset($_GET['package_id']) ? intval($_GET['package_id']) : 0;
if ($package_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid package ID.']);
    exit();
}

try {
    $stmt = $db->prepare('SELECT o.option_id, o.option_name, o.description, o.price, o.max_quantity, c.category_name, c.category_type
        FROM package_customization_options pco
        JOIN customization_options o ON pco.option_id = o.option_id
        JOIN customization_categories c ON o.category_id = c.category_id
        WHERE pco.package_id = :pid AND o.status = "active" AND c.status = "active"');
    $stmt->execute([':pid' => $package_id]);
    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'options' => $options]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
