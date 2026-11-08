<?php

namespace Tests\E2E;

use Tests\Support\TestCase;
use Tests\Support\BrowserClient;
use Tests\Fixtures\DatabaseSeeder;

class BahanBakuE2ETest extends TestCase
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
        // Login flow
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

    public function testAddAndListRawMaterialWorkflow(): void
    {
        // 1. Login sebagai Pegawai Dapur
        $this->authenticateAs('Siska Nur', 'Pegawai Dapur');

        // 2. Akses halaman tambah bahan baku
        $formPage = $this->browser->get('dashboard/bahan_baku/bahan_bakutambah.php');
        $formPage->assertStatus(200);
        $csrf = $this->browser->extractCsrfToken($formPage->getHtml());

        // 3. Submit form tambah bahan baku
        $postResp = $this->browser->post('dashboard/bahan_baku/controllers/create.php', [
            'nama_bahan'          => 'Gula Pasir Kristal',
            'satuan'              => 'kg',
            'harga_satuan_bahan'  => 16000.00,
            'stok'                => 25.00,
            'stok_kritis'         => 5.00,
            'status_ketersediaan' => 'Tersedia',
            'csrf_token'          => $csrf,
        ]);

        $postResp->assertStatus(302);

        // 4. Verifikasi di basis data
        $this->assertDatabaseHas('bahan_baku', [
            'nama_bahan'         => 'Gula Pasir Kristal',
            'harga_satuan_bahan' => 16000.00,
            'stok'               => 25.00,
        ]);

        // 5. Buka halaman daftar dan pastikan teks muncul
        $listPage = $this->browser->get('dashboard/bahan_baku/bahan_bakulihat.php');
        $listPage->assertStatus(200);
        $listPage->assertSee('Gula Pasir Kristal');
    }

    // ==========================================
    // Test to Fail (Negative Scenarios)
    // ==========================================

    public function testAddRawMaterialFailsWithMissingRequiredName(): void
    {
        $this->authenticateAs('Siska Nur', 'Pegawai Dapur');

        $formPage = $this->browser->get('dashboard/bahan_baku/bahan_bakutambah.php');
        $csrf = $this->browser->extractCsrfToken($formPage->getHtml());

        // Submit dengan nama bahan kosong
        $postResp = $this->browser->post('dashboard/bahan_baku/controllers/create.php', [
            'nama_bahan'          => '',
            'satuan'              => 'kg',
            'harga_satuan_bahan'  => 15000.00,
            'stok'                => 10.00,
            'stok_kritis'         => 2.00,
            'status_ketersediaan' => 'Tersedia',
            'csrf_token'          => $csrf,
        ]);

        $postResp->assertStatus(302);
        $this->assertDatabaseMissing('bahan_baku', ['satuan' => 'kg', 'stok' => 10.00]);
    }

    public function testAddRawMaterialFailsWithNegativePrice(): void
    {
        $this->authenticateAs('Siska Nur', 'Pegawai Dapur');

        $formPage = $this->browser->get('dashboard/bahan_baku/bahan_bakutambah.php');
        $csrf = $this->browser->extractCsrfToken($formPage->getHtml());

        // Submit dengan harga negatif
        $postResp = $this->browser->post('dashboard/bahan_baku/controllers/create.php', [
            'nama_bahan'          => 'Bahan Invalid',
            'satuan'              => 'kg',
            'harga_satuan_bahan'  => -5000.00,
            'stok'                => 10.00,
            'stok_kritis'         => 2.00,
            'status_ketersediaan' => 'Tersedia',
            'csrf_token'          => $csrf,
        ]);

        $postResp->assertStatus(302);
        $this->assertDatabaseMissing('bahan_baku', ['nama_bahan' => 'Bahan Invalid']);
    }

    public function testUnauthorizedRoleCannotAccessBahanBaku(): void
    {
        // Login sebagai Pegawai Kantin (hanya berhak pesanan dan pelanggan)
        $this->authenticateAs('Rina Putri', 'Pegawai Kantin');

        // Mencoba akses halaman bahan baku -> diarahkan pergi dengan status 302 dan pesan flash error
        $resp = $this->browser->get('dashboard/bahan_baku/bahan_bakulihat.php');
        $resp->assertRedirect();
        $this->assertContains('Akses ditolak', $this->browser->getSession()['flash_error'] ?? '');
    }
}
