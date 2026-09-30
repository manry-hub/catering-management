<?php
include '../../layouts/session.php';
include '../../layouts/config.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: pesananlihat.php');
    exit;
}

$sql = "SELECT p.*, cat.tanggal_acara, cat.lokasi_acara 
        FROM pesanan p
        LEFT JOIN catering cat ON p.id_catering = cat.id_catering
        WHERE p.id_pesanan = ?";
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

$pelanggan_result = mysqli_query($conn, "SELECT * FROM pelanggan ORDER BY nama_pelanggan ASC");
$cabang_result = mysqli_query($conn, "SELECT * FROM cabang_kantin ORDER BY nama_cabang ASC");

include '../../layouts/head.php';
include '../../layouts/menu.php';
?>
<div class="mb-12 relative z-10">
    <h2 class="font-serif text-4xl tracking-tight mb-2">Ubah Data Pesanan</h2>
    <a href="pesananlihat.php" class="text-xs tracking-[0.2em] uppercase text-luxury-mutedFg hover:text-luxury-accent transition-colors">← Kembali</a>
</div>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="mb-8 p-4 rounded-lg border border-red-200 text-red-800 text-sm bg-red-50/80 backdrop-blur-sm relative z-10">
        <?= $_SESSION['flash_error'] ?>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="relative z-10 glass-panel p-8 max-w-4xl">
    <div class="mb-8 pb-6 border-b border-luxury-fg/10 text-sm">
        <p class="text-luxury-mutedFg">Pesanan <strong class="text-luxury-fg text-lg">#<?= htmlspecialchars($data['id_pesanan']) ?></strong></p>
        <p class="text-luxury-mutedFg">Total Harga: <strong class="text-luxury-accent">Rp <?= number_format($data['total_harga'], 0, ',', '.') ?></strong></p>
    </div>
    
    <form action="controllers/update.php" method="POST" class="space-y-8">
        <input type="hidden" name="id_pesanan" value="<?= htmlspecialchars($data['id_pesanan']) ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <label for="status_pesanan" class="block text-xs text-luxury-mutedFg font-serif italic mb-2">Status Pesanan</label>
                <select name="status_pesanan" id="status_pesanan" required class="w-full glass-input text-sm cursor-pointer font-medium
                    <?php if($data['status_pesanan'] === 'Diproses') echo 'text-blue-600'; elseif($data['status_pesanan'] === 'Selesai') echo 'text-green-600'; else echo 'text-red-600'; ?>">
                    <option value="Diproses" <?= $data['status_pesanan'] === 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                    <option value="Selesai" <?= $data['status_pesanan'] === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="Dibatalkan" <?= $data['status_pesanan'] === 'Dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                </select>
            </div>
            <div>
                <label for="id_pelanggan" class="block text-xs text-luxury-mutedFg font-serif italic mb-2">Pelanggan</label>
                <select name="id_pelanggan" id="id_pelanggan" required class="w-full glass-input text-sm cursor-pointer">
                    <?php while ($pel = mysqli_fetch_assoc($pelanggan_result)): ?>
                        <option value="<?= $pel['id_pelanggan'] ?>" <?= $data['id_pelanggan'] == $pel['id_pelanggan'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pel['nama_pelanggan']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <!-- Read Only Jenis Pesanan -->
            <div>
                <label class="block text-xs text-luxury-mutedFg font-serif italic mb-2">Jenis Pesanan</label>
                <input type="text" value="<?= htmlspecialchars($data['jenis_pesanan']) ?>" readonly class="w-full glass-input text-sm bg-black/5 text-luxury-mutedFg cursor-not-allowed">
            </div>

            <?php if ($data['jenis_pesanan'] === 'Kantin'): ?>
            <div>
                <label for="id_cabang_kantin" class="block text-xs text-luxury-mutedFg font-serif italic mb-2">Cabang Kantin</label>
                <select name="id_cabang_kantin" id="id_cabang_kantin" required class="w-full glass-input text-sm cursor-pointer">
                    <?php while ($cab = mysqli_fetch_assoc($cabang_result)): ?>
                        <option value="<?= $cab['id_cabang_kantin'] ?>" <?= $data['id_cabang_kantin'] == $cab['id_cabang_kantin'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cab['nama_cabang']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($data['jenis_pesanan'] === 'Catering'): ?>
        <div class="space-y-6 pt-6 border-t border-luxury-fg/10">
            <h4 class="font-serif text-base text-luxury-fg mb-2">Detail Catering</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label for="tanggal_acara" class="block text-xs text-luxury-mutedFg font-serif italic mb-2">Tanggal Acara</label>
                    <input type="date" name="tanggal_acara" id="tanggal_acara" value="<?= htmlspecialchars($data['tanggal_acara'] ?? '') ?>" required class="w-full glass-input text-sm">
                </div>
                <div>
                    <label for="lokasi_acara" class="block text-xs text-luxury-mutedFg font-serif italic mb-2">Lokasi Acara</label>
                    <textarea name="lokasi_acara" id="lokasi_acara" rows="2" required class="w-full glass-input text-sm resize-none"><?= htmlspecialchars($data['lokasi_acara'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div>
            <label for="catatan" class="block text-xs text-luxury-mutedFg font-serif italic mb-2">Catatan (opsional)</label>
            <textarea name="catatan" id="catatan" rows="3" class="w-full glass-input text-sm resize-none"><?= htmlspecialchars($data['catatan'] ?? '') ?></textarea>
        </div>

        <div class="pt-4">
            <button type="submit" class="h-12 px-8 border border-[#1A1A1A] bg-[#1A1A1A] text-white text-[10px] tracking-[0.2em] uppercase relative overflow-hidden group hover:bg-transparent hover:text-[#1A1A1A] transition-all duration-500">
                <span class="relative z-10 transition-colors duration-500">Simpan Perubahan</span>
            </button>
        </div>
    </form>
</div>

<?php
include '../../layouts/footer.php';
include '../../layouts/vendor-scripts.php';
?>
