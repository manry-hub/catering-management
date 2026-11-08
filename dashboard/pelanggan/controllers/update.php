<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Kantin', 'Pegawai Catering'], '../pelangganlihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pelanggan = intval($_POST['id_pelanggan'] ?? 0);
    verify_csrf_token('../pelangganubah.php?id=' . urlencode($id_pelanggan));

    $errors = [];
    $nama_pelanggan   = trim($_POST['nama_pelanggan'] ?? '');
    $no_telp          = trim($_POST['no_telp'] ?? '');
    $alamat_pelanggan = trim($_POST['alamat_pelanggan'] ?? '');

    if (empty($id_pelanggan)) {
        $errors[] = 'ID Pelanggan tidak ditemukan.';
    }
    if (empty($nama_pelanggan)) {
        $errors[] = 'Nama Pelanggan wajib diisi.';
    }
    if (empty($no_telp)) {
        $errors[] = 'Nomor Telepon wajib diisi.';
    }

    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        header('Location: ../pelangganubah.php?id=' . urlencode($id_pelanggan));
        exit;
    }

    $sql  = "UPDATE pelanggan SET nama_pelanggan = ?, no_telp = ?, alamat_pelanggan = ? WHERE id_pelanggan = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sssi', $nama_pelanggan, $no_telp, $alamat_pelanggan, $id_pelanggan);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_success'] = 'Data pelanggan berhasil diubah.';
    } else {
        $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal mengubah data pelanggan.');
    }

    header('Location: ../pelangganlihat.php');
    exit;
}
