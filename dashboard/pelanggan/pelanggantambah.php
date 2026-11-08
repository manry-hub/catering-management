<?php
include '../../layouts/session.php';
include '../../layouts/config.php';
include '../../layouts/guard.php';
include '../../layouts/csrf.php';

require_role(['Owner', 'Admin', 'Pegawai Kantin', 'Pegawai Catering'], 'pelangganlihat.php');
include '../../layouts/head.php';
include '../../layouts/menu.php';
?>
<div class="mb-12 relative z-10">
    <h2 class="font-serif text-4xl tracking-tight mb-2">Tambah Pelanggan</h2>
    <a href="pelangganlihat.php" class="text-xs tracking-[0.2em] uppercase text-luxury-mutedFg hover:text-luxury-accent transition-colors">← Kembali</a>
</div>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="mb-8 p-4 border border-red-900/20 text-red-900 text-sm bg-red-50/50 relative z-10">
        <?= render_flash_error() ?>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="border-t border-luxury-fg/20 pt-8 relative z-10 max-w-2xl bg-luxury-bg/50 backdrop-blur-sm p-8">
    <form action="controllers/create.php" method="POST" class="space-y-8">
        <?= csrf_field() ?>
        <div class="relative group">
            <input type="text" name="nama_pelanggan" id="nama_pelanggan" required
                class="w-full h-12 bg-transparent border-b border-luxury-fg/50 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-500 peer placeholder-transparent" placeholder="Nama Pelanggan">
            <label for="nama_pelanggan" class="absolute left-0 top-3 text-luxury-mutedFg font-serif italic transition-all duration-500 peer-focus:-top-4 peer-focus:text-xs peer-focus:text-luxury-accent peer-valid:-top-4 peer-valid:text-xs peer-valid:text-luxury-mutedFg">Nama Pelanggan</label>
        </div>

        <div class="relative group">
            <input type="text" name="no_telp" id="no_telp" required
                class="w-full h-12 bg-transparent border-b border-luxury-fg/50 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-500 peer placeholder-transparent" placeholder="No Telepon">
            <label for="no_telp" class="absolute left-0 top-3 text-luxury-mutedFg font-serif italic transition-all duration-500 peer-focus:-top-4 peer-focus:text-xs peer-focus:text-luxury-accent peer-valid:-top-4 peer-valid:text-xs peer-valid:text-luxury-mutedFg">No Telepon</label>
        </div>

        <div class="relative group mt-12">
            <textarea name="alamat_pelanggan" id="alamat_pelanggan" rows="3"
                class="w-full bg-transparent border-b border-luxury-fg/50 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-500 peer placeholder-transparent resize-none" placeholder="Alamat Pelanggan"></textarea>
            <label for="alamat_pelanggan" class="absolute left-0 top-3 text-luxury-mutedFg font-serif italic transition-all duration-500 peer-focus:-top-4 peer-focus:text-xs peer-focus:text-luxury-accent peer-valid:-top-4 peer-valid:text-xs peer-valid:text-luxury-mutedFg">Alamat Pelanggan</label>
        </div>

        <div class="pt-4">
            <button type="submit"
                class="h-12 px-8 border border-[#1A1A1A] bg-[#1A1A1A] text-white text-[10px] tracking-[0.2em] uppercase relative overflow-hidden group hover:bg-transparent hover:text-[#1A1A1A] transition-all duration-500">
                <span class="relative z-10 transition-colors duration-500">Simpan</span>
            </button>
        </div>
    </form>
</div>

<?php
include '../../layouts/footer.php';
include '../../layouts/vendor-scripts.php';
?>