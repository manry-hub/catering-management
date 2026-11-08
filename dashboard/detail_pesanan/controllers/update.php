<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Kantin', 'Pegawai Catering'], '../../pesanan/pesananlihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pesanan = intval($_POST['id_pesanan'] ?? 0);
    $id_produk = intval($_POST['id_produk'] ?? 0);
    $new_qty = intval($_POST['qty'] ?? 0);

    verify_csrf_token("../detail_pesananlihat.php?id=$id_pesanan");

    if (!$id_pesanan || !$id_produk || $new_qty <= 0) {
        $_SESSION['flash_error'] = 'Data tidak lengkap atau kuantitas tidak valid.';
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
            throw new Exception('Item tidak dapat diubah karena pesanan sudah berstatus ' . $pes['status_pesanan'] . '.');
        }

        // 2. Ambil detail_pesanan lama beserta harga produk
        $sql_det = "SELECT dp.id_pesanan, dp.id_produk, dp.qty, pr.harga_jual 
                    FROM detail_pesanan dp 
                    JOIN produk pr ON dp.id_produk = pr.id_produk 
                    WHERE dp.id_pesanan = ? AND dp.id_produk = ?";
        $stmt_det = mysqli_prepare($conn, $sql_det);
        mysqli_stmt_bind_param($stmt_det, 'ii', $id_pesanan, $id_produk);
        mysqli_stmt_execute($stmt_det);
        $det = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_det));

        if (!$det) {
            throw new Exception('Detail pesanan tidak ditemukan.');
        }

        $old_qty = $det['qty'];
        $harga_jual = $det['harga_jual'];
        $delta_qty = $new_qty - $old_qty;

        if ($delta_qty != 0) {
            $delta_subtotal = $delta_qty * $harga_jual;
            $new_subtotal = $new_qty * $harga_jual;

            // 3. Jika kuantitas bertambah, cek ketersediaan stok bahan baku
            if ($delta_qty > 0) {
                $sql_bom = "SELECT b.id_bahan_baku, b.qty AS qty_bom, bb.nama_bahan, bb.stok 
                            FROM bom b 
                            JOIN bahan_baku bb ON b.id_bahan_baku = bb.id_bahan_baku 
                            WHERE b.id_produk = ?";
                $stmt_bom = mysqli_prepare($conn, $sql_bom);
                mysqli_stmt_bind_param($stmt_bom, 'i', $id_produk);
                mysqli_stmt_execute($stmt_bom);
                $res_bom = mysqli_stmt_get_result($stmt_bom);

                while ($bom = mysqli_fetch_assoc($res_bom)) {
                    $needed = $bom['qty_bom'] * $delta_qty;
                    if ($bom['stok'] < $needed) {
                        throw new Exception("Stok bahan baku '{$bom['nama_bahan']}' tidak mencukupi untuk penambahan kuantitas (Tersedia: {$bom['stok']}, Tambahan dibutuhkan: {$needed}).");
                    }
                }
            }

            // 4. Update detail_pesanan
            $sql_upd_det = "UPDATE detail_pesanan SET qty = ?, subtotal = ? WHERE id_pesanan = ? AND id_produk = ?";
            $stmt_upd_det = mysqli_prepare($conn, $sql_upd_det);
            mysqli_stmt_bind_param($stmt_upd_det, 'idii', $new_qty, $new_subtotal, $id_pesanan, $id_produk);
            mysqli_stmt_execute($stmt_upd_det);

            // 5. Update pesanan total_harga
            $sql_upd_pes = "UPDATE pesanan SET total_harga = total_harga + ? WHERE id_pesanan = ?";
            $stmt_upd_pes = mysqli_prepare($conn, $sql_upd_pes);
            mysqli_stmt_bind_param($stmt_upd_pes, 'di', $delta_subtotal, $id_pesanan);
            mysqli_stmt_execute($stmt_upd_pes);

            // 6. Update stok bahan baku (BOM)
            $sql_bom2 = "SELECT id_bahan_baku, qty AS qty_bom FROM bom WHERE id_produk = ?";
            $stmt_bom2 = mysqli_prepare($conn, $sql_bom2);
            mysqli_stmt_bind_param($stmt_bom2, 'i', $id_produk);
            mysqli_stmt_execute($stmt_bom2);
            $res_bom2 = mysqli_stmt_get_result($stmt_bom2);

            while ($bom = mysqli_fetch_assoc($res_bom2)) {
                $id_bahan = $bom['id_bahan_baku'];
                $perubahan = $bom['qty_bom'] * $delta_qty;

                if ($delta_qty > 0) {
                    $sql_stok = "UPDATE bahan_baku SET stok = stok - ? WHERE id_bahan_baku = ? AND stok >= ?";
                    $stmt_stok = mysqli_prepare($conn, $sql_stok);
                    mysqli_stmt_bind_param($stmt_stok, 'did', $perubahan, $id_bahan, $perubahan);
                } else {
                    $pengembalian = abs($perubahan);
                    $sql_stok = "UPDATE bahan_baku SET stok = stok + ? WHERE id_bahan_baku = ?";
                    $stmt_stok = mysqli_prepare($conn, $sql_stok);
                    mysqli_stmt_bind_param($stmt_stok, 'di', $pengembalian, $id_bahan);
                }
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
        }

        mysqli_commit($conn);
        $_SESSION['flash_success'] = 'Jumlah kuantitas item berhasil diperbarui.';
    } catch (\Throwable $e) {
        mysqli_rollback($conn);
        $_SESSION['flash_error'] = handle_db_error($e, $e->getMessage());
    }

    header("Location: ../detail_pesananlihat.php?id=$id_pesanan");
    exit;
}
