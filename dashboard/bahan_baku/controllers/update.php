<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $id_bahan_baku       = $_POST['id_bahan_baku'] ?? 0;
    $nama_bahan          = trim($_POST['nama_bahan'] ?? '');
    $satuan              = trim($_POST['satuan'] ?? '');
    $harga_satuan_bahan  = trim($_POST['harga_satuan_bahan'] ?? '');
    $stok       = trim($_POST['stok'] ?? 0);
    $stok_kritis         = trim($_POST['stok_kritis'] ?? 0);
    $status_ketersediaan = trim($_POST['status_ketersediaan'] ?? 'Tersedia');

    if (empty($id_bahan_baku)) {
        $errors[] = 'ID Bahan Baku tidak ditemukan.';
    }
    if (empty($nama_bahan)) {
        $errors[] = 'Nama Bahan wajib diisi.';
    }
    if (empty($harga_satuan_bahan) || !is_numeric($harga_satuan_bahan)) {
        $errors[] = 'Harga Satuan wajib diisi dengan angka valid.';
    }

    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        header('Location: ../bahan_bakuubah.php?id=' . urlencode($id_bahan_baku));
        exit;
    }

    $sql  = "UPDATE bahan_baku SET nama_bahan = ?, satuan = ?, harga_satuan_bahan = ?, stok = ?, stok_kritis = ?, status_ketersediaan = ? WHERE id_bahan_baku = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ssdidsi', $nama_bahan, $satuan, $harga_satuan_bahan, $stok, $stok_kritis, $status_ketersediaan, $id_bahan_baku);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_success'] = 'Data bahan baku berhasil diubah.';
    } else {
        $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal mengubah data bahan baku.');
    }

    header('Location: ../bahan_bakulihat.php');
    exit;
}
