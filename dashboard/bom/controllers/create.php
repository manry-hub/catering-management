<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $id_produk     = intval($_POST['id_produk'] ?? 0);
    $id_bahan_baku = intval($_POST['id_bahan_baku'] ?? 0);
    $qty           = floatval($_POST['qty'] ?? 0);

    if (empty($id_produk)) $errors[] = 'Produk tidak valid.';
    if (empty($id_bahan_baku)) $errors[] = 'Bahan Baku wajib dipilih.';
    if ($qty <= 0) $errors[] = 'Qty harus lebih dari 0.';

    // Check duplicate
    if (empty($errors)) {
        $check = mysqli_prepare($conn, "SELECT 1 FROM bom WHERE id_produk = ? AND id_bahan_baku = ?");
        mysqli_stmt_bind_param($check, 'ii', $id_produk, $id_bahan_baku);
        mysqli_stmt_execute($check);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($check))) {
            $errors[] = 'Bahan baku ini sudah ada di resep. Silakan ubah qty-nya.';
        }
    }

    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        header('Location: ../bomdetail.php?id=' . $id_produk);
        exit;
    }

    // Ambil harga_satuan_bahan
    $stmt_harga = mysqli_prepare($conn, "SELECT harga_satuan_bahan FROM bahan_baku WHERE id_bahan_baku = ?");
    mysqli_stmt_bind_param($stmt_harga, 'i', $id_bahan_baku);
    mysqli_stmt_execute($stmt_harga);
    $harga_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_harga));
    $harga_satuan = $harga_data ? $harga_data['harga_satuan_bahan'] : 0;
    $subtotal = $qty * $harga_satuan;

    $sql  = "INSERT INTO bom (id_produk, id_bahan_baku, qty, subtotal) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'iidd', $id_produk, $id_bahan_baku, $qty, $subtotal);

    if (mysqli_stmt_execute($stmt)) {
        // Auto-sync harga_pokok
        syncHargaPokok($conn, $id_produk);
        $_SESSION['flash_success'] = 'Bahan baku berhasil ditambahkan ke resep.';
    } else {
        $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menambahkan bahan baku.');
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
