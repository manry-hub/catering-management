<?php
include '../../layouts/session.php';
include '../../layouts/config.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ../pesanan/pesananlihat.php');
    exit;
}

$sql = "SELECT dp.*, pr.nama_produk, pr.harga_jual FROM detail_pesanan dp JOIN produk pr ON dp.id_produk = pr.id_produk WHERE dp.id_detail_pesanan = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$data) {
    $_SESSION['flash_error'] = 'Data tidak ditemukan.';
    header('Location: ../pesanan/pesananlihat.php');
    exit;
}

include '../../layouts/head.php';
include '../../layouts/menu.php';
?>
<div class="mb-12 relative z-10">
    <h2 class="font-serif text-4xl tracking-tight mb-2">Ubah Qty Item</h2>
    <a href="detail_pesananlihat.php?id=<?= $data['id_pesanan'] ?>" class="text-xs tracking-[0.2em] uppercase text-luxury-mutedFg hover:text-luxury-accent transition-colors">← Kembali ke Detail</a>
</div>

<div class="relative z-10 glass-panel p-8 max-w-lg">
    <form action="controllers/update.php" method="POST" class="space-y-6">
        <input type="hidden" name="id_detail_pesanan" value="<?= $data['id_detail_pesanan'] ?>">
        <input type="hidden" name="id_pesanan" value="<?= $data['id_pesanan'] ?>">
        
        <div>
            <label class="block text-xs text-luxury-mutedFg font-serif italic mb-2">Produk</label>
            <input type="text" value="<?= htmlspecialchars($data['nama_produk']) ?> (Rp <?= number_format($data['harga_jual'], 0, ',', '.') ?>)" readonly class="w-full glass-input text-sm bg-black/5 text-luxury-mutedFg cursor-not-allowed">
        </div>

        <div>
            <label class="block text-xs text-luxury-mutedFg font-serif italic mb-2">Quantity (Qty)</label>
            <input type="number" name="qty" min="1" value="<?= htmlspecialchars($data['qty']) ?>" required class="w-full glass-input text-sm">
        </div>

        <div class="pt-4">
            <button type="submit" class="h-12 px-8 border border-[#1A1A1A] bg-[#1A1A1A] text-white text-[10px] tracking-[0.2em] uppercase relative overflow-hidden group hover:bg-transparent hover:text-[#1A1A1A] transition-all duration-500 w-full">
                <span class="relative z-10 transition-colors duration-500">Simpan Perubahan</span>
            </button>
        </div>
    </form>
</div>
<?php include '../../layouts/footer.php'; include '../../layouts/vendor-scripts.php'; ?>
