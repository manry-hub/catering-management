<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Catering'], '../cateringlihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token('../cateringtambah.php');

    $errors = [];
    $tanggal_acara = trim($_POST['tanggal_acara'] ?? '');
    $lokasi_acara  = trim($_POST['lokasi_acara'] ?? '');

    if (empty($tanggal_acara)) {
        $errors[] = 'Tanggal Acara wajib diisi.';
    } elseif (strtotime($tanggal_acara) === false) {
        $errors[] = 'Format Tanggal Acara tidak valid.';
    }
    if (empty($lokasi_acara)) {
        $errors[] = 'Lokasi Acara wajib diisi.';
    }

    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        header('Location: ../cateringtambah.php');
        exit;
    }

    try {
        $sql  = "INSERT INTO catering (tanggal_acara, lokasi_acara) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $tanggal_acara, $lokasi_acara);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_success'] = 'Jadwal catering berhasil ditambahkan.';
        } else {
            $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menambahkan jadwal catering.');
        }
    } catch (\mysqli_sql_exception $e) {
        $_SESSION['flash_error'] = handle_db_error($e, 'Gagal menambahkan jadwal catering.');
    }

    header('Location: ../cateringlihat.php');
    exit;
}
