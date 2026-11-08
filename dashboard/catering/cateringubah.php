<?php
include '../../layouts/session.php';
include '../../layouts/config.php';
include '../../layouts/guard.php';
include '../../layouts/csrf.php';

require_role(['Owner', 'Admin', 'Pegawai Catering'], 'cateringlihat.php');

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: cateringlihat.php');
    exit;
}

$sql = "SELECT * FROM catering WHERE id_catering = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    $_SESSION['flash_error'] = 'Data tidak ditemukan.';
    header('Location: cateringlihat.php');
    exit;
}

include '../../layouts/head.php';
include '../../layouts/menu.php';
?>
<div class="mb-12 relative z-10">
    <h2 class="font-serif text-4xl tracking-tight mb-2">Ubah Jadwal Catering</h2>
    <a href="cateringlihat.php" class="text-xs tracking-[0.2em] uppercase text-luxury-mutedFg hover:text-luxury-accent transition-colors">← Kembali</a>
</div>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="mb-8 p-4 border border-red-900/20 text-red-900 text-sm bg-red-50/50 relative z-10">
        <?= $_SESSION['flash_error'] ?>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="border-t border-luxury-fg/20 pt-8 relative z-10 max-w-2xl bg-luxury-bg/50 backdrop-blur-sm p-8">
    <form action="controllers/update.php" method="POST" class="space-y-8">
        <?= csrf_field() ?>
        <input type="hidden" name="id_catering" value="<?= htmlspecialchars($data['id_catering']) ?>">

        <div class="relative group">
            <input type="date" name="tanggal_acara" id="tanggal_acara" required value="<?= htmlspecialchars($data['tanggal_acara']) ?>"
                class="w-full h-12 bg-transparent border-b border-luxury-fg/50 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-500 peer">
            <label for="tanggal_acara" class="absolute left-0 -top-4 text-xs text-luxury-mutedFg font-serif italic transition-all duration-500 peer-focus:text-luxury-accent">Tanggal Acara</label>
        </div>

        <div class="relative group mt-12">
            <textarea name="lokasi_acara" id="lokasi_acara" rows="3" required
                class="w-full bg-transparent border-b border-luxury-fg/50 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-500 peer placeholder-transparent resize-none" placeholder="Lokasi Acara"><?= htmlspecialchars($data['lokasi_acara']) ?></textarea>
            <label for="lokasi_acara" class="absolute left-0 -top-4 text-xs text-luxury-mutedFg font-serif italic transition-all duration-500 peer-focus:-top-4 peer-focus:text-xs peer-focus:text-luxury-accent">Lokasi Acara</label>
        </div>

        <div class="pt-4">
            <button type="submit" class="h-12 px-8 bg-luxury-fg text-luxury-white text-xs tracking-[0.2em] uppercase font-medium relative overflow-hidden group shadow-btn hover:shadow-btn-hover transition-all duration-500">
                <span class="absolute inset-0 bg-luxury-accent -translate-x-full group-hover:translate-x-0 transition-transform duration-500" style="transition-timing-function: cubic-bezier(0.25, 0.46, 0.45, 0.94);"></span>
                <span class="relative z-10 text-white">Simpan Perubahan</span>
            </button>
        </div>
    </form>
</div>

<?php
include '../../layouts/footer.php';
include '../../layouts/vendor-scripts.php';
?>
