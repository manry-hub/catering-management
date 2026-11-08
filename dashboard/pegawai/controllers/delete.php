<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

// Otorisasi: Hanya Owner dan Admin yang dapat menghapus pegawai
require_role(['Owner', 'Admin'], '../pegawailihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token('../pegawailihat.php');

    $id_pegawai = intval($_POST['id_pegawai'] ?? 0);

    if (!empty($id_pegawai)) {
        // Cegah menghapus akun yang sedang aktif digunakan
        if ($id_pegawai == $_SESSION['id_pegawai']) {
            $_SESSION['flash_error'] = 'Tidak dapat menghapus akun yang sedang aktif digunakan.';
            header('Location: ../pegawailihat.php');
            exit;
        }

        // Hirarki: Admin tidak boleh menghapus Owner
        $cek_target = mysqli_prepare($conn, "SELECT jabatan FROM pegawai WHERE id_pegawai = ?");
        mysqli_stmt_bind_param($cek_target, 'i', $id_pegawai);
        mysqli_stmt_execute($cek_target);
        $target_user = mysqli_fetch_assoc(mysqli_stmt_get_result($cek_target));
        if ($target_user && $target_user['jabatan'] === 'Owner' && ($_SESSION['jabatan'] ?? '') !== 'Owner') {
            $_SESSION['flash_error'] = 'Hanya Owner yang dapat menghapus akun sesama Owner.';
            header('Location: ../pegawailihat.php');
            exit;
        }

        try {
            $sql  = "DELETE FROM pegawai WHERE id_pegawai = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'i', $id_pegawai);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_success'] = 'Data pegawai berhasil dihapus.';
            } else {
                $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menghapus data pegawai.');
            }
        } catch (\mysqli_sql_exception $e) {
            $_SESSION['flash_error'] = handle_db_error($e, 'Gagal menghapus data pegawai.');
        }
    } else {
        $_SESSION['flash_error'] = 'ID Pegawai tidak valid.';
    }

    header('Location: ../pegawailihat.php');
    exit;
}
?>
