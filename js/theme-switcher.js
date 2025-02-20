// Function to toggle theme
function toggleTheme() {
    const isDark = document.body.classList.contains('dark-theme');
    document.body.classList.remove('light-theme', 'dark-theme');
    const newTheme = isDark ? 'light-theme' : 'dark-theme';
    document.body.classList.add(newTheme);
    localStorage.setItem('theme', newTheme);
}

// Initialize theme
function initializeTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light-theme';
    document.body.classList.remove('light-theme', 'dark-theme');
    document.body.classList.add(savedTheme);
}

// Add event listener for theme toggle
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }
    
    // Initialize theme
    initializeTheme();
});
