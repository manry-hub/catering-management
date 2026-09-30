<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk = intval($_POST['id_produk'] ?? 0);
    $total_bom = floatval($_POST['total_bom'] ?? 0);

    if (empty($id_produk)) {
        $_SESSION['flash_error'] = 'ID Produk tidak valid.';
        header('Location: ../../produk/produklihat.php');
        exit;
    }

    // Recalculate from DB to prevent manipulation
    $stmt = mysqli_prepare($conn, "
        SELECT SUM(subtotal) AS total 
        FROM bom 
        WHERE id_produk = ?
    ");
    mysqli_stmt_bind_param($stmt, 'i', $id_produk);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    $calculated_total = $row['total'] ?? 0;

    $upd = mysqli_prepare($conn, "UPDATE produk SET harga_pokok = ? WHERE id_produk = ?");
    mysqli_stmt_bind_param($upd, 'di', $calculated_total, $id_produk);

    if (mysqli_stmt_execute($upd)) {
        $_SESSION['flash_success'] = 'Harga Pokok berhasil disinkronkan dengan total BOM (Rp ' . number_format($calculated_total, 0, ',', '.') . ').';
    } else {
        $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menyinkronkan harga pokok.');
    }

    header('Location: ../bomdetail.php?id=' . $id_produk);
    exit;
}
