<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Dapur'], '../../produk/produklihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk     = intval($_POST['id_produk'] ?? 0);
    $id_bahan_baku = intval($_POST['id_bahan_baku'] ?? 0);
    $qty           = floatval($_POST['qty'] ?? 0);

    verify_csrf_token('../bomdetail.php?id=' . $id_produk);

    $errors = [];
    if (empty($id_produk)) $errors[] = 'Produk tidak valid.';
    if (empty($id_bahan_baku)) $errors[] = 'Bahan Baku tidak valid.';
    if ($qty <= 0) $errors[] = 'Qty harus lebih dari 0.';

    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        header('Location: ../bomdetail.php?id=' . $id_produk);
        exit;
    }

    try {
        // Ambil harga_satuan_bahan terkini
        $stmt_harga = mysqli_prepare($conn, "SELECT harga_satuan_bahan FROM bahan_baku WHERE id_bahan_baku = ?");
        mysqli_stmt_bind_param($stmt_harga, 'i', $id_bahan_baku);
        mysqli_stmt_execute($stmt_harga);
        $harga_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_harga));
        $harga_satuan = $harga_data ? floatval($harga_data['harga_satuan_bahan']) : 0;
        $subtotal = round($qty * $harga_satuan, 2);

        // Update bom
        $sql = "UPDATE bom SET qty = ?, subtotal = ? WHERE id_produk = ? AND id_bahan_baku = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ddii', $qty, $subtotal, $id_produk, $id_bahan_baku);
        mysqli_stmt_execute($stmt);

        // Recalculate HPP (harga_pokok) for the product
        $calc_stmt = mysqli_prepare($conn, "SELECT SUM(subtotal) AS total_bom FROM bom WHERE id_produk = ?");
        mysqli_stmt_bind_param($calc_stmt, 'i', $id_produk);
        mysqli_stmt_execute($calc_stmt);
        $total_row = mysqli_fetch_assoc(mysqli_stmt_get_result($calc_stmt));
        $new_hpp = $total_row['total_bom'] ? floatval($total_row['total_bom']) : 0;

        $upd_prod = mysqli_prepare($conn, "UPDATE produk SET harga_pokok = ? WHERE id_produk = ?");
        mysqli_stmt_bind_param($upd_prod, 'di', $new_hpp, $id_produk);
        mysqli_stmt_execute($upd_prod);

        $_SESSION['flash_success'] = 'Bahan baku resep berhasil diperbarui.';
        header('Location: ../bomdetail.php?id=' . $id_produk);
        exit;
    } catch (\mysqli_sql_exception $e) {
        handle_db_error($e, '../bomdetail.php?id=' . $id_produk);
    }
}

header('Location: ../../produk/produklihat.php');
exit;
