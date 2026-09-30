<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pesanan = intval($_POST['id_pesanan'] ?? 0);

    if (empty($id_pesanan)) {
        $_SESSION['flash_error'] = 'ID Pesanan tidak valid.';
        header('Location: ../pesananlihat.php');
        exit;
    }

    // ========== BEGIN TRANSACTION ==========
    mysqli_begin_transaction($conn);
    try {
        // 1. Ambil semua detail_pesanan
        $sql_detail = "SELECT dp.id_produk, dp.qty FROM detail_pesanan dp WHERE dp.id_pesanan = ?";
        $stmt_detail = mysqli_prepare($conn, $sql_detail);
        mysqli_stmt_bind_param($stmt_detail, 'i', $id_pesanan);
        mysqli_stmt_execute($stmt_detail);
        $res_detail = mysqli_stmt_get_result($stmt_detail);

        // 2. Untuk setiap detail, kembalikan stok bahan baku via BOM
        while ($detail = mysqli_fetch_assoc($res_detail)) {
            $pid = $detail['id_produk'];
            $qty_pesanan = $detail['qty'];

            // Baca BOM
            $sql_bom = "SELECT id_bahan_baku, qty AS qty_bom FROM bom WHERE id_produk = ?";
            $stmt_bom = mysqli_prepare($conn, $sql_bom);
            mysqli_stmt_bind_param($stmt_bom, 'i', $pid);
            mysqli_stmt_execute($stmt_bom);
            $res_bom = mysqli_stmt_get_result($stmt_bom);

            while ($bom = mysqli_fetch_assoc($res_bom)) {
                $id_bahan = $bom['id_bahan_baku'];
                $pengembalian = $bom['qty_bom'] * $qty_pesanan;

                // Kembalikan stok
                $sql_upd = "UPDATE bahan_baku SET stok = stok + ? WHERE id_bahan_baku = ?";
                $stmt_upd = mysqli_prepare($conn, $sql_upd);
                mysqli_stmt_bind_param($stmt_upd, 'di', $pengembalian, $id_bahan);
                if (!mysqli_stmt_execute($stmt_upd)) {
                    throw new Exception(handle_db_error($conn, 'Gagal mengembalikan stok bahan baku ID ' . $id_bahan));
                }

                // Update status_ketersediaan
                $sql_stok = "SELECT stok, stok_kritis FROM bahan_baku WHERE id_bahan_baku = ?";
                $stmt_stok = mysqli_prepare($conn, $sql_stok);
                mysqli_stmt_bind_param($stmt_stok, 'i', $id_bahan);
                mysqli_stmt_execute($stmt_stok);
                $res_stok = mysqli_stmt_get_result($stmt_stok);
                $bahan_data = mysqli_fetch_assoc($res_stok);

                if ($bahan_data) {
                    $sisa = $bahan_data['stok'];
                    $kritis = $bahan_data['stok_kritis'];
                    if ($sisa <= 0) $new_status = 'Habis';
                    elseif ($sisa <= $kritis) $new_status = 'Menipis';
                    else $new_status = 'Tersedia';

                    $sql_status = "UPDATE bahan_baku SET status_ketersediaan = ? WHERE id_bahan_baku = ?";
                    $stmt_status = mysqli_prepare($conn, $sql_status);
                    mysqli_stmt_bind_param($stmt_status, 'si', $new_status, $id_bahan);
                    mysqli_stmt_execute($stmt_status);
                }
            }
        }

        // 3. DELETE detail_pesanan
        $sql_del_detail = "DELETE FROM detail_pesanan WHERE id_pesanan = ?";
        $stmt_del_detail = mysqli_prepare($conn, $sql_del_detail);
        mysqli_stmt_bind_param($stmt_del_detail, 'i', $id_pesanan);
        if (!mysqli_stmt_execute($stmt_del_detail)) {
            throw new Exception(handle_db_error($conn, 'Gagal menghapus detail pesanan.'));
        }

        // 4. DELETE pesanan
        $sql_del = "DELETE FROM pesanan WHERE id_pesanan = ?";
        $stmt_del = mysqli_prepare($conn, $sql_del);
        mysqli_stmt_bind_param($stmt_del, 'i', $id_pesanan);
        if (!mysqli_stmt_execute($stmt_del)) {
            throw new Exception(handle_db_error($conn, 'Gagal menghapus pesanan.'));
        }

        // ========== COMMIT ==========
        mysqli_commit($conn);
        $_SESSION['flash_success'] = 'Pesanan berhasil dihapus dan stok dikembalikan.';

    } catch (Exception $e) {
        // ========== ROLLBACK ==========
        mysqli_rollback($conn);
        $_SESSION['flash_error'] = 'Transaksi gagal: ' . $e->getMessage();
    }

    header('Location: ../pesananlihat.php');
    exit;
}
