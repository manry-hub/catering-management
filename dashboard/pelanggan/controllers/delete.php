<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Kantin', 'Pegawai Catering'], '../pelangganlihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token('../pelangganlihat.php');

    $id_pelanggan = intval($_POST['id_pelanggan'] ?? 0);

    if (!empty($id_pelanggan)) {
        $sql  = "DELETE FROM pelanggan WHERE id_pelanggan = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id_pelanggan);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_success'] = 'Data pelanggan berhasil dihapus.';
        } else {
            $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menghapus data pelanggan.');
        }
    } else {
        $_SESSION['flash_error'] = 'ID Pelanggan tidak valid.';
    }

    header('Location: ../pelangganlihat.php');
    exit;
}
?>
