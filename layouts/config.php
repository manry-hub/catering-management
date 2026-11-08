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
function handle_db_error($conn_or_exception, $default_msg = 'Terjadi kesalahan pada database.')
{
    if ($conn_or_exception instanceof \mysqli_sql_exception) {
        $errno = $conn_or_exception->getCode();
        $error = $conn_or_exception->getMessage();
    } elseif ($conn_or_exception instanceof \mysqli) {
        $errno = mysqli_errno($conn_or_exception);
        $error = mysqli_error($conn_or_exception);
    } else {
        $errno = 0;
        $error = '';
    }

    if ($errno == 1451) {
        return "Data tidak bisa dihapus karena masih terkait dengan data lain (misal: sudah ada di transaksi).";
    } elseif ($errno == 1062) {
        return "Data yang Anda masukkan sudah ada (duplikat, misal: nama pegawai sudah terdaftar).";
    } elseif ($errno == 1452) {
        return "Gagal menyimpan karena referensi data (ID) tidak ditemukan.";
    } elseif ($errno == 4025) {
        return "Operasi ditolak: Kuantitas stok bahan baku tidak mencukupi atau bernilai negatif.";
    }

    return $default_msg . ($error ? " (Detail: " . htmlspecialchars($error) . ")" : "");
}

/**
 * Render flash error safely with HTML escaping
 */
function render_flash_error(): string
{
    if (empty($_SESSION['flash_error'])) {
        return '';
    }
    $raw = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);

    $lines = preg_split('/<br\s*\/?>|\n/i', (string)$raw);
    $escaped = [];
    foreach ($lines as $l) {
        $t = trim($l);
        if ($t !== '') {
            $escaped[] = htmlspecialchars($t, ENT_QUOTES, 'UTF-8');
        }
    }
    return implode('<br>', $escaped);
}

/**
 * Render flash success safely with HTML escaping
 */
function render_flash_success(): string
{
    if (empty($_SESSION['flash_success'])) {
        return '';
    }
    $raw = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
    return htmlspecialchars((string)$raw, ENT_QUOTES, 'UTF-8');
}
