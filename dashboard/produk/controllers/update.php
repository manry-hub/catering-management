<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $id_produk   = $_POST['id_produk'] ?? 0;
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $kategori    = trim($_POST['kategori'] ?? '');

    $harga_jual  = $_POST['harga_jual'] ?? 0;
    $harga_pokok = $_POST['harga_pokok'] ?? 0;

    if (empty($id_produk)) {
        $errors[] = 'ID Produk tidak ditemukan.';
    }
    if (empty($nama_produk)) {
        $errors[] = 'Nama Produk wajib diisi.';
    }
    if (empty($harga_jual) || !is_numeric($harga_jual)) {
        $errors[] = 'Harga Jual wajib diisi dengan angka valid.';
    }
    if (empty($harga_pokok) || !is_numeric($harga_pokok)) {
        $errors[] = 'Harga Pokok wajib diisi dengan angka valid.';
    }

    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        header('Location: ../produkubah.php?id=' . urlencode($id_produk));
        exit;
    }

    $sql  = "UPDATE produk SET nama_produk = ?, kategori = ?, harga_jual = ?, harga_pokok = ? WHERE id_produk = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ssddi', $nama_produk, $kategori, $harga_jual, $harga_pokok, $id_produk);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_success'] = 'Data produk berhasil diubah.';
    } else {
        $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal mengubah data produk.');
    }

    header('Location: ../produklihat.php');
    exit;
}
