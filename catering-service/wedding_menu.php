<?php
session_start();

require '../db.php';

// Fetch menus for wedding service type (join with categories)
try {
    $stmt = $db->prepare("SELECT m.*, c.category_name FROM menus m LEFT JOIN menu_categories c ON m.category_id = c.category_id WHERE m.service_type = 'wedding' AND m.status = 'active' ORDER BY c.category_name, m.title");
    $stmt->execute();
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '<div class="alert alert-warning">Unable to load menus</div>';
}

// Group menus by category_name
$grouped_menus = [];
foreach ($menus as $menu) {
    $grouped_menus[$menu['category_name']][] = $menu;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Wedding Menu - Pochie Catering</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Playball&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <!-- <link href="../lib/animate/animate.min.css" rel="stylesheet"> -->
    <link href="../lib/lightbox/css/lightbox.min.css" rel="stylesheet">
    <link href="../lib/owlcarousel/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/themes.css" rel="stylesheet">
</head>

<body class="light-theme">

    <!-- Loading Screen -->
    <!-- <div id="loading-screen">
        <div class="loader"></div>
    </div> -->

    <?php include '../layout/navbar.php'; ?>

    <!-- Hero Section -->
    <div class="container-fluid hero-section py-6 my-6 text-center wow fadeInUp" data-wow-delay="0.3s">
        <div class="hero-overlay"></div>
        <div class="container position-relative text-white">
            <h1 class="display-1 mb-4">Wedding <span class="text-primary">Menu</span></h1>
            <p class="lead">Explore our exquisite menu options for your wedding event.</p>
            <a href="./wedding.php" class="btn btn-primary border-0 rounded-pill py-2 px-3 px-md-3 animated bounceInLeft">Back to Wedding Booking</a>
        </div>
    </div>
    <!-- Hero End -->

    <!-- Menu Categories -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <?php foreach ($grouped_menus as $category => $items): ?>
                <div class="mb-5">
                    <h2 class="display-5 mb-4"><?php echo htmlspecialchars($category); ?></h2>
                    <div class="row g-4">
                        <?php foreach ($items as $item): ?>
                            <div class="col-md-4">
                                <div class="card h-100 theme-card">
                                    <?php
                                    $image_path = $item['image_path'];
                                    // Fix the image path - remove any './' prefix and ensure it's relative to admin/uploads
                                    if (strpos($image_path, './uploads/') === 0) {
                                        $image_path = '../admin/uploads/' . substr($image_path, 10);
                                    } elseif (strpos($image_path, 'uploads/') === 0) {
                                        $image_path = '../admin/uploads/' . substr($image_path, 8);
                                    }
                                    ?>
                                    <img src="<?php echo htmlspecialchars($image_path); ?>" class="card-img-top img-fluid" style="height: 200px; object-fit: cover;" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title theme-text"><?php echo htmlspecialchars($item['title']); ?></h5>
                                        <p class="card-text theme-text"><?php echo htmlspecialchars($item['description']); ?></p>
                                        <div class="mt-2">
                                            <!-- <span class="badge bg-info">₱<?php echo number_format($item['price'],2); ?></span>
                                            <span class="badge bg-success">Max: <?php echo $item['max_quantity']; ?></span> -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include '../layout/footer.php'; ?>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../lib/wow/wow.min.js"></script>
    <script src="../lib/easing/easing.min.js"></script>
    <script src="../lib/waypoints/waypoints.min.js"></script>
    <script src="../lib/counterup/counterup.min.js"></script>
    <script src="../lib/lightbox/js/lightbox.min.js"></script>
    <script src="../lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="../js/main.js"></script>
    <script src="../js/theme-switcher.js"></script>
    <script>
        new WOW().init();
    </script>
</body>

</html>
