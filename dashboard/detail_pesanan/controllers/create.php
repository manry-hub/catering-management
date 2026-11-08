<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Kantin', 'Pegawai Catering'], '../../pesanan/pesananlihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pesanan = intval($_POST['id_pesanan'] ?? 0);
    $id_produk = intval($_POST['id_produk'] ?? 0);
    $qty = intval($_POST['qty'] ?? 0);

    verify_csrf_token("../detail_pesanantambah.php?id_pesanan=$id_pesanan");

    if (!$id_pesanan || !$id_produk || $qty <= 0) {
        $_SESSION['flash_error'] = 'Data tidak lengkap atau kuantitas tidak valid.';
        header("Location: ../detail_pesananlihat.php?id=$id_pesanan");
        exit;
    }

    mysqli_begin_transaction($conn);
    try {
        // 1. Verifikasi status pesanan (hanya pesanan Diproses yang boleh ditambah item)
        $sql_pes = "SELECT status_pesanan FROM pesanan WHERE id_pesanan = ?";
        $stmt_pes = mysqli_prepare($conn, $sql_pes);
        mysqli_stmt_bind_param($stmt_pes, 'i', $id_pesanan);
        mysqli_stmt_execute($stmt_pes);
        $pes = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_pes));
        if (!$pes) {
            throw new Exception('Pesanan tidak ditemukan.');
        }
        if ($pes['status_pesanan'] !== 'Diproses') {
            throw new Exception('Item tidak dapat ditambahkan karena pesanan sudah berstatus ' . $pes['status_pesanan'] . '.');
        }

        // 2. Ambil harga produk
        $sql_prod = "SELECT harga_jual, nama_produk FROM produk WHERE id_produk = ?";
        $stmt_prod = mysqli_prepare($conn, $sql_prod);
        mysqli_stmt_bind_param($stmt_prod, 'i', $id_produk);
        mysqli_stmt_execute($stmt_prod);
        $prod = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_prod));
        
        if (!$prod) {
            throw new Exception('Produk tidak ditemukan.');
        }
        
        $subtotal = $prod['harga_jual'] * $qty;

        // 3. Validasi ketersediaan stok bahan baku berdasarkan BOM
        $sql_bom = "SELECT b.id_bahan_baku, b.qty AS qty_bom, bb.nama_bahan, bb.stok 
                    FROM bom b 
                    JOIN bahan_baku bb ON b.id_bahan_baku = bb.id_bahan_baku 
                    WHERE b.id_produk = ?";
        $stmt_bom = mysqli_prepare($conn, $sql_bom);
        mysqli_stmt_bind_param($stmt_bom, 'i', $id_produk);
        mysqli_stmt_execute($stmt_bom);
        $res_bom = mysqli_stmt_get_result($stmt_bom);

        $bom_deductions = [];
        while ($bom = mysqli_fetch_assoc($res_bom)) {
            $required = $bom['qty_bom'] * $qty;
            if ($bom['stok'] < $required) {
                throw new Exception("Stok bahan baku '{$bom['nama_bahan']}' tidak mencukupi (Tersedia: {$bom['stok']}, Dibutuhkan: {$required}).");
            }
            $bom_deductions[] = [
                'id_bahan' => $bom['id_bahan_baku'],
                'amount'   => $required,
            ];
        }

        // 4. Cek apakah item produk sudah ada di detail_pesanan
        $sql_check = "SELECT qty, subtotal FROM detail_pesanan WHERE id_pesanan = ? AND id_produk = ?";
        $stmt_check = mysqli_prepare($conn, $sql_check);
        mysqli_stmt_bind_param($stmt_check, 'ii', $id_pesanan, $id_produk);
        mysqli_stmt_execute($stmt_check);
        $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_check));

        if ($existing) {
            $new_item_qty = $existing['qty'] + $qty;
            $new_item_subtotal = $existing['subtotal'] + $subtotal;
            $sql_ins = "UPDATE detail_pesanan SET qty = ?, subtotal = ? WHERE id_pesanan = ? AND id_produk = ?";
            $stmt_ins = mysqli_prepare($conn, $sql_ins);
            mysqli_stmt_bind_param($stmt_ins, 'idii', $new_item_qty, $new_item_subtotal, $id_pesanan, $id_produk);
        } else {
            $sql_ins = "INSERT INTO detail_pesanan (id_pesanan, id_produk, qty, subtotal) VALUES (?, ?, ?, ?)";
            $stmt_ins = mysqli_prepare($conn, $sql_ins);
            mysqli_stmt_bind_param($stmt_ins, 'iiid', $id_pesanan, $id_produk, $qty, $subtotal);
        }
        mysqli_stmt_execute($stmt_ins);

        // 5. Update total_harga pesanan
        $sql_upd = "UPDATE pesanan SET total_harga = total_harga + ? WHERE id_pesanan = ?";
        $stmt_upd = mysqli_prepare($conn, $sql_upd);
        mysqli_stmt_bind_param($stmt_upd, 'di', $subtotal, $id_pesanan);
        mysqli_stmt_execute($stmt_upd);

        // 6. Kurangi stok bahan baku (BOM)
        foreach ($bom_deductions as $deduction) {
            $sql_stok = "UPDATE bahan_baku SET stok = stok - ? WHERE id_bahan_baku = ? AND stok >= ?";
            $stmt_stok = mysqli_prepare($conn, $sql_stok);
            mysqli_stmt_bind_param($stmt_stok, 'did', $deduction['amount'], $deduction['id_bahan'], $deduction['amount']);
            mysqli_stmt_execute($stmt_stok);
            if (mysqli_stmt_affected_rows($stmt_stok) === 0) {
                throw new Exception('Gagal memotong stok: stok tidak mencukupi.');
            }

            // Update status ketersediaan
            $stmt_cek = mysqli_prepare($conn, "SELECT stok, stok_kritis FROM bahan_baku WHERE id_bahan_baku = ?");
            mysqli_stmt_bind_param($stmt_cek, 'i', $deduction['id_bahan']);
            mysqli_stmt_execute($stmt_cek);
            $bahan_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cek));
            if ($bahan_data) {
                $sisa = $bahan_data['stok'];
                $kritis = $bahan_data['stok_kritis'];
                $status = ($sisa <= 0) ? 'Habis' : (($sisa <= $kritis) ? 'Menipis' : 'Tersedia');
                $stmt_stat = mysqli_prepare($conn, "UPDATE bahan_baku SET status_ketersediaan = ? WHERE id_bahan_baku = ?");
                mysqli_stmt_bind_param($stmt_stat, 'si', $status, $deduction['id_bahan']);
                mysqli_stmt_execute($stmt_stat);
            }
        }

        mysqli_commit($conn);
        $_SESSION['flash_success'] = 'Item produk berhasil ditambahkan.';
    } catch (\Throwable $e) {
        mysqli_rollback($conn);
        $_SESSION['flash_error'] = handle_db_error($e, $e->getMessage());
    }

    header("Location: ../detail_pesananlihat.php?id=$id_pesanan");
    exit;
}
