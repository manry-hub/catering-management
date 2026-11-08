<?php
$host     = getenv('DB_HOST') ?: 'localhost';
$user     = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'Sorry.240106';
$database = getenv('DB_NAME') ?: 'sistem_pemesanan_kantin_gorjes_db';
$port     = getenv('DB_PORT') ? intval(getenv('DB_PORT')) : null;
$socket   = getenv('DB_SOCKET') ?: null;

$conn = mysqli_connect($host, $user, $password, $database, $port, $socket);

if (!$conn) {
    die('Koneksi gagal: ' . mysqli_connect_error());
}

// Set charset ke utf8mb4 untuk keamanan encoding
mysqli_set_charset($conn, 'utf8mb4');

/**
 * Handle database error and translate into user-friendly message
 */
function handle_db_error($conn, $default_msg = 'Terjadi kesalahan pada database.')
{
    $errno = mysqli_errno($conn);
    $error = mysqli_error($conn);

    if ($errno == 1451) {
        return "Data tidak bisa dihapus karena masih terkait dengan data lain (misal: sudah ada di transaksi).";
    } elseif ($errno == 1062) {
        return "Data yang Anda masukkan sudah ada (duplikat).";
    } elseif ($errno == 1452) {
        return "Gagal menyimpan karena referensi data (ID) tidak ditemukan.";
    }

    return $default_msg . " (Error: " . htmlspecialchars($error) . ")";
}
