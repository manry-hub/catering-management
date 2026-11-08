<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Dapur'], '../produklihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token('../produktambah.php');

    $errors = [];
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $kategori    = trim($_POST['kategori'] ?? '');

    $harga_jual  = floatval($_POST['harga_jual'] ?? 0);
    $harga_pokok = floatval($_POST['harga_pokok'] ?? 0);

    if (empty($nama_produk)) {
        $errors[] = 'Nama Produk wajib diisi.';
    }
    if ($harga_jual <= 0) {
        $errors[] = 'Harga Jual wajib diisi dengan angka valid lebih dari 0.';
    }
    if ($harga_pokok < 0) {
        $errors[] = 'Harga Pokok tidak boleh kurang dari 0.';
    }

    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        header('Location: ../produktambah.php');
        exit;
    }

    $sql  = "INSERT INTO produk (nama_produk, kategori, harga_jual, harga_pokok) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ssdd', $nama_produk, $kategori, $harga_jual, $harga_pokok);

    if (mysqli_stmt_execute($stmt)) {
        $new_id = mysqli_insert_id($conn);
        $_SESSION['flash_success'] = 'Produk berhasil ditambahkan. Silakan lengkapi resep (BOM) untuk produk ini.';
        header('Location: ../../bom/bomdetail.php?id=' . $new_id);
        exit;
    } else {
        $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menambahkan data produk.');
        header('Location: ../produklihat.php');
        exit;
    }
}
