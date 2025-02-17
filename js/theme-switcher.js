// Function to set theme
function setTheme(themeName) {
    // Remove all theme classes
    document.body.classList.remove('light-theme', 'dark-theme', 'dark-grey-theme', 'dark-blue-theme');
    // Add selected theme class
    document.body.classList.add(themeName);
    // Save theme preference
    localStorage.setItem('theme', themeName);
}

// Function to toggle theme
function initializeTheme() {
    // Get saved theme from localStorage
    const savedTheme = localStorage.getItem('theme') || 'light-theme';
    setTheme(savedTheme);
    
    // Set the select value to match the current theme
    const themeSelect = document.getElementById('theme-select');
    if (themeSelect) {
        themeSelect.value = savedTheme;
    }
}

// Add event listener for theme selection
document.addEventListener('DOMContentLoaded', function() {
    const themeSelect = document.getElementById('theme-select');
    if (themeSelect) {
        themeSelect.addEventListener('change', function() {
            setTheme(this.value);
        });
    }
    
    // Initialize theme
    initializeTheme();
});
