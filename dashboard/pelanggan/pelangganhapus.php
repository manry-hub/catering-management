<?php
include '../../layouts/session.php';
include '../../layouts/config.php';

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: pelangganlihat.php');
    exit;
}

$sql = "SELECT * FROM pelanggan WHERE id_pelanggan = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    $_SESSION['flash_error'] = 'Data tidak ditemukan.';
    header('Location: pelangganlihat.php');
    exit;
}

include '../../layouts/head.php';
include '../../layouts/menu.php';
?>
<div class="mb-12 relative z-10">
    <h2 class="font-serif text-4xl tracking-tight mb-2 text-red-900">Hapus Pelanggan</h2>
    <a href="pelangganlihat.php" class="text-xs tracking-[0.2em] uppercase text-luxury-mutedFg hover:text-luxury-accent transition-colors">← Batal</a>
</div>

<div class="border-t border-luxury-fg/20 pt-8 relative z-10 max-w-2xl bg-luxury-bg/50 backdrop-blur-sm p-8">
    <p class="text-sm mb-8 text-luxury-mutedFg">Apakah Anda yakin ingin menghapus pelanggan <strong><?= htmlspecialchars($data['nama_pelanggan']) ?></strong>? Tindakan ini tidak dapat dibatalkan.</p>
    
    <form action="controllers/delete.php" method="POST">
        <input type="hidden" name="id_pelanggan" value="<?= htmlspecialchars($data['id_pelanggan']) ?>">
        
        <button type="submit" class="h-12 px-8 bg-red-900 text-luxury-white text-xs tracking-[0.2em] uppercase font-medium relative overflow-hidden group shadow-btn hover:shadow-btn-hover transition-all duration-500">
            <span class="absolute inset-0 bg-red-700 -translate-x-full group-hover:translate-x-0 transition-transform duration-500" style="transition-timing-function: cubic-bezier(0.25, 0.46, 0.45, 0.94);"></span>
            <span class="relative z-10 text-white">Ya, Hapus</span>
        </button>
    </form>
</div>

<?php
include '../../layouts/footer.php';
include '../../layouts/vendor-scripts.php';
?>
