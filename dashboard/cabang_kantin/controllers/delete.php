<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

// Otorisasi: Hanya Owner dan Admin yang berhak menghapus data cabang kantin
require_role(['Owner', 'Admin'], '../cabang_kantinlihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token('../cabang_kantinlihat.php');

    $id_cabang_kantin = intval($_POST['id_cabang'] ?? $_POST['id_cabang_kantin'] ?? 0);

    if (!empty($id_cabang_kantin)) {
        try {
            $sql  = "DELETE FROM cabang_kantin WHERE id_cabang_kantin = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'i', $id_cabang_kantin);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_success'] = 'Data cabang kantin berhasil dihapus.';
            } else {
                $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menghapus data cabang kantin.');
            }
        } catch (\mysqli_sql_exception $e) {
            $_SESSION['flash_error'] = handle_db_error($e, 'Gagal menghapus data cabang kantin.');
        }
    } else {
        $_SESSION['flash_error'] = 'ID Cabang tidak valid.';
    }

    header('Location: ../cabang_kantinlihat.php');
    exit;
}
?>
