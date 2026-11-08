<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Dapur'], '../produklihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token('../produklihat.php');

    $id_produk = intval($_POST['id_produk'] ?? 0);

    if (!empty($id_produk)) {
        try {
            $sql  = "DELETE FROM produk WHERE id_produk = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'i', $id_produk);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_success'] = 'Data produk berhasil dihapus.';
            } else {
                $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menghapus data produk.');
            }
        } catch (\mysqli_sql_exception $e) {
            $_SESSION['flash_error'] = handle_db_error($e, 'Gagal menghapus data produk.');
        }
    } else {
        $_SESSION['flash_error'] = 'ID Produk tidak valid.';
    }

    header('Location: ../produklihat.php');
    exit;
}
?>
