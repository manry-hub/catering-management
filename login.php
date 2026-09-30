<?php
session_start();
if (isset($_SESSION['id_pegawai'])) {
    header('Location: /gorjes/dashboard/index.php');
    exit;
}

include 'layouts/config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pegawai = mysqli_real_escape_string($conn, $_POST['nama_pegawai']);
    $password = $_POST['password'];

    $sql    = "SELECT * FROM pegawai WHERE nama_pegawai = ? LIMIT 1";
    $stmt   = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $nama_pegawai);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user   = mysqli_fetch_assoc($result);

    if ($user && $password === $user['password']) {
        $_SESSION['id_pegawai']   = $user['id_pegawai'];
        $_SESSION['nama_pegawai'] = $user['nama_pegawai'];
        $_SESSION['jabatan']      = $user['jabatan'];
        header('Location: /gorjes/dashboard/index.php');
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
