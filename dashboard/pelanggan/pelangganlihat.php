<?php
include '../../layouts/session.php';
include '../../layouts/config.php';
include '../../layouts/head.php';
include '../../layouts/menu.php';

$sql = "SELECT * FROM pelanggan ORDER BY id_pelanggan DESC";
$result = mysqli_query($conn, $sql);
?>
<div class="mb-12 flex justify-between items-end relative z-10">
    <div>
        <h2 class="font-serif text-4xl tracking-tight mb-2">Pelanggan</h2>
        <p class="text-sm text-luxury-mutedFg">Kelola data pelanggan kantin.</p>
    </div>
    <a href="pelanggantambah.php" class="h-10 px-6 border border-[#1A1A1A] bg-[#1A1A1A] text-white text-[10px] tracking-[0.2em] uppercase flex items-center hover:bg-transparent hover:text-[#1A1A1A] transition-all duration-300">
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
                <th class="py-4 px-4 font-normal">Nama Pelanggan</th>
                <th class="py-4 px-4 font-normal">No Telepon</th>
                <th class="py-4 px-4 font-normal">Alamat</th>
                <th class="py-4 px-4 font-normal text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr class="border-b border-[#1A1A1A]/10 hover:bg-[#F9F8F6] transition-colors duration-300">
                    <td class="py-4 px-4 font-mono text-xs text-luxury-mutedFg"><?= htmlspecialchars($row['id_pelanggan']) ?></td>
                    <td class="py-4 px-4 font-serif"><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                    <td class="py-4 px-4 text-luxury-mutedFg font-mono text-sm"><?= htmlspecialchars($row['no_telp']) ?></td>
                    <td class="py-4 px-4 text-luxury-mutedFg truncate max-w-xs text-sm"><?= htmlspecialchars($row['alamat_pelanggan']) ?></td>
                    <td class="py-4 px-4 text-right space-x-4">
                        <a href="pelangganubah.php?id=<?= $row['id_pelanggan'] ?>" class="text-[10px] uppercase tracking-widest text-luxury-mutedFg hover:text-luxury-accent transition-colors">Ubah</a>
                        <a href="pelangganhapus.php?id=<?= $row['id_pelanggan'] ?>" class="text-[10px] uppercase tracking-widest text-luxury-mutedFg hover:text-red-700 transition-colors">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if (mysqli_num_rows($result) === 0): ?>
                <tr>
                    <td colspan="5" class="py-8 text-center text-luxury-mutedFg text-sm">Belum ada data pelanggan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
include '../../layouts/footer.php';
include '../../layouts/vendor-scripts.php';
?>