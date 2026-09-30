<?php
include '../../layouts/session.php';
include '../../layouts/config.php';
include '../../layouts/head.php';
include '../../layouts/menu.php';

$pelanggan_result = mysqli_query($conn, "SELECT * FROM pelanggan ORDER BY nama_pelanggan ASC");
$cabang_result = mysqli_query($conn, "SELECT * FROM cabang_kantin ORDER BY nama_cabang ASC");

// Deteksi jabatan untuk auto-select jenis pesanan
$jabatan = $_SESSION['jabatan'] ?? '';
$forced_jenis = null;
if ($jabatan === 'Pegawai Kantin') {
    $forced_jenis = 'Kantin';
} elseif ($jabatan === 'Pegawai Catering') {
    $forced_jenis = 'Catering';
}

/*
    Atribut entitas pesanan:
    - id_pesanan       : int auto_increment
    - tanggal_pesanan  : datetime (dibuat otomatis saat insert)
    - status_pesanan   : varchar(50) (default "Diproses")
    - total_harga      : decimal(12,2) (default 0.00)
    - jenis_pesanan    : enum('Kantin','Catering')
    - id_pelanggan     : int (dari tabel pelanggan)
    - id_pegawai       : int (dari session pegawai)
    - id_cabang_kantin : int|null (hanya untuk jenis "Kantin")
    - id_catering      : int|null (hanya untuk jenis "Catering" disimpan di tabel catering)
    - catatan          : text|null
    Catatan: tanggal_acara dan lokasi_acara bukan kolom langsung di tabel pesanan,
    melainkan disimpan di tabel catering dan dihubungkan melalui id_catering.
*/
?>
<div class="mb-12 relative z-10">
    <h2 class="font-serif text-4xl tracking-tight mb-2">Buat Pesanan Baru</h2>
    <a href="pesananlihat.php"
        class="text-xs tracking-[0.2em] uppercase text-luxury-mutedFg hover:text-luxury-accent transition-colors">←
        Kembali</a>
</div>
<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="mb-8 p-4 rounded-lg border border-red-200 text-red-800 text-sm bg-red-50/80 relative z-10">
        <?= $_SESSION['flash_error'] ?>
    </div>
    <?php unset($_SESSION['flash_error']); endif; ?>

