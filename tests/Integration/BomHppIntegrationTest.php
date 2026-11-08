<?php

namespace Tests\Integration;

use Tests\Support\TestCase;
use Tests\Fixtures\DatabaseSeeder;

class BomHppIntegrationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        DatabaseSeeder::resetAndSeed();
    }

    // ==========================================
    // Test to Pass (Positive Testing)
    // ==========================================

    public function testAddBomItemAndSynchronizeHppToProduct(): void
    {
        $db = $this->getDb();
        $idProduk = 2; // Nasi Putih Pulen (Awalnya HPP = 3000)
        $idBahanBaku = 3; // Minyak Goreng (Harga satuan: 18.000)
        $qty = 0.0500; // 0.05 L @ 18000 = 900.00

        // 1. Verifikasi HPP awal di database
        $this->assertDatabaseHas('produk', ['id_produk' => $idProduk, 'harga_pokok' => 3000.00]);

        // 2. Ambil harga bahan baku dari DB
        $stmtBahan = mysqli_prepare($db, "SELECT harga_satuan_bahan FROM bahan_baku WHERE id_bahan_baku = ?");
        mysqli_stmt_bind_param($stmtBahan, 'i', $idBahanBaku);
        mysqli_stmt_execute($stmtBahan);
        $bahan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtBahan));
        $hargaSatuan = (float)$bahan['harga_satuan_bahan'];
        $subtotal = round($qty * $hargaSatuan, 2); // 900.00

        // 3. Insert ke tabel bom
        $stmtIns = mysqli_prepare($db, "INSERT INTO bom (id_produk, id_bahan_baku, qty, subtotal) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmtIns, 'iidd', $idProduk, $idBahanBaku, $qty, $subtotal);
        $executed = mysqli_stmt_execute($stmtIns);
        $this->assertTrue($executed, 'Insert BOM baru harus berhasil dieksekusi.');

        // 4. Verifikasi baris bom tersimpan di database
        $this->assertDatabaseHas('bom', [
            'id_produk'     => $idProduk,
            'id_bahan_baku' => $idBahanBaku,
            'qty'           => 0.0500,
            'subtotal'      => 900.00,
        ]);

        // 5. Hitung total HPP baru dari database via JOIN
        $stmtSum = mysqli_prepare($db, "SELECT SUM(bom.qty * bb.harga_satuan_bahan) AS total_hpp FROM bom JOIN bahan_baku bb ON bom.id_bahan_baku = bb.id_bahan_baku WHERE bom.id_produk = ?");
        mysqli_stmt_bind_param($stmtSum, 'i', $idProduk);
        mysqli_stmt_execute($stmtSum);
        $resSum = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtSum));
        $newTotalHpp = (float)$resSum['total_hpp'];

        // 3000 + 900 = 3900.00
        $this->assertEquals(3900.00, $newTotalHpp, 'Total HPP baru harus terhitung Rp 3.900.');

        // 6. Sinkronkan ke tabel produk
        $stmtUpd = mysqli_prepare($db, "UPDATE produk SET harga_pokok = ? WHERE id_produk = ?");
        mysqli_stmt_bind_param($stmtUpd, 'di', $newTotalHpp, $idProduk);
        mysqli_stmt_execute($stmtUpd);

        // 7. Verifikasi nilai HPP di tabel produk ter-update di database
        $this->assertDatabaseHas('produk', [
            'id_produk'   => $idProduk,
            'harga_pokok' => 3900.00,
        ]);
    }

    public function testDeleteBomItemUpdatesProductHpp(): void
    {
        $db = $this->getDb();
        $idProduk = 1; // Ayam Goreng Crispy (Total HPP awal = 12000: Ayam 9000 + Tepung 1200 + Minyak 1800)
        $idBahanHapus = 3; // Minyak Goreng (1800)

        $this->assertDatabaseHas('produk', ['id_produk' => $idProduk, 'harga_pokok' => 12000.00]);

        // Hapus item BOM
        $del = mysqli_prepare($db, "DELETE FROM bom WHERE id_produk = ? AND id_bahan_baku = ?");
        mysqli_stmt_bind_param($del, 'ii', $idProduk, $idBahanHapus);
        mysqli_stmt_execute($del);

        $this->assertDatabaseMissing('bom', ['id_produk' => $idProduk, 'id_bahan_baku' => $idBahanHapus]);

        // Hitung ulang dan sinkronkan HPP
        $stmtSum = mysqli_prepare($db, "SELECT COALESCE(SUM(subtotal), 0) AS total_hpp FROM bom WHERE id_produk = ?");
        mysqli_stmt_bind_param($stmtSum, 'i', $idProduk);
        mysqli_stmt_execute($stmtSum);
        $recalculated = (float)mysqli_fetch_assoc(mysqli_stmt_get_result($stmtSum))['total_hpp'];

        // 12000 - 1800 = 10200
        $this->assertEquals(10200.00, $recalculated, 'HPP setelah hapus minyak harus Rp 10.200.');

        $upd = mysqli_prepare($db, "UPDATE produk SET harga_pokok = ? WHERE id_produk = ?");
        mysqli_stmt_bind_param($upd, 'di', $recalculated, $idProduk);
        mysqli_stmt_execute($upd);

        $this->assertDatabaseHas('produk', ['id_produk' => $idProduk, 'harga_pokok' => 10200.00]);
    }

    // ==========================================
    // Test to Fail (Negative Testing)
    // ==========================================

    public function testDuplicateBomIngredientRejection(): void
    {
        $db = $this->getDb();
        $idProduk = 1;
        $idBahanBakuDuplikat = 1; // Tepung Terigu sudah ada di resep Ayam Goreng Crispy

        // Cek duplikasi di DB
        $check = mysqli_prepare($db, "SELECT 1 FROM bom WHERE id_produk = ? AND id_bahan_baku = ?");
        mysqli_stmt_bind_param($check, 'ii', $idProduk, $idBahanBakuDuplikat);
        mysqli_stmt_execute($check);
        $exists = mysqli_fetch_assoc(mysqli_stmt_get_result($check));

        $this->assertNotNull($exists, 'Pemeriksaan duplikasi harus mendeteksi bahan sudah terdaftar di resep.');

        // Mencoba insert langsung ke PK komposit harus melempar mysqli_sql_exception (Error 1062 Duplicate entry)
        $this->assertThrows(\mysqli_sql_exception::class, function () use ($db, $idProduk, $idBahanBakuDuplikat) {
            $ins = mysqli_prepare($db, "INSERT INTO bom (id_produk, id_bahan_baku, qty, subtotal) VALUES (?, ?, 0.5, 6000)");
            mysqli_stmt_bind_param($ins, 'ii', $idProduk, $idBahanBakuDuplikat);
            mysqli_stmt_execute($ins);
        }, 'Insert duplikasi kunci komposit (id_produk, id_bahan_baku) harus gagal di database.');
    }

    public function testAddBomWithNonExistentIngredientRejection(): void
    {
        $db = $this->getDb();
        $idProduk = 1;
        $fakeBahanId = 99999;

        // Constraint Foreign Key fk_bom_bahan_baku harus memblokir insert
        $this->assertThrows(\mysqli_sql_exception::class, function () use ($db, $idProduk, $fakeBahanId) {
            $ins = mysqli_prepare($db, "INSERT INTO bom (id_produk, id_bahan_baku, qty, subtotal) VALUES (?, ?, 1.0, 10000)");
            mysqli_stmt_bind_param($ins, 'ii', $idProduk, $fakeBahanId);
            mysqli_stmt_execute($ins);
        }, 'Insert BOM dengan bahan baku fiktif harus gagal ditolak FK constraint.');
    }
}
