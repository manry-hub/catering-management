<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Kantin', 'Pegawai Catering'], '../../pesanan/pesananlihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pesanan = intval($_POST['id_pesanan'] ?? 0);
    $id_produk = intval($_POST['id_produk'] ?? 0);
    $id_detail_pesanan = intval($_POST['id_detail_pesanan'] ?? 0);
    $new_qty = intval($_POST['qty'] ?? 0);

    verify_csrf_token("../detail_pesananlihat.php?id=$id_pesanan");

    if (!$id_pesanan || (!$id_produk && !$id_detail_pesanan) || $new_qty <= 0) {
        $_SESSION['flash_error'] = 'Data tidak lengkap atau qty tidak valid.';
        header("Location: ../detail_pesananlihat.php?id=$id_pesanan");
        exit;
    }

    mysqli_begin_transaction($conn);
    try {
        // 1. Ambil detail_pesanan lama beserta harga produk
        if ($id_detail_pesanan > 0) {
            $sql_det = "SELECT dp.id_pesanan, dp.id_produk, dp.qty, pr.harga_jual FROM detail_pesanan dp JOIN produk pr ON dp.id_produk = pr.id_produk WHERE dp.id_detail_pesanan = ?";
            $stmt_det = mysqli_prepare($conn, $sql_det);
            mysqli_stmt_bind_param($stmt_det, 'i', $id_detail_pesanan);
        } else {
            $sql_det = "SELECT dp.id_pesanan, dp.id_produk, dp.qty, pr.harga_jual FROM detail_pesanan dp JOIN produk pr ON dp.id_produk = pr.id_produk WHERE dp.id_pesanan = ? AND dp.id_produk = ?";
            $stmt_det = mysqli_prepare($conn, $sql_det);
            mysqli_stmt_bind_param($stmt_det, 'ii', $id_pesanan, $id_produk);
        }

        mysqli_stmt_execute($stmt_det);
        $det = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_det));

        if (!$det) throw new Exception('Detail pesanan tidak ditemukan.');

        $id_produk = $det['id_produk'];
        $old_qty = $det['qty'];
        $harga_jual = $det['harga_jual'];
        
        $delta_qty = $new_qty - $old_qty;
        
        if ($delta_qty != 0) {
            $delta_subtotal = $delta_qty * $harga_jual;
            $new_subtotal = $new_qty * $harga_jual;

            // 2. Update detail_pesanan
            if ($id_detail_pesanan > 0) {
                $sql_upd_det = "UPDATE detail_pesanan SET qty = ?, subtotal = ? WHERE id_detail_pesanan = ?";
                $stmt_upd_det = mysqli_prepare($conn, $sql_upd_det);
                mysqli_stmt_bind_param($stmt_upd_det, 'idi', $new_qty, $new_subtotal, $id_detail_pesanan);
            } else {
                $sql_upd_det = "UPDATE detail_pesanan SET qty = ?, subtotal = ? WHERE id_pesanan = ? AND id_produk = ?";
                $stmt_upd_det = mysqli_prepare($conn, $sql_upd_det);
                mysqli_stmt_bind_param($stmt_upd_det, 'idii', $new_qty, $new_subtotal, $id_pesanan, $id_produk);
            }
            if (!mysqli_stmt_execute($stmt_upd_det)) throw new Exception(handle_db_error($conn, 'Gagal memperbarui item pesanan.'));

            // 3. Update pesanan total_harga
            $sql_upd_pes = "UPDATE pesanan SET total_harga = total_harga + ? WHERE id_pesanan = ?";
            $stmt_upd_pes = mysqli_prepare($conn, $sql_upd_pes);
            mysqli_stmt_bind_param($stmt_upd_pes, 'di', $delta_subtotal, $id_pesanan);
            if (!mysqli_stmt_execute($stmt_upd_pes)) throw new Exception(handle_db_error($conn, 'Gagal memperbarui total harga pesanan.'));

            // 4. Update stok bahan baku (BOM)
            $sql_bom = "SELECT id_bahan_baku, qty AS qty_bom FROM bom WHERE id_produk = ?";
            $stmt_bom = mysqli_prepare($conn, $sql_bom);
            mysqli_stmt_bind_param($stmt_bom, 'i', $id_produk);
            mysqli_stmt_execute($stmt_bom);
            $res_bom = mysqli_stmt_get_result($stmt_bom);

            while ($bom = mysqli_fetch_assoc($res_bom)) {
                $id_bahan = $bom['id_bahan_baku'];
                // Jika delta_qty positif, kurangi stok; jika negatif, tambah stok
                $pengurangan = $bom['qty_bom'] * $delta_qty;

                $sql_stok = "UPDATE bahan_baku SET stok = stok - ? WHERE id_bahan_baku = ?";
                $stmt_stok = mysqli_prepare($conn, $sql_stok);
                mysqli_stmt_bind_param($stmt_stok, 'di', $pengurangan, $id_bahan);
                if (!mysqli_stmt_execute($stmt_stok)) throw new Exception(handle_db_error($conn, 'Gagal memperbarui stok bahan baku.'));

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
        }

        mysqli_commit($conn);
        $_SESSION['flash_success'] = 'Jumlah kuantitas item berhasil diperbarui.';
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['flash_error'] = $e->getMessage();
    }

    header("Location: ../detail_pesananlihat.php?id=$id_pesanan");
    exit;
}
