<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Menegakkan kontrol akses berbasis peran (RBAC).
 * Jika pengguna tidak memiliki peran yang diizinkan, akses ditolak.
 *
 * @param array $allowed_roles Daftar peran yang diizinkan (misal: ['Owner', 'Admin'])
 * @param string|null $redirect_url URL pengalihan jika otorisasi gagal
 */
function require_role(array $allowed_roles, ?string $redirect_url = '/gorjes/dashboard/index.php'): void
{
    if (!isset($_SESSION['id_pegawai'])) {
        header('Location: /gorjes/login.php');
        exit;
    }

    $current_role = $_SESSION['jabatan'] ?? '';

    if (!in_array($current_role, $allowed_roles, true)) {
        http_response_code(403);
        $_SESSION['flash_error'] = 'Akses ditolak: Jabatan (' . htmlspecialchars($current_role ?: 'Tidak Dikenal', ENT_QUOTES, 'UTF-8') . ') tidak berwenang mengakses halaman ini.';
        if ($redirect_url) {
            header('Location: ' . $redirect_url);
            exit;
        }
        die('Akses ditolak: Anda tidak memiliki wewenang untuk tindakan ini.');
    }
}
