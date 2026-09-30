<?php
include '../../layouts/session.php';
include '../../layouts/config.php';
include '../../layouts/head.php';
include '../../layouts/menu.php';
?>
<div class="mb-12 relative z-10">
    <h2 class="font-serif text-4xl tracking-tight mb-2">Tambah Pegawai</h2>
    <a href="pegawailihat.php"
        class="text-xs tracking-[0.2em] uppercase text-luxury-mutedFg hover:text-luxury-accent transition-colors">←
        Kembali</a>
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
            <input type="text" name="nama_pegawai" id="nama_pegawai" required
                class="w-full h-12 bg-transparent border-b border-luxury-fg/50 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-500 peer placeholder-transparent"
                placeholder="Nama Pegawai">
            <label for="nama_pegawai"
                class="absolute left-0 top-3 text-luxury-mutedFg font-serif italic transition-all duration-500 peer-focus:-top-4 peer-focus:text-xs peer-focus:text-luxury-accent peer-valid:-top-4 peer-valid:text-xs peer-valid:text-luxury-mutedFg">Nama
                Pegawai</label>
        </div>

        <div class="relative group">
            <input type="password" name="password" id="password" required
                class="w-full h-12 bg-transparent border-b border-luxury-fg/50 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-500 peer placeholder-transparent"
                placeholder="Password">
            <label for="password"
                class="absolute left-0 top-3 text-luxury-mutedFg font-serif italic transition-all duration-500 peer-focus:-top-4 peer-focus:text-xs peer-focus:text-luxury-accent peer-valid:-top-4 peer-valid:text-xs peer-valid:text-luxury-mutedFg">Password</label>
        </div>

        <div>
            <label for="jabatan" class="block text-xs text-luxury-mutedFg font-serif italic mb-2">Jabatan</label>
            <select name="jabatan" id="jabatan" required
                class="w-full h-12 bg-transparent border-b border-luxury-fg/50 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-500 cursor-pointer">
                <option value="">-- Pilih Jabatan --</option>
                <option value="Pegawai Kantin">Pegawai Kantin</option>
                <option value="Pegawai Catering">Pegawai Catering</option>
                <option value="Pegawai Dapur">Pegawai Dapur</option>
                <option value="Owner">Owner</option>
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