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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="catering-services.php" id="cateringDropdown" role="button">
                            Catering Services
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="catering-service/wedding.php">Wedding Catering</a></li>
                            <li><a class="dropdown-item" href="catering-service/corporate.php">Corporate Catering</a></li>
                            <li><a class="dropdown-item" href="catering-service/birthday.php">Birthday Catering</a></li>
                            <li><a class="dropdown-item" href="catering-service/debut.php">Debut Catering</a></li>
                            <li><a class="dropdown-item" href="catering-service/private.php">Private Event Catering</a></li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="menus.php">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="./auth/login.php">Log In</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./auth/signup.php">Sign Up</a>
                        </li>
                    <?php endif; ?>
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
<style>
    .nav-link.active {
    font-weight: bold;
    color:rgb(62, 120, 245) !important; 
}
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    let currentPage = window.location.pathname.split("/").pop();
    let navLinks = document.querySelectorAll(".nav-link"); 

    navLinks.forEach(link => {
        let linkPage = link.getAttribute("href").split("/").pop(); 
        if (linkPage === currentPage) {
            link.classList.add("active");
        }
    });
});

document.addEventListener("DOMContentLoaded", function() {
    var dropdowns = document.querySelectorAll(".nav-item.dropdown");

    dropdowns.forEach(function(dropdown) {
        dropdown.addEventListener("mouseenter", function() {
            this.querySelector(".dropdown-menu").classList.add("show");
        });

        dropdown.addEventListener("mouseleave", function() {
            this.querySelector(".dropdown-menu").classList.remove("show");
        });
    });
});

</script>