<div class="relative z-10 glass-panel p-8 max-w-4xl">
    <form action="controllers/create.php" method="POST" class="space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <label for="id_pelanggan"
                    class="block text-xs text-luxury-mutedFg font-serif italic mb-2">Pelanggan</label>
                <select name="id_pelanggan" id="id_pelanggan" required
                    class="w-full glass-input text-sm cursor-pointer">
                    <option value="">-- Pilih Pelanggan --</option>
                    <?php while ($pel = mysqli_fetch_assoc($pelanggan_result)): ?>
                        <option value="<?= $pel['id_pelanggan'] ?>"><?= htmlspecialchars($pel['nama_pelanggan']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label for="jenis_pesanan" class="block text-xs text-luxury-mutedFg font-serif italic mb-2">Jenis
                    Pesanan</label>
                <?php if ($forced_jenis): ?>
                    <input type="hidden" name="jenis_pesanan" value="<?= $forced_jenis ?>">
                    <input type="text" value="<?= $forced_jenis ?>" readonly
                        class="w-full glass-input text-sm bg-black/5 text-luxury-mutedFg cursor-not-allowed">
                    <p class="text-[10px] text-luxury-mutedFg mt-1 italic">Otomatis berdasarkan jabatan Anda
                        (<?= htmlspecialchars($jabatan) ?>)</p>
                <?php else: ?>
                    <select name="jenis_pesanan" id="jenis_pesanan" required
                        class="w-full glass-input text-sm cursor-pointer">
                        <option value="">-- Pilih Jenis --</option>
                        <option value="Kantin">Kantin</option>
                        <option value="Catering">Catering</option>
                    </select>
                <?php endif; ?>
            </div>
        </div>

        <!-- Conditional: Cabang Kantin (for Kantin ) -->
        <div id="cabang-section" class="hidden">
            <label for="id_cabang_kantin" class="block text-xs text-luxury-mutedFg font-serif italic mb-2">Cabang
                Kantin</label>
            <select name="id_cabang_kantin" id="id_cabang_kantin" class="w-full glass-input text-sm cursor-pointer">
                <option value="">-- Pilih Cabang --</option>
                <?php while ($cab = mysqli_fetch_assoc($cabang_result)): ?>
                    <option value="<?= $cab['id_cabang_kantin'] ?>"><?= htmlspecialchars($cab['nama_cabang']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Conditional: Catering fields (inline, for Catering) -->
        <div id="catering-section" class="hidden space-y-6 pt-6 border-t border-luxury-fg/10">
            <h4 class="font-serif text-base text-luxury-fg mb-2">Detail Catering</h4>
            <div>
                <label for="tanggal_acara" class="block text-xs text-luxury-mutedFg font-serif italic mb-2">Tanggal
                    Acara</label>
                <input type="date" name="tanggal_acara" id="tanggal_acara" class="w-full glass-input text-sm">
            </div>
            <div>
                <label for="lokasi_acara" class="block text-xs text-luxury-mutedFg font-serif italic mb-2">Lokasi
                    Acara</label>
                <textarea name="lokasi_acara" id="lokasi_acara" rows="2" class="w-full glass-input text-sm resize-none"
                    placeholder="Alamat lengkap lokasi acara"></textarea>
            </div>
        </div>

        <!-- General: Catatan (for all orders) -->
        <div>
            <label for="catatan" class="block text-xs text-luxury-mutedFg font-serif italic mb-2">Catatan
                (opsional)</label>
            <textarea name="catatan" id="catatan" rows="2" class="w-full glass-input text-sm resize-none"
                placeholder="Catatan khusus, permintaan menu, preferensi, dll."></textarea>
        </div>


        <div class="pt-4 border-t border-luxury-fg/10">
            <button type="submit"
                class="h-12 px-8 border border-[#1A1A1A] bg-[#1A1A1A] text-white text-[10px] tracking-[0.2em] uppercase relative overflow-hidden group hover:bg-transparent hover:text-[#1A1A1A] transition-all duration-500">
                <span class="relative z-10 transition-colors duration-500">Simpan Pesanan</span>
            </button>
        </div>
    </form>
</div>

<script>
    const forcedJenis = <?= json_encode($forced_jenis) ?>;

    const jenisSelect = document.getElementById('jenis_pesanan');
    const cabangSection = document.getElementById('cabang-section');
    const cateringSection = document.getElementById('catering-section');

    function applyJenis(value) {
        cabangSection.classList.add('hidden');
        cateringSection.classList.add('hidden');

        document.getElementById('id_cabang_kantin').removeAttribute('required');
        document.getElementById('tanggal_acara').removeAttribute('required');
        document.getElementById('lokasi_acara').removeAttribute('required');

        if (value === 'Kantin') {
            cabangSection.classList.remove('hidden');
            document.getElementById('id_cabang_kantin').setAttribute('required', 'required');
        } else if (value === 'Catering') {
            cateringSection.classList.remove('hidden');
            document.getElementById('tanggal_acara').setAttribute('required', 'required');
            document.getElementById('lokasi_acara').setAttribute('required', 'required');
        }
    }

    // If jabatan forces a jenis, apply immediately on page load
    if (forcedJenis) {
        applyJenis(forcedJenis);
    }

    // For  who can choose
    if (jenisSelect) {
        jenisSelect.addEventListener('change', function () {
            applyJenis(this.value);
        });
    }
</script>
<?php include '../../layouts/footer.php';
include '../../layouts/vendor-scripts.php'; ?>