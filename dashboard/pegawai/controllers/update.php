<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';
include '../../../layouts/csrf.php';
include '../../../layouts/guard.php';

// Otorisasi: Hanya Owner dan Admin yang dapat mengedit data pegawai
require_role(['Owner', 'Admin'], '../pegawailihat.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pegawai = intval($_POST['id_pegawai'] ?? 0);
    verify_csrf_token('../pegawaiubah.php?id=' . urlencode($id_pegawai));

    $errors = [];
    $nama_pegawai = trim($_POST['nama_pegawai'] ?? '');
    $password     = $_POST['password'] ?? '';
    $jabatan      = trim($_POST['jabatan'] ?? '');

    $allowed_roles = ['Owner', 'Admin', 'Pegawai Kantin', 'Pegawai Catering', 'Pegawai Dapur'];

    if (empty($id_pegawai)) {
        $errors[] = 'ID Pegawai tidak ditemukan.';
    }
    if (empty($nama_pegawai)) {
        $errors[] = 'Nama Pegawai wajib diisi.';
    }
    if (empty($jabatan)) {
        $errors[] = 'Jabatan wajib diisi.';
    } elseif (!in_array($jabatan, $allowed_roles, true)) {
        $errors[] = 'Jabatan tidak valid.';
    }
    if (!empty($password) && strlen($password) < 6) {
        $errors[] = 'Password minimal terdiri dari 6 karakter.';
    }

    // Hirarki: Cek target user
    $cek_target = mysqli_prepare($conn, "SELECT jabatan FROM pegawai WHERE id_pegawai = ?");
    mysqli_stmt_bind_param($cek_target, 'i', $id_pegawai);
    mysqli_stmt_execute($cek_target);
    $target_user = mysqli_fetch_assoc(mysqli_stmt_get_result($cek_target));

    if (!$target_user) {
        $errors[] = 'Data pegawai tidak ditemukan.';
    } else {
        // Admin tidak boleh mengedit akun Owner
        if ($target_user['jabatan'] === 'Owner' && ($_SESSION['jabatan'] ?? '') !== 'Owner') {
            $errors[] = 'Hanya Owner yang dapat mengubah data akun Owner.';
        }
        // Non-Owner tidak boleh menaikkan jabatan siapapun menjadi Owner
        if ($jabatan === 'Owner' && ($_SESSION['jabatan'] ?? '') !== 'Owner') {
            $errors[] = 'Hanya Owner yang dapat menetapkan jabatan Owner.';
        }
    }

    // Cek keunikan nama_pegawai (jika nama diubah)
    if (empty($errors)) {
        $stmt_check = mysqli_prepare($conn, "SELECT id_pegawai FROM pegawai WHERE nama_pegawai = ? AND id_pegawai != ?");
        mysqli_stmt_bind_param($stmt_check, 'si', $nama_pegawai, $id_pegawai);
        mysqli_stmt_execute($stmt_check);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_check))) {
            $errors[] = 'Nama Pegawai sudah digunakan oleh akun lain.';
        }
    }

    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        header('Location: ../pegawaiubah.php?id=' . urlencode($id_pegawai));
        exit;
    }

    try {
        if (!empty($password)) {
            // Enkripsi kata sandi baru dengan Bcrypt
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $sql  = "UPDATE pegawai SET nama_pegawai = ?, password = ?, jabatan = ? WHERE id_pegawai = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'sssi', $nama_pegawai, $hashed_password, $jabatan, $id_pegawai);
        } else {
            // Pertahankan kata sandi yang sudah ada
            $sql  = "UPDATE pegawai SET nama_pegawai = ?, jabatan = ? WHERE id_pegawai = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'ssi', $nama_pegawai, $jabatan, $id_pegawai);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            // Jika mengedit akun sendiri, perbarui data sesi
            if ($id_pegawai == $_SESSION['id_pegawai']) {
                $_SESSION['nama_pegawai'] = $nama_pegawai;
                $_SESSION['jabatan']      = $jabatan;
            }
            $_SESSION['flash_success'] = 'Data pegawai berhasil diperbarui.';
        } else {
            $_SESSION['flash_error'] = handle_db_error($conn, 'Gagal mengubah data pegawai.');
        }
    } catch (\mysqli_sql_exception $e) {
        $_SESSION['flash_error'] = handle_db_error($e, 'Gagal mengubah data pegawai.');
    }

    header('Location: ../pegawailihat.php');
    exit;
}
?>
