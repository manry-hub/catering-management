<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

// Otorisasi: Hanya Owner dan Admin yang dapat menambah pegawai
require_role(['Owner', 'Admin'], '../pegawailihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token('../pegawaitambah.php');

    $errors = [];
    $nama_pegawai = trim($_POST['nama_pegawai'] ?? '');
    $password     = $_POST['password'] ?? '';
    $jabatan      = trim($_POST['jabatan'] ?? '');

    if (empty($nama_pegawai)) {
        $errors[] = 'Nama Pegawai wajib diisi.';
    }
    if (empty($password)) {
        $errors[] = 'Password wajib diisi.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password minimal terdiri dari 6 karakter.';
    }
    if (empty($jabatan)) {
        $errors[] = 'Jabatan wajib diisi.';
    }

    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        header('Location: ../pegawaitambah.php');
        exit;
    }

    // Hash kata sandi dengan Bcrypt
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $sql  = "INSERT INTO pegawai (nama_pegawai, password, jabatan) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sss', $nama_pegawai, $hashed_password, $jabatan);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_success'] = 'Data pegawai berhasil ditambahkan dengan aman.';
    } else {
        $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menambahkan data pegawai.');
    }

    header('Location: ../pegawailihat.php');
    exit;
}
?>
