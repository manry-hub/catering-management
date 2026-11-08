<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Catering'], '../cateringlihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token('../cateringlihat.php');

    $id_catering = intval($_POST['id_catering'] ?? 0);

    if (!empty($id_catering)) {
        try {
            $sql  = "DELETE FROM catering WHERE id_catering = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'i', $id_catering);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_success'] = 'Jadwal catering berhasil dihapus.';
            } else {
                $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menghapus jadwal catering.');
            }
        } catch (\mysqli_sql_exception $e) {
            $_SESSION['flash_error'] = handle_db_error($e, 'Gagal menghapus jadwal catering.');
        }
    } else {
        $_SESSION['flash_error'] = 'ID Catering tidak valid.';
    }

    header('Location: ../cateringlihat.php');
    exit;
}
?>
