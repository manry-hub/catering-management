<?php

namespace Tests\Integration;

use Tests\Support\TestCase;
use Tests\Fixtures\DatabaseSeeder;

class OrderIntegrationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        DatabaseSeeder::resetAndSeed();
        $this->resetGlobals();
    }

    // ==========================================
    // Test to Pass (Positive Testing)
    // ==========================================

    public function testCreateCanteenOrderAndDeductStockCommitSuccess(): void
    {
        $db = $this->getDb();

        // 1. Verifikasi stok awal bahan baku sebelum transaksi
        // Bahan 1: Tepung (50.00), Bahan 2: Ayam (20.00), Bahan 3: Minyak (30.00)
        $this->assertDatabaseHas('bahan_baku', ['id_bahan_baku' => 1, 'stok' => 50.00]);
        $this->assertDatabaseHas('bahan_baku', ['id_bahan_baku' => 2, 'stok' => 20.00]);
        $this->assertDatabaseHas('bahan_baku', ['id_bahan_baku' => 3, 'stok' => 30.00]);

        // 2. Mulai Transaksi ACID
        mysqli_begin_transaction($db);

        try {
            // A. Buat Header Pesanan Kantin
            $stmtOrder = mysqli_prepare($db, "
                INSERT INTO pesanan (tanggal_pesanan, status_pesanan, total_harga, jenis_pesanan, id_pelanggan, id_pegawai, id_cabang_kantin)
                VALUES (NOW(), 'Diproses', 0.00, 'Kantin', 1, 2, 1)
            ");
            mysqli_stmt_execute($stmtOrder);
            $idPesanan = mysqli_insert_id($db);

            // B. Tambahkan Detail Pesanan: 10 porsi Ayam Goreng Crispy (id_produk = 1, harga_jual = 20.000)
            $idProduk = 1;
            $orderQty = 10;
            $subtotal = $orderQty * 20000.00; // 200.000

            $stmtDetail = mysqli_prepare($db, "INSERT INTO detail_pesanan (id_pesanan, id_produk, qty, subtotal) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmtDetail, 'iiid', $idPesanan, $idProduk, $orderQty, $subtotal);
            mysqli_stmt_execute($stmtDetail);

            // C. Update total_harga pesanan
            $stmtUpdOrder = mysqli_prepare($db, "UPDATE pesanan SET total_harga = ? WHERE id_pesanan = ?");
            mysqli_stmt_bind_param($stmtUpdOrder, 'di', $subtotal, $idPesanan);
            mysqli_stmt_execute($stmtUpdOrder);

            // D. Potong stok bahan baku berdasarkan resep BOM
            // Produk 1 resep: Ayam 0.2kg, Tepung 0.1kg, Minyak 0.1L
            // Kebutuhan untuk 10 porsi: Ayam = 2.0kg, Tepung = 1.0kg, Minyak = 1.0L
            $stmtBom = mysqli_prepare($db, "SELECT id_bahan_baku, qty FROM bom WHERE id_produk = ?");
            mysqli_stmt_bind_param($stmtBom, 'i', $idProduk);
            mysqli_stmt_execute($stmtBom);
            $resBom = mysqli_stmt_get_result($stmtBom);

            while ($bom = mysqli_fetch_assoc($resBom)) {
                $idBahan = (int)$bom['id_bahan_baku'];
                $pengurangan = (float)$bom['qty'] * $orderQty;

                $stmtStok = mysqli_prepare($db, "UPDATE bahan_baku SET stok = stok - ? WHERE id_bahan_baku = ?");
                mysqli_stmt_bind_param($stmtStok, 'di', $pengurangan, $idBahan);
                mysqli_stmt_execute($stmtStok);
            }

            // E. COMMIT Transaksi
            mysqli_commit($db);

        } catch (\Throwable $e) {
            mysqli_rollback($db);
            $this->assertTrue(false, 'Transaksi tidak boleh gagal: ' . $e->getMessage());
        }

        // 3. Verifikasi kondisi database pasca-COMMIT
        $this->assertDatabaseHas('pesanan', [
            'id_pesanan'    => $idPesanan,
            'jenis_pesanan' => 'Kantin',
            'total_harga'   => 200000.00,
            'status_pesanan'=> 'Diproses',
        ]);

        $this->assertDatabaseHas('detail_pesanan', [
            'id_pesanan' => $idPesanan,
            'id_produk'  => 1,
            'qty'        => 10,
            'subtotal'   => 200000.00,
        ]);

        // Stok terpotong dengan akurat
        $this->assertDatabaseHas('bahan_baku', ['id_bahan_baku' => 1, 'stok' => 49.00]); // 50 - 1
        $this->assertDatabaseHas('bahan_baku', ['id_bahan_baku' => 2, 'stok' => 18.00]); // 20 - 2
        $this->assertDatabaseHas('bahan_baku', ['id_bahan_baku' => 3, 'stok' => 29.00]); // 30 - 1
    }

    public function testDeleteOrderRestoresStockToOriginalLevel(): void
    {
        $db = $this->getDb();

        // 1. Buat pesanan terlebih dahulu (10 porsi Ayam Goreng Crispy)
        $this->testCreateCanteenOrderAndDeductStockCommitSuccess();

        // Verifikasi stok saat pesanan aktif: Ayam = 18.00
        $this->assertDatabaseHas('bahan_baku', ['id_bahan_baku' => 2, 'stok' => 18.00]);

        // Ambil ID pesanan yang baru dibuat
        $resOrder = mysqli_query($db, "SELECT id_pesanan FROM pesanan ORDER BY id_pesanan DESC LIMIT 1");
        $idPesanan = (int)mysqli_fetch_assoc($resOrder)['id_pesanan'];

        // 2. Mulai Transaksi Penghapusan & Pemulihan Stok
        mysqli_begin_transaction($db);
        try {
            // A. Ambil detail pesanan
            $stmtDet = mysqli_prepare($db, "SELECT id_produk, qty FROM detail_pesanan WHERE id_pesanan = ?");
            mysqli_stmt_bind_param($stmtDet, 'i', $idPesanan);
            mysqli_stmt_execute($stmtDet);
            $resDet = mysqli_stmt_get_result($stmtDet);

            while ($det = mysqli_fetch_assoc($resDet)) {
                $pId = (int)$det['id_produk'];
                $pQty = (int)$det['qty'];

                // Baca BOM dan kembalikan stok
                $stmtBom = mysqli_prepare($db, "SELECT id_bahan_baku, qty FROM bom WHERE id_produk = ?");
                mysqli_stmt_bind_param($stmtBom, 'i', $pId);
                mysqli_stmt_execute($stmtBom);
                $resBom = mysqli_stmt_get_result($stmtBom);

                while ($bom = mysqli_fetch_assoc($resBom)) {
                    $idBahan = (int)$bom['id_bahan_baku'];
                    $pemulihan = (float)$bom['qty'] * $pQty;

                    $stmtRestore = mysqli_prepare($db, "UPDATE bahan_baku SET stok = stok + ? WHERE id_bahan_baku = ?");
                    mysqli_stmt_bind_param($stmtRestore, 'di', $pemulihan, $idBahan);
                    mysqli_stmt_execute($stmtRestore);
                }
            }

            // B. Hapus detail pesanan dan pesanan
            $delDet = mysqli_prepare($db, "DELETE FROM detail_pesanan WHERE id_pesanan = ?");
            mysqli_stmt_bind_param($delDet, 'i', $idPesanan);
            mysqli_stmt_execute($delDet);

            $delOrder = mysqli_prepare($db, "DELETE FROM pesanan WHERE id_pesanan = ?");
            mysqli_stmt_bind_param($delOrder, 'i', $idPesanan);
            mysqli_stmt_execute($delOrder);

            mysqli_commit($db);

        } catch (\Throwable $e) {
            mysqli_rollback($db);
            $this->assertTrue(false, 'Penghapusan pesanan gagal: ' . $e->getMessage());
        }

        // 3. Verifikasi stok kembali utuh ke nilai semula (50, 20, 30)
        $this->assertDatabaseHas('bahan_baku', ['id_bahan_baku' => 1, 'stok' => 50.00]);
        $this->assertDatabaseHas('bahan_baku', ['id_bahan_baku' => 2, 'stok' => 20.00]);
        $this->assertDatabaseHas('bahan_baku', ['id_bahan_baku' => 3, 'stok' => 30.00]);

        // Verifikasi pesanan dan detailnya terhapus
        $this->assertDatabaseMissing('pesanan', ['id_pesanan' => $idPesanan]);
        $this->assertDatabaseMissing('detail_pesanan', ['id_pesanan' => $idPesanan]);
    }

    public function testCateringOrderCreatesAndCleansUpLinkedCateringRow(): void
    {
        $db = $this->getDb();

        // 1. Buat pesanan katering
        mysqli_begin_transaction($db);

        $stmtCat = mysqli_prepare($db, "INSERT INTO catering (tanggal_acara, lokasi_acara) VALUES ('2026-12-15', 'Aula Pertemuan Jakarta')");
        mysqli_stmt_execute($stmtCat);
        $idCatering = mysqli_insert_id($db);

        $stmtOrder = mysqli_prepare($db, "
            INSERT INTO pesanan (tanggal_pesanan, status_pesanan, total_harga, jenis_pesanan, id_pelanggan, id_pegawai, id_catering)
            VALUES (NOW(), 'Diproses', 0.00, 'Catering', 2, 3, ?)
        ");
        mysqli_stmt_bind_param($stmtOrder, 'i', $idCatering);
        mysqli_stmt_execute($stmtOrder);
        $idPesanan = mysqli_insert_id($db);

        mysqli_commit($db);

        // Verifikasi baris katering dan relasi tersimpan
        $this->assertDatabaseHas('catering', ['id_catering' => $idCatering, 'lokasi_acara' => 'Aula Pertemuan Jakarta']);
        $this->assertDatabaseHas('pesanan', ['id_pesanan' => $idPesanan, 'id_catering' => $idCatering]);

        // 2. Hapus pesanan katering dan pastikan tabel catering ikut dibersihkan (no orphaned record)
        mysqli_begin_transaction($db);

        $delOrder = mysqli_prepare($db, "DELETE FROM pesanan WHERE id_pesanan = ?");
        mysqli_stmt_bind_param($delOrder, 'i', $idPesanan);
        mysqli_stmt_execute($delOrder);

        $delCat = mysqli_prepare($db, "DELETE FROM catering WHERE id_catering = ?");
        mysqli_stmt_bind_param($delCat, 'i', $idCatering);
        mysqli_stmt_execute($delCat);

        mysqli_commit($db);

        $this->assertDatabaseMissing('pesanan', ['id_pesanan' => $idPesanan]);
        $this->assertDatabaseMissing('catering', ['id_catering' => $idCatering]);
    }

    // ==========================================
    // Test to Fail (Negative Testing & ACID Rollback)
    // ==========================================

    public function testTransactionRollbackOnMidOperationFailureLeavesDataUnchanged(): void
    {
        $db = $this->getDb();

        // Kondisi awal stok Tepung (id 1) = 50.00
        $this->assertDatabaseHas('bahan_baku', ['id_bahan_baku' => 1, 'stok' => 50.00]);

        $initialOrderCount = (int)mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as c FROM pesanan"))['c'];

        // Mulai Transaksi
        mysqli_begin_transaction($db);

        try {
            // 1. Buat pesanan (berhasil)
            $stmtOrder = mysqli_prepare($db, "
                INSERT INTO pesanan (tanggal_pesanan, status_pesanan, total_harga, jenis_pesanan, id_pelanggan, id_pegawai, id_cabang_kantin)
                VALUES (NOW(), 'Diproses', 100000.00, 'Kantin', 1, 2, 1)
            ");
            mysqli_stmt_execute($stmtOrder);
            $idPesanan = mysqli_insert_id($db);

            // 2. Kurangi stok Tepung parsial (berhasil di memori transaksi: 50 -> 40)
            $stmtStok = mysqli_prepare($db, "UPDATE bahan_baku SET stok = stok - 10.0 WHERE id_bahan_baku = 1");
            mysqli_stmt_execute($stmtStok);

            // 3. Simulasi kegagalan di tengah jalan: Query ke produk fiktif yang melanggar FK
            $fakeProductId = 99999;
            $stmtFail = mysqli_prepare($db, "INSERT INTO detail_pesanan (id_pesanan, id_produk, qty, subtotal) VALUES (?, ?, 5, 50000)");
            mysqli_stmt_bind_param($stmtFail, 'ii', $idPesanan, $fakeProductId);

            if (!@mysqli_stmt_execute($stmtFail)) {
                throw new \RuntimeException('Gagal insert detail pesanan: Foreign key constraint error.');
            }

            // Jika sampai sini, commit (tidak boleh terjadi pada skenario gagal)
            mysqli_commit($db);

        } catch (\Throwable $e) {
            // ROLLBACK harus membatalkan seluruh langkah di atas!
            mysqli_rollback($db);
        }

        // Verifikasi Integritas ACID:
        // 1. Jumlah pesanan tidak boleh bertambah
        $currentOrderCount = (int)mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as c FROM pesanan"))['c'];
        $this->assertEquals($initialOrderCount, $currentOrderCount, 'Pesanan baru tidak boleh tersimpan saat rollback.');

        // 2. Stok bahan baku TIDAK BOLEH terpotong (harus tetap 50.00, bukan 40.00)
        $this->assertDatabaseHas('bahan_baku', [
            'id_bahan_baku' => 1,
            'stok'          => 50.00,
        ]);
    }

    public function testInsufficientStockAbortsTransactionWithoutModifyingDatabase(): void
    {
        $db = $this->getDb();

        // Bahan 2: Daging Ayam Fillet (stok: 20.00 kg)
        // Resep Produk 1 butuh 0.2 kg per porsi.
        // Jika memesan 200 porsi -> butuh 40 kg (Stok kurang 20 kg!)
        $orderQty = 200;
        $idProduk = 1;

        $stmtBom = mysqli_prepare($db, "SELECT bb.id_bahan_baku, bb.stok, bom.qty FROM bom JOIN bahan_baku bb ON bom.id_bahan_baku = bb.id_bahan_baku WHERE bom.id_produk = ?");
        mysqli_stmt_bind_param($stmtBom, 'i', $idProduk);
        mysqli_stmt_execute($stmtBom);
        $resBom = mysqli_stmt_get_result($stmtBom);

        $hasDeficit = false;
        $deficitDetails = [];

        while ($row = mysqli_fetch_assoc($resBom)) {
            $stokTersedia = (float)$row['stok'];
            $stokDibutuhkan = (float)$row['qty'] * $orderQty;

            if ($stokTersedia < $stokDibutuhkan) {
                $hasDeficit = true;
                $deficitDetails[] = [
                    'id_bahan' => $row['id_bahan_baku'],
                    'kurang'   => $stokDibutuhkan - $stokTersedia,
                ];
            }
        }

        $this->assertTrue($hasDeficit, 'Sistem harus mendeteksi kekurangan stok bahan baku sebelum pesanan diproses.');
        $this->assertEquals(20.0, $deficitDetails[0]['kurang'], 'Defisit ayam harus terdeteksi sebesar 20 kg.');

        // Karena defisit terdeteksi, database tidak boleh diubah sedikit pun
        $this->assertDatabaseHas('bahan_baku', ['id_bahan_baku' => 2, 'stok' => 20.00]);
    }
}
