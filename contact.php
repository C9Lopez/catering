<?php
  session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Us - Pochie Catering</title>
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
    <!-- <link href="lib/animate/animate.min.css" rel="stylesheet"> -->
    <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <link href="../css/themes.css" rel="stylesheet">
    
    <style>
        .contact-title {
            font-family: 'Orbitron', sans-serif;
            text-transform: uppercase;
            letter-spacing: 2px;
            background: linear-gradient(45deg, #0d6efd, #00ffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
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

        .contact-form input,
        .contact-form textarea {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(13, 110, 253, 0.2);
            border-radius: 15px;
            padding: 15px 20px;
            color: #333;
            transition: all 0.3s ease;
        }

        .contact-form input:focus,
        .contact-form textarea:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: #0d6efd;
            box-shadow: 0 0 20px rgba(13, 110, 253, 0.2);
        }

        .contact-info-card {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.1), rgba(0, 255, 255, 0.1));
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }

        .contact-info-card:hover {
            transform: translateX(10px);
        }

        .contact-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #0d6efd, #00ffff);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: white;
            font-size: 1.5em;
        }

        .map-container {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .map-container iframe {
            width: 100%;
            height: 400px;
            border: none;
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

        .business-hours {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.1), rgba(0, 255, 255, 0.1));
            padding: 20px;
            border-radius: 15px;
            margin-top: 30px;
        }

        .hours-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(13, 110, 253, 0.2);
        }

        .submit-btn {
            background: linear-gradient(45deg, #0d6efd, #00ffff);
            border: none;
            border-radius: 30px;
            padding: 15px 40px;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }

        .faq-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .faq-card:hover {
            transform: translateX(10px);
            background: rgba(255, 255, 255, 0.2);
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
            <h1 class="display-1 mb-4 contact-title">Contact Us</h1>
            <p class="lead">Get in touch with us for your catering needs</p>
        </div>
    </div>

    <!-- Contact Info Cards -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="contact-info-card">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h4>Visit Us</h4>
                        <p>123 Catering Street<br>Brgy. Sample, City 1234<br>Philippines</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="contact-info-card">
                        <div class="contact-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <h4>Call Us</h4>
                        <p>Mobile: +63 912 345 6789<br>Landline: (02) 8123 4567<br>Toll Free: 1-800-123-4567</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="contact-info-card">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4>Email Us</h4>
                        <p>info@pochiecatering.com<br>bookings@pochiecatering.com<br>support@pochiecatering.com</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Map and Contact Form -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="row g-5">
                <!-- Contact Form -->
                <div class="col-lg-6">
                    <div class="glass-card">
                        <h2 class="contact-title mb-4">Send Us a Message</h2>
                        <form class="contact-form" action="mailto:bongbongcastro19@gmail.com" method="POST" enctype="text/plain">
                        <div class="row g-3">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" placeholder="Your Name">
                                </div>
                                <div class="col-md-6">
                                    <input type="email" class="form-control" placeholder="Your Email">
                                </div>
                                <div class="col-12">
                                    <input type="text" class="form-control" placeholder="Subject">
                                </div>
                                <div class="col-12">
                                    <textarea class="form-control" rows="6" placeholder="Your Message"></textarea>
                                </div>
                                <div class="col-12">
                                    <button class="submit-btn w-100" type="submit">Send Message</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Map -->
                <div class="col-lg-6">
                    <div class="map-container">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3861.802548850011!2d121.04131661484821!3d14.554743589828378!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397c8efd99aad53%3A0xb64b39847a866fde!2sMakati%2C%20Metro%20Manila!5e0!3m2!1sen!2sph!4v1625843981760!5m2!1sen!2sph"
                            allowfullscreen="" 
                            loading="lazy">
                        </iframe>
                    </div>
                    <!-- Business Hours -->
                    <div class="business-hours">
                        <h4 class="mb-4">Business Hours</h4>
                        <div class="hours-item">
                            <span>Monday - Friday</span>
                            <span>9:00 AM - 6:00 PM</span>
                        </div>
                        <div class="hours-item">
                            <span>Saturday</span>
                            <span>9:00 AM - 4:00 PM</span>
                        </div>
                        <div class="hours-item">
                            <span>Sunday</span>
                            <span>By Appointment</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <h2 class="contact-title text-center mb-5">Frequently Asked Questions</h2>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="faq-card">
                        <h5>How do I book your services?</h5>
                        <p>You can book through our online form, call us directly, or visit our office. We recommend booking at least 2-3 months in advance.</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="faq-card">
                        <h5>What areas do you serve?</h5>
                        <p>We cater to all areas within Metro Manila and nearby provinces. Additional charges may apply for distant locations.</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="faq-card">
                        <h5>Do you offer food tasting?</h5>
                        <p>Yes, we offer food tasting sessions for confirmed bookings of wedding and corporate packages.</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="faq-card">
                        <h5>What's your minimum guest count?</h5>
                        <p>Our minimum guest count varies by package, starting from 30 guests for intimate gatherings.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Social Media Section -->
    <div class="container-fluid py-6 wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="text-center">
                <h2 class="contact-title mb-4">Connect With Us</h2>
                <p class="mb-4">Follow us on social media for updates, inspiration, and special offers</p>
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
