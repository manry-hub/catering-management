<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Mendapatkan atau menghasilkan token CSRF aktif untuk sesi saat ini.
 */
function get_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Menghasilkan elemen input tersembunyi untuk formulir HTML.
 */
function csrf_field(): string
{
    $token = htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Memvalidasi token CSRF dari permintaan POST.
 * Jika tidak valid, menghentikan eksekusi atau mengarahkan kembali dengan pesan error.
 */
function verify_csrf_token(?string $redirect_url = null): void
{
    $token = $_POST['csrf_token'] ?? '';
    $valid_token = $_SESSION['csrf_token'] ?? '';

    if (empty($token) || empty($valid_token) || !hash_equals($valid_token, $token)) {
        $_SESSION['flash_error'] = 'Validasi keamanan (CSRF Token) gagal atau sesi Anda telah kedaluwarsa. Silakan coba lagi.';
        if ($redirect_url) {
            header('Location: ' . $redirect_url);
            exit;
        }
        http_response_code(403);
        die('Akses ditolak: Validasi token keamanan (CSRF) tidak valid.');
    }
}
