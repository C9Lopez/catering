<?php
require_once 'db.php';

if (isset($_GET['id'])) {
  $id = $_GET['id'];

  try {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
  $id = $_POST['id'];
  $name = $_POST['name'];
  $description = $_POST['description'];
  $price = $_POST['price'];

  try {
    $stmt = $db->prepare("UPDATE products SET name = :name, description = :description, price = :price WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt->execute();
    echo "Product updated successfully!";
  } catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Product</title>
</head>
<body>

<h1>Edit Product</h1>

<?php if (isset($product)): ?>
<form action="edit.php" method="post">
  <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
  <label for="name">Name:</label>
  <input type="text" id="name" name="name" value="<?php echo $product['name']; ?>" required><br><br>
  <label for="description">Description:</label>
  <textarea id="description" name="description"><?php echo $product['description']; ?></textarea><br><br>
  <label for="price">Price:</label>
  <input type="number" id="price" name="price" step="0.01" value="<?php echo $product['price']; ?>" required><br><br>
  <input type="submit" value="Update Product">
</form>
<?php else: ?>
<p>Product not found.</p>
<?php endif; ?>

</body>
</html>