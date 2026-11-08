<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const hamburgerBtn = document.getElementById('hamburger-btn');
        const sidebar = document.getElementById('mobile-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const closeBtn = document.getElementById('sidebar-close-btn');

        if (hamburgerBtn && sidebar && overlay) {
            function openSidebar() {
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.remove('opacity-0'), 10);
                sidebar.classList.remove('-translate-x-[150%]');
                hamburgerBtn.setAttribute('aria-expanded', 'true');
            }

            function closeSidebar() {
                sidebar.classList.add('-translate-x-[150%]');
                overlay.classList.add('opacity-0');
                setTimeout(() => overlay.classList.add('hidden'), 300);
                hamburgerBtn.setAttribute('aria-expanded', 'false');
            }

            function toggleSidebar() {
                const isOpen = !sidebar.classList.contains('-translate-x-[150%]');
                if (isOpen) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            }

            hamburgerBtn.addEventListener('click', toggleSidebar);
            overlay.addEventListener('click', closeSidebar);
            if (closeBtn) {
                closeBtn.addEventListener('click', closeSidebar);
            }

            // Keyboard accessibility: Escape key closes mobile sidebar
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !sidebar.classList.contains('-translate-x-[150%]')) {
                    closeSidebar();
                }
            });
        }
    });
</script>
<!-- Vendor scripts placeholder -->
</body>
</html>
