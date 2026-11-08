<?php
// Ensure this is included after head.php
$current_page_uri = $_SERVER['REQUEST_URI'] ?? '';

if (!function_exists('is_active_nav')) {
    function is_active_nav($patterns, $current_uri) {
        if (!is_array($patterns)) {
            $patterns = [$patterns];
        }
        foreach ($patterns as $pattern) {
            if (str_contains($current_uri, $pattern)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('nav_link_class')) {
    function nav_link_class($patterns, $current_uri) {
        if (is_active_nav($patterns, $current_uri)) {
            return 'block pb-2 text-xs tracking-[0.2em] uppercase transition-all duration-300 font-semibold text-luxury-accent border-b-2 border-luxury-accent';
        }
        return 'block pb-2 text-xs tracking-[0.2em] uppercase transition-all duration-300 text-luxury-mutedFg hover:text-luxury-fg border-b-2 border-transparent hover:border-luxury-accent/50';
    }
}
?>
<div class="flex flex-1 w-full relative">
    <!-- Gridlines for Architectural Precision (Desktop) -->
    <div class="hidden md:block gridline left-64"></div>
    <div class="hidden md:block gridline left-[calc(100%/3)]"></div>
    <div class="hidden md:block gridline left-[calc(200%/3)]"></div>

    <!-- Overlay for mobile -->
    <div id="sidebar-overlay"
        class="fixed inset-0 bg-black/30 backdrop-blur-sm z-30 hidden transition-opacity duration-300 opacity-0 md:hidden"
        aria-hidden="true">
    </div>

    <!-- Sidebar -->
    <nav id="mobile-sidebar" aria-label="Navigasi Utama"
        class="w-64 border-r border-[#1A1A1A]/10 bg-[#F9F8F6] md:bg-transparent flex flex-col fixed md:relative z-40 transform -translate-x-[150%] md:translate-x-0 transition-transform duration-500 shrink-0 inset-y-0 left-0 h-screen overflow-y-auto">
        <div class="p-8 border-b border-[#1A1A1A]/10 flex items-center justify-between">
            <h1 class="font-serif text-2xl tracking-[0.3em] uppercase text-luxury-fg text-center flex-1">Gorjes</h1>
            <button id="sidebar-close-btn" class="md:hidden w-8 h-8 flex items-center justify-center text-luxury-mutedFg hover:text-luxury-fg transition-colors" aria-label="Tutup Navigasi">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <ul class="flex-col py-8 px-6 flex gap-4 h-full">
            <?php if (in_array($_SESSION['jabatan'] ?? '', ['Owner', 'Admin'])): ?>
            <li>
                <a href="/gorjes/dashboard/index.php"
                    class="<?= nav_link_class(['/dashboard/index.php'], $current_page_uri) ?>">Dashboard</a>
            </li>
            <?php endif; ?>
            <?php if (in_array($_SESSION['jabatan'] ?? '', ['Owner', 'Admin'])): ?>
            <li>
                <a href="/gorjes/dashboard/pegawai/pegawailihat.php"
                    class="<?= nav_link_class(['/dashboard/pegawai/'], $current_page_uri) ?>">Pegawai</a>
            </li>
            <?php endif; ?>
            <?php if (in_array($_SESSION['jabatan'] ?? '', ['Pegawai Kantin', 'Pegawai Catering', 'Owner', 'Admin'])): ?>
            <li>
                <a href="/gorjes/dashboard/pelanggan/pelangganlihat.php"
                    class="<?= nav_link_class(['/dashboard/pelanggan/'], $current_page_uri) ?>">Pelanggan</a>
            </li>
            <?php endif; ?>
            <?php if (in_array($_SESSION['jabatan'] ?? '', ['Pegawai Kantin', 'Owner', 'Admin'])): ?>
            <li>
                <a href="/gorjes/dashboard/cabang_kantin/cabang_kantinlihat.php"
                    class="<?= nav_link_class(['/dashboard/cabang_kantin/'], $current_page_uri) ?>">Cabang Kantin</a>
            </li>
            <?php endif; ?>

            <?php if (in_array($_SESSION['jabatan'] ?? '', ['Pegawai Dapur', 'Owner', 'Admin'])): ?>
            <li>
                <a href="/gorjes/dashboard/bahan_baku/bahan_bakulihat.php"
                    class="<?= nav_link_class(['/dashboard/bahan_baku/'], $current_page_uri) ?>">Bahan Baku</a>
            </li>
            <?php endif; ?>
            <?php if (in_array($_SESSION['jabatan'] ?? '', ['Pegawai Dapur', 'Owner', 'Admin'])): ?>
            <li>
                <a href="/gorjes/dashboard/produk/produklihat.php"
                    class="<?= nav_link_class(['/dashboard/produk/', '/dashboard/bom/'], $current_page_uri) ?>">Produk</a>
            </li>
            <?php endif; ?>

            <?php if (in_array($_SESSION['jabatan'] ?? '', ['Pegawai Kantin', 'Pegawai Catering', 'Owner', 'Admin'])): ?>
            <li>
                <a href="/gorjes/dashboard/pesanan/pesananlihat.php"
                    class="<?= nav_link_class(['/dashboard/pesanan/', '/dashboard/detail_pesanan/'], $current_page_uri) ?>">Pesanan</a>
            </li>
            <?php endif; ?>

            <?php if (in_array($_SESSION['jabatan'] ?? '', ['Pegawai Catering', 'Owner', 'Admin'])): ?>
            <li>
                <a href="/gorjes/dashboard/catering/cateringlihat.php"
                    class="<?= nav_link_class(['/dashboard/catering/'], $current_page_uri) ?>">Catering</a>
            </li>
            <?php endif; ?>

            <li class="mt-auto pt-8 border-t border-[#1A1A1A]/10">
                <a href="/gorjes/logout.php"
                    class="block text-center text-xs tracking-[0.2em] uppercase hover:text-red-600 transition-all duration-300 text-luxury-mutedFg font-medium">Logout</a>
            </li>
        </ul>
    </nav>

    <!-- Mobile Header (Visible only on small screens) -->
    <div
        class="md:hidden fixed top-0 left-0 right-0 h-16 bg-[#F9F8F6] border-b border-[#1A1A1A]/10 flex items-center justify-between px-6 z-20">
        <div class="flex items-center gap-3">
            <button id="hamburger-btn" aria-label="Buka Menu Navigasi" aria-expanded="false"
                class="w-11 h-11 flex items-center justify-center -ml-2 text-luxury-fg focus:outline-none focus:ring-1 focus:ring-luxury-accent hover:text-luxury-accent transition-colors">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <h1 class="font-serif text-xl tracking-[0.3em] uppercase text-luxury-fg">Gorjes</h1>
        </div>
        <a href="/gorjes/logout.php"
            class="text-[10px] tracking-[0.2em] uppercase text-luxury-mutedFg hover:text-red-600 transition-colors">Logout</a>
    </div>

    <!-- Content wrapper -->
    <main class="flex-1 p-6 md:p-8 mt-16 md:mt-0 relative w-full overflow-x-hidden min-w-0">
        <div class="w-full">
