<?php
include '../../layouts/session.php';
include '../../layouts/config.php';

$id_pesanan = intval($_GET['id_pesanan'] ?? 0);
if (!$id_pesanan) {
    header('Location: ../pesanan/pesananlihat.php');
    exit;
}

$produk_result = mysqli_query($conn, "SELECT * FROM produk ORDER BY nama_produk ASC");

include '../../layouts/head.php';
include '../../layouts/menu.php';
?>
<div class="mb-12 relative z-10">
    <h2 class="font-serif text-4xl tracking-tight mb-2">Tambah Item Produk</h2>
    <a href="detail_pesananlihat.php?id=<?= $id_pesanan ?>" class="text-xs tracking-[0.2em] uppercase text-luxury-mutedFg hover:text-luxury-accent transition-colors">← Kembali ke Detail</a>
</div>

<div class="relative z-10 glass-panel p-8 max-w-lg">
    <form action="controllers/create.php" method="POST" class="space-y-6">
        <input type="hidden" name="id_pesanan" value="<?= $id_pesanan ?>">

        <div>
            <label class="block text-xs text-luxury-mutedFg font-serif italic mb-2">Pilih Produk</label>
            <select name="id_produk" required class="w-full glass-input text-sm cursor-pointer">
                <option value="">-- Pilih Produk --</option>
                <?php while ($p = mysqli_fetch_assoc($produk_result)): ?>
                    <option value="<?= $p['id_produk'] ?>"><?= htmlspecialchars($p['nama_produk']) ?> (Rp <?= number_format($p['harga_jual'], 0, ',', '.') ?>)</option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label class="block text-xs text-luxury-mutedFg font-serif italic mb-2">Quantity (Qty)</label>
            <input type="number" name="qty" min="1" value="1" required class="w-full glass-input text-sm">
        </div>

        <div class="pt-4">
            <button type="submit" class="h-12 px-8 border border-[#1A1A1A] bg-[#1A1A1A] text-white text-[10px] tracking-[0.2em] uppercase relative overflow-hidden group hover:bg-transparent hover:text-[#1A1A1A] transition-all duration-500 w-full">
                <span class="relative z-10 transition-colors duration-500">Tambah Item</span>
            </button>
        </div>
    </form>
</div>
<?php include '../../layouts/footer.php'; include '../../layouts/vendor-scripts.php'; ?>
