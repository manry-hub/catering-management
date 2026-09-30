<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk     = intval($_POST['id_produk'] ?? 0);
    $id_bahan_baku = intval($_POST['id_bahan_baku'] ?? 0);

    if (empty($id_produk) || empty($id_bahan_baku)) {
        $_SESSION['flash_error'] = 'Data tidak valid.';
        header('Location: ../bomdetail.php?id=' . $id_produk);
        exit;
    }

    $sql  = "DELETE FROM bom WHERE id_produk = ? AND id_bahan_baku = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $id_produk, $id_bahan_baku);

    if (mysqli_stmt_execute($stmt)) {
        // Auto-sync harga_pokok
        syncHargaPokok($conn, $id_produk);
        $_SESSION['flash_success'] = 'Bahan baku berhasil dihapus dari resep.';
    } else {
        $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menghapus bahan baku.');
    }

    header('Location: ../bomdetail.php?id=' . $id_produk);
    exit;
}

function syncHargaPokok($conn, $id_produk) {
    $stmt = mysqli_prepare($conn, "SELECT SUM(bom.qty * bb.harga_satuan_bahan) AS total FROM bom JOIN bahan_baku bb ON bom.id_bahan_baku = bb.id_bahan_baku WHERE bom.id_produk = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id_produk);
    mysqli_stmt_execute($stmt);
    $total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'] ?? 0;
    $upd = mysqli_prepare($conn, "UPDATE produk SET harga_pokok = ? WHERE id_produk = ?");
    mysqli_stmt_bind_param($upd, 'di', $total, $id_produk);
    mysqli_stmt_execute($upd);
}
