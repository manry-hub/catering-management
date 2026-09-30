<?php
include '../../../layouts/config.php';
include '../../../layouts/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pesanan       = intval($_POST['id_pesanan'] ?? 0);
    $status_pesanan   = trim($_POST['status_pesanan'] ?? '');
    $id_pelanggan     = intval($_POST['id_pelanggan'] ?? 0);
    $id_cabang_kantin = isset($_POST['id_cabang_kantin']) ? intval($_POST['id_cabang_kantin']) : null;
    $catatan          = trim($_POST['catatan'] ?? '');
    
    // Catering fields
    $tanggal_acara    = trim($_POST['tanggal_acara'] ?? '');
    $lokasi_acara     = trim($_POST['lokasi_acara'] ?? '');

    $valid_statuses = ['Diproses', 'Selesai', 'Dibatalkan'];

    if (empty($id_pesanan) || empty($id_pelanggan)) {
        $_SESSION['flash_error'] = 'Data pesanan tidak lengkap.';
        header('Location: ../pesananlihat.php');
        exit;
    }
    if (!in_array($status_pesanan, $valid_statuses)) {
        $_SESSION['flash_error'] = 'Status pesanan tidak valid.';
        header('Location: ../pesananubah.php?id=' . urlencode($id_pesanan));
        exit;
    }

    // Ambil data pesanan untuk tahu jenisnya
    $sql_cek = "SELECT jenis_pesanan, id_catering FROM pesanan WHERE id_pesanan = ?";
    $stmt_cek = mysqli_prepare($conn, $sql_cek);
    mysqli_stmt_bind_param($stmt_cek, 'i', $id_pesanan);
    mysqli_stmt_execute($stmt_cek);
    $data_pesanan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cek));

    if (!$data_pesanan) {
        $_SESSION['flash_error'] = 'Pesanan tidak ditemukan.';
        header('Location: ../pesananlihat.php');
        exit;
    }

    mysqli_begin_transaction($conn);
    try {
        if ($data_pesanan['jenis_pesanan'] === 'Catering' && !empty($data_pesanan['id_catering'])) {
            // Update catering table
            $sql_cat = "UPDATE catering SET tanggal_acara = ?, lokasi_acara = ? WHERE id_catering = ?";
            $stmt_cat = mysqli_prepare($conn, $sql_cat);
            mysqli_stmt_bind_param($stmt_cat, 'ssi', $tanggal_acara, $lokasi_acara, $data_pesanan['id_catering']);
            if (!mysqli_stmt_execute($stmt_cat)) {
                throw new Exception(handle_db_error($conn, 'Gagal mengupdate detail catering.'));
            }
        } else {
            // Ensure catering fields are null if not catering, and id_cabang_kantin is properly updated
            if (empty($id_cabang_kantin)) {
                throw new Exception('Cabang Kantin wajib diisi untuk jenis pesanan Kantin.');
            }
        }

        // Jika Catering, id_cabang_kantin biasanya NULL
        if ($data_pesanan['jenis_pesanan'] === 'Catering') {
            $id_cabang_kantin = null;
        }

        // Update pesanan table
        $sql  = "UPDATE pesanan SET status_pesanan = ?, id_pelanggan = ?, id_cabang_kantin = ?, catatan = ? WHERE id_pesanan = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'siisi', $status_pesanan, $id_pelanggan, $id_cabang_kantin, $catatan, $id_pesanan);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(handle_db_error($conn, 'Gagal mengupdate tabel pesanan.'));
        }

        mysqli_commit($conn);
        $_SESSION['flash_success'] = 'Data pesanan berhasil diubah.';
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['flash_error'] = $e->getMessage();
    }

    header('Location: ../pesananlihat.php');
    exit;
}
