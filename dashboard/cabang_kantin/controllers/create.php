<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Kantin'], '../cabang_kantinlihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token('../cabang_kantintambah.php');

    $errors = [];
    $nama_cabang   = trim($_POST['nama_cabang'] ?? '');
    $alamat_cabang = trim($_POST['alamat_cabang'] ?? '');

    if (empty($nama_cabang)) {
        $errors[] = 'Nama Cabang wajib diisi.';
    }

    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        header('Location: ../cabang_kantintambah.php');
        exit;
    }

    $sql  = "INSERT INTO cabang_kantin (nama_cabang, alamat_cabang) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $nama_cabang, $alamat_cabang);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_success'] = 'Data cabang kantin berhasil ditambahkan.';
    } else {
        $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menambahkan data cabang kantin.');
    }

    header('Location: ../cabang_kantinlihat.php');
    exit;
}
