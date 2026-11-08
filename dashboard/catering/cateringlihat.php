<?php
include '../../layouts/session.php';
include '../../layouts/config.php';
include '../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Catering'], '../index.php');
include '../../layouts/head.php';
include '../../layouts/menu.php';

$sql = "SELECT * FROM catering ORDER BY tanggal_acara DESC";
$result = mysqli_query($conn, $sql);
?>
<div class="mb-12 flex justify-between items-end relative z-10">
    <div>
        <h2 class="font-serif text-4xl tracking-tight mb-2">Catering</h2>
        <p class="text-sm text-luxury-mutedFg">Kelola pesanan dan jadwal acara catering.</p>
    </div>
    <a href="cateringtambah.php" class="h-10 px-6 bg-luxury-fg text-luxury-white text-xs tracking-[0.2em] uppercase font-medium flex items-center shadow-btn hover:shadow-btn-hover transition-all duration-500">
        Tambah Data
    </a>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="mb-8 p-4 border border-green-900/20 text-green-900 text-sm bg-green-50/50 relative z-10">
        <?= htmlspecialchars($_SESSION['flash_success']) ?>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="mb-8 p-4 border border-red-900/20 text-red-900 text-sm bg-red-50/50 relative z-10">
        <?= render_flash_error() ?>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="border-t border-luxury-fg/20 overflow-x-auto relative z-10 bg-luxury-bg/50 backdrop-blur-sm">
    <table class="w-full text-left text-sm whitespace-nowrap">
        <thead>
            <tr class="border-b border-luxury-fg/10 uppercase text-[10px] tracking-[0.2em] text-luxury-mutedFg">
                <th class="py-4 px-4 font-normal">ID</th>
                <th class="py-4 px-4 font-normal">Tanggal Acara</th>
                <th class="py-4 px-4 font-normal">Lokasi</th>
                <th class="py-4 px-4 font-normal text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr class="border-b border-luxury-fg/10 hover:bg-luxury-bg/80 transition-colors duration-300">
                    <td class="py-4 px-4"><?= htmlspecialchars($row['id_catering']) ?></td>
                    <td class="py-4 px-4 font-medium"><?= date('d M Y', strtotime($row['tanggal_acara'])) ?></td>
                    <td class="py-4 px-4 text-luxury-mutedFg truncate max-w-xs"><?= htmlspecialchars($row['lokasi_acara']) ?></td>
                    <td class="py-4 px-4 text-right space-x-4">
                        <a href="cateringubah.php?id=<?= $row['id_catering'] ?>" class="text-[10px] uppercase tracking-widest text-luxury-mutedFg hover:text-luxury-accent transition-colors">Ubah</a>
                        <a href="cateringhapus.php?id=<?= $row['id_catering'] ?>" class="text-[10px] uppercase tracking-widest text-luxury-mutedFg hover:text-red-700 transition-colors">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if (mysqli_num_rows($result) === 0): ?>
                <tr>
                    <td colspan="4" class="py-8 text-center text-luxury-mutedFg text-sm">Belum ada jadwal catering.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
include '../../layouts/footer.php';
include '../../layouts/vendor-scripts.php';
?>