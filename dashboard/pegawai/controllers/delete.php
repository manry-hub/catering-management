<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pegawai = $_POST['id_pegawai'] ?? 0;

    if (!empty($id_pegawai)) {
        // Prevent deleting the currently logged in user to avoid session issues
        if ($id_pegawai == $_SESSION['id_pegawai']) {
            $_SESSION['flash_error'] = 'Tidak dapat menghapus akun yang sedang login.';
            header('Location: ../pegawailihat.php');
            exit;
        }

        $sql  = "DELETE FROM pegawai WHERE id_pegawai = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id_pegawai);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_success'] = 'Data pegawai berhasil dihapus.';
        } else {
            $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menghapus data pegawai.');
        }
    } else {
        $_SESSION['flash_error'] = 'ID Pegawai tidak valid.';
    }

    header('Location: ../pegawailihat.php');
    exit;
}
?>
