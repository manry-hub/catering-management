<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $id_catering   = $_POST['id_catering'] ?? 0;
    $tanggal_acara = trim($_POST['tanggal_acara'] ?? '');
    $lokasi_acara  = trim($_POST['lokasi_acara'] ?? '');
    $catatan       = trim($_POST['catatan'] ?? '');

    if (empty($id_catering)) {
        $errors[] = 'ID Catering tidak ditemukan.';
    }
    if (empty($tanggal_acara)) {
        $errors[] = 'Tanggal Acara wajib diisi.';
    }
    if (empty($lokasi_acara)) {
        $errors[] = 'Lokasi Acara wajib diisi.';
    }


    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        header('Location: ../cateringubah.php?id=' . urlencode($id_catering));
        exit;
    }

    $sql  = "UPDATE catering SET tanggal_acara = ?, lokasi_acara = ?, catatan = ? WHERE id_catering = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sssi', $tanggal_acara, $lokasi_acara, $catatan, $id_catering);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_success'] = 'Jadwal catering berhasil diubah.';
    } else {
        $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal mengubah jadwal catering.');
    }

    header('Location: ../cateringlihat.php');
    exit;
}
