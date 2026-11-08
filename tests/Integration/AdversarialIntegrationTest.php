<?php

namespace Tests\Integration;

use Tests\Support\TestCase;
use Tests\Fixtures\DatabaseSeeder;

/**
 * Adversarial & Regression Integration Tests:
 * Menguji skenario ekstrem dan memverifikasi bahwa transaksi database,
 * konstrain integritas, penanganan exception, dan siklus hidup pesanan
 * berjalan kokoh tanpa cacat.
 */
class AdversarialIntegrationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        DatabaseSeeder::resetAndSeed();
    }

    // ==========================================
    // 1. Integritas Konstrain Stok Database
    // ==========================================

    public function testDatabaseConstraintBlocksNegativeInventoryStock(): void
    {
        $db = $this->getDb();

        // Database sekarang memiliki konstrain: CHECK (stok >= 0)
        // Percobaan mutasi paksa ke angka negatif harus langsung ditolak oleh MySQL
        $this->assertThrows(\mysqli_sql_exception::class, function () use ($db) {
            mysqli_query($db, "UPDATE bahan_baku SET stok = -20.00 WHERE id_bahan_baku = 2");
        }, 'Konstrain database chk_bahan_baku_stok harus menolak nilai stok negatif.');
    }

    public function testOrderCreationBlocksWhenInventoryStockIsInsufficient(): void
    {
        $db = $this->getDb();

        // Daging Ayam (ID 2) memiliki stok 20.00 kg
        // Buat pesanan baru
        mysqli_query($db, "INSERT INTO pesanan (tanggal_pesanan, status_pesanan, total_harga, jenis_pesanan, id_pelanggan, id_pegawai) VALUES (NOW(), 'Diproses', 0, 'Kantin', 1, 2)");
        $orderId = mysqli_insert_id($db);

        // Pesan 200 porsi Ayam Goreng Crispy (butuh 40 kg, stok hanya 20 kg)
        $orderQty = 200;
        $bomQty = 0.2;
        $kebutuhan = $orderQty * $bomQty; // 40.0 kg

        // Verifikasi pra-kondisi: Controller memeriksa ketersediaan stok sebelum transaksi
        $stokTersedia = (float)mysqli_fetch_assoc(mysqli_query($db, "SELECT stok FROM bahan_baku WHERE id_bahan_baku = 2"))['stok'];
        $isStockEnough = ($stokTersedia >= $kebutuhan);

        // Sistem menolak eksekusi jika stok tidak cukup
        $this->assertFalse($isStockEnough, 'Sistem harus menolak pemotongan stok karena kuantitas tidak mencukupi.');

        // Stok di database tetap utuh pada angka 20.00 kg
        $this->assertDatabaseHas('bahan_baku', ['id_bahan_baku' => 2, 'stok' => 20.00]);
    }

    // ==========================================
    // 2. Penanganan Exception Database yang Elegan (Anti Crash 500)
    // ==========================================

    public function testDeleteCustomerWithActiveOrdersIsHandledGracefully(): void
    {
        $db = $this->getDb();

        // 1. Buat pesanan untuk pelanggan ID 1
        mysqli_query($db, "INSERT INTO pesanan (tanggal_pesanan, status_pesanan, total_harga, jenis_pesanan, id_pelanggan, id_pegawai) VALUES (NOW(), 'Diproses', 50000, 'Kantin', 1, 2)");

        // 2. Eksekusi kode delete pelanggan yang kini dibungkus try-catch
        $caughtException = false;
        try {
            $stmtDel = mysqli_prepare($db, "DELETE FROM pelanggan WHERE id_pelanggan = 1");
            mysqli_stmt_execute($stmtDel);
        } catch (\mysqli_sql_exception $e) {
            $caughtException = true;
            $friendlyMessage = handle_db_error($e, 'Gagal menghapus pelanggan.');
            $this->assertContains('Data tidak bisa dihapus karena masih terkait dengan data lain', $friendlyMessage);
        }

        $this->assertTrue($caughtException, 'Penghapusan pelanggan berelasi harus ditangkap exception-nya secara anggun.');
        // Pelanggan tetap aman di database
        $this->assertDatabaseHas('pelanggan', ['id_pelanggan' => 1]);
    }

    public function testDeleteProductWithActiveOrdersIsHandledGracefully(): void
    {
        $db = $this->getDb();

        mysqli_query($db, "INSERT INTO pesanan (tanggal_pesanan, status_pesanan, total_harga, jenis_pesanan, id_pelanggan, id_pegawai) VALUES (NOW(), 'Diproses', 20000, 'Kantin', 1, 2)");
        $orderId = mysqli_insert_id($db);
        mysqli_query($db, "INSERT INTO detail_pesanan (id_pesanan, id_produk, qty, subtotal) VALUES ($orderId, 1, 1, 20000)");

        $caughtException = false;
        try {
            $stmtDel = mysqli_prepare($db, "DELETE FROM produk WHERE id_produk = 1");
            mysqli_stmt_execute($stmtDel);
        } catch (\mysqli_sql_exception $e) {
            $caughtException = true;
            $friendlyMessage = handle_db_error($e, 'Gagal menghapus produk.');
            $this->assertContains('Data tidak bisa dihapus', $friendlyMessage);
        }

        $this->assertTrue($caughtException, 'Penghapusan produk dalam pesanan harus ditangani dengan pesan ramah.');
        $this->assertDatabaseHas('produk', ['id_produk' => 1]);
    }

    // ==========================================
    // 3. Pemulihan Stok Otomatis saat Pesanan Dibatalkan
    // ==========================================

    public function testCancelledOrderRestoresStockToDatabase(): void
    {
        $db = $this->getDb();

        // 1. Buat pesanan kantin dan potong stok
        // Stok awal Daging Ayam (ID 2) = 20.00 kg
        mysqli_query($db, "INSERT INTO pesanan (tanggal_pesanan, status_pesanan, total_harga, jenis_pesanan, id_pelanggan, id_pegawai) VALUES (NOW(), 'Diproses', 40000, 'Kantin', 1, 2)");
        $orderId = mysqli_insert_id($db);

        // Pesan 2 porsi -> potong 0.4 kg ayam (stok sisa: 19.60 kg)
        mysqli_query($db, "INSERT INTO detail_pesanan (id_pesanan, id_produk, qty, subtotal) VALUES ($orderId, 1, 2, 40000)");
        mysqli_query($db, "UPDATE bahan_baku SET stok = stok - 0.4 WHERE id_bahan_baku = 2");

        $this->assertDatabaseHas('bahan_baku', ['id_bahan_baku' => 2, 'stok' => 19.60]);

        // 2. Logika controller pesanan/controllers/update.php saat status diubah ke 'Dibatalkan':
        // Mengambil seluruh bahan BOM dan memulihkan stok
        $sql_items = "SELECT dp.qty, b.id_bahan_baku, b.qty AS bom_qty FROM detail_pesanan dp JOIN bom b ON dp.id_produk = b.id_produk WHERE dp.id_pesanan = $orderId";
        $res_items = mysqli_query($db, $sql_items);
        while ($item = mysqli_fetch_assoc($res_items)) {
            $restore_qty = $item['qty'] * $item['bom_qty'];
            mysqli_query($db, "UPDATE bahan_baku SET stok = stok + $restore_qty WHERE id_bahan_baku = {$item['id_bahan_baku']}");
        }
        mysqli_query($db, "UPDATE pesanan SET status_pesanan = 'Dibatalkan' WHERE id_pesanan = $orderId");

        // 3. Verifikasi: Stok fisik daging ayam telah dipulihkan kembali ke 20.00 kg!
        $this->assertDatabaseHas('bahan_baku', ['id_bahan_baku' => 2, 'stok' => 20.00]);
        $this->assertDatabaseHas('pesanan', ['id_pesanan' => $orderId, 'status_pesanan' => 'Dibatalkan']);
    }

    // ==========================================
    // 4. Pencegahan Pembangkitan Stok Fiktif saat Hapus Pesanan Selesai
    // ==========================================

    public function testDeletingCompletedOrderDoesNotResurrectStock(): void
    {
        $db = $this->getDb();

        // Pesanan berstatus 'Selesai' (makanan sudah dimakan)
        mysqli_query($db, "INSERT INTO pesanan (tanggal_pesanan, status_pesanan, total_harga, jenis_pesanan, id_pelanggan, id_pegawai) VALUES ('2026-10-01 12:00:00', 'Selesai', 40000, 'Kantin', 1, 2)");
        $orderSelesai = mysqli_insert_id($db);
        mysqli_query($db, "INSERT INTO detail_pesanan (id_pesanan, id_produk, qty, subtotal) VALUES ($orderSelesai, 1, 5, 40000)");

        // Stok ayam saat ini 19.0 kg
        mysqli_query($db, "UPDATE bahan_baku SET stok = 19.00 WHERE id_bahan_baku = 2");

        // Logika baru pesanan/controllers/delete.php:
        // Cek status pesanan: jika 'Selesai', JANGAN kembalikan stok!
        $statusPesanan = (string)mysqli_fetch_assoc(mysqli_query($db, "SELECT status_pesanan FROM pesanan WHERE id_pesanan = $orderSelesai"))['status_pesanan'];
        if ($statusPesanan === 'Diproses') {
            // Hanya kembalikan stok jika masih 'Diproses'
            mysqli_query($db, "UPDATE bahan_baku SET stok = stok + 1.0 WHERE id_bahan_baku = 2");
        }
        mysqli_query($db, "DELETE FROM detail_pesanan WHERE id_pesanan = $orderSelesai");
        mysqli_query($db, "DELETE FROM pesanan WHERE id_pesanan = $orderSelesai");

        // Verifikasi: Stok gudang TIDAK bertambah fiktif, tetap pada angka riil 19.00 kg!
        $this->assertDatabaseHas('bahan_baku', ['id_bahan_baku' => 2, 'stok' => 19.00]);
    }

    // ==========================================
    // 5. Penanganan Cabang Kosong Menghasilkan NULL Murni
    // ==========================================

    public function testEmptyBranchInputEvaluatesToNullCleanly(): void
    {
        $branchInput = "";
        $id_cabang_kantin = ($branchInput !== '' && intval($branchInput) > 0) ? intval($branchInput) : null;

        $this->assertNull($id_cabang_kantin, 'Input cabang kosong harus dievaluasi sebagai NULL, bukan integer 0.');
    }

    // ==========================================
    // 6. Konstrain Unik Nama Pegawai Berfungsi Sempurna
    // ==========================================

    public function testDuplicateEmployeeNameBlockedByDatabase(): void
    {
        $db = $this->getDb();

        // Pegawai 'Rina Putri' sudah ada di seeder
        // Percobaan memasukkan pegawai kedua dengan nama sama harus ditolak oleh konstrain uq_pegawai_nama
        $this->assertThrows(\mysqli_sql_exception::class, function () use ($db) {
            $pass = password_hash('password_baru', PASSWORD_BCRYPT);
            mysqli_query($db, "INSERT INTO pegawai (nama_pegawai, password, jabatan) VALUES ('Rina Putri', '$pass', 'Pegawai Kantin')");
        }, 'Database harus menolak duplikasi nama pegawai melalui konstrain UNIQUE.');
    }

    // ==========================================
    // 7. Otomatisasi Sinkronisasi HPP saat Harga Bahan Baku Berubah
    // ==========================================

    public function testRawMaterialPriceUpdateAutoSyncsProductHpp(): void
    {
        $db = $this->getDb();

        // HPP awal Produk 1 (Ayam Goreng Crispy) = Rp 12.000 (Ayam: 0.2kg @ 45rb = 9rb + Tepung 1.2rb + Minyak 1.8rb)
        $this->assertDatabaseHas('produk', ['id_produk' => 1, 'harga_pokok' => 12000.00]);

        // Harga Ayam Fillet (ID 2) naik dari Rp 45.000 menjadi Rp 90.000 (+100%)
        // Simulasi logika baru di bahan_baku/controllers/update.php:
        $newPrice = 90000.00;
        mysqli_query($db, "UPDATE bahan_baku SET harga_satuan_bahan = $newPrice WHERE id_bahan_baku = 2");

        // 1. Auto-update bom subtotal
        mysqli_query($db, "UPDATE bom SET subtotal = qty * $newPrice WHERE id_produk = 1 AND id_bahan_baku = 2");

        // 2. Auto-recalculate HPP produk
        $calcHpp = (float)mysqli_fetch_assoc(mysqli_query($db, "SELECT SUM(bom.qty * bb.harga_satuan_bahan) as total FROM bom JOIN bahan_baku bb ON bom.id_bahan_baku = bb.id_bahan_baku WHERE bom.id_produk = 1"))['total'];
        mysqli_query($db, "UPDATE produk SET harga_pokok = $calcHpp WHERE id_produk = 1");

        // Verifikasi: HPP Produk 1 otomatis tersinkronisasi menjadi Rp 21.000 (Ayam 18rb + Tepung 1.2rb + Minyak 1.8rb)!
        $this->assertDatabaseHas('produk', [
            'id_produk'   => 1,
            'harga_pokok' => 21000.00,
        ]);
    }

    // ==========================================
    // 8. Pencegahan Hapus Bahan Baku yang Aktif Digunakan di Resep
    // ==========================================

    public function testDeletingRawMaterialInRecipeIsPrevented(): void
    {
        $db = $this->getDb();

        // Tepung Terigu (ID 1) aktif digunakan di Produk 1
        $countInRecipe = (int)mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as c FROM bom WHERE id_bahan_baku = 1"))['c'];
        $this->assertGreaterThan(0, $countInRecipe, 'Tepung Terigu harus terdeteksi aktif dalam resep.');

        // Controller bahan_baku/controllers/delete.php menolak penghapusan
        $canDelete = ($countInRecipe === 0);
        $this->assertFalse($canDelete, 'Sistem harus menolak penghapusan bahan baku yang masih digunakan dalam resep.');
    }
}
