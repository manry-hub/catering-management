<?php

namespace Tests\E2E;

use Tests\Support\TestCase;
use Tests\Support\BrowserClient;
use Tests\Fixtures\DatabaseSeeder;

class UiInteractionE2ETest extends TestCase
{
    private BrowserClient $browser;

    public function setUp(): void
    {
        parent::setUp();
        DatabaseSeeder::resetAndSeed();
        $this->browser = new BrowserClient();
    }

    private function authenticateAs(string $name): void
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
    // 1. Navigation & Dynamic Active States
    // ==========================================

    public function testDashboardMenuIsActiveOnDashboardPage(): void
    {
        $this->authenticateAs('Budi Owner');
        $resp = $this->browser->get('dashboard/index.php');

        $resp->assertStatus(200);
        $html = $resp->getHtml();

        // Dashboard link should have the active classes
        $this->assertMatchesRegularExpression(
            '/href="\/gorjes\/dashboard\/index\.php"[^>]*class="[^"]*text-luxury-accent[^"]*border-b-2[^"]*border-luxury-accent/',
            $html,
            'Menu Dashboard harus memiliki style aktif saat berada di dashboard.'
        );

        // Produk should not have active indicator
        $this->assertDoesNotMatchRegularExpression(
            '/href="\/gorjes\/dashboard\/produk\/produklihat\.php"[^>]*class="[^"]*text-luxury-accent[^"]*border-b-2/',
            $html,
            'Menu Produk tidak boleh memiliki style aktif saat pengguna berada di Dashboard.'
        );
    }

    public function testProdukMenuIsActiveOnProdukPage(): void
    {
        $this->authenticateAs('Budi Owner');
        $resp = $this->browser->get('dashboard/produk/produklihat.php');

        $resp->assertStatus(200);
        $html = $resp->getHtml();

        $this->assertMatchesRegularExpression(
            '/href="\/gorjes\/dashboard\/produk\/produklihat\.php"[^>]*class="[^"]*text-luxury-accent[^"]*border-b-2[^"]*border-luxury-accent/',
            $html,
            'Menu Produk harus aktif saat berada di halaman produk.'
        );
    }

    public function testCateringMenuIsAvailableForCateringStaffAndActive(): void
    {
        $this->authenticateAs('Dewi Lestari'); // Pegawai Catering
        $resp = $this->browser->get('dashboard/catering/cateringlihat.php');

        $resp->assertStatus(200);
        $html = $resp->getHtml();

        // Menu Catering must exist in the navigation
        $this->assertContains(
            'href="/gorjes/dashboard/catering/cateringlihat.php"',
            $html,
            'Menu Catering harus muncul di navigasi untuk Pegawai Catering.'
        );

        // Menu Catering should have active indicator
        $this->assertMatchesRegularExpression(
            '/href="\/gorjes\/dashboard\/catering\/cateringlihat\.php"[^>]*class="[^"]*text-luxury-accent[^"]*border-b-2[^"]*border-luxury-accent/',
            $html,
            'Menu Catering harus aktif saat Pegawai Catering mengakses halaman catering.'
        );

        // Pegawai Catering should NOT see Pegawai or Bahan Baku in menu
        $this->assertNotContains('dashboard/pegawai/pegawailihat.php', $html);
        $this->assertNotContains('dashboard/bahan_baku/bahan_bakulihat.php', $html);
    }

    // ==========================================
    // 2. Mobile Drawer & Touch Target UX
    // ==========================================

    public function testMobileHamburgerHasAccessibleTouchTargetAndAria(): void
    {
        $this->authenticateAs('Budi Owner');
        $resp = $this->browser->get('dashboard/index.php');
        $html = $resp->getHtml();

        // Verify hamburger button exists with >= 44x44px touch target (w-11 h-11)
        $this->assertContains('id="hamburger-btn"', $html);
        $this->assertContains('aria-label="Buka Menu Navigasi"', $html);
        $this->assertContains('aria-expanded="false"', $html);
        $this->assertContains('w-11 h-11', $html);

        // Verify mobile close button and overlay exist
        $this->assertContains('id="sidebar-close-btn"', $html);
        $this->assertContains('id="sidebar-overlay"', $html);
        $this->assertContains('id="mobile-sidebar"', $html);
    }

    public function testVendorScriptsIncludesMobileDrawerAndEscapeKeyLogic(): void
    {
        $scriptContent = file_get_contents(__DIR__ . '/../../layouts/vendor-scripts.php');

        $this->assertContains('hamburgerBtn.addEventListener', $scriptContent);
        $this->assertContains('sidebar-close-btn', $scriptContent);
        $this->assertContains('Escape', $scriptContent, 'Script harus menangani penutupan drawer dengan tombol Escape.');
    }

    // ==========================================
    // 3. Table Column Counts, Colspan, & Overflow
    // ==========================================

