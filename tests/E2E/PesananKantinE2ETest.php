<?php

namespace Tests\E2E;

use Tests\Support\TestCase;
use Tests\Support\BrowserClient;
use Tests\Fixtures\DatabaseSeeder;

class PesananKantinE2ETest extends TestCase
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

    public function testCreateCanteenOrderWorkflow(): void
    {
        // 1. Login sebagai Pegawai Kantin
        $this->authenticateAs('Rina Putri', 'Pegawai Kantin');

        // 2. Akses form tambah pesanan
        $formPage = $this->browser->get('dashboard/pesanan/pesanantambah.php');
        $formPage->assertStatus(200);
        $formPage->assertSee('Buat Pesanan Baru');
        $csrf = $this->browser->extractCsrfToken($formPage->getHtml());

        // 3. Submit form pesanan kantin valid
        $postResp = $this->browser->post('dashboard/pesanan/controllers/create.php', [
            'id_pelanggan'     => 1,
            'jenis_pesanan'    => 'Kantin',
            'id_cabang_kantin' => 1,
            'catatan'          => 'Pesanan Kantin Istirahat Siang',
            'csrf_token'       => $csrf,
        ]);

        $postResp->assertStatus(302);

        // 4. Verifikasi pesanan tersimpan di database
        $this->assertDatabaseHas('pesanan', [
            'jenis_pesanan'    => 'Kantin',
            'id_pelanggan'     => 1,
            'id_cabang_kantin' => 1,
            'status_pesanan'   => 'Diproses',
        ]);
    }

    // ==========================================
    // Test to Fail (Negative Scenarios)
    // ==========================================

    public function testCreateCanteenOrderFailsWithoutBranch(): void
    {
        $this->authenticateAs('Rina Putri', 'Pegawai Kantin');

        $formPage = $this->browser->get('dashboard/pesanan/pesanantambah.php');
        $csrf = $this->browser->extractCsrfToken($formPage->getHtml());

        // Submit tanpa cabang kantin
        $postResp = $this->browser->post('dashboard/pesanan/controllers/create.php', [
            'id_pelanggan'     => 1,
            'jenis_pesanan'    => 'Kantin',
            'id_cabang_kantin' => '',
            'catatan'          => 'Tanpa Cabang',
            'csrf_token'       => $csrf,
        ]);

        $postResp->assertStatus(302);
        $this->assertDatabaseMissing('pesanan', ['catatan' => 'Tanpa Cabang']);
    }

    public function testUnauthorizedRoleCannotCreateOrder(): void
    {
        // Pegawai Dapur tidak memiliki wewenang transaksi pesanan
        $this->authenticateAs('Siska Nur', 'Pegawai Dapur');

        $resp = $this->browser->get('dashboard/pesanan/pesanantambah.php');
        $resp->assertRedirect();
        $this->assertContains('Akses ditolak', $this->browser->getSession()['flash_error'] ?? '');
    }
}
