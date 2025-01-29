document.addEventListener("DOMContentLoaded", function () {
    let currentPage = window.location.pathname.split("/").pop(); // Get current page name
    let navLinks = document.querySelectorAll(".nav-link"); // Select all nav links

    navLinks.forEach(link => {
        let linkPage = link.getAttribute("href").split("/").pop(); // Extract file name from href
        if (linkPage === currentPage) {
            link.classList.add("active"); // Add Bootstrap's 'active' class
        }
    });
});