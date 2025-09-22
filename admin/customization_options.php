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
    $_SESSION['success'] = 'Option added successfully.';
}

function edit_option($db, $oid, $cat_id, $name, $desc, $price, $max_qty) {
    $params = [$cat_id, $name, $desc, $price, $max_qty, $oid];
    $stmt = $db->prepare('UPDATE customization_options SET category_id=?, option_name=?, description=?, price=?, max_quantity=? WHERE option_id=?');
    $stmt->execute($params);
    $_SESSION['success'] = 'Option updated successfully.';
}

function delete_option($db, $oid) {
    try {
        $stmt = $db->prepare('SELECT COUNT(*) FROM booking_customizations WHERE option_id = ?');
        $stmt->execute([$oid]);
        $booking_count = $stmt->fetchColumn();

        $stmt = $db->prepare('SELECT COUNT(*) FROM package_customization_options WHERE option_id = ?');
        $stmt->execute([$oid]);
        $package_count = $stmt->fetchColumn();

        if ($booking_count > 0 || $package_count > 0) {
            $_SESSION['error'] = 'Cannot delete option: It is linked to existing bookings or packages.';
            return false;
        }

        $stmt = $db->prepare('DELETE FROM customization_options WHERE option_id = ?');
        $stmt->execute([$oid]);
        $_SESSION['success'] = 'Option deleted successfully.';
        return true;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error deleting option: ' . $e->getMessage();
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['option_name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $max_qty = intval($_POST['max_quantity'] ?? 1);
    $cat_id = intval($_POST['category_id'] ?? 0);

    if ($cat_id === 0) {
        $firstCat = $db->query('SELECT category_id FROM customization_categories WHERE status="active" ORDER BY category_id ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        if ($firstCat) {
            $cat_id = intval($firstCat['category_id']);
        } else {
            $_SESSION['error'] = 'No active categories found. Please add a category first.';
            header('Location: customization_options.php');
            exit();
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customization Options</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../css/admin.css" rel="stylesheet">
</head>
<body>
    <?php include '../layout/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4">Customization Options (Menu Items)</h2>

                    <?php if (count($categories) === 0): ?>
                        <div class="alert alert-warning">No categories found. Please add a category first in the admin panel before adding options.</div>
                        <form class="mb-4">
                            <fieldset disabled>
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-2">
                                        <label class="form-label">Category</label>
                                        <select class="form-select"><option>No categories</option></select>
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
                                        <button type="button" class="btn btn-success">Add Option</button>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    <?php else: ?>
                        <form method="post" class="row g-3 align-items-end mb-4">
                            <input type="hidden" name="action" value="add">
                            <div class="col-md-2">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select" required>
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
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" name="price" class="form-control" min="0" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Max Qty</label>
                                <input type="number" name="max_quantity" class="form-control" min="1" value="1" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus"></i> Add Option</button>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-12">
                                <small class="text-muted">Category examples: Food, Beverage, Service, Decoration, etc. You can add more categories in the admin category management.</small>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="table-container">
                        <table class="table table-striped table-hover">
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
                                        <td>₱<?php echo number_format($opt['price'], 2); ?></td>
                                        <td><?php echo $opt['max_quantity']; ?></td>
                                        <td>
                                            <button class="btn btn-warning action-btn me-2" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $opt['option_id']; ?>" title="Edit Option">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="post" style="display:inline-block">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="option_id" value="<?php echo $opt['option_id']; ?>">
                                                <button type="submit" class="btn btn-danger action-btn" title="Delete Option" onclick="return confirm('Are you sure you want to delete this option?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <div class="modal fade" id="editModal<?php echo $opt['option_id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $opt['option_id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editModalLabel<?php echo $opt['option_id']; ?>">Edit Option</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="post">
                                                    <input type="hidden" name="action" value="edit">
                                                    <input type="hidden" name="option_id" value="<?php echo $opt['option_id']; ?>">
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Category</label>
                                                            <select name="category_id" class="form-select" required>
                                                                <option value="">Select Category</option>
                                                                <?php foreach ($categories as $cat): ?>
                                                                    <option value="<?php echo $cat['category_id']; ?>" <?php echo $cat['category_id'] == $opt['category_id'] ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($cat['category_name']); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Name</label>
                                                            <input type="text" name="option_name" class="form-control" value="<?php echo htmlspecialchars($opt['option_name']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Description</label>
                                                            <input type="text" name="description" class="form-control" value="<?php echo htmlspecialchars($opt['description']); ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Price</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">₱</span>
                                                                <input type="number" name="price" class="form-control" min="0" step="0.01" value="<?php echo $opt['price']; ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Max Qty</label>
                                                            <input type="number" name="max_quantity" class="form-control" min="1" value="<?php echo $opt['max_quantity']; ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>