<?php

namespace Tests\Integration;

use Tests\Support\TestCase;
use Tests\Fixtures\DatabaseSeeder;

class InventoryIntegrationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        DatabaseSeeder::resetAndSeed();
    }

    // ==========================================
    // Test to Pass (Positive Testing)
    // ==========================================

    public function testCreateNewRawMaterialUpdatesDatabase(): void
    {
        $db = $this->getDb();
        $namaBahan = 'Telur Ayam Negeri';
        $satuan = 'butir';
        $hargaSatuan = 2500.00;
        $stok = 100.00;
        $stokKritis = 20.00;
        $status = 'Tersedia';

        $stmt = mysqli_prepare($db, "INSERT INTO bahan_baku (nama_bahan, satuan, harga_satuan_bahan, stok, stok_kritis, status_ketersediaan) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssddds', $namaBahan, $satuan, $hargaSatuan, $stok, $stokKritis, $status);
        $executed = mysqli_stmt_execute($stmt);
        $this->assertTrue($executed, 'Insert bahan baku baru harus berhasil.');
        $newId = mysqli_insert_id($db);

        $this->assertDatabaseHas('bahan_baku', [
            'id_bahan_baku'       => $newId,
            'nama_bahan'          => $namaBahan,
            'harga_satuan_bahan'  => 2500.00,
            'stok'                => 100.00,
            'status_ketersediaan' => 'Tersedia',
        ]);
    }

    public function testStockMutationDynamicallyUpdatesStatusInDatabase(): void
    {
        $db = $this->getDb();
        $idBahan = 5; // 'Bahan Menipis Test' (stok: 4.00, kritis: 5.00, status: 'Menipis')

        $this->assertDatabaseHas('bahan_baku', [
            'id_bahan_baku'       => $idBahan,
            'stok'                => 4.00,
            'status_ketersediaan' => 'Menipis',
        ]);

        // 1. Tambah stok menjadi 15.00 (status harus berubah ke Tersedia)
        $newStok1 = 15.00;
        $status1 = ($newStok1 <= 0) ? 'Habis' : (($newStok1 <= 5.00) ? 'Menipis' : 'Tersedia');
        $upd1 = mysqli_prepare($db, "UPDATE bahan_baku SET stok = ?, status_ketersediaan = ? WHERE id_bahan_baku = ?");
        mysqli_stmt_bind_param($upd1, 'dsi', $newStok1, $status1, $idBahan);
        mysqli_stmt_execute($upd1);

        $this->assertDatabaseHas('bahan_baku', [
            'id_bahan_baku'       => $idBahan,
            'stok'                => 15.00,
            'status_ketersediaan' => 'Tersedia',
        ]);

        // 2. Kurangi stok menjadi 0.00 (status harus berubah ke Habis)
        $newStok2 = 0.00;
        $status2 = ($newStok2 <= 0) ? 'Habis' : (($newStok2 <= 5.00) ? 'Menipis' : 'Tersedia');
        $upd2 = mysqli_prepare($db, "UPDATE bahan_baku SET stok = ?, status_ketersediaan = ? WHERE id_bahan_baku = ?");
        mysqli_stmt_bind_param($upd2, 'dsi', $newStok2, $status2, $idBahan);
        mysqli_stmt_execute($upd2);

        $this->assertDatabaseHas('bahan_baku', [
            'id_bahan_baku'       => $idBahan,
            'stok'                => 0.00,
            'status_ketersediaan' => 'Habis',
        ]);
    }

    public function testDeleteRawMaterialCascadesToBomTable(): void
    {
        $db = $this->getDb();
        $idBahan = 1; // Tepung Terigu, digunakan di resep Produk 1 (Ayam Goreng Crispy)

        // Pastikan relasi BOM ada
        $this->assertDatabaseHas('bom', ['id_bahan_baku' => $idBahan, 'id_produk' => 1]);

        // Hapus bahan baku dari database
        $del = mysqli_prepare($db, "DELETE FROM bahan_baku WHERE id_bahan_baku = ?");
        mysqli_stmt_bind_param($del, 'i', $idBahan);
        mysqli_stmt_execute($del);

        // Verifikasi bahan baku terhapus
        $this->assertDatabaseMissing('bahan_baku', ['id_bahan_baku' => $idBahan]);

        // Verifikasi bahwa baris BOM terkait terhapus secara otomatis oleh CASCADE
        $this->assertDatabaseMissing('bom', ['id_bahan_baku' => $idBahan]);
    }

    // ==========================================
    // Test to Fail (Negative Testing)
    // ==========================================

    public function testCreateRawMaterialWithNullRequiredFieldsFails(): void
    {
        $db = $this->getDb();

        // nama_bahan adalah NOT NULL tanpa default
        $this->assertThrows(\mysqli_sql_exception::class, function () use ($db) {
            $ins = mysqli_prepare($db, "INSERT INTO bahan_baku (nama_bahan, satuan, harga_satuan_bahan) VALUES (NULL, 'kg', 10000)");
            mysqli_stmt_execute($ins);
        }, 'Insert dengan nama_bahan bernilai NULL harus melempar mysqli_sql_exception.');
    }
}
