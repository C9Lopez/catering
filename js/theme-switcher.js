// Function to toggle theme
function toggleTheme() {
    const isDark = document.body.classList.contains('dark-theme');
    document.body.classList.remove('light-theme', 'dark-theme');
    const newTheme = isDark ? 'light-theme' : 'dark-theme';
    document.body.classList.add(newTheme);
    localStorage.setItem('theme', newTheme);

    // Re-render announcements to apply new theme styles
    loadAnnouncements(currentPage); // Reload current page to update card styles
}

// Initialize theme
function initializeTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light-theme';
    document.body.classList.remove('light-theme', 'dark-theme');
    document.body.classList.add(savedTheme);
    loadAnnouncements(currentPage); // Load announcements with the initial theme
}

// Add event listener for theme toggle
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }
    
    // Initialize theme and load announcements
    initializeTheme();
});