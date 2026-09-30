<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $nama_pelanggan = trim($_POST['nama_pelanggan'] ?? '');
    $no_telp        = trim($_POST['no_telp'] ?? '');
    $alamat_pelanggan = trim($_POST['alamat_pelanggan'] ?? '');

    if (empty($nama_pelanggan)) {
        $errors[] = 'Nama Pelanggan wajib diisi.';
    }
    if (empty($no_telp)) {
        $errors[] = 'Nomor Telepon wajib diisi.';
    }

    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        header('Location: ../pelanggantambah.php');
        exit;
    }

    $sql  = "INSERT INTO pelanggan (nama_pelanggan, no_telp, alamat_pelanggan) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sss', $nama_pelanggan, $no_telp, $alamat_pelanggan);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_success'] = 'Data pelanggan berhasil ditambahkan.';
    } else {
        $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menambahkan data pelanggan.');
    }

    header('Location: ../pelangganlihat.php');
    exit;
}
