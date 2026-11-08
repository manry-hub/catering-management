<?php
include '../../layouts/session.php';
include '../../layouts/config.php';
include '../../layouts/guard.php';
include '../../layouts/csrf.php';

require_role(['Owner', 'Admin', 'Pegawai Dapur'], 'produklihat.php');

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: produklihat.php');
    exit;
}

$sql = "SELECT * FROM produk WHERE id_produk = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    $_SESSION['flash_error'] = 'Data tidak ditemukan.';
    header('Location: produklihat.php');
    exit;
}

include '../../layouts/head.php';
include '../../layouts/menu.php';
?>
<div class="mb-12 relative z-10">
    <h2 class="font-serif text-4xl tracking-tight mb-2">Ubah Produk</h2>
    <a href="produklihat.php"
        class="text-xs tracking-[0.2em] uppercase text-luxury-mutedFg hover:text-luxury-accent transition-colors">←
        Kembali</a>
</div>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="mb-8 p-4 border border-red-900/20 text-red-900 text-sm bg-red-50/50 relative z-10">
        <?= render_flash_error() ?>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="border-t border-luxury-fg/20 pt-8 relative z-10 max-w-2xl bg-luxury-bg/50 backdrop-blur-sm p-8">
    <form action="controllers/update.php" method="POST" class="space-y-8">
        <?= csrf_field() ?>
        <input type="hidden" name="id_produk" value="<?= htmlspecialchars($data['id_produk']) ?>">

        <div class="relative group">
            <input type="text" name="nama_produk" id="nama_produk" required
                value="<?= htmlspecialchars($data['nama_produk']) ?>"
                class="w-full h-12 bg-transparent border-b border-luxury-fg/50 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-500 peer placeholder-transparent"
                placeholder="Nama Produk">
            <label for="nama_produk"
                class="absolute left-0 -top-4 text-xs text-luxury-mutedFg font-serif italic transition-all duration-500 peer-focus:-top-4 peer-focus:text-xs peer-focus:text-luxury-accent">Nama
                Produk</label>
        </div>

        <div class="relative group">
            <select name="kategori" id="kategori" required
                class="w-full h-12 bg-transparent border-b border-luxury-fg/50 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-500 peer appearance-none cursor-pointer">
                <option value="" disabled hidden <?= empty($data['kategori']) ? 'selected' : '' ?>></option>
                <option value="Makanan (Kantin)" <?= $data['kategori'] === 'Makanan (Kantin)' ? 'selected' : '' ?>>Makanan
                    (Kantin)</option>
                <option value="Minuman (Kantin)" <?= $data['kategori'] === 'Minuman (Kantin)' ? 'selected' : '' ?>>Minuman
                    (Kantin)</option>
                <option value="Snack (Kantin)" <?= $data['kategori'] === 'Snack (Kantin)' ? 'selected' : '' ?>>Snack
                    (Kantin) </option>
                <option value="Dessert (Kantin)" <?= $data['kategori'] === 'Dessert (Kantin)' ? 'selected' : '' ?>>Dessert
                    (Kantin)</option>
                <option value="Paket Nasi Box" <?= $data['kategori'] === 'Paket Nasi Box' ? 'selected' : '' ?>>Paket Nasi
                    Box</option>
                <option value="Catering Harian" <?= $data['kategori'] === 'Catering Harian' ? 'selected' : '' ?>>Catering
                    Harian</option>
                <option value="Lainnya" <?= $data['kategori'] === 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
            </select>
            <label for="kategori"
                class="absolute left-0 -top-4 text-xs text-luxury-mutedFg font-serif italic transition-all duration-500 peer-focus:-top-4 peer-focus:text-xs peer-focus:text-luxury-accent pointer-events-none">Kategori</label>
            <div class="absolute right-0 top-4 text-luxury-mutedFg pointer-events-none">
                <i class="fas fa-chevron-down text-[10px]"></i>
            </div>
        </div>



        <div class="relative group">
            <input type="number" name="harga_jual" id="harga_jual" required min="0" step="0.01"
                value="<?= htmlspecialchars($data['harga_jual']) ?>"
                class="w-full h-12 bg-transparent border-b border-luxury-fg/50 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-500 peer placeholder-transparent"
                placeholder="Harga Jual (Rp)">
            <label for="harga_jual"
                class="absolute left-0 -top-4 text-xs text-luxury-mutedFg font-serif italic transition-all duration-500 peer-focus:-top-4 peer-focus:text-xs peer-focus:text-luxury-accent">Harga
                Jual (Rp)</label>
        </div>

        <div class="relative group">
            <input type="number" name="harga_pokok" id="harga_pokok" required min="0" step="0.01"
                value="<?= htmlspecialchars($data['harga_pokok']) ?>"
                class="w-full h-12 bg-transparent border-b border-luxury-fg/50 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-500 peer placeholder-transparent"
                placeholder="Harga Pokok (Rp)">
            <label for="harga_pokok"
                class="absolute left-0 -top-4 text-xs text-luxury-mutedFg font-serif italic transition-all duration-500 peer-focus:-top-4 peer-focus:text-xs peer-focus:text-luxury-accent">Harga
                Pokok (Rp)</label>
        </div>

        <div class="pt-4">
            <button type="submit"
                class="h-12 px-8 border border-[#1A1A1A] bg-[#1A1A1A] text-white text-[10px] tracking-[0.2em] uppercase relative overflow-hidden group hover:bg-transparent hover:text-[#1A1A1A] transition-all duration-500">
                <span class="relative z-10 transition-colors duration-500">Simpan Perubahan</span>
            </button>
        </div>
    </form>
</div>

<?php
include '../../layouts/footer.php';
include '../../layouts/vendor-scripts.php';
?>