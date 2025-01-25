<?php
require_once 'db.php';

if (isset($_GET['id'])) {
  $id = $_GET['id'];

  try {
    $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    echo "Product deleted successfully!";
  } catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
  }
}

header('Location: index.php');
exit;
?>