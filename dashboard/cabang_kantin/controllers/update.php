<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Kantin'], '../cabang_kantinlihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cabang_kantin = intval($_POST['id_cabang_kantin'] ?? 0);
    verify_csrf_token('../cabang_kantinubah.php?id=' . urlencode($id_cabang_kantin));

    $errors = [];
    $nama_cabang   = trim($_POST['nama_cabang'] ?? '');
    $alamat_cabang = trim($_POST['alamat_cabang'] ?? '');

    if (empty($id_cabang_kantin)) {
        $errors[] = 'ID Cabang tidak ditemukan.';
    }
    if (empty($nama_cabang)) {
        $errors[] = 'Nama Cabang wajib diisi.';
    }

    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        header('Location: ../cabang_kantinubah.php?id=' . urlencode($id_cabang_kantin));
        exit;
    }

    $sql  = "UPDATE cabang_kantin SET nama_cabang = ?, alamat_cabang = ? WHERE id_cabang_kantin = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ssi', $nama_cabang, $alamat_cabang, $id_cabang_kantin);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_success'] = 'Data cabang kantin berhasil diubah.';
    } else {
        $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal mengubah data cabang kantin.');
    }

    header('Location: ../cabang_kantinlihat.php');
    exit;
}
