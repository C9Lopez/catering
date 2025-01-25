<?php require_once 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Catering Service - Menus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: sans-serif;
        }

        header {
            background-color: #f8f9fa;
            padding: 20px;
        }

        .navbar-brand {
            font-weight: bold;
        }

        .navbar-nav .nav-link {
            color: #333;
        }

        .navbar-nav .nav-link:hover {
            color: #007bff;
        }

        section {
            padding: 40px 0;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        footer {
            background-color: #343a40;
            color: #fff;
            text-align: center;
            padding: 10px 0;
        }
    </style>
</head>
<body>

<header>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#">Your Catering Service</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Event Booking</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="Menus.php">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Menus.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Menus.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Management.php">Management Console</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="cart-link">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="badge bg-danger" id="cart-count">0</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<section id="menus" class="container mt-4">
    <h2>Our Menus</h2>
    <p>We have a variety of menus to choose from, or we can create a custom menu to fit your needs.</p>

    <div class="row">
        <?php
        $stmt = $db->query("SELECT * FROM products");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<div class='col-md-4'>";
            echo "<div class='card'>";
            echo "<div class='card-body'>";
            echo "<h3 class='card-title'>" . $row['name'] . "</h3>";
            echo "<p class='card-text'>" . $row['description'] . "</p>";
            echo "<p class='card-text'>Price: $" . $row['price'] . "</p>";
            echo "<button class='btn btn-primary order-button' data-product-id='" . $row['id'] . "'>Order Now!</button>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
        }
        ?>
    </div>
</section>

<footer>
    <div class="container">
        <p>&copy; 2023 Your Catering Service</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
<script>
    const cartLink = document.getElementById('cart-link');
    const cartCount = document.getElementById('cart-count');
    let cartItems = 0;

    const orderButtons = document.querySelectorAll('.order-button');

    orderButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;

            // Store order information (e.g., in local storage or using cookies)
            // ... You can use productId to identify the ordered product

            cartItems++;
            cartCount.textContent = cartItems;
        });
    });
</script>
</body>
</html>