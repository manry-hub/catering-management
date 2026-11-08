<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Kantin', 'Pegawai Catering'], '../pesananlihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token('../pesananlihat.php');

    $id_pesanan = intval($_POST['id_pesanan'] ?? 0);

    if (empty($id_pesanan)) {
        $_SESSION['flash_error'] = 'ID Pesanan tidak valid.';
        header('Location: ../pesananlihat.php');
        exit;
    }

    mysqli_begin_transaction($conn);
    try {
        // Cek info pesanan
        $sql_info = "SELECT id_catering, status_pesanan FROM pesanan WHERE id_pesanan = ?";
        $stmt_info = mysqli_prepare($conn, $sql_info);
        mysqli_stmt_bind_param($stmt_info, 'i', $id_pesanan);
        mysqli_stmt_execute($stmt_info);
        $order_info = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_info));

        if (!$order_info) {
            throw new Exception('Pesanan tidak ditemukan.');
        }

        $linked_catering_id = $order_info['id_catering'] ?? null;
        $order_status = $order_info['status_pesanan'];

        // 1. Hanya kembalikan stok jika pesanan masih 'Diproses'
        // Jika pesanan sudah 'Selesai', makanan sudah dikonsumsi (jangan pulihkan stok fiktif).
        // Jika pesanan sudah 'Dibatalkan', stok sudah dikembalikan saat pembatalan (jangan kembalikan dua kali).
        if ($order_status === 'Diproses') {
            $sql_detail = "SELECT dp.id_produk, dp.qty FROM detail_pesanan dp WHERE dp.id_pesanan = ?";
            $stmt_detail = mysqli_prepare($conn, $sql_detail);
            mysqli_stmt_bind_param($stmt_detail, 'i', $id_pesanan);
            mysqli_stmt_execute($stmt_detail);
            $res_detail = mysqli_stmt_get_result($stmt_detail);

            while ($detail = mysqli_fetch_assoc($res_detail)) {
                $pid = $detail['id_produk'];
                $qty_pesanan = $detail['qty'];

                $sql_bom = "SELECT id_bahan_baku, qty AS qty_bom FROM bom WHERE id_produk = ?";
                $stmt_bom = mysqli_prepare($conn, $sql_bom);
                mysqli_stmt_bind_param($stmt_bom, 'i', $pid);
                mysqli_stmt_execute($stmt_bom);
                $res_bom = mysqli_stmt_get_result($stmt_bom);

                while ($bom = mysqli_fetch_assoc($res_bom)) {
                    $id_bahan = $bom['id_bahan_baku'];
                    $pengembalian = $bom['qty_bom'] * $qty_pesanan;

                    $sql_upd = "UPDATE bahan_baku SET stok = stok + ? WHERE id_bahan_baku = ?";
                    $stmt_upd = mysqli_prepare($conn, $sql_upd);
                    mysqli_stmt_bind_param($stmt_upd, 'di', $pengembalian, $id_bahan);
                    mysqli_stmt_execute($stmt_upd);

                    // Update status_ketersediaan
                    $sql_stok = "SELECT stok, stok_kritis FROM bahan_baku WHERE id_bahan_baku = ?";
                    $stmt_stok = mysqli_prepare($conn, $sql_stok);
                    mysqli_stmt_bind_param($stmt_stok, 'i', $id_bahan);
                    mysqli_stmt_execute($stmt_stok);
                    $bahan_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_stok));

                    if ($bahan_data) {
                        $sisa = $bahan_data['stok'];
                        $kritis = $bahan_data['stok_kritis'];
                        $new_status = ($sisa <= 0) ? 'Habis' : (($sisa <= $kritis) ? 'Menipis' : 'Tersedia');

                        $sql_status = "UPDATE bahan_baku SET status_ketersediaan = ? WHERE id_bahan_baku = ?";
                        $stmt_status = mysqli_prepare($conn, $sql_status);
                        mysqli_stmt_bind_param($stmt_status, 'si', $new_status, $id_bahan);
                        mysqli_stmt_execute($stmt_status);
                    }
                }
            }
        }

        // 2. Hapus detail_pesanan
        $sql_del_detail = "DELETE FROM detail_pesanan WHERE id_pesanan = ?";
        $stmt_del_detail = mysqli_prepare($conn, $sql_del_detail);
        mysqli_stmt_bind_param($stmt_del_detail, 'i', $id_pesanan);
        mysqli_stmt_execute($stmt_del_detail);

        // 3. Hapus pesanan
        $sql_del = "DELETE FROM pesanan WHERE id_pesanan = ?";
        $stmt_del = mysqli_prepare($conn, $sql_del);
        mysqli_stmt_bind_param($stmt_del, 'i', $id_pesanan);
        mysqli_stmt_execute($stmt_del);

        // 4. Bersihkan data catering terkait
        if (!empty($linked_catering_id)) {
            $sql_del_cat = "DELETE FROM catering WHERE id_catering = ?";
            $stmt_del_cat = mysqli_prepare($conn, $sql_del_cat);
            mysqli_stmt_bind_param($stmt_del_cat, 'i', $linked_catering_id);
            mysqli_stmt_execute($stmt_del_cat);
        }

        mysqli_commit($conn);
        $_SESSION['flash_success'] = 'Pesanan berhasil dihapus.';

    } catch (\Throwable $e) {
        mysqli_rollback($conn);
        $_SESSION['flash_error'] = handle_db_error($e, 'Gagal menghapus pesanan.');
    }

    header('Location: ../pesananlihat.php');
    exit;
}
