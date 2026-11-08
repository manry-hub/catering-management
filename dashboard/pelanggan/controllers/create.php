<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Kantin', 'Pegawai Catering'], '../pelangganlihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token('../pelanggantambah.php');

    $errors = [];
    $nama_pelanggan   = trim($_POST['nama_pelanggan'] ?? '');
    $no_telp          = trim($_POST['no_telp'] ?? '');
    $alamat_pelanggan = trim($_POST['alamat_pelanggan'] ?? '');

    if (empty($nama_pelanggan)) {
        $errors[] = 'Nama Pelanggan wajib diisi.';
    }
    if (empty($no_telp)) {
        $errors[] = 'Nomor Telepon wajib diisi.';
    } elseif (!preg_match('/^[0-9+\-\s()]{8,20}$/', $no_telp)) {
        $errors[] = 'Format Nomor Telepon tidak valid (hanya angka, spasi, tanda + atau - dengan panjang 8-20 digit).';
    }

    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        header('Location: ../pelanggantambah.php');
        exit;
    }

    try {
        $sql  = "INSERT INTO pelanggan (nama_pelanggan, no_telp, alamat_pelanggan) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sss', $nama_pelanggan, $no_telp, $alamat_pelanggan);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_success'] = 'Data pelanggan berhasil ditambahkan.';
        } else {
            $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menambahkan data pelanggan.');
        }
    } catch (\mysqli_sql_exception $e) {
        $_SESSION['flash_error'] = handle_db_error($e, 'Gagal menambahkan data pelanggan.');
    }

    header('Location: ../pelangganlihat.php');
    exit;
}
