<?php
// Ensure this is included after head.php
?>
<div class="flex flex-1 w-full relative">
    <!-- Gridlines for Architectural Precision (Desktop) -->
    <div class="hidden md:block gridline left-64"></div>
    <div class="hidden md:block gridline left-[calc(100%/3)]"></div>
    <div class="hidden md:block gridline left-[calc(200%/3)]"></div>

    <!-- Overlay for mobile -->
    <div id="sidebar-overlay"
        class="fixed inset-0 bg-black/20 backdrop-blur-sm z-30 hidden transition-opacity duration-300 opacity-0 md:hidden">
    </div>

    <!-- Sidebar -->
    <nav id="mobile-sidebar"
        class="w-64 border-r border-[#1A1A1A]/10 bg-[#F9F8F6] md:bg-transparent flex flex-col fixed md:relative z-40 transform -translate-x-[150%] md:translate-x-0 transition-transform duration-500 shrink-0 inset-y-0 left-0 h-screen overflow-y-auto">
        <div class="p-8 border-b border-[#1A1A1A]/10">
            <h1 class="font-serif text-2xl tracking-[0.3em] uppercase text-luxury-fg text-center">Gorjes</h1>
        </div>
        <ul class="flex-col py-8 px-6 flex gap-4 h-full">
            <?php if (in_array($_SESSION['jabatan'] ?? '', ['Owner', 'Admin'])): ?>
            <li>
                <a href="/gorjes/dashboard/index.php"
                    class="block pb-2 text-xs tracking-[0.2em] uppercase transition-all duration-300 font-medium border-b border-transparent hover:border-luxury-accent text-luxury-fg">Dashboard</a>
            </li>
            <?php endif; ?>
            <?php if (in_array($_SESSION['jabatan'] ?? '', ['Owner', 'Admin'])): ?>
            <li>
                <a href="/gorjes/dashboard/pegawai/pegawailihat.php"
                    class="block pb-2 text-xs tracking-[0.2em] uppercase transition-all duration-300 text-luxury-mutedFg hover:text-luxury-fg border-b border-transparent hover:border-luxury-accent">Pegawai</a>
            </li>
            <?php endif; ?>
            <?php if (in_array($_SESSION['jabatan'] ?? '', ['Pegawai Kantin', 'Pegawai Catering', 'Owner', 'Admin'])): ?>
            <li>
                <a href="/gorjes/dashboard/pelanggan/pelangganlihat.php"
                    class="block pb-2 text-xs tracking-[0.2em] uppercase transition-all duration-300 text-luxury-mutedFg hover:text-luxury-fg border-b border-transparent hover:border-luxury-accent">Pelanggan</a>
            </li>
            <?php endif; ?>
            <?php if (in_array($_SESSION['jabatan'] ?? '', ['Pegawai Kantin', 'Owner', 'Admin'])): ?>
            <li>
                <a href="/gorjes/dashboard/cabang_kantin/cabang_kantinlihat.php"
                    class="block pb-2 text-xs tracking-[0.2em] uppercase transition-all duration-300 text-luxury-mutedFg hover:text-luxury-fg border-b border-transparent hover:border-luxury-accent">Cabang Kantin</a>
            </li>
            <?php endif; ?>

            <?php if (in_array($_SESSION['jabatan'] ?? '', ['Pegawai Dapur', 'Owner', 'Admin'])): ?>
            <li>
                <a href="/gorjes/dashboard/bahan_baku/bahan_bakulihat.php"
                    class="block pb-2 text-xs tracking-[0.2em] uppercase transition-all duration-300 text-luxury-mutedFg hover:text-luxury-fg border-b border-transparent hover:border-luxury-accent">Bahan Baku</a>
            </li>
            <?php endif; ?>
            <?php if (in_array($_SESSION['jabatan'] ?? '', ['Pegawai Dapur', 'Owner', 'Admin'])): ?>
            <li>
                <a href="/gorjes/dashboard/produk/produklihat.php"
                    class="block pb-2 text-xs tracking-[0.2em] uppercase transition-all duration-300 font-medium text-luxury-accent border-b border-transparent hover:border-luxury-accent">Produk</a>
            </li>
            <?php endif; ?>


            <?php if (in_array($_SESSION['jabatan'] ?? '', ['Pegawai Kantin', 'Pegawai Catering', 'Owner', 'Admin'])): ?>
            <li>
                <a href="/gorjes/dashboard/pesanan/pesananlihat.php"
                    class="block pb-2 text-xs tracking-[0.2em] uppercase transition-all duration-300 font-medium text-luxury-accent border-b border-transparent hover:border-luxury-accent">Pesanan</a>
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
        <div class="flex items-center gap-4">
            <button id="hamburger-btn"
                class="text-luxury-fg focus:outline-none hover:text-luxury-accent transition-colors">
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