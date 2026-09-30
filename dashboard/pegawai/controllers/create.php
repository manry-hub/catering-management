<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $nama_pegawai = trim($_POST['nama_pegawai'] ?? '');
    $password     = $_POST['password'] ?? '';
    $jabatan      = trim($_POST['jabatan'] ?? '');

    if (empty($nama_pegawai)) {
        $errors[] = 'Nama Pegawai wajib diisi.';
    }
    if (empty($password)) {
        $errors[] = 'Password wajib diisi.';
    }
    if (empty($jabatan)) {
        $errors[] = 'Jabatan wajib diisi.';
    }

    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        header('Location: ../pegawaitambah.php');
        exit;
    }

    $sql  = "INSERT INTO pegawai (nama_pegawai, password, jabatan) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sss', $nama_pegawai, $password, $jabatan);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_success'] = 'Data pegawai berhasil ditambahkan.';
    } else {
        $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal menambahkan data pegawai.');
    }

    header('Location: ../pegawailihat.php');
    exit;
}
?>
