<?php
include '../../layouts/session.php';
include '../../layouts/config.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ../pesanan/pesananlihat.php');
    exit;
}

$sql = "SELECT dp.*, pr.nama_produk FROM detail_pesanan dp JOIN produk pr ON dp.id_produk = pr.id_produk WHERE dp.id_detail_pesanan = ?";
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
    <h2 class="font-serif text-4xl tracking-tight mb-2">Hapus Item Pesanan</h2>
    <a href="detail_pesananlihat.php?id=<?= $data['id_pesanan'] ?>" class="text-xs tracking-[0.2em] uppercase text-luxury-mutedFg hover:text-luxury-accent transition-colors">← Batal & Kembali</a>
</div>

<div class="relative z-10 glass-panel p-8 max-w-lg">
    <div class="mb-8 p-4 rounded-lg border border-red-200 bg-red-50/50">
        <p class="text-red-800 text-sm">
            Apakah Anda yakin ingin menghapus item produk <strong><?= htmlspecialchars($data['nama_produk']) ?></strong> dari pesanan ini? 
            <br><br>
            Tindakan ini juga akan memperbarui Total Harga pesanan dan mengembalikan stok bahan baku secara otomatis.
        </p>
    </div>
    
    <form action="controllers/delete.php" method="POST" class="flex gap-4">
        <input type="hidden" name="id_detail_pesanan" value="<?= $data['id_detail_pesanan'] ?>">
        <input type="hidden" name="id_pesanan" value="<?= $data['id_pesanan'] ?>">
        
        <button type="submit" class="h-12 px-8 bg-red-600 text-white text-xs tracking-[0.2em] uppercase font-medium rounded-lg hover:bg-red-700 transition-colors flex-1">
            Ya, Hapus Item
        </button>
        <a href="detail_pesananlihat.php?id=<?= $data['id_pesanan'] ?>" class="h-12 px-8 bg-black/5 text-luxury-fg text-xs tracking-[0.2em] uppercase font-medium rounded-lg hover:bg-black/10 transition-colors flex items-center justify-center flex-1">
            Batal
        </a>
    </form>
</div>
<?php include '../../layouts/footer.php'; include '../../layouts/vendor-scripts.php'; ?>
