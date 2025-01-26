<?php require_once 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pochie Catering Service</title>
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

        .featured-menu img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .service-icon {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 10px;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .star-rating i {
            color: #ffc107;
            font-size: 1.5rem;
        }

        .star-rating i:hover,
        .star-rating i:hover ~ i {
            color: #ff9800;
        }

        footer {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 1px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>

<header>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#">Pochie Catering Service</a>
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
                        <a class="nav-link" href="Menus.php">Menu</a>
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

<section class="featured-menu container">
    <div class="row">
        <div class="col-md-6">
            <img src="images/featured_menu_image.jpg" alt="Featured Menu Image" class="img-fluid">
        </div>
        <div class="col-md-6 d-flex flex-column justify-content-center">
            <h2>Featured Menu</h2>
            <p>Explore our exquisite selection of delectable dishes, crafted with the finest ingredients and culinary expertise.</p>
            <a href="Menus.php" class="btn btn-primary">View Menu</a>
        </div>
    </div>
</section>

<section class="services container">
    <div class="row">
        <div class="col-md-4">
            <div class="text-center">
                <i class="fas fa-birthday-cake service-icon"></i>
                <h3>Birthday Parties</h3>
                <p>Celebrate your special day with our customized catering services, tailored to your preferences and theme.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center">
                <i class="fas fa-glass-cheers service-icon"></i>
                <h3>Corporate Events</h3>
                <p>Impress your clients and colleagues with our sophisticated catering solutions, perfect for corporate gatherings and conferences.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center">
                <i class="fas fa-ring service-icon"></i>
                <h3>Weddings</h3>
                <p>Create unforgettable memories with our exceptional wedding catering services, designed to complement your special day.</p>
            </div>
        </div>
    </div>
</section>

<section class="customer-reviews container">
    <h2>Customer Reviews</h2>
    <div class="row d-flex">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">John Doe</h5>
                    <p class="card-text">The food was delicious and the service was excellent. I would highly recommend this catering service.</p>
                    <div class="star-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card mb-4">
            <div class="card-body">
            <h5 class="card-title">Jane Smith</h5>
            <p class="card-text">I was very impressed with the quality of the food and the professionalism of the staff. I will definitely be using this catering service again.</p>
            <div class="star-rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
            </div>
        </div>
    </div>
</div>
<div class="col-md-12">
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Peter Jones</h5>
            <p class="card-text">The catering service exceeded my expectations. The food was beautifully presented and tasted amazing. I highly recommend them for any event.</p>
            <div class="star-rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="far fa-star"></i>
            </div>
        </div>
    </div>
</div>
</div>
</section>

<section class="contact container">
<h2>Contact Us</h2>
<p>Please contact us to discuss your catering needs.</p>
<p>Email: <a href="mailto:info@yourcateringservice.com">info@yourcateringservice.com</a></p>
<p>Phone: <a href="tel:+15551234567">+1-555-123-4567</a></p>
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

// Sample product data (replace with your actual product data)
const products = [
    { id: 1, name: 'Product 1', price: 10 },
    { id: 2, name: 'Product 2', price: 20 },
    { id: 3, name: 'Product 3', price: 30 }
];

// Add event listeners to "Order Now!" buttons (replace with your actual buttons)
// You might need to adjust the selectors based on your HTML structure
const orderButtons = document.querySelectorAll('.btn-primary'); // Assuming your "Order Now!" buttons have the class "btn-primary"

orderButtons.forEach(button => {
    button.addEventListener('click', function() {
        // Assuming your buttons have a data-product-id attribute
        const productId = this.dataset.productId;

        // Find the product in the products array
        const product = products.find(p => p.id === parseInt(productId));

        if (product) {
            // Store order information (e.g., in local storage or using cookies)
            // ... You can use the product object to access product details

            cartItems++;
            cartCount.textContent = cartItems;
        } else {
            console.error('Product not found!');
        }
    });
});

const starRatings = document.querySelectorAll('.star-rating');

starRatings.forEach(starRating => {
    const stars = starRating.querySelectorAll('i');

    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = stars.indexOf(star) + 1;
            console.log(`Rating: ${rating}`);

            stars.forEach((s, i) => {
                if (i < rating) {
                    s.classList.add('fas', 'text-warning');
                    s.classList.remove('far');
                } else {
                    s.classList.remove('fas', 'text-warning');
                    s.classList.add('far');
                }
            });
        });
    });
});
</script>
</body>
</html>