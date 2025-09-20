<?php
require '../db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}



function add_option($db, $cat_id, $name, $desc, $price, $max_qty) {
    $stmt = $db->prepare('INSERT INTO customization_options (category_id, option_name, description, price, max_quantity, status) VALUES (?, ?, ?, ?, ?, "active")');
    $stmt->execute([$cat_id, $name, $desc, $price, $max_qty]);
}

function edit_option($db, $oid, $cat_id, $name, $desc, $price, $max_qty) {
    $params = [$cat_id, $name, $desc, $price, $max_qty, $oid];
    $stmt = $db->prepare('UPDATE customization_options SET category_id=?, option_name=?, description=?, price=?, max_quantity=? WHERE option_id=?');
    $stmt->execute($params);
}

function delete_option($db, $oid) {
    $db->prepare('DELETE FROM customization_options WHERE option_id=?')->execute([$oid]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['option_name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $max_qty = intval($_POST['max_quantity'] ?? 1);
    $cat_id = intval($_POST['category_id'] ?? 0);
    // If category_id is 0 or invalid, auto-select the first available active category
    if ($cat_id === 0) {
        $firstCat = $db->query('SELECT category_id FROM customization_categories WHERE status="active" ORDER BY category_id ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        if ($firstCat) {
            $cat_id = intval($firstCat['category_id']);
        } else {
            // No categories exist, abort with error
            die('No active categories found. Please add a category first.');
        }
    }
    if ($action === 'add') {
        add_option($db, $cat_id, $name, $desc, $price, $max_qty);
    } elseif ($action === 'edit' && isset($_POST['option_id'])) {
        $oid = intval($_POST['option_id']);
        edit_option($db, $oid, $cat_id, $name, $desc, $price, $max_qty);
    } elseif ($action === 'delete' && isset($_POST['option_id'])) {
        $oid = intval($_POST['option_id']);
        delete_option($db, $oid);
    }
    header('Location: customization_options.php');
    exit();
}

$options = $db->query('SELECT o.*, c.category_name FROM customization_options o JOIN customization_categories c ON o.category_id = c.category_id')->fetchAll(PDO::FETCH_ASSOC);
$categories = $db->query('SELECT * FROM customization_categories WHERE status="active"')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Customization Options</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/admin.css" rel="stylesheet">
</head>
<body class="admin-dashboard">
<?php include '../layout/sidebar.php'; ?>
<div class="main-content container mt-4">
    <h2>Customization Options (Menu Items)</h2>
    <?php if (count($categories) === 0): ?>
        <div class="alert alert-warning">No categories found. Please add a category first in the admin panel before adding options.</div>
        <form class="mb-4">
            <fieldset disabled>
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select class="form-control"><option>No categories</option></select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Price</label>
                        <input type="number" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Max Qty</label>
                        <input type="number" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Image</label>
                        <input type="file" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-success">Add Option</button>
                    </div>
                </div>
            </fieldset>
        </form>
    <?php else: ?>
    <form method="post" class="mb-4">
            <input type="hidden" name="action" value="add">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>">
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Name</label>
                    <input type="text" name="option_name" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Price</label>
                    <input type="number" name="price" class="form-control" min="0" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Max Qty</label>
                    <input type="number" name="max_quantity" class="form-control" min="1" value="1" required>
                </div>
                <!-- Image field removed -->
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success">Add Option</button>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <small class="text-muted">Category examples: Food, Beverage, Service, Decoration, etc. You can add more categories in the admin category management.</small>
                </div>
            </div>
        </form>
    <?php endif; ?>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Category</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Max Qty</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($options as $opt): ?>
            <tr>
                <td><?php echo htmlspecialchars($opt['category_name']); ?></td>
                <td><?php echo htmlspecialchars($opt['option_name']); ?></td>
                <td><?php echo htmlspecialchars($opt['description']); ?></td>
                <td>₱<?php echo number_format($opt['price'],2); ?></td>
                <td><?php echo $opt['max_quantity']; ?></td>
                <td>
                    <form method="post" style="display:inline-block">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="option_id" value="<?php echo $opt['option_id']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this option?')">Delete</button>
                    </form>
                    <!-- Edit form could be modal or inline, for brevity not included here -->
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
