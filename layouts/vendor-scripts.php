<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const hamburgerBtn = document.getElementById('hamburger-btn');
        const sidebar = document.getElementById('mobile-sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        if (hamburgerBtn && sidebar && overlay) {
            function toggleSidebar() {
                const isOpen = !sidebar.classList.contains('-translate-x-[150%]');
                
                if (isOpen) {
                    // Close sidebar
                    sidebar.classList.add('-translate-x-[150%]');
                    overlay.classList.add('opacity-0');
                    setTimeout(() => overlay.classList.add('hidden'), 300);
                } else {
                    // Open sidebar
                    overlay.classList.remove('hidden');
                    setTimeout(() => overlay.classList.remove('opacity-0'), 10);
                    sidebar.classList.remove('-translate-x-[150%]');
                }
            }

            hamburgerBtn.addEventListener('click', toggleSidebar);
            overlay.addEventListener('click', toggleSidebar);
        }
    });
</script>
<!-- Vendor scripts placeholder -->
</body>
</html>
