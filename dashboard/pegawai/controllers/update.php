<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $id_pegawai   = $_POST['id_pegawai'] ?? 0;
    $nama_pegawai = trim($_POST['nama_pegawai'] ?? '');
    $password     = $_POST['password'] ?? '';
    $jabatan      = trim($_POST['jabatan'] ?? '');

    if (empty($id_pegawai)) {
        $errors[] = 'ID Pegawai tidak ditemukan.';
    }
    if (empty($nama_pegawai)) {
        $errors[] = 'Nama Pegawai wajib diisi.';
    }
    if (empty($jabatan)) {
        $errors[] = 'Jabatan wajib diisi.';
    }

    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        header('Location: ../pegawaiubah.php?id=' . urlencode($id_pegawai));
        exit;
    }

    if (!empty($password)) {
        $sql  = "UPDATE pegawai SET nama_pegawai = ?, password = ?, jabatan = ? WHERE id_pegawai = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sssi', $nama_pegawai, $password, $jabatan, $id_pegawai);
    } else {
        $sql  = "UPDATE pegawai SET nama_pegawai = ?, jabatan = ? WHERE id_pegawai = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssi', $nama_pegawai, $jabatan, $id_pegawai);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_success'] = 'Data pegawai berhasil diubah.';
    } else {
        $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal mengubah data pegawai.');
    }

    header('Location: ../pegawailihat.php');
    exit;
}
?>
