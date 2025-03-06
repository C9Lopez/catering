document.addEventListener('DOMContentLoaded', function() {
    console.log('admin.js loaded'); // Debug log to confirm script is running

    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    // Debug logs to confirm elements are found
    console.log('Sidebar:', sidebar);
    console.log('Sidebar Toggle:', sidebarToggle);
    console.log('Sidebar Close:', sidebarClose);
    console.log('Sidebar Overlay:', sidebarOverlay);

    if (sidebar && sidebarToggle && sidebarOverlay) {
        // Function to toggle sidebar and overlay
        function toggleSidebar() {
            console.log('Toggling sidebar'); // Debug log to confirm function is called
            const isExpanded = sidebar.classList.contains('active');
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            sidebarToggle.setAttribute('aria-expanded', !isExpanded);
        }

        // Toggle sidebar on toggle button click
        sidebarToggle.addEventListener('click', toggleSidebar);

        // Toggle sidebar on close button click (mobile only)
        if (sidebarClose) {
            sidebarClose.addEventListener('click', toggleSidebar);
        }

        // Close sidebar when clicking on overlay
        sidebarOverlay.addEventListener('click', function() {
            console.log('Closing sidebar via overlay'); // Debug log
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            sidebarToggle.setAttribute('aria-expanded', 'false');
        });

        // Close sidebar when clicking a nav link on mobile
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 991) {
                    console.log('Closing sidebar via nav link'); // Debug log
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                    sidebarToggle.setAttribute('aria-expanded', 'false');
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                console.log('Closing sidebar on resize to desktop'); // Debug log
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
                sidebarToggle.setAttribute('aria-expanded', 'false');
            }
        });
    } else {
        console.error('One or more sidebar elements not found:', {
            sidebar: !!sidebar,
            sidebarToggle: !!sidebarToggle,
            sidebarOverlay: !!sidebarOverlay
        });
    }
});