<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Kantin', 'Pegawai Catering'], '../../pesanan/pesananlihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pesanan = intval($_POST['id_pesanan'] ?? 0);
    $id_produk = intval($_POST['id_produk'] ?? 0);

    verify_csrf_token("../detail_pesananlihat.php?id=$id_pesanan");

    if (!$id_pesanan || !$id_produk) {
        $_SESSION['flash_error'] = 'Data tidak lengkap atau ID tidak valid.';
        header("Location: ../detail_pesananlihat.php?id=$id_pesanan");
        exit;
    }

    mysqli_begin_transaction($conn);
    try {
        // 1. Verifikasi status pesanan
        $sql_pes = "SELECT status_pesanan FROM pesanan WHERE id_pesanan = ?";
        $stmt_pes = mysqli_prepare($conn, $sql_pes);
        mysqli_stmt_bind_param($stmt_pes, 'i', $id_pesanan);
        mysqli_stmt_execute($stmt_pes);
        $pes = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_pes));
        if (!$pes) {
            throw new Exception('Pesanan tidak ditemukan.');
        }
        if ($pes['status_pesanan'] !== 'Diproses') {
            throw new Exception('Item tidak dapat dihapus karena pesanan sudah berstatus ' . $pes['status_pesanan'] . '.');
        }

        // 2. Ambil info detail_pesanan
        $sql_det = "SELECT id_pesanan, id_produk, qty, subtotal FROM detail_pesanan WHERE id_pesanan = ? AND id_produk = ?";
        $stmt_det = mysqli_prepare($conn, $sql_det);
        mysqli_stmt_bind_param($stmt_det, 'ii', $id_pesanan, $id_produk);
        mysqli_stmt_execute($stmt_det);
        $det = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_det));

        if (!$det) {
            throw new Exception('Detail pesanan tidak ditemukan.');
        }

        $qty = $det['qty'];
        $subtotal = $det['subtotal'];

        // 3. Kembalikan stok bahan baku (BOM)
        $sql_bom = "SELECT id_bahan_baku, qty AS qty_bom FROM bom WHERE id_produk = ?";
        $stmt_bom = mysqli_prepare($conn, $sql_bom);
        mysqli_stmt_bind_param($stmt_bom, 'i', $id_produk);
        mysqli_stmt_execute($stmt_bom);
        $res_bom = mysqli_stmt_get_result($stmt_bom);

        while ($bom = mysqli_fetch_assoc($res_bom)) {
            $id_bahan = $bom['id_bahan_baku'];
            $pengembalian = $bom['qty_bom'] * $qty;

            $sql_stok = "UPDATE bahan_baku SET stok = stok + ? WHERE id_bahan_baku = ?";
            $stmt_stok = mysqli_prepare($conn, $sql_stok);
            mysqli_stmt_bind_param($stmt_stok, 'di', $pengembalian, $id_bahan);
            mysqli_stmt_execute($stmt_stok);

            // Update status ketersediaan
            $stmt_cek = mysqli_prepare($conn, "SELECT stok, stok_kritis FROM bahan_baku WHERE id_bahan_baku = ?");
            mysqli_stmt_bind_param($stmt_cek, 'i', $id_bahan);
            mysqli_stmt_execute($stmt_cek);
            $bahan_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cek));
            if ($bahan_data) {
                $sisa = $bahan_data['stok'];
                $kritis = $bahan_data['stok_kritis'];
                $status = ($sisa <= 0) ? 'Habis' : (($sisa <= $kritis) ? 'Menipis' : 'Tersedia');
                $stmt_stat = mysqli_prepare($conn, "UPDATE bahan_baku SET status_ketersediaan = ? WHERE id_bahan_baku = ?");
                mysqli_stmt_bind_param($stmt_stat, 'si', $status, $id_bahan);
                mysqli_stmt_execute($stmt_stat);
            }
        }

        // 4. Delete detail_pesanan
        $sql_del = "DELETE FROM detail_pesanan WHERE id_pesanan = ? AND id_produk = ?";
        $stmt_del = mysqli_prepare($conn, $sql_del);
        mysqli_stmt_bind_param($stmt_del, 'ii', $id_pesanan, $id_produk);
        mysqli_stmt_execute($stmt_del);

        // 5. Update total_harga pesanan
        $sql_upd = "UPDATE pesanan SET total_harga = GREATEST(0, total_harga - ?) WHERE id_pesanan = ?";
        $stmt_upd = mysqli_prepare($conn, $sql_upd);
        mysqli_stmt_bind_param($stmt_upd, 'di', $subtotal, $id_pesanan);
        mysqli_stmt_execute($stmt_upd);

        mysqli_commit($conn);
        $_SESSION['flash_success'] = 'Item produk berhasil dihapus dari pesanan dan stok dipulihkan.';
    } catch (\Throwable $e) {
        mysqli_rollback($conn);
        $_SESSION['flash_error'] = handle_db_error($e, $e->getMessage());
    }

    header("Location: ../detail_pesananlihat.php?id=$id_pesanan");
    exit;
}
