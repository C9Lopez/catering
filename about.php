<?php
  session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us - Pochie Catering</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Playball&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <link href="../css/themes.css" rel="stylesheet">
    
    
    <style>
        .futuristic-title {
            font-family: 'Orbitron', sans-serif;
            text-transform: uppercase;
            letter-spacing: 2px;
            background: linear-gradient(45deg, #0d6efd, #00ffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 30px;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 30px;
            transition: all 0.3s ease;
            height: 100%;
        }

        .glass-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .timeline {
            position: relative;
            padding: 40px 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            width: 2px;
            height: 100%;
            background: linear-gradient(to bottom, #0d6efd, #00ffff);
            transform: translateX(-50%);
        }

        .timeline-item {
            margin-bottom: 50px;
            position: relative;
            width: 50%;
            padding: 20px;
        }

        .timeline-item:nth-child(odd) {
            left: 0;
            padding-right: 40px;
        }

        .timeline-item:nth-child(even) {
            left: 50%;
            padding-left: 40px;
        }

        .timeline-content {
            position: relative;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .timeline-content:hover {
            transform: scale(1.05);
        }

        .timeline-date {
            font-family: 'Orbitron', sans-serif;
            color: #0d6efd;
            margin-bottom: 10px;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.1), rgba(0, 255, 255, 0.1));
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: scale(1.05);
        }

        .stat-number {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5em;
            background: linear-gradient(45deg, #0d6efd, #00ffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .team-member {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            margin-bottom: 30px;
        }

        .team-member img {
            width: 100%;
            height: auto;
            transition: all 0.5s ease;
        }

        .team-member:hover img {
            transform: scale(1.1);
        }

        .team-info {
            position: absolute;
            bottom: -100%;
            left: 0;
            width: 100%;
            padding: 20px;
            background: linear-gradient(to top, rgba(13, 110, 253, 0.9), transparent);
            transition: all 0.5s ease;
        }

        .team-member:hover .team-info {
            bottom: 0;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }

        .social-link {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(45deg, #0d6efd, #00ffff);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5em;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            transform: scale(1.1);
            color: white;
        }

        .value-card {
            text-align: center;
            padding: 30px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.1), rgba(0, 255, 255, 0.1));
            transition: all 0.3s ease;
        }

        .value-card:hover {
            transform: translateY(-10px);
        }

        .value-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(45deg, #0d6efd, #00ffff);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2em;
        }
    </style>
</head>
<body class="light-theme">

<!-- Loading Screen -->
<div id="loading-screen">
    <div class="loader"></div>
</div>

<?php include 'layout/navbar.php'; ?>

    <!-- Hero Section -->
    <div class="container-fluid hero-section py-6 my-6 text-center wow fadeInUp" data-wow-delay="0.3s">
        <div class="hero-overlay"></div>
        <div class="container position-relative text-white">
            <h1 class="display-1 mb-4 futuristic-title">About Pochie Catering</h1>
            <p class="lead">Crafting Memorable Experiences Through Exceptional Catering Since 2010</p>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-number" data-toggle="counter-up">13</div>
                        <p>Years of Excellence</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-number" data-toggle="counter-up">5000</div>
                        <p>Events Catered</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-number" data-toggle="counter-up">50</div>
                        <p>Professional Staff</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card">
                        <div class="stat-number" data-toggle="counter-up">98</div>
                        <p>Client Satisfaction %</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- About Section -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <h2 class="futuristic-title">Our Story</h2>
                    <p class="mb-4">
                        Founded in 2010, Pochie Catering began as a small family business with a big dream - to provide 
                        exceptional catering services that create lasting memories. What started as a passion for food 
                        and service has grown into one of the region's most trusted catering companies.
                    </p>
                    <p class="mb-4">
                        Today, we pride ourselves on delivering innovative culinary experiences, combining traditional 
                        flavors with modern presentation techniques. Our commitment to quality and customer satisfaction 
                        has earned us numerous accolades and the trust of countless satisfied clients.
                    </p>
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check text-primary me-3"></i>
                                <span>Quality Ingredients</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check text-primary me-3"></i>
                                <span>Expert Chefs</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check text-primary me-3"></i>
                                <span>Custom Menus</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check text-primary me-3"></i>
                                <span>Professional Service</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="glass-card">
                        <img src="img/about.jpg" class="img-fluid rounded" alt="About Pochie Catering">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Our Values -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <h2 class="futuristic-title text-center">Our Core Values</h2>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h4>Passion</h4>
                        <p>We pour our heart into every dish and service we provide</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h4>Excellence</h4>
                        <p>We strive for perfection in every detail</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h4>Integrity</h4>
                        <p>We maintain the highest standards of professionalism</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline Section -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <h2 class="futuristic-title text-center">Our Journey</h2>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-date">2010</div>
                        <h4>The Beginning</h4>
                        <p>Founded as a small family catering business</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-date">2015</div>
                        <h4>Major Expansion</h4>
                        <p>Expanded services to include corporate events</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-date">2018</div>
                        <h4>Innovation</h4>
                        <p>Introduced modern culinary techniques</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-date">2023</div>
                        <h4>Industry Leader</h4>
                        <p>Recognized as a premier catering service</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Section -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <h2 class="futuristic-title text-center">Our Expert Team</h2>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="team-member">
                        <img src="img/team-1.jpg" alt="Team Member">
                        <div class="team-info">
                            <h5 class="text-white">ipsum1</h5>
                            <p class="text-white-50">Executive Chef</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="team-member">
                        <img src="img/team-2.jpg" alt="Team Member">
                        <div class="team-info">
                            <h5 class="text-white">ipsum2</h5>
                            <p class="text-white-50">Event Director</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="team-member">
                        <img src="img/team-3.jpg" alt="Team Member">
                        <div class="team-info">
                            <h5 class="text-white">ipsum3</h5>
                            <p class="text-white-50">Operations Manager</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="team-member">
                        <img src="img/team-4.jpg" alt="Team Member">
                        <div class="team-info">
                            <h5 class="text-white">ipsum4</h5>
                            <p class="text-white-50">Customer Relations</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Social Media Section -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <h2 class="futuristic-title text-center">Connect With Us</h2>
            <div class="social-links">
                <a href="https://www.facebook.com/pochiecatering" target="_blank" class="social-link">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://www.instagram.com/pochiecatering" target="_blank" class="social-link">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="https://twitter.com/pochiecatering" target="_blank" class="social-link">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://www.linkedin.com/company/pochiecatering" target="_blank" class="social-link">
                    <i class="fab fa-linkedin-in"></i>
                </a>
            </div>
        </div>
    </div>

    <?php include 'layout/footer.php'; ?>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    <script src="lib/lightbox/js/lightbox.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    <script src="../js/theme-switcher.js"></script>
    <script>
        new WOW().init();
    </script>
</body>
</html>
