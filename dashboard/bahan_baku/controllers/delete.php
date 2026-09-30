<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_bahan_baku = $_POST['id_bahan_baku'] ?? 0;

    if (!empty($id_bahan_baku)) {
        $sql  = "DELETE FROM bahan_baku WHERE id_bahan_baku = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id_bahan_baku);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_success'] = 'Data bahan baku berhasil dihapus.';
        } else {
            $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menghapus data bahan baku.');
        }
    } else {
        $_SESSION['flash_error'] = 'ID Bahan Baku tidak valid.';
    }

    header('Location: ../bahan_bakulihat.php');
    exit;
}
?>
