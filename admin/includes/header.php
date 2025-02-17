<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/admin.css" rel="stylesheet">
</head>
<body class="admin-dashboard">
<nav class="sidebar">
    <div class="sidebar-header">
        <h3>Catering Admin</h3>
    </div>
    <ul class="list-unstyled components">
        <li class="nav-item">
            <a href="index.php" class="nav-link active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="packages.php" class="nav-link">
                <i class="fas fa-box-open"></i>
                <span>Packages</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="orders.php" class="nav-link">
                <i class="fas fa-shopping-cart"></i>
                <span>Orders</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="users.php" class="nav-link">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="settings.php" class="nav-link">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../auth/logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</nav>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
    .sidebar {
        width: 250px;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        background: #343a40;
        padding: 20px;
        color: #fff;
    }
    
    .sidebar-header {
        margin-bottom: 20px;
        text-align: center;
    }
    
    .nav-item {
        margin: 10px 0;
    }
    
    .nav-link {
        color: #fff;
        padding: 10px;
        border-radius: 5px;
        display: flex;
        align-items: center;
        transition: all 0.3s;
    }
    
    .nav-link:hover {
        background: #495057;
        text-decoration: none;
    }
    
    .nav-link.active {
        background: #007bff;
    }
    
    .nav-link i {
        width: 30px;
        text-align: center;
        margin-right: 10px;
    }
</style>
</body>
</html>
