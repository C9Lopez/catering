<?php
require 'db.php';

try {
    $stmt = $db->prepare("SELECT id, title, image_path FROM menus WHERE service_type = 'wedding' LIMIT 5");
    $stmt->execute();
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Wedding Menu Image Paths</h2>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Title</th><th>Image Path</th><th>File Exists?</th></tr>";
    
    foreach ($menus as $menu) {
        $file_exists = file_exists($menu['image_path']) ? 'Yes' : 'No';
        echo "<tr>";
        echo "<td>{$menu['id']}</td>";
        echo "<td>{$menu['title']}</td>";
        echo "<td>{$menu['image_path']}</td>";
        echo "<td>$file_exists</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>