<?php
include '../../layouts/session.php';
include '../../layouts/config.php';
include '../../layouts/guard.php';
include '../../layouts/csrf.php';

// Otorisasi: Hanya Owner dan Admin yang dapat mengedit pegawai
require_role(['Owner', 'Admin'], 'pegawailihat.php');

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: pegawailihat.php');
    exit;
}

$sql = "SELECT * FROM pegawai WHERE id_pegawai = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    $_SESSION['flash_error'] = 'Data tidak ditemukan.';
    header('Location: pegawailihat.php');
    exit;
}

include '../../layouts/head.php';
include '../../layouts/menu.php';
?>
<div class="mb-12 relative z-10">
    <h2 class="font-serif text-4xl tracking-tight mb-2">Ubah Pegawai</h2>
    <a href="pegawailihat.php"
        class="text-xs tracking-[0.2em] uppercase text-luxury-mutedFg hover:text-luxury-accent transition-colors">←
        Kembali</a>
</div>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="mb-8 p-4 border border-red-900/20 text-red-900 text-sm bg-red-50/50 relative z-10">
        <?= render_flash_error() ?>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="border-t border-luxury-fg/20 pt-8 relative z-10 max-w-2xl bg-luxury-bg/50 backdrop-blur-sm p-8">
    <form action="controllers/update.php" method="POST" class="space-y-8">
        <?= csrf_field() ?>
        <input type="hidden" name="id_pegawai" value="<?= htmlspecialchars($data['id_pegawai']) ?>">

        <div class="relative group">
            <input type="text" name="nama_pegawai" id="nama_pegawai" required
                value="<?= htmlspecialchars($data['nama_pegawai']) ?>"
                class="w-full h-12 bg-transparent border-b border-luxury-fg/50 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-500 peer placeholder-transparent"
                placeholder="Nama Pegawai">
            <label for="nama_pegawai"
                class="absolute left-0 top-3 text-luxury-mutedFg font-serif italic transition-all duration-500 peer-focus:-top-4 peer-focus:text-xs peer-focus:text-luxury-accent peer-valid:-top-4 peer-valid:text-xs peer-valid:text-luxury-mutedFg">Nama
                Pegawai</label>
        </div>

        <div class="relative group">
            <input type="password" name="password" id="password" minlength="6"
                class="w-full h-12 bg-transparent border-b border-luxury-fg/50 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-500 peer placeholder-transparent"
                placeholder="Password Baru">
            <label for="password"
                class="absolute left-0 top-3 text-luxury-mutedFg font-serif italic transition-all duration-500 peer-focus:-top-4 peer-focus:text-xs peer-focus:text-luxury-accent peer-valid:-top-4 peer-valid:text-xs peer-valid:text-luxury-mutedFg">Password Baru (Kosongkan jika tidak diubah)</label>
        </div>

        <div>
            <label for="jabatan" class="block text-xs text-luxury-mutedFg font-serif italic mb-2">Jabatan</label>
            <select name="jabatan" id="jabatan" required
                class="w-full h-12 bg-transparent border-b border-luxury-fg/50 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-500 cursor-pointer">
                <option value="Pegawai Kantin" <?= $data['jabatan'] == 'Pegawai Kantin' ? 'selected' : '' ?>>Pegawai Kantin</option>
                <option value="Pegawai Catering" <?= $data['jabatan'] == 'Pegawai Catering' ? 'selected' : '' ?>>Pegawai Catering</option>
                <option value="Pegawai Dapur" <?= $data['jabatan'] == 'Pegawai Dapur' ? 'selected' : '' ?>>Pegawai Dapur</option>
                <option value="Admin" <?= $data['jabatan'] == 'Admin' ? 'selected' : '' ?>>Admin</option>
                <option value="Owner" <?= $data['jabatan'] == 'Owner' ? 'selected' : '' ?>>Owner</option>
            </select>
        </div>

        <div class="pt-8">
            <button type="submit"
                class="h-12 px-8 border border-[#1A1A1A] bg-[#1A1A1A] text-white text-[10px] tracking-[0.2em] uppercase relative overflow-hidden group hover:bg-transparent hover:text-[#1A1A1A] transition-all duration-500 w-full md:w-auto">
                <span class="relative z-10 transition-colors duration-500">Perbarui Data</span>
            </button>
        </div>
    </form>
</div>

<?php
include '../../layouts/footer.php';
include '../../layouts/vendor-scripts.php';
?>
