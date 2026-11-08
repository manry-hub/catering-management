<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Kantin', 'Pegawai Catering'], '../pesananlihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pesanan = intval($_POST['id_pesanan'] ?? 0);
    $id_produk = intval($_POST['id_produk'] ?? 0);
    $id_detail_pesanan = intval($_POST['id_detail_pesanan'] ?? 0);

    verify_csrf_token("../pesanandetail.php?id=$id_pesanan");

    if (!$id_pesanan || (!$id_produk && !$id_detail_pesanan)) {
        $_SESSION['flash_error'] = 'Data tidak valid.';
        header("Location: ../pesanandetail.php?id=$id_pesanan");
        exit;
    }

    mysqli_begin_transaction($conn);
    try {
        // 1. Ambil info detail_pesanan
        if ($id_detail_pesanan > 0) {
            $sql_det = "SELECT id_pesanan, id_produk, qty, subtotal FROM detail_pesanan WHERE id_detail_pesanan = ?";
            $stmt_det = mysqli_prepare($conn, $sql_det);
            mysqli_stmt_bind_param($stmt_det, 'i', $id_detail_pesanan);
        } else {
            $sql_det = "SELECT id_pesanan, id_produk, qty, subtotal FROM detail_pesanan WHERE id_pesanan = ? AND id_produk = ?";
            $stmt_det = mysqli_prepare($conn, $sql_det);
            mysqli_stmt_bind_param($stmt_det, 'ii', $id_pesanan, $id_produk);
        }
        mysqli_stmt_execute($stmt_det);
        $det = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_det));

        if (!$det) throw new Exception('Detail pesanan tidak ditemukan.');

        $id_produk = $det['id_produk'];
        $qty = $det['qty'];
        $subtotal = $det['subtotal'];

        // 2. Kembalikan stok bahan baku (BOM)
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
            if (!mysqli_stmt_execute($stmt_stok)) throw new Exception('Gagal mengembalikan stok bahan baku.');

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

        // 3. Delete detail_pesanan
        if ($id_detail_pesanan > 0) {
            $sql_del = "DELETE FROM detail_pesanan WHERE id_detail_pesanan = ?";
            $stmt_del = mysqli_prepare($conn, $sql_del);
            mysqli_stmt_bind_param($stmt_del, 'i', $id_detail_pesanan);
        } else {
            $sql_del = "DELETE FROM detail_pesanan WHERE id_pesanan = ? AND id_produk = ?";
            $stmt_del = mysqli_prepare($conn, $sql_del);
            mysqli_stmt_bind_param($stmt_del, 'ii', $id_pesanan, $id_produk);
        }
        if (!mysqli_stmt_execute($stmt_del)) throw new Exception('Gagal menghapus item dari pesanan.');

        // 4. Update total_harga pesanan
        $sql_upd = "UPDATE pesanan SET total_harga = total_harga - ? WHERE id_pesanan = ?";
        $stmt_upd = mysqli_prepare($conn, $sql_upd);
        mysqli_stmt_bind_param($stmt_upd, 'di', $subtotal, $id_pesanan);
        if (!mysqli_stmt_execute($stmt_upd)) throw new Exception('Gagal memperbarui total harga pesanan.');

        mysqli_commit($conn);
        $_SESSION['flash_success'] = 'Item produk berhasil dihapus dan stok dikembalikan.';
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['flash_error'] = $e->getMessage();
    }

    header("Location: ../pesanandetail.php?id=$id_pesanan");
    exit;
}
