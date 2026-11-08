<?php
include '../../layouts/session.php';
include '../../layouts/config.php';
include '../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Dapur'], '../index.php');
include '../../layouts/head.php';
include '../../layouts/menu.php';

$sql = "SELECT * FROM produk ORDER BY id_produk DESC";
$result = mysqli_query($conn, $sql);
?>
<div class="mb-12 flex justify-between items-end relative z-10">
    <div>
        <h2 class="font-serif text-4xl tracking-tight mb-2">Produk</h2>
        <p class="text-sm text-luxury-mutedFg">Kelola data menu produk kantin.</p>
    </div>
    <a href="produktambah.php" class="h-10 px-6 border border-[#1A1A1A] bg-[#1A1A1A] text-white text-[10px] tracking-[0.2em] uppercase flex items-center hover:bg-transparent hover:text-[#1A1A1A] transition-all duration-300">
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
                <th class="py-4 px-4 font-normal">Nama Produk</th>
                <th class="py-4 px-4 font-normal">Kategori</th>
                <th class="py-4 px-4 font-normal">Harga Jual</th>
                <th class="py-4 px-4 font-normal">Harga Pokok</th>
                <th class="py-4 px-4 font-normal text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr class="border-b border-[#1A1A1A]/10 hover:bg-[#F9F8F6] transition-colors duration-300">
                    <td class="py-4 px-4 font-mono text-xs text-luxury-mutedFg"><?= htmlspecialchars($row['id_produk']) ?></td>
                    <td class="py-4 px-4 font-serif"><?= htmlspecialchars($row['nama_produk']) ?></td>
                    <td class="py-4 px-4 font-mono text-xs uppercase text-luxury-mutedFg"><?= htmlspecialchars($row['kategori'] ?? '-') ?></td>
                    <td class="py-4 px-4 font-mono text-sm text-luxury-mutedFg">Rp <?= number_format($row['harga_jual'], 0, ',', '.') ?></td>
                    <td class="py-4 px-4 font-mono text-sm text-luxury-mutedFg">Rp <?= number_format($row['harga_pokok'], 0, ',', '.') ?></td>
                    <td class="py-4 px-4 text-right space-x-4">
                        <a href="../bom/bomdetail.php?id=<?= $row['id_produk'] ?>&export=1" class="text-[10px] uppercase tracking-widest text-luxury-mutedFg hover:text-luxury-accent transition-colors">Export PDF</a>
                        <a href="../bom/bomdetail.php?id=<?= $row['id_produk'] ?>" class="text-[10px] uppercase tracking-widest text-luxury-mutedFg hover:text-luxury-accent transition-colors">Detail</a>
                        <a href="produkubah.php?id=<?= $row['id_produk'] ?>" class="text-[10px] uppercase tracking-widest text-luxury-mutedFg hover:text-luxury-accent transition-colors">Ubah</a>
                        <a href="produkhapus.php?id=<?= $row['id_produk'] ?>" class="text-[10px] uppercase tracking-widest text-luxury-mutedFg hover:text-red-700 transition-colors">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if (mysqli_num_rows($result) === 0): ?>
                <tr>
                    <td colspan="4" class="py-8 text-center text-luxury-mutedFg text-sm">Belum ada data produk.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
include '../../layouts/footer.php';
include '../../layouts/vendor-scripts.php';
?>