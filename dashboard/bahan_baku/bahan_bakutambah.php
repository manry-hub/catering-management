<?php
include '../../layouts/session.php';
include '../../layouts/config.php';
include '../../layouts/head.php';
include '../../layouts/menu.php';
?>
<div class="mb-12 relative z-10">
    <h2 class="font-serif text-4xl tracking-tight mb-2">Tambah Bahan Baku</h2>
    <a href="bahan_bakulihat.php" class="text-xs tracking-[0.2em] uppercase text-luxury-mutedFg hover:text-luxury-accent transition-colors">← Kembali</a>
</div>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="mb-8 p-4 border border-red-900/20 text-red-900 text-sm bg-red-50/50 relative z-10">
        <?= $_SESSION['flash_error'] ?>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="border-t border-luxury-fg/20 pt-8 relative z-10 max-w-2xl bg-luxury-bg/50 backdrop-blur-sm p-8">
    <form action="controllers/create.php" method="POST" class="space-y-8">
        <div class="relative group">
            <input type="text" name="nama_bahan" id="nama_bahan" required
                class="w-full h-12 bg-transparent border-b border-luxury-fg/50 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-500 peer placeholder-transparent" placeholder="Nama Bahan">
            <label for="nama_bahan" class="absolute left-0 top-3 text-luxury-mutedFg font-serif italic transition-all duration-500 peer-focus:-top-4 peer-focus:text-xs peer-focus:text-luxury-accent peer-valid:-top-4 peer-valid:text-xs peer-valid:text-luxury-mutedFg">Nama Bahan</label>
        </div>

        <div class="relative group">
            <input type="text" name="satuan" id="satuan" required
                class="w-full h-12 bg-transparent border-b border-luxury-fg/50 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-500 peer placeholder-transparent" placeholder="Satuan (contoh: kg, liter, pcs)">
            <label for="satuan" class="absolute left-0 top-3 text-luxury-mutedFg font-serif italic transition-all duration-500 peer-focus:-top-4 peer-focus:text-xs peer-focus:text-luxury-accent peer-valid:-top-4 peer-valid:text-xs peer-valid:text-luxury-mutedFg">Satuan</label>
        </div>

        <div class="relative group">
            <input type="number" name="harga_satuan_bahan" id="harga_satuan_bahan" required min="0" step="0.01"
                class="w-full h-12 bg-transparent border-b border-luxury-fg/50 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-500 peer placeholder-transparent" placeholder="Harga per Satuan (Rp)">
            <label for="harga_satuan_bahan" class="absolute left-0 top-3 text-luxury-mutedFg font-serif italic transition-all duration-500 peer-focus:-top-4 peer-focus:text-xs peer-focus:text-luxury-accent peer-valid:-top-4 peer-valid:text-xs peer-valid:text-luxury-mutedFg">Harga per Satuan (Rp)</label>
        </div>

        <div class="grid grid-cols-2 gap-8">
            <div class="relative group">
                <input type="number" name="stok" id="stok" required min="0" value="0"
                    class="w-full h-12 bg-transparent border-b border-luxury-fg/50 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-500 peer placeholder-transparent" placeholder="Stok Sekarang">
                <label for="stok" class="absolute left-0 -top-4 text-xs text-luxury-mutedFg font-serif italic transition-all duration-500 peer-focus:text-luxury-accent">Stok Sekarang</label>
            </div>

            <div class="relative group">
                <input type="number" name="stok_kritis" id="stok_kritis" required min="0" value="0"
                    class="w-full h-12 bg-transparent border-b border-luxury-fg/50 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-500 peer placeholder-transparent" placeholder="Stok Kritis">
                <label for="stok_kritis" class="absolute left-0 -top-4 text-xs text-luxury-mutedFg font-serif italic transition-all duration-500 peer-focus:text-luxury-accent">Stok Kritis</label>
            </div>
        </div>
        
        <div class="relative group mt-4">
            <label for="status_ketersediaan" class="block text-xs text-luxury-mutedFg font-serif italic mb-2">Status Ketersediaan</label>
            <select name="status_ketersediaan" id="status_ketersediaan" class="w-full h-12 bg-transparent border-b border-luxury-fg/50 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-500 cursor-pointer">
                <option value="Tersedia" class="bg-luxury-bg text-luxury-fg">Tersedia</option>
                <option value="Menipis" class="bg-luxury-bg text-luxury-fg">Menipis</option>
                <option value="Habis" class="bg-luxury-bg text-luxury-fg">Habis</option>
            </select>
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