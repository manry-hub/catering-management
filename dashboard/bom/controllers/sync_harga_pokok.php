<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Dapur'], '../../produk/produklihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk = intval($_POST['id_produk'] ?? 0);

    verify_csrf_token('../bomdetail.php?id=' . $id_produk);

    if (empty($id_produk)) {
        $_SESSION['flash_error'] = 'ID Produk tidak valid.';
        header('Location: ../../produk/produklihat.php');
        exit;
    }

    try {
        // 1. Sinkronkan subtotal di tabel bom dengan harga satuan bahan baku terbaru
        $upd_bom = mysqli_prepare($conn, "
            UPDATE bom b
            JOIN bahan_baku bb ON b.id_bahan_baku = bb.id_bahan_baku
            SET b.subtotal = b.qty * bb.harga_satuan_bahan
            WHERE b.id_produk = ?
        ");
        mysqli_stmt_bind_param($upd_bom, 'i', $id_produk);
        mysqli_stmt_execute($upd_bom);

        // 2. Hitung total HPP berdasarkan harga bahan baku riil
        $stmt = mysqli_prepare($conn, "
            SELECT SUM(b.qty * bb.harga_satuan_bahan) AS total 
            FROM bom b
            JOIN bahan_baku bb ON b.id_bahan_baku = bb.id_bahan_baku
            WHERE b.id_produk = ?
        ");
        mysqli_stmt_bind_param($stmt, 'i', $id_produk);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        $calculated_total = $row['total'] ?? 0;

        $upd = mysqli_prepare($conn, "UPDATE produk SET harga_pokok = ? WHERE id_produk = ?");
        mysqli_stmt_bind_param($upd, 'di', $calculated_total, $id_produk);

        if (mysqli_stmt_execute($upd)) {
            $_SESSION['flash_success'] = 'Harga Pokok berhasil disinkronkan dengan total BOM (Rp ' . number_format($calculated_total, 0, ',', '.') . ').';
        } else {
            $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menyinkronkan harga pokok.');
        }
    } catch (\mysqli_sql_exception $e) {
        $_SESSION['flash_error'] = handle_db_error($e, 'Gagal menyinkronkan harga pokok.');
    }

    header('Location: ../bomdetail.php?id=' . $id_produk);
    exit;
}
