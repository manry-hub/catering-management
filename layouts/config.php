<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'sistem_pemesanan_kantin_gorjes_db';

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die('Koneksi gagal: ' . mysqli_connect_error());
}

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
