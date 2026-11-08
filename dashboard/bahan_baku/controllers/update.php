<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Dapur'], '../bahan_bakulihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_bahan_baku = intval($_POST['id_bahan_baku'] ?? 0);
    verify_csrf_token('../bahan_bakuubah.php?id=' . urlencode($id_bahan_baku));

    $errors = [];
    $nama_bahan          = trim($_POST['nama_bahan'] ?? '');
    $satuan              = trim($_POST['satuan'] ?? '');
    $harga_satuan_bahan  = floatval($_POST['harga_satuan_bahan'] ?? 0);
    $stok                = floatval($_POST['stok'] ?? 0);
    $stok_kritis         = floatval($_POST['stok_kritis'] ?? 0);
    $status_ketersediaan = trim($_POST['status_ketersediaan'] ?? 'Tersedia');

    if (empty($id_bahan_baku)) {
        $errors[] = 'ID Bahan Baku tidak ditemukan.';
    }
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
        $errors[] = 'Stok tidak boleh bernilai negatif.';
    }
    if ($stok_kritis < 0) {
        $errors[] = 'Stok kritis tidak boleh bernilai negatif.';
    }

    // Auto-kalkulasi status ketersediaan berdasarkan stok riil
    if ($stok <= 0) {
        $status_ketersediaan = 'Habis';
    } elseif ($stok <= $stok_kritis) {
        $status_ketersediaan = 'Menipis';
    } else {
        $status_ketersediaan = 'Tersedia';
    }

    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        header('Location: ../bahan_bakuubah.php?id=' . urlencode($id_bahan_baku));
        exit;
    }

    mysqli_begin_transaction($conn);
    try {
        $sql  = "UPDATE bahan_baku SET nama_bahan = ?, satuan = ?, harga_satuan_bahan = ?, stok = ?, stok_kritis = ?, status_ketersediaan = ? WHERE id_bahan_baku = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssdddsi', $nama_bahan, $satuan, $harga_satuan_bahan, $stok, $stok_kritis, $status_ketersediaan, $id_bahan_baku);
        mysqli_stmt_execute($stmt);

        // Otomatis sinkronkan subtotal BOM dan HPP seluruh produk yang menggunakan bahan baku ini
        $aff_stmt = mysqli_prepare($conn, "SELECT DISTINCT id_produk FROM bom WHERE id_bahan_baku = ?");
        mysqli_stmt_bind_param($aff_stmt, 'i', $id_bahan_baku);
        mysqli_stmt_execute($aff_stmt);
        $aff_res = mysqli_stmt_get_result($aff_stmt);

        while ($row = mysqli_fetch_assoc($aff_res)) {
            $pid = $row['id_produk'];

            // 1. Update subtotal pada baris BOM ini
            $stmt_sync_sub = mysqli_prepare($conn, "UPDATE bom SET subtotal = qty * ? WHERE id_produk = ? AND id_bahan_baku = ?");
            mysqli_stmt_bind_param($stmt_sync_sub, 'dii', $harga_satuan_bahan, $pid, $id_bahan_baku);
            mysqli_stmt_execute($stmt_sync_sub);

            // 2. Hitung ulang total HPP produk
            $stmt_calc = mysqli_prepare($conn, "SELECT SUM(b.qty * bb.harga_satuan_bahan) as total FROM bom b JOIN bahan_baku bb ON b.id_bahan_baku = bb.id_bahan_baku WHERE b.id_produk = ?");
            mysqli_stmt_bind_param($stmt_calc, 'i', $pid);
            mysqli_stmt_execute($stmt_calc);
            $total_hpp = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_calc))['total'] ?? 0;

            // 3. Update HPP di tabel produk
            $stmt_upd_p = mysqli_prepare($conn, "UPDATE produk SET harga_pokok = ? WHERE id_produk = ?");
            mysqli_stmt_bind_param($stmt_upd_p, 'di', $total_hpp, $pid);
            mysqli_stmt_execute($stmt_upd_p);
        }

        mysqli_commit($conn);
        $_SESSION['flash_success'] = 'Data bahan baku dan sinkronisasi HPP produk terkait berhasil diperbarui.';
    } catch (\Throwable $e) {
        mysqli_rollback($conn);
        $_SESSION['flash_error'] = handle_db_error($e, 'Gagal mengubah data bahan baku.');
    }

    header('Location: ../bahan_bakulihat.php');
    exit;
}
