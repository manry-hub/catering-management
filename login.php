<?php
session_start();
if (isset($_SESSION['id_pegawai'])) {
    header('Location: /gorjes/dashboard/index.php');
    exit;
}

include 'layouts/config.php';
include 'layouts/csrf.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token('/gorjes/login.php');

    $nama_pegawai = trim($_POST['nama_pegawai'] ?? '');
    $password     = $_POST['password'] ?? '';

    $sql    = "SELECT * FROM pegawai WHERE nama_pegawai = ? LIMIT 1";
    $stmt   = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $nama_pegawai);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user   = mysqli_fetch_assoc($result);

    $is_authenticated = false;

    if ($user) {
        // Cek dengan password_verify (Bcrypt)
        if (password_verify($password, $user['password'])) {
            $is_authenticated = true;
            // Rehash jika konfigurasi algoritma Bcrypt diperbarui
            if (password_needs_rehash($user['password'], PASSWORD_BCRYPT)) {
                $new_hash = password_hash($password, PASSWORD_BCRYPT);
                $upd = mysqli_prepare($conn, "UPDATE pegawai SET password = ? WHERE id_pegawai = ?");
                mysqli_stmt_bind_param($upd, 'si', $new_hash, $user['id_pegawai']);
                mysqli_stmt_execute($upd);
            }
        } elseif ($password === $user['password']) {
            // Auto-upgrade transparan dari plaintext lama ke hash Bcrypt
            $is_authenticated = true;
            $new_hash = password_hash($password, PASSWORD_BCRYPT);
            $upd = mysqli_prepare($conn, "UPDATE pegawai SET password = ? WHERE id_pegawai = ?");
            mysqli_stmt_bind_param($upd, 'si', $new_hash, $user['id_pegawai']);
            mysqli_stmt_execute($upd);
        }
    }

    if ($is_authenticated) {
        // Regenerasi session ID untuk mencegah serangan session fixation
        session_regenerate_id(true);
        $_SESSION['id_pegawai']   = $user['id_pegawai'];
        $_SESSION['nama_pegawai'] = $user['nama_pegawai'];
        $_SESSION['jabatan']      = $user['jabatan'];

        $destination = match ($user['jabatan'] ?? '') {
            'Pegawai Kantin', 'Pegawai Catering' => '/gorjes/dashboard/pesanan/pesananlihat.php',
            'Pegawai Dapur' => '/gorjes/dashboard/bahan_baku/bahan_bakulihat.php',
            default => '/gorjes/dashboard/index.php',
        };

        header('Location: ' . $destination);
        exit;
    } else {
        $error = 'Nama atau password salah.';
    }
}
?>
<?php include 'layouts/head.php'; ?>
<div class="min-h-screen w-full flex items-center justify-center p-6 relative z-10">
    <div class="w-full max-w-md relative z-20 glass-panel p-10 md:p-12 border-t-2 border-t-luxury-accent bg-luxury-bg/90 backdrop-blur-sm shadow-hero">
        <div class="text-center mb-10">
            <h1 class="font-serif text-4xl md:text-5xl tracking-tight mb-2 text-luxury-fg">Gorjes</h1>
            <p class="text-[10px] tracking-[0.3em] uppercase text-luxury-mutedFg font-medium">Portal Admin</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-8 p-4 border border-red-900/20 text-red-900 text-sm text-center bg-red-50/70">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-8">
            <?= csrf_field() ?>
            <div class="relative group">
                <input type="text" name="nama_pegawai" id="nama_pegawai" required 
                    class="w-full h-12 bg-transparent border-b border-luxury-fg/30 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-300 peer placeholder-transparent" placeholder="Nama Pegawai">
                <label for="nama_pegawai" class="absolute left-0 top-3 text-luxury-mutedFg font-serif italic text-sm transition-all duration-300 peer-focus:-top-4 peer-focus:text-xs peer-focus:text-luxury-accent peer-valid:-top-4 peer-valid:text-xs peer-valid:text-luxury-mutedFg">Nama Pegawai</label>
            </div>

            <div class="relative group">
                <input type="password" name="password" id="password" required 
                    class="w-full h-12 bg-transparent border-b border-luxury-fg/30 text-sm px-0 py-2 focus:outline-none focus:border-luxury-accent transition-colors duration-300 peer placeholder-transparent" placeholder="Password">
                <label for="password" class="absolute left-0 top-3 text-luxury-mutedFg font-serif italic text-sm transition-all duration-300 peer-focus:-top-4 peer-focus:text-xs peer-focus:text-luxury-accent peer-valid:-top-4 peer-valid:text-xs peer-valid:text-luxury-mutedFg">Password</label>
            </div>

            <button type="submit" class="w-full h-12 bg-luxury-fg text-luxury-white text-xs tracking-[0.2em] uppercase font-medium relative overflow-hidden group shadow-btn hover:shadow-btn-hover mt-8 block transition-all duration-500">
                <span class="absolute inset-0 bg-luxury-accent -translate-x-full group-hover:translate-x-0 transition-transform duration-500" style="transition-timing-function: cubic-bezier(0.25, 0.46, 0.45, 0.94);"></span>
                <span class="relative z-10 text-white font-semibold">Sign In</span>
            </button>
        </form>
    </div>
</div>
<?php include 'layouts/vendor-scripts.php'; ?>
