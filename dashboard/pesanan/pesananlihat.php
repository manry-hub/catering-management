<?php
include '../../layouts/session.php';
include '../../layouts/config.php';
include '../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Kantin', 'Pegawai Catering'], '../index.php');
include '../../layouts/head.php';
include '../../layouts/menu.php';

/*
    Atribut entitas pesanan:
    - id_pesanan       : int auto_increment
    - tanggal_pesanan  : datetime (dibuat otomatis saat insert)
    - status_pesanan   : varchar(50) (Diproses/Selesai/Dibatalkan)
    - total_harga      : decimal(12,2) (default 0.00)
    - jenis_pesanan    : enum('Kantin','Catering')
    - id_pelanggan     : int (relasi ke tabel pelanggan)
    - id_pegawai       : int (relasi ke tabel pegawai)
    - id_cabang_kantin : int|null (hanya untuk jenis "Kantin")
    - id_catering      : int|null (hanya untuk jenis "Catering" disimpan di tabel catering)
    - catatan          : text|null
    Catatan: tanggal_acara dan lokasi_acara tidak berada langsung di tabel pesanan,
    melainkan di tabel catering dan terhubung melalui id_catering.
*/

$sql = "SELECT p.*, pel.nama_pelanggan, ck.nama_cabang, peg.nama_pegawai, cat.lokasi_acara
        FROM pesanan p 
        LEFT JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan 
        LEFT JOIN cabang_kantin ck ON p.id_cabang_kantin = ck.id_cabang_kantin 
        LEFT JOIN pegawai peg ON p.id_pegawai = peg.id_pegawai 
        LEFT JOIN catering cat ON p.id_catering = cat.id_catering
        ORDER BY p.id_pesanan DESC";
$result = mysqli_query($conn, $sql);
?>
<div class="mb-12 flex justify-between items-end relative z-10">
    <div>
        <h2 class="font-serif text-4xl tracking-tight mb-2">Pesanan</h2>
        <p class="text-sm text-luxury-mutedFg">Kelola transaksi pesanan pelanggan.</p>
    </div>
    <a href="pesanantambah.php"
        class="h-10 px-6 border border-[#1A1A1A] bg-[#1A1A1A] text-white text-xs tracking-[0.2em] uppercase font-medium flex items-center hover:bg-transparent hover:text-[#1A1A1A] transition-all duration-500">
        Buat Pesanan
    </a>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div
        class="mb-8 p-4 rounded-lg border border-green-200 text-green-800 text-sm bg-green-50/80 backdrop-blur-sm relative z-10">
        <?= htmlspecialchars($_SESSION['flash_success']) ?>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="mb-8 p-4 rounded-lg border border-red-200 text-red-800 text-sm bg-red-50/80 backdrop-blur-sm relative z-10">
        <?= render_flash_error() ?>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="overflow-x-auto relative z-10">
    <table class="w-full text-left text-sm whitespace-nowrap">
        <thead>
            <tr class="border-b border-luxury-fg/10 uppercase text-[10px] tracking-[0.2em] text-luxury-mutedFg">
                <th class="py-4 px-4 font-normal">ID</th>
                <th class="py-4 px-4 font-normal">Pelanggan</th>
                <th class="py-4 px-4 font-normal">Jenis</th>
                <th class="py-4 px-4 font-normal">Cabang / Lokasi</th>
                <th class="py-4 px-4 font-normal">Nama Pegawai</th>
                <th class="py-4 px-4 font-normal">Tanggal</th>
                <th class="py-4 px-4 font-normal text-right">Total</th>
                <th class="py-4 px-4 font-normal">Status</th>
                <th class="py-4 px-4 font-normal text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <?php
                if ($row['status_pesanan'] === 'Selesai') {
                    $statusBadge = 'text-emerald-800 bg-emerald-50 border border-emerald-200/80';
                } elseif ($row['status_pesanan'] === 'Dibatalkan') {
                    $statusBadge = 'text-rose-800 bg-rose-50 border border-rose-200/80 line-through';
                } else {
                    $statusBadge = 'text-amber-900 bg-amber-50 border border-amber-200/80';
                }

                // Jenis badge
                if ($row['jenis_pesanan'] === 'Catering') {
                    $jenisBadge = 'text-purple-900 bg-purple-50 border border-purple-200/80';
                } else {
                    $jenisBadge = 'text-stone-800 bg-stone-100 border border-stone-200/80';
                }

                // Cabang / Lokasi display
                if ($row['jenis_pesanan'] === 'Kantin') {
                    $lokasi = $row['nama_cabang'] ?? '-';
                } else {
                    $lokasi = $row['lokasi_acara'] ?? '-';
                }
                ?>
                <tr class="border-b border-[#1A1A1A]/10 hover:bg-[#F9F8F6] transition-colors duration-300">
                    <td class="py-4 px-4 font-mono text-xs text-luxury-mutedFg">#<?= htmlspecialchars($row['id_pesanan']) ?>
                    </td>
                    <td class="py-4 px-4 font-serif"><?= htmlspecialchars($row['nama_pelanggan'] ?? '-') ?></td>
                    <td class="py-4 px-4">
                        <span class="inline-block px-2 py-0.5 text-[9px] tracking-[0.15em] uppercase font-medium <?= $jenisBadge ?>">
                            <?= htmlspecialchars($row['jenis_pesanan']) ?>
                        </span>
                    </td>
                    <td class="py-4 px-4 text-sm max-w-[160px] truncate"><?= htmlspecialchars($lokasi) ?></td>
                    <td class="py-4 px-4 font-serif text-sm"><?= htmlspecialchars($row['nama_pegawai'] ?? '-') ?></td>
                    <td class="py-4 px-4 text-luxury-mutedFg text-sm">
                        <?= date('d M Y, H:i', strtotime($row['tanggal_pesanan'])) ?>
                    </td>
                    <td class="py-4 px-4 font-mono text-sm font-medium text-right tabular-nums">Rp
                        <?= number_format($row['total_harga'], 0, ',', '.') ?>
                    </td>
                    <td class="py-4 px-4">
                        <span class="inline-block px-2.5 py-1 text-[9px] tracking-[0.15em] uppercase font-semibold <?= $statusBadge ?>">
                            <?= htmlspecialchars($row['status_pesanan']) ?>
                        </span>
                    </td>
                    <td class="py-4 px-4 text-right space-x-4">
                        <a href="../detail_pesanan/detail_pesananlihat.php?id=<?= $row['id_pesanan'] ?>&export=1"
                            class="text-[10px] uppercase tracking-widest text-luxury-mutedFg hover:text-luxury-accent transition-colors">Export PDF</a>
                        <a href="../detail_pesanan/detail_pesananlihat.php?id=<?= $row['id_pesanan'] ?>"
                            class="text-[10px] uppercase tracking-widest text-luxury-mutedFg hover:text-luxury-accent transition-colors">Detail</a>
                        <a href="pesananubah.php?id=<?= $row['id_pesanan'] ?>"
                            class="text-[10px] uppercase tracking-widest text-luxury-mutedFg hover:text-luxury-accent transition-colors">Ubah</a>
                        <a href="pesananhapus.php?id=<?= $row['id_pesanan'] ?>"
                            class="text-[10px] uppercase tracking-widest text-luxury-mutedFg hover:text-red-700 transition-colors">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if (mysqli_num_rows($result) === 0): ?>
                <tr>
                    <td colspan="9" class="py-8 text-center text-luxury-mutedFg text-sm">Belum ada data pesanan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
include '../../layouts/footer.php';
include '../../layouts/vendor-scripts.php';
?>