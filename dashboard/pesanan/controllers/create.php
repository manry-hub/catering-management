<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Kantin', 'Pegawai Catering'], '../pesananlihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token('../pesanantambah.php');

    $errors = [];
    $id_pelanggan = intval($_POST['id_pelanggan'] ?? 0);
    $id_cabang_kantin = null;
    $id_catering = null;
    $id_pegawai = intval($_SESSION['id_pegawai'] ?? 0);
    $jenis_pesanan = trim($_POST['jenis_pesanan'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');

    // Validasi umum
    if (empty($id_pelanggan))
        $errors[] = 'Pelanggan wajib dipilih.';
    if (!in_array($jenis_pesanan, ['Kantin', 'Catering']))
        $errors[] = 'Jenis pesanan tidak valid.';
    if ($jenis_pesanan === 'Kantin') {
        $id_cabang_kantin = !empty($_POST['id_cabang_kantin']) ? intval($_POST['id_cabang_kantin']) : null;
        if (empty($id_cabang_kantin))
            $errors[] = 'Cabang Kantin wajib dipilih.';
    }
    // Validasi catering fields
    $tanggal_acara = trim($_POST['tanggal_acara'] ?? '');
    $lokasi_acara = trim($_POST['lokasi_acara'] ?? '');

    if ($jenis_pesanan === 'Catering') {
        if (empty($tanggal_acara))
            $errors[] = 'Tanggal acara wajib diisi.';
        if (empty($lokasi_acara))
            $errors[] = 'Lokasi acara wajib diisi.';
    }

    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        header('Location: ../pesanantambah.php');
        exit;
    }

    // ========== BEGIN TRANSACTION ==========
    mysqli_begin_transaction($conn);
    try {
        // Jika Catering, buat record catering dulu
        if ($jenis_pesanan === 'Catering') {
            $sql_cat = "INSERT INTO catering (tanggal_acara, lokasi_acara) VALUES (?, ?)";
            $stmt_cat = mysqli_prepare($conn, $sql_cat);
            mysqli_stmt_bind_param($stmt_cat, 'ss', $tanggal_acara, $lokasi_acara);
            if (!mysqli_stmt_execute($stmt_cat)) {
                throw new Exception('Gagal menyimpan data catering: ' . mysqli_error($conn));
            }
            $id_catering = mysqli_insert_id($conn);
        }

        // 1. INSERT pesanan
        $tanggal = date('Y-m-d H:i:s');
        $status = 'Diproses';
        $total_harga = 0;

        $sql = "INSERT INTO pesanan (tanggal_pesanan, status_pesanan, total_harga, jenis_pesanan, id_pelanggan, id_pegawai, id_cabang_kantin, id_catering, catatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssdsiiiss', $tanggal, $status, $total_harga, $jenis_pesanan, $id_pelanggan, $id_pegawai, $id_cabang_kantin, $id_catering, $catatan);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Gagal menyimpan pesanan: ' . mysqli_error($conn));
        }
        $id_pesanan = mysqli_insert_id($conn);

        mysqli_commit($conn);
        $_SESSION['flash_success'] = 'Pesanan berhasil dibuat. Silakan tambahkan produk ke dalam pesanan ini.';
        header('Location: ../../detail_pesanan/detail_pesananlihat.php?id=' . $id_pesanan);
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['flash_error'] = 'Transaksi gagal: ' . $e->getMessage();
        header('Location: ../pesananlihat.php');
        exit;
    }
}
