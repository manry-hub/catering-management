<?php
include '../../layouts/session.php';
include '../../layouts/config.php';
include '../../layouts/guard.php';

// Otorisasi: Hanya Owner dan Admin yang dapat melihat manajemen pegawai
require_role(['Owner', 'Admin']);

include '../../layouts/head.php';
include '../../layouts/menu.php';

$sql = "SELECT * FROM pegawai ORDER BY id_pegawai DESC";
$result = mysqli_query($conn, $sql);
?>
<div class="mb-12 flex justify-between items-end relative z-10">
    <div>
        <h2 class="font-serif text-4xl tracking-tight mb-2">Pegawai</h2>
        <p class="text-sm text-luxury-mutedFg">Kelola data pegawai dan hak akses sistem.</p>
    </div>
    <a href="pegawaitambah.php" class="h-10 px-6 border border-[#1A1A1A] bg-[#1A1A1A] text-white text-[10px] tracking-[0.2em] uppercase flex items-center hover:bg-transparent hover:text-[#1A1A1A] transition-all duration-300">
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
        <?= $_SESSION['flash_error'] ?>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="border-t border-luxury-fg/20 overflow-x-auto relative z-10 bg-luxury-bg/50 backdrop-blur-sm">
    <table class="w-full text-left text-sm whitespace-nowrap">
        <thead>
            <tr class="border-b border-luxury-fg/10 uppercase text-[10px] tracking-[0.2em] text-luxury-mutedFg">
                <th class="py-4 px-4 font-normal">ID</th>
                <th class="py-4 px-4 font-normal">Nama Pegawai</th>
                <th class="py-4 px-4 font-normal">Jabatan</th>
                <th class="py-4 px-4 font-normal text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr class="border-b border-[#1A1A1A]/10 hover:bg-[#F9F8F6] transition-colors duration-300">
                    <td class="py-4 px-4 font-mono text-xs"><?= htmlspecialchars($row['id_pegawai']) ?></td>
                    <td class="py-4 px-4 font-medium"><?= htmlspecialchars($row['nama_pegawai']) ?></td>
                    <td class="py-4 px-4 text-luxury-mutedFg"><?= htmlspecialchars($row['jabatan']) ?></td>
                    <td class="py-4 px-4 text-right space-x-4">
                        <a href="pegawaiubah.php?id=<?= $row['id_pegawai'] ?>" class="text-xs uppercase tracking-widest hover:text-luxury-accent transition-colors">Ubah</a>
                        <?php if ($row['id_pegawai'] != $_SESSION['id_pegawai']): ?>
                            <a href="pegawaihapus.php?id=<?= $row['id_pegawai'] ?>" class="text-xs uppercase tracking-widest text-red-700 hover:text-red-900 transition-colors">Hapus</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php
include '../../layouts/footer.php';
include '../../layouts/vendor-scripts.php';
?>
