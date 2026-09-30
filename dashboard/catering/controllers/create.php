<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $tanggal_acara = trim($_POST['tanggal_acara'] ?? '');
    $lokasi_acara  = trim($_POST['lokasi_acara'] ?? '');
    $catatan       = trim($_POST['catatan'] ?? '');

    if (empty($tanggal_acara)) {
        $errors[] = 'Tanggal Acara wajib diisi.';
    }
    if (empty($lokasi_acara)) {
        $errors[] = 'Lokasi Acara wajib diisi.';
    }


    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        header('Location: ../cateringtambah.php');
        exit;
    }

    $sql  = "INSERT INTO catering (tanggal_acara, lokasi_acara, catatan) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sss', $tanggal_acara, $lokasi_acara, $catatan);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_success'] = 'Jadwal catering berhasil ditambahkan.';
    } else {
        $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menambahkan jadwal catering.');
    }

    header('Location: ../cateringlihat.php');
    exit;
}
