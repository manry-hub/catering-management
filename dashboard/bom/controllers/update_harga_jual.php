<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Dapur'], '../../produk/produklihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk = intval($_POST['id_produk'] ?? 0);
    $harga_jual = floatval($_POST['harga_jual'] ?? 0);

    verify_csrf_token('../bomdetail.php?id=' . $id_produk);

    if (empty($id_produk)) {
        $_SESSION['flash_error'] = 'Produk tidak valid.';
        header('Location: ../../produk/produklihat.php');
        exit;
    }

    if ($harga_jual < 0) {
        $_SESSION['flash_error'] = 'Harga Jual tidak boleh kurang dari 0.';
        header('Location: ../bomdetail.php?id=' . $id_produk);
        exit;
    }

    $stmt = mysqli_prepare($conn, "UPDATE produk SET harga_jual = ? WHERE id_produk = ?");
    mysqli_stmt_bind_param($stmt, 'di', $harga_jual, $id_produk);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_success'] = 'Harga Jual berhasil diperbarui.';
    } else {
        $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal memperbarui Harga Jual.');
    }

    header('Location: ../bomdetail.php?id=' . $id_produk);
    exit;
}
