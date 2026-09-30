<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pesanan = intval($_POST['id_pesanan'] ?? 0);
    $id_produk = intval($_POST['id_produk'] ?? 0);
    $qty = intval($_POST['qty'] ?? 0);

    if (!$id_pesanan || !$id_produk || $qty <= 0) {
        $_SESSION['flash_error'] = 'Data tidak lengkap atau qty tidak valid.';
        header("Location: ../detail_pesananlihat.php?id=$id_pesanan");
        exit;
    }

    mysqli_begin_transaction($conn);
    try {
        // 1. Ambil harga produk
        $sql_prod = "SELECT harga_jual FROM produk WHERE id_produk = ?";
        $stmt_prod = mysqli_prepare($conn, $sql_prod);
        mysqli_stmt_bind_param($stmt_prod, 'i', $id_produk);
        mysqli_stmt_execute($stmt_prod);
        $prod = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_prod));
        
        if (!$prod) throw new Exception('Produk tidak ditemukan.');
        
        $subtotal = $prod['harga_jual'] * $qty;

        // 2. Insert ke detail_pesanan
        $sql_ins = "INSERT INTO detail_pesanan (id_pesanan, id_produk, qty, subtotal) VALUES (?, ?, ?, ?)";
        $stmt_ins = mysqli_prepare($conn, $sql_ins);
        mysqli_stmt_bind_param($stmt_ins, 'iiid', $id_pesanan, $id_produk, $qty, $subtotal);
        if (!mysqli_stmt_execute($stmt_ins)) throw new Exception(handle_db_error($conn, 'Gagal menyimpan detail pesanan.'));

        // 3. Update total_harga pesanan
        $sql_upd = "UPDATE pesanan SET total_harga = total_harga + ? WHERE id_pesanan = ?";
        $stmt_upd = mysqli_prepare($conn, $sql_upd);
        mysqli_stmt_bind_param($stmt_upd, 'di', $subtotal, $id_pesanan);
        if (!mysqli_stmt_execute($stmt_upd)) throw new Exception(handle_db_error($conn, 'Gagal memperbarui total harga pesanan.'));

        // 4. Kurangi stok bahan baku (BOM)
        $sql_bom = "SELECT id_bahan_baku, qty AS qty_bom FROM bom WHERE id_produk = ?";
        $stmt_bom = mysqli_prepare($conn, $sql_bom);
        mysqli_stmt_bind_param($stmt_bom, 'i', $id_produk);
        mysqli_stmt_execute($stmt_bom);
        $res_bom = mysqli_stmt_get_result($stmt_bom);

        while ($bom = mysqli_fetch_assoc($res_bom)) {
            $id_bahan = $bom['id_bahan_baku'];
            $pengurangan = $bom['qty_bom'] * $qty;

            $sql_stok = "UPDATE bahan_baku SET stok = stok - ? WHERE id_bahan_baku = ?";
            $stmt_stok = mysqli_prepare($conn, $sql_stok);
            mysqli_stmt_bind_param($stmt_stok, 'di', $pengurangan, $id_bahan);
            if (!mysqli_stmt_execute($stmt_stok)) throw new Exception(handle_db_error($conn, 'Gagal mengurangi stok bahan baku.'));

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

        mysqli_commit($conn);
        $_SESSION['flash_success'] = 'Item produk berhasil ditambahkan.';
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['flash_error'] = $e->getMessage();
    }

    header("Location: ../detail_pesananlihat.php?id=$id_pesanan");
    exit;
}
