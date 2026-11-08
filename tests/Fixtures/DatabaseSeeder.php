<?php

namespace Tests\Fixtures;

use Tests\Support\DatabaseTester;

class DatabaseSeeder
{
    public static function resetAndSeed(): void
    {
        $conn = DatabaseTester::getConnection();

        // Disable foreign key checks for clean truncation
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0;");

        $tables = [
            'detail_pesanan',
            'pesanan',
            'bom',
            'produk',
            'bahan_baku',
            'catering',
            'cabang_kantin',
            'pelanggan',
            'pegawai',
        ];

        foreach ($tables as $t) {
            mysqli_query($conn, "TRUNCATE TABLE `$t`;");
        }

        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1;");

        // 1. Pegawai
        $passHash = password_hash('123456', PASSWORD_BCRYPT);
        $stmt_peg = mysqli_prepare($conn, "INSERT INTO pegawai (id_pegawai, nama_pegawai, jabatan, password) VALUES (?, ?, ?, ?)");
        $pegawai = [
            [1, 'Indri', 'Admin', $passHash],
            [2, 'Rina Putri', 'Pegawai Kantin', $passHash],
            [3, 'Dewi Lestari', 'Pegawai Catering', $passHash],
            [4, 'Siska Nur', 'Pegawai Dapur', $passHash],
            [5, 'Budi Owner', 'Owner', $passHash],
        ];
        foreach ($pegawai as $p) {
            mysqli_stmt_bind_param($stmt_peg, 'isss', $p[0], $p[1], $p[2], $p[3]);
            mysqli_stmt_execute($stmt_peg);
        }

        // 2. Cabang Kantin
        $stmt_cb = mysqli_prepare($conn, "INSERT INTO cabang_kantin (id_cabang_kantin, nama_cabang, alamat_cabang) VALUES (?, ?, ?)");
        $cabang = [
            [1, 'SMP Islam Al Azhar 2', 'Jl. Siaga Raya No. 2, Pasar Minggu, Jakarta Selatan'],
            [2, 'SMA Negeri 1 Jakarta', 'Jl. Budi Utomo No. 7, Jakarta Pusat'],
        ];
        foreach ($cabang as $c) {
            mysqli_stmt_bind_param($stmt_cb, 'iss', $c[0], $c[1], $c[2]);
            mysqli_stmt_execute($stmt_cb);
        }

        // 3. Pelanggan
        $stmt_pel = mysqli_prepare($conn, "INSERT INTO pelanggan (id_pelanggan, nama_pelanggan, no_telp, alamat_pelanggan) VALUES (?, ?, ?, ?)");
        $pelanggan = [
            [1, 'Budi Santoso', '081234567890', 'Jl. Cempaka No. 10 Jakarta'],
            [2, 'Siti Aminah', '089876543210', 'Jl. Melati No. 5 Jakarta'],
        ];
        foreach ($pelanggan as $pl) {
            mysqli_stmt_bind_param($stmt_pel, 'isss', $pl[0], $pl[1], $pl[2], $pl[3]);
            mysqli_stmt_execute($stmt_pel);
        }

        // 4. Bahan Baku
        $stmt_bb = mysqli_prepare($conn, "INSERT INTO bahan_baku (id_bahan_baku, nama_bahan, satuan, harga_satuan_bahan, stok, stok_kritis, status_ketersediaan) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $bahan = [
            [1, 'Tepung Terigu', 'kg', 12000.00, 50.00, 10.00, 'Tersedia'],
            [2, 'Daging Ayam Fillet', 'kg', 45000.00, 20.00, 5.00, 'Tersedia'],
            [3, 'Minyak Goreng', 'liter', 18000.00, 30.00, 8.00, 'Tersedia'],
            [4, 'Beras Pandan Wangi', 'kg', 15000.00, 100.00, 20.00, 'Tersedia'],
            [5, 'Bahan Menipis Test', 'kg', 10000.00, 4.00, 5.00, 'Menipis'],
            [6, 'Bahan Habis Test', 'kg', 10000.00, 0.00, 5.00, 'Habis'],
        ];
        foreach ($bahan as $b) {
            mysqli_stmt_bind_param($stmt_bb, 'issddds', $b[0], $b[1], $b[2], $b[3], $b[4], $b[5], $b[6]);
            mysqli_stmt_execute($stmt_bb);
        }

        // 5. Produk
        $stmt_pr = mysqli_prepare($conn, "INSERT INTO produk (id_produk, nama_produk, kategori, harga_jual, harga_pokok) VALUES (?, ?, ?, ?, ?)");
        $produk = [
            [1, 'Ayam Goreng Crispy', 'Makanan', 20000.00, 12000.00],
            [2, 'Nasi Putih Pulen', 'Makanan', 5000.00, 3000.00],
            [3, 'Produk Tanpa BOM', 'Snack', 10000.00, 0.00],
        ];
        foreach ($produk as $pr) {
            mysqli_stmt_bind_param($stmt_pr, 'issdd', $pr[0], $pr[1], $pr[2], $pr[3], $pr[4]);
            mysqli_stmt_execute($stmt_pr);
        }

        // 6. BOM (Bill of Materials)
        $stmt_bom = mysqli_prepare($conn, "INSERT INTO bom (id_produk, id_bahan_baku, qty, subtotal) VALUES (?, ?, ?, ?)");
        $boms = [
            // Ayam Goreng Crispy
            [1, 2, 0.2000, 9000.00], // 0.2kg Ayam @ 45000 = 9000
            [1, 1, 0.1000, 1200.00], // 0.1kg Tepung @ 12000 = 1200
            [1, 3, 0.1000, 1800.00], // 0.1L Minyak @ 18000 = 1800 (Total = 12000)
            // Nasi Putih Pulen
            [2, 4, 0.2000, 3000.00], // 0.2kg Beras @ 15000 = 3000
        ];
        foreach ($boms as $bm) {
            mysqli_stmt_bind_param($stmt_bom, 'iidd', $bm[0], $bm[1], $bm[2], $bm[3]);
            mysqli_stmt_execute($stmt_bom);
        }
    }
}
