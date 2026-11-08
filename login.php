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
    <!-- Decorator circles for glass effect background -->
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-luxury-accent/10 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob"></div>
    <div class="absolute top-1/3 right-1/4 w-96 h-96 bg-amber-100/40 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob" style="animation-delay: 2s;"></div>

    <div class="w-full max-w-md relative z-20 glass-panel p-12 shadow-hero">
        <div class="text-center mb-12">
            <h1 class="font-serif text-5xl tracking-tight mb-3 bg-clip-text text-transparent bg-gradient-to-r from-luxury-accent to-yellow-600">Gorjes</h1>
            <p class="text-[10px] tracking-[0.3em] uppercase text-luxury-mutedFg font-medium">Portal Admin</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-8 p-4 rounded-lg border border-red-200 text-red-700 text-sm text-center bg-red-50/80 backdrop-blur-sm">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-6">
            <?= csrf_field() ?>
            <div class="relative group">
                <input type="text" name="nama_pegawai" id="nama_pegawai" required 
                    class="w-full glass-input text-sm peer placeholder-transparent" placeholder="Nama Pegawai">
                <label for="nama_pegawai" class="absolute left-4 top-3 text-luxury-mutedFg text-sm transition-all duration-300 peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-luxury-accent peer-valid:-top-2.5 peer-valid:text-xs peer-valid:text-luxury-mutedFg bg-white/60 backdrop-blur-md px-1 rounded">Nama Pegawai</label>
            </div>

            <div class="relative group mt-8">
                <input type="password" name="password" id="password" required 
                    class="w-full glass-input text-sm peer placeholder-transparent" placeholder="Password">
                <label for="password" class="absolute left-4 top-3 text-luxury-mutedFg text-sm transition-all duration-300 peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-luxury-accent peer-valid:-top-2.5 peer-valid:text-xs peer-valid:text-luxury-mutedFg bg-white/60 backdrop-blur-md px-1 rounded">Password</label>
            </div>

            <button type="submit" class="w-full h-12 bg-luxury-fg text-luxury-white text-xs tracking-[0.2em] uppercase font-medium relative overflow-hidden group shadow-btn hover:shadow-btn-hover mt-10 block rounded-xl">
                <span class="absolute inset-0 bg-gradient-to-r from-luxury-accent to-yellow-600 -translate-x-full group-hover:translate-x-0 transition-transform duration-500" style="transition-timing-function: cubic-bezier(0.25, 0.46, 0.45, 0.94);"></span>
                <span class="relative z-10 text-white font-semibold">Sign In</span>
            </button>
        </form>
    </div>
</div>
<?php include 'layouts/vendor-scripts.php'; ?>
