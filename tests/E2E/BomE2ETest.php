<?php

namespace Tests\E2E;

use Tests\Support\TestCase;
use Tests\Support\BrowserClient;
use Tests\Fixtures\DatabaseSeeder;

class BomE2ETest extends TestCase
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

    // ==========================================
    // Test to Pass (Positive Scenarios)
    // ==========================================

    public function testViewBomDetailAndSyncHppWorkflow(): void
    {
        $this->authenticateAs('Siska Nur', 'Pegawai Dapur');

        // 1. Buka detail BOM produk 1 (Ayam Goreng Crispy)
        $detailPage = $this->browser->get('dashboard/bom/bomdetail.php', ['id' => 1]);
        $detailPage->assertStatus(200);
        $detailPage->assertSee('Ayam Goreng Crispy');
        $detailPage->assertSee('Bill of Materials (BOM)');

        $csrf = $this->browser->extractCsrfToken($detailPage->getHtml());
        $this->assertNotNull($csrf);

        // 2. Eksekusi sinkronisasi harga pokok
        $syncResp = $this->browser->post('dashboard/bom/controllers/sync_harga_pokok.php', [
            'id_produk'  => 1,
            'csrf_token' => $csrf,
        ]);

        $syncResp->assertStatus(302);

        // 3. Verifikasi harga pokok di database tersinkronisasi Rp 12.000
        $this->assertDatabaseHas('produk', [
            'id_produk'   => 1,
            'harga_pokok' => 12000.00,
        ]);
    }

    // ==========================================
    // Test to Fail (Negative Scenarios)
    // ==========================================

    public function testAddBomWithZeroQuantityRejected(): void
    {
        $this->authenticateAs('Siska Nur', 'Pegawai Dapur');

        $detailPage = $this->browser->get('dashboard/bom/bomdetail.php', ['id' => 2]);
        $csrf = $this->browser->extractCsrfToken($detailPage->getHtml());

        // Tambah bahan dengan qty 0
        $postResp = $this->browser->post('dashboard/bom/controllers/create.php', [
            'id_produk'     => 2,
            'id_bahan_baku' => 1,
            'qty'           => 0,
            'csrf_token'    => $csrf,
        ]);

        $postResp->assertStatus(302);
        $this->assertDatabaseMissing('bom', ['id_produk' => 2, 'id_bahan_baku' => 1]);
    }

    public function testUnauthorizedRoleCannotAccessBom(): void
    {
        $this->authenticateAs('Dewi Lestari', 'Pegawai Catering');

        $resp = $this->browser->get('dashboard/bom/bomdetail.php', ['id' => 1]);
        $resp->assertRedirect();
        $this->assertContains('Akses ditolak', $this->browser->getSession()['flash_error'] ?? '');
    }
}
