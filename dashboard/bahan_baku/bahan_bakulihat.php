<?php
include '../../layouts/session.php';
include '../../layouts/config.php';
include '../../layouts/head.php';
include '../../layouts/menu.php';

$sql = "SELECT * FROM bahan_baku ORDER BY id_bahan_baku DESC";
$result = mysqli_query($conn, $sql);
?>
<div class="mb-12 flex justify-between items-end relative z-10">
    <div>
        <h2 class="font-serif text-4xl tracking-tight mb-2">Bahan Baku</h2>
        <p class="text-sm text-luxury-mutedFg">Kelola inventori dan status ketersediaan bahan.</p>
    </div>
    <a href="bahan_bakutambah.php" class="h-10 px-6 border border-[#1A1A1A] bg-[#1A1A1A] text-white text-[10px] tracking-[0.2em] uppercase flex items-center hover:bg-transparent hover:text-[#1A1A1A] transition-all duration-300">
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
                <th class="py-4 px-4 font-normal">Nama Bahan</th>
                <th class="py-4 px-4 font-normal">Harga / Satuan</th>
                <th class="py-4 px-4 font-normal">Stok Sekarang</th>
                <th class="py-4 px-4 font-normal">Stok Kritis</th>
                <th class="py-4 px-4 font-normal">Status</th>
                <th class="py-4 px-4 font-normal text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <?php
                    // Set color based on status
                    $statusColor = 'text-green-700';
                    if ($row['status_ketersediaan'] === 'Menipis') $statusColor = 'text-yellow-600';
                    if ($row['status_ketersediaan'] === 'Habis') $statusColor = 'text-red-700';
                ?>
                <tr class="border-b border-[#1A1A1A]/10 hover:bg-[#F9F8F6] transition-colors duration-300">
                    <td class="py-4 px-4 font-mono text-xs text-luxury-mutedFg"><?= htmlspecialchars($row['id_bahan_baku']) ?></td>
                    <td class="py-4 px-4 font-serif"><?= htmlspecialchars($row['nama_bahan']) ?></td>
                    <td class="py-4 px-4 font-mono text-sm text-luxury-mutedFg">Rp <?= number_format($row['harga_satuan_bahan'], 0, ',', '.') ?> / <?= htmlspecialchars($row['satuan']) ?></td>
                    <td class="py-4 px-4 font-mono text-sm text-luxury-mutedFg"><?= htmlspecialchars($row['stok']) ?></td>
                    <td class="py-4 px-4 font-mono text-sm text-luxury-mutedFg"><?= htmlspecialchars($row['stok_kritis']) ?></td>
                    <td class="py-4 px-4 font-mono text-xs uppercase tracking-[0.1em] <?= $statusColor ?>"><?= htmlspecialchars($row['status_ketersediaan']) ?></td>
                    <td class="py-4 px-4 text-right space-x-4">
                        <a href="bahan_bakuubah.php?id=<?= $row['id_bahan_baku'] ?>" class="text-[10px] uppercase tracking-widest text-luxury-mutedFg hover:text-luxury-accent transition-colors">Ubah</a>
                        <a href="bahan_bakuhapus.php?id=<?= $row['id_bahan_baku'] ?>" class="text-[10px] uppercase tracking-widest text-luxury-mutedFg hover:text-red-700 transition-colors">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if (mysqli_num_rows($result) === 0): ?>
                <tr>
                    <td colspan="7" class="py-8 text-center text-luxury-mutedFg text-sm">Belum ada data bahan baku.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
include '../../layouts/footer.php';
include '../../layouts/vendor-scripts.php';
?>