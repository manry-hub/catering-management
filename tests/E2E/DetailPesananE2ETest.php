<?php

namespace Tests\E2E;

use Tests\Support\TestCase;
use Tests\Support\BrowserClient;
use Tests\Fixtures\DatabaseSeeder;

class DetailPesananE2ETest extends TestCase
{
    private BrowserClient $browser;

    public function setUp(): void
    {
        parent::setUp();
        DatabaseSeeder::resetAndSeed();
        $this->browser = new BrowserClient();
    }

    private function authenticateAs(string $name, string $role): void
    {
        $loginPage = $this->browser->get('login.php');
        $csrf = $this->browser->extractCsrfToken($loginPage->getHtml());

        $this->browser->post('login.php', [
            'nama_pegawai' => $name,
            'password'     => '123456',
            'csrf_token'   => $csrf,
        ]);
    }

    private function createOrderHelper(): int
    {
        $db = $this->getDb();
        $stmt = mysqli_prepare($db, "
            INSERT INTO pesanan (tanggal_pesanan, status_pesanan, total_harga, jenis_pesanan, id_pelanggan, id_pegawai, id_cabang_kantin)
            VALUES (NOW(), 'Diproses', 0.00, 'Kantin', 1, 2, 1)
        ");
        mysqli_stmt_execute($stmt);
        return mysqli_insert_id($db);
    }

    // ==========================================
    // Test to Pass (Positive Scenarios)
    // ==========================================

    public function testAddAndRemoveOrderItemWorkflow(): void
    {
        $this->authenticateAs('Rina Putri', 'Pegawai Kantin');

        $idPesanan = $this->createOrderHelper();

        // 1. Buka halaman detail pesanan
        $page = $this->browser->get('dashboard/detail_pesanan/detail_pesanantambah.php', ['id_pesanan' => $idPesanan]);
        $page->assertStatus(200);
        $csrf = $this->browser->extractCsrfToken($page->getHtml());

        // 2. Tambahkan item produk: 5 porsi Ayam Goreng Crispy (@ Rp 20.000)
        // Kebutuhan BOM per porsi: Daging Ayam 0.2kg (Total 5 porsi = 1.0kg)
        // Stok awal Daging Ayam = 20.00kg -> Harusnya jadi 19.00kg
        $postResp = $this->browser->post('dashboard/detail_pesanan/controllers/create.php', [
            'id_pesanan' => $idPesanan,
            'id_produk'  => 1,
            'qty'        => 5,
            'csrf_token' => $csrf,
        ]);

        $postResp->assertStatus(302);

        // Verifikasi item tersimpan dan total harga pesanan ter-update (Rp 100.000)
        $this->assertDatabaseHas('detail_pesanan', [
            'id_pesanan' => $idPesanan,
            'id_produk'  => 1,
            'qty'        => 5,
            'subtotal'   => 100000.00,
        ]);

        $this->assertDatabaseHas('pesanan', [
            'id_pesanan'  => $idPesanan,
            'total_harga' => 100000.00,
        ]);

        // Verifikasi stok Daging Ayam terpotong 1.0kg menjadi 19.00kg
        $this->assertDatabaseHas('bahan_baku', [
            'id_bahan_baku' => 2,
            'stok'          => 19.00,
        ]);

        // 3. Hapus item dari pesanan
        $delResp = $this->browser->post('dashboard/detail_pesanan/controllers/delete.php', [
            'id_pesanan' => $idPesanan,
            'id_produk'  => 1,
            'csrf_token' => $csrf,
        ]);

        $delResp->assertStatus(302);

        // Verifikasi item terhapus dari detail_pesanan
        $this->assertDatabaseMissing('detail_pesanan', [
            'id_pesanan' => $idPesanan,
            'id_produk'  => 1,
        ]);

        // Verifikasi stok Daging Ayam pulih kembali ke 20.00kg
        $this->assertDatabaseHas('bahan_baku', [
            'id_bahan_baku' => 2,
            'stok'          => 20.00,
        ]);
    }

    // ==========================================
    // Test to Fail (Negative Scenarios)
    // ==========================================

    public function testAddOrderItemFailsWithZeroOrNegativeQuantity(): void
    {
        $this->authenticateAs('Rina Putri', 'Pegawai Kantin');
        $idPesanan = $this->createOrderHelper();

        $page = $this->browser->get('dashboard/detail_pesanan/detail_pesanantambah.php', ['id_pesanan' => $idPesanan]);
        $csrf = $this->browser->extractCsrfToken($page->getHtml());

        // Submit dengan kuantitas 0
        $postResp = $this->browser->post('dashboard/detail_pesanan/controllers/create.php', [
            'id_pesanan' => $idPesanan,
            'id_produk'  => 1,
            'qty'        => 0,
            'csrf_token' => $csrf,
        ]);

        $postResp->assertStatus(302);
        $this->assertDatabaseMissing('detail_pesanan', [
            'id_pesanan' => $idPesanan,
            'id_produk'  => 1,
        ]);
    }

    public function testDeleteNonExistentOrderItemFailsGracefully(): void
    {
        $this->authenticateAs('Rina Putri', 'Pegawai Kantin');
        $idPesanan = $this->createOrderHelper();

        $page = $this->browser->get('dashboard/detail_pesanan/detail_pesananlihat.php', ['id' => $idPesanan]);
        $csrf = $this->browser->extractCsrfToken($page->getHtml());

        // Hapus item yang tidak pernah ada di pesanan (id_produk = 999)
        $delResp = $this->browser->post('dashboard/detail_pesanan/controllers/delete.php', [
            'id_pesanan' => $idPesanan,
            'id_produk'  => 999,
            'csrf_token' => $csrf,
        ]);

        $delResp->assertStatus(302);
    }
}
