<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Dapur'], '../bahan_bakulihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token('../bahan_bakulihat.php');

    $id_bahan_baku = intval($_POST['id_bahan_baku'] ?? 0);

    if (!empty($id_bahan_baku)) {
        // Cek apakah bahan baku masih dipakai dalam resep (BOM) produk
        $cek_bom = mysqli_prepare($conn, "SELECT COUNT(*) as c FROM bom WHERE id_bahan_baku = ?");
        mysqli_stmt_bind_param($cek_bom, 'i', $id_bahan_baku);
        mysqli_stmt_execute($cek_bom);
        $count_bom = intval(mysqli_fetch_assoc(mysqli_stmt_get_result($cek_bom))['c'] ?? 0);

        if ($count_bom > 0) {
            $_SESSION['flash_error'] = "Bahan baku tidak dapat dihapus karena masih digunakan dalam $count_bom resep produk (BOM). Hapus bahan dari resep produk terlebih dahulu.";
            header('Location: ../bahan_bakulihat.php');
            exit;
        }

        try {
            $sql  = "DELETE FROM bahan_baku WHERE id_bahan_baku = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'i', $id_bahan_baku);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_success'] = 'Data bahan baku berhasil dihapus.';
            } else {
                $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menghapus data bahan baku.');
            }
        } catch (\mysqli_sql_exception $e) {
            $_SESSION['flash_error'] = handle_db_error($e, 'Gagal menghapus data bahan baku.');
        }
    } else {
        $_SESSION['flash_error'] = 'ID Bahan Baku tidak valid.';
    }

    header('Location: ../bahan_bakulihat.php');
    exit;
}
?>
