<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cabang_kantin = $_POST['id_cabang'] ?? 0;

    if (!empty($id_cabang_kantin)) {
        $sql  = "DELETE FROM cabang_kantin WHERE id_cabang_kantin = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id_cabang_kantin);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_success'] = 'Data cabang kantin berhasil dihapus.';
        } else {
            $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menghapus data cabang kantin.');
        }
    } else {
        $_SESSION['flash_error'] = 'ID Cabang tidak valid.';
    }

    header('Location: ../cabang_kantinlihat.php');
    exit;
}
?>