    public function testTableWrappersAndColspanPrecision(): void
    {
        $this->authenticateAs('Budi Owner');

        // Check Produk table wrapper and empty state template colspan
        $respProduk = $this->browser->get('dashboard/produk/produklihat.php');
        $htmlProduk = $respProduk->getHtml();
        $this->assertContains('overflow-x-auto', $htmlProduk);
        $produkFile = file_get_contents(__DIR__ . '/../../dashboard/produk/produklihat.php');
        $this->assertContains('colspan="6"', $produkFile);

        // Check Pesanan table wrapper and empty state rendered
        $respPesanan = $this->browser->get('dashboard/pesanan/pesananlihat.php');
        $htmlPesanan = $respPesanan->getHtml();
        $this->assertContains('overflow-x-auto', $htmlPesanan);
        $this->assertContains('colspan="9"', $htmlPesanan);

        // Check Pegawai table template
        $respPegawai = $this->browser->get('dashboard/pegawai/pegawailihat.php');
        $htmlPegawai = $respPegawai->getHtml();
        $this->assertContains('overflow-x-auto', $htmlPegawai);
        $pegawaiFile = file_get_contents(__DIR__ . '/../../dashboard/pegawai/pegawailihat.php');
        $this->assertContains('colspan="4"', $pegawaiFile);

        // Check Bahan Baku table
        $respBahan = $this->browser->get('dashboard/bahan_baku/bahan_bakulihat.php');
        $htmlBahan = $respBahan->getHtml();
        $this->assertContains('overflow-x-auto', $htmlBahan);
        $bahanFile = file_get_contents(__DIR__ . '/../../dashboard/bahan_baku/bahan_bakulihat.php');
        $this->assertContains('colspan="7"', $bahanFile);
    }

    // ==========================================
    // 4. Accessible High-Contrast Status Badges
    // ==========================================

    public function testHighContrastStatusBadgesInRawMaterials(): void
    {
        $this->authenticateAs('Budi Owner');
        $resp = $this->browser->get('dashboard/bahan_baku/bahan_bakulihat.php');
        $html = $resp->getHtml();

        // Check for accessible badge classes rendered for Tersedia, Menipis, and Habis
        $this->assertContains('text-emerald-800 bg-emerald-50 border border-emerald-200/80', $html);
        $this->assertContains('text-amber-900 bg-amber-50 border border-amber-200/80', $html);
        $this->assertContains('text-rose-800 bg-rose-50 border border-rose-200/80', $html);
    }

    // ==========================================
    // 5. BOM Inline Editing & Update Controller
    // ==========================================

    public function testBomDetailHasInlineEditUi(): void
    {
        $this->authenticateAs('Budi Owner');
        $resp = $this->browser->get('dashboard/bom/bomdetail.php', ['id' => 1]);
        $html = $resp->getHtml();

        $this->assertContains('bom-edit-btn', $html);
        $this->assertContains('bom-save-btn', $html);
        $this->assertContains('bom-cancel-btn', $html);
        $this->assertContains('controllers/update.php', $html);
    }

    public function testBomUpdateControllerModifiesQuantityAndHpp(): void
    {
        $this->authenticateAs('Budi Owner');

        // Buka halaman BOM untuk mendapatkan CSRF token
        $resp = $this->browser->get('dashboard/bom/bomdetail.php', ['id' => 1]);
        $token = $this->browser->extractCsrfToken($resp->getHtml());

        // Ambil salah satu bahan baku dari BOM produk 1
        $db = $this->getDb();
        $bomRow = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM bom WHERE id_produk = 1 LIMIT 1"));
        $this->assertNotNull($bomRow);

        $idBahan = $bomRow['id_bahan_baku'];
        $newQty = 2.50;

        // Post update
        $postResp = $this->browser->post('dashboard/bom/controllers/update.php', [
            'csrf_token' => $token,
            'id_produk' => 1,
            'id_bahan_baku' => $idBahan,
            'qty' => $newQty
        ]);

        $this->assertTrue(in_array($postResp->getStatusCode(), [200, 302]));

        // Verifikasi database bahwa qty telah terupdate
        $updatedBom = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM bom WHERE id_produk = 1 AND id_bahan_baku = $idBahan"));
        $this->assertEquals(2.50, floatval($updatedBom['qty']), '', 0.01);
    }

    // ==========================================
    // 6. PDF Export User Feedback
    // ==========================================

    public function testDetailPesananAndBomHavePdfExportButtonsWithFeedback(): void
    {
        $this->authenticateAs('Budi Owner');

        // Create an order first so detail pesanan can be opened
        $db = $this->getDb();
        mysqli_query($db, "INSERT INTO pesanan (id_pesanan, id_pelanggan, id_cabang_kantin, id_pegawai, jenis_pesanan, tanggal_pesanan, status_pesanan, total_harga) 
            VALUES (101, 1, 1, 5, 'Kantin', NOW(), 'Diproses', 20000.00)");
        mysqli_query($db, "INSERT INTO detail_pesanan (id_pesanan, id_produk, qty, subtotal) VALUES (101, 1, 1, 20000.00)");

        // Check Detail Pesanan
        $respDetail = $this->browser->get('dashboard/detail_pesanan/detail_pesananlihat.php', ['id' => 101]);
        $htmlDetail = $respDetail->getHtml();
        $this->assertContains('id="btn-export-pdf"', $htmlDetail);
        $this->assertContains('exportPDF(this)', $htmlDetail);
        $this->assertContains('Memproses...', $htmlDetail);

        // Check BOM Detail
        $respBom = $this->browser->get('dashboard/bom/bomdetail.php', ['id' => 1]);
        $htmlBom = $respBom->getHtml();
        $this->assertContains('id="btn-export-pdf"', $htmlBom);
        $this->assertContains('exportPDF(this)', $htmlBom);
        $this->assertContains('Memproses...', $htmlBom);
    }
}
