<?php
include '../../layouts/session.php';
include '../../layouts/config.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: pesananlihat.php');
    exit;
}

$sql = "SELECT p.*, pel.nama_pelanggan FROM pesanan p LEFT JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan WHERE p.id_pesanan = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    $_SESSION['flash_error'] = 'Data tidak ditemukan.';
    header('Location: pesananlihat.php');
    exit;
}

include '../../layouts/head.php';
include '../../layouts/menu.php';
?>
<div class="mb-12 relative z-10">
    <h2 class="font-serif text-4xl tracking-tight mb-2 text-red-900">Hapus Pesanan</h2>
    <a href="pesananlihat.php" class="text-xs tracking-[0.2em] uppercase text-luxury-mutedFg hover:text-luxury-accent transition-colors">← Batal</a>
</div>

<div class="relative z-10 glass-panel p-8 max-w-lg">
    <p class="text-sm mb-4 text-luxury-mutedFg">
        Apakah Anda yakin ingin menghapus pesanan <strong class="text-luxury-fg">#<?= htmlspecialchars($data['id_pesanan']) ?></strong> 
        dari pelanggan <strong class="text-luxury-fg"><?= htmlspecialchars($data['nama_pelanggan'] ?? '-') ?></strong>?
    </p>
    <p class="text-sm mb-8 text-luxury-mutedFg">
        Total: <strong>Rp <?= number_format($data['total_harga'], 0, ',', '.') ?></strong><br>
        <span class="text-xs text-amber-600"><i class="fas fa-info-circle mr-1"></i>Stok bahan baku yang telah terpotong akan dikembalikan.</span>
    </p>
    
    <form action="controllers/delete.php" method="POST">
        <input type="hidden" name="id_pesanan" value="<?= htmlspecialchars($data['id_pesanan']) ?>">
        
        <button type="submit" class="h-12 px-8 bg-red-900 text-luxury-white text-xs tracking-[0.2em] uppercase font-medium relative overflow-hidden group shadow-btn hover:shadow-btn-hover transition-all duration-500 rounded-lg">
            <span class="absolute inset-0 bg-red-700 -translate-x-full group-hover:translate-x-0 transition-transform duration-500" style="transition-timing-function: cubic-bezier(0.25, 0.46, 0.45, 0.94);"></span>
            <span class="relative z-10 text-white">Ya, Hapus Pesanan</span>
        </button>
    </form>
</div>

<?php
include '../../layouts/footer.php';
include '../../layouts/vendor-scripts.php';
?>
