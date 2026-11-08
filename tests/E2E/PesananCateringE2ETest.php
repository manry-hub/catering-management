<?php

namespace Tests\E2E;

use Tests\Support\TestCase;
use Tests\Support\BrowserClient;
use Tests\Fixtures\DatabaseSeeder;

class PesananCateringE2ETest extends TestCase
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

    public function testCreateCateringOrderWorkflow(): void
    {
        $this->authenticateAs('Dewi Lestari', 'Pegawai Catering');

        $formPage = $this->browser->get('dashboard/pesanan/pesanantambah.php');
        $formPage->assertStatus(200);
        $csrf = $this->browser->extractCsrfToken($formPage->getHtml());

        $postResp = $this->browser->post('dashboard/pesanan/controllers/create.php', [
            'id_pelanggan'  => 2,
            'jenis_pesanan' => 'Catering',
            'tanggal_acara' => '2026-12-25',
            'lokasi_acara'  => 'Gedung Pernikahan Serbaguna Jakarta',
            'catatan'       => 'Penyajian Prasmanan Siang',
            'csrf_token'    => $csrf,
        ]);

        $postResp->assertStatus(302);

        // Verifikasi entitas catering dan pesanan tersimpan
        $this->assertDatabaseHas('catering', [
            'lokasi_acara' => 'Gedung Pernikahan Serbaguna Jakarta',
        ]);

        $this->assertDatabaseHas('pesanan', [
            'jenis_pesanan' => 'Catering',
            'id_pelanggan'  => 2,
            'catatan'       => 'Penyajian Prasmanan Siang',
        ]);
    }

    // ==========================================
    // Test to Fail (Negative Scenarios)
    // ==========================================

    public function testCreateCateringOrderFailsWithoutEventDetails(): void
    {
        $this->authenticateAs('Dewi Lestari', 'Pegawai Catering');

        $formPage = $this->browser->get('dashboard/pesanan/pesanantambah.php');
        $csrf = $this->browser->extractCsrfToken($formPage->getHtml());

        // Submit katering tanpa tanggal dan lokasi acara
        $postResp = $this->browser->post('dashboard/pesanan/controllers/create.php', [
            'id_pelanggan'  => 2,
            'jenis_pesanan' => 'Catering',
            'tanggal_acara' => '',
            'lokasi_acara'  => '',
            'catatan'       => 'Catering Gagal',
            'csrf_token'    => $csrf,
        ]);

        $postResp->assertStatus(302);
        $this->assertDatabaseMissing('pesanan', ['catatan' => 'Catering Gagal']);
    }
}
