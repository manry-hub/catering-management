<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Dapur'], '../bahan_bakulihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token('../bahan_bakutambah.php');

    $errors = [];
    $nama_bahan          = trim($_POST['nama_bahan'] ?? '');
    $satuan              = trim($_POST['satuan'] ?? '');
    $harga_satuan_bahan  = floatval($_POST['harga_satuan_bahan'] ?? 0);
    $stok                = floatval($_POST['stok'] ?? 0);
    $stok_kritis         = floatval($_POST['stok_kritis'] ?? 0);

    if (empty($nama_bahan)) {
        $errors[] = 'Nama Bahan wajib diisi.';
    }
    if (empty($satuan)) {
        $errors[] = 'Satuan bahan wajib diisi.';
    }
    if ($harga_satuan_bahan <= 0) {
        $errors[] = 'Harga Satuan wajib diisi dengan angka valid lebih dari 0.';
    }
    if ($stok < 0) {
        $errors[] = 'Stok awal tidak boleh bernilai negatif.';
    }
    if ($stok_kritis < 0) {
        $errors[] = 'Stok kritis tidak boleh bernilai negatif.';
    }

    // Tentukan status ketersediaan
    if ($stok <= 0) {
        $status_ketersediaan = 'Habis';
    } elseif ($stok <= $stok_kritis) {
        $status_ketersediaan = 'Menipis';
    } else {
        $status_ketersediaan = 'Tersedia';
    }

    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        header('Location: ../bahan_bakutambah.php');
        exit;
    }

    try {
        $sql  = "INSERT INTO bahan_baku (nama_bahan, satuan, harga_satuan_bahan, stok, stok_kritis, status_ketersediaan) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssddds', $nama_bahan, $satuan, $harga_satuan_bahan, $stok, $stok_kritis, $status_ketersediaan);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_success'] = 'Data bahan baku berhasil ditambahkan.';
        } else {
            $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menambahkan data bahan baku.');
        }
    } catch (\mysqli_sql_exception $e) {
        $_SESSION['flash_error'] = handle_db_error($e, 'Gagal menambahkan data bahan baku.');
    }

    header('Location: ../bahan_bakulihat.php');
    exit;
}
