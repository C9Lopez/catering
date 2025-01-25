<?php require_once 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Catering Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #f0f0f0;
            padding: 20px;
            text-align: center;
        }

        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        nav ul li {
            display: inline;
            margin-right: 20px;
        }

        section {
            padding: 20px;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }

        .card {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px;
            width: 30%;
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
            transition: 0.3s;
        }

        .card:hover {
            box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
        }

        .card h3 {
            margin-top: 0;
        }

        footer {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 10px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>

    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Your Catering Service</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="#services">Services</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="Menus.php">Menus</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#testimonials">Testimonials</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#contact">Contact</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <section id="services" class="container mt-4">
        <h2>Our Services</h2>
        <p>We offer a wide range of catering services for all occasions, including:</p>
        <ul>
            <li>Corporate events</li>
            <li>Weddings</li>
            <li>Private parties</li>
            <li>And more!</li>
        </ul>
    </section>

    <section id="menus" class="container mt-4">
        <h2>Our Menus</h2>
        <p>We have a variety of menus to choose from, or we can create a custom menu to fit your needs.</p>

        <h2>Add New Product</h2>

        <form action="create.php" method="post" class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Name:</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="price" class="form-label">Price:</label>
                <input type="number" id="price" name="price" step="0.01" class="form-control" required>
            </div>
            <div class="col-12">
                <label for="description" class="form-label">Description:</label>
                <textarea id="description" name="description" class="form-control"></textarea>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Add Product</button>
            </div>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];

            try {
                $stmt = $db->prepare("INSERT INTO products (name, description, price) VALUES (:name, :description, :price)");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':price', $price);
                $stmt->execute();
                echo "<div class='alert alert-success mt-3' role='alert'>Product added successfully!</div>";
            } catch(PDOException $e) {
                echo "<div class='alert alert-danger mt-3' role='alert'>Error: " . $e->getMessage() . "</div>";
            }
        }
        ?>
    </section>

    <section id="testimonials" class="container mt-4">
        <h2>Testimonials</h2>
        <p>See what our satisfied customers have to say about our services.</p>
    </section>

    <section id="contact" class="container mt-4">
        <h2>Contact Us</h2>
        <p>Please contact us to discuss your catering needs.</p>
    </section>

    <footer>
        <p>&copy; 2023 Your Catering Service</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>
</html>