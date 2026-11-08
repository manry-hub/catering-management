<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Kantin', 'Pegawai Catering'], '../pesananlihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pesanan = intval($_POST['id_pesanan'] ?? 0);
    verify_csrf_token('../pesananubah.php?id=' . urlencode($id_pesanan));

    $status_pesanan   = trim($_POST['status_pesanan'] ?? '');
    $id_pelanggan     = intval($_POST['id_pelanggan'] ?? 0);
    
    // Pastikan nilai cabang kosong dikonversi ke NULL murni, bukan integer 0
    $id_cabang_raw    = trim($_POST['id_cabang_kantin'] ?? '');
    $id_cabang_kantin = ($id_cabang_raw !== '' && intval($id_cabang_raw) > 0) ? intval($id_cabang_raw) : null;
    
    $catatan          = trim($_POST['catatan'] ?? '');
    
    // Catering fields
    $tanggal_acara    = trim($_POST['tanggal_acara'] ?? '');
    $lokasi_acara     = trim($_POST['lokasi_acara'] ?? '');

    $valid_statuses = ['Diproses', 'Selesai', 'Dibatalkan'];

    if (empty($id_pesanan) || empty($id_pelanggan)) {
        $_SESSION['flash_error'] = 'Data pesanan tidak lengkap.';
        header('Location: ../pesananlihat.php');
        exit;
    }
    if (!in_array($status_pesanan, $valid_statuses, true)) {
        $_SESSION['flash_error'] = 'Status pesanan tidak valid.';
        header('Location: ../pesananubah.php?id=' . urlencode($id_pesanan));
        exit;
    }

    // Ambil data pesanan untuk tahu status lama dan jenisnya
    $sql_cek = "SELECT jenis_pesanan, id_catering, status_pesanan FROM pesanan WHERE id_pesanan = ?";
    $stmt_cek = mysqli_prepare($conn, $sql_cek);
    mysqli_stmt_bind_param($stmt_cek, 'i', $id_pesanan);
    mysqli_stmt_execute($stmt_cek);
    $data_pesanan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cek));

    if (!$data_pesanan) {
        $_SESSION['flash_error'] = 'Pesanan tidak ditemukan.';
        header('Location: ../pesananlihat.php');
        exit;
    }

    $old_status = $data_pesanan['status_pesanan'];

    // Aturan siklus pesanan:
    // 1. Pesanan yang sudah dibatalkan tidak dapat diaktifkan kembali
    if ($old_status === 'Dibatalkan' && $status_pesanan !== 'Dibatalkan') {
        $_SESSION['flash_error'] = 'Pesanan yang sudah dibatalkan tidak dapat diubah kembali statusnya.';
        header('Location: ../pesananubah.php?id=' . urlencode($id_pesanan));
        exit;
    }

    mysqli_begin_transaction($conn);
    try {
        if ($data_pesanan['jenis_pesanan'] === 'Catering' && !empty($data_pesanan['id_catering'])) {
            // Update catering table
            $sql_cat = "UPDATE catering SET tanggal_acara = ?, lokasi_acara = ? WHERE id_catering = ?";
            $stmt_cat = mysqli_prepare($conn, $sql_cat);
            mysqli_stmt_bind_param($stmt_cat, 'ssi', $tanggal_acara, $lokasi_acara, $data_pesanan['id_catering']);
            mysqli_stmt_execute($stmt_cat);
        }

        // Jika status berubah dari bukan 'Dibatalkan' menjadi 'Dibatalkan', kembalikan stok bahan baku
        if ($old_status !== 'Dibatalkan' && $status_pesanan === 'Dibatalkan') {
            $sql_items = "SELECT dp.id_produk, dp.qty, b.id_bahan_baku, b.qty AS bom_qty 
                          FROM detail_pesanan dp 
                          JOIN bom b ON dp.id_produk = b.id_produk 
                          WHERE dp.id_pesanan = ?";
            $stmt_items = mysqli_prepare($conn, $sql_items);
            mysqli_stmt_bind_param($stmt_items, 'i', $id_pesanan);
            mysqli_stmt_execute($stmt_items);
            $res_items = mysqli_stmt_get_result($stmt_items);

            while ($item = mysqli_fetch_assoc($res_items)) {
                $restore_qty = $item['qty'] * $item['bom_qty'];
                $upd_stok = mysqli_prepare($conn, "UPDATE bahan_baku SET stok = stok + ? WHERE id_bahan_baku = ?");
                mysqli_stmt_bind_param($upd_stok, 'di', $restore_qty, $item['id_bahan_baku']);
                mysqli_stmt_execute($upd_stok);

                // Update status ketersediaan
                $stmt_cek_stok = mysqli_prepare($conn, "SELECT stok, stok_kritis FROM bahan_baku WHERE id_bahan_baku = ?");
                mysqli_stmt_bind_param($stmt_cek_stok, 'i', $item['id_bahan_baku']);
                mysqli_stmt_execute($stmt_cek_stok);
                $stok_row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cek_stok));
                if ($stok_row) {
                    $new_stat = ($stok_row['stok'] <= 0) ? 'Habis' : (($stok_row['stok'] <= $stok_row['stok_kritis']) ? 'Menipis' : 'Tersedia');
                    $upd_stat = mysqli_prepare($conn, "UPDATE bahan_baku SET status_ketersediaan = ? WHERE id_bahan_baku = ?");
                    mysqli_stmt_bind_param($upd_stat, 'si', $new_stat, $item['id_bahan_baku']);
                    mysqli_stmt_execute($upd_stat);
                }
            }
        }

        // Update pesanan table
        $sql = "UPDATE pesanan SET 
                    status_pesanan = ?, 
                    id_pelanggan = ?, 
                    id_cabang_kantin = ?, 
                    catatan = ? 
                WHERE id_pesanan = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'siisi', $status_pesanan, $id_pelanggan, $id_cabang_kantin, $catatan, $id_pesanan);
        mysqli_stmt_execute($stmt);

        mysqli_commit($conn);
        $_SESSION['flash_success'] = 'Pesanan berhasil diperbarui.';

    } catch (\Throwable $e) {
        mysqli_rollback($conn);
        $_SESSION['flash_error'] = handle_db_error($e, 'Terjadi kesalahan saat memperbarui pesanan: ' . $e->getMessage());
    }

    header('Location: ../pesananlihat.php');
    exit;
}
