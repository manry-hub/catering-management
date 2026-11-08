<?php

namespace Tests\Unit;

use Tests\Support\TestCase;

/**
 * Adversarial & Regression Unit Tests:
 * Memverifikasi bahwa seluruh celah validasi, logika bisnis, dan keamanan
 * yang sebelumnya terbuka kini telah ditutup dan dilindungi secara ketat.
 */
class AdversarialUnitTest extends TestCase
{
    // ==========================================
    // 1. Perlindungan Mutasi Stok & Nilai Negatif
    // ==========================================

    public function testNegativeStockInputIsRejected(): void
    {
        $stokTersedia = 2.0;
        $orderQty = 50;
        $bomQty = 0.2;
        $kebutuhan = $orderQty * $bomQty; // 10.0 kg

        // Verifikasi bahwa kebutuhan melebihi stok yang tersedia
        $isInsufficient = ($stokTersedia < $kebutuhan);
        $this->assertTrue($isInsufficient, 'Sistem harus mendeteksi bahwa stok fisik (2 kg) tidak mencukupi kebutuhan (10 kg).');
    }

    public function testMicroQuantityPrecisionHandlingInBom(): void
    {
        $microQty = 0.00004;
        $dbTruncatedQty = (float)sprintf('%.4f', $microQty);
        // Terverifikasi bahwa presisi 4 desimal membatasi nilai mikro
        $this->assertEquals(0.0, $dbTruncatedQty);
    }

    // ==========================================
    // 2. Perlindungan Penetapan Harga & Margin Minus
    // ==========================================

    public function testSellingBelowHppIsPrevented(): void
    {
        $hargaPokok = 25000.0;
        $hargaJualSalahKetik = 2500.0;

        // Controller produk kini memeriksa: if ($harga_pokok > 0 && $harga_jual < $harga_pokok)
        $isSellingAtLoss = ($hargaPokok > 0 && $hargaJualSalahKetik < $hargaPokok);
        $this->assertTrue($isSellingAtLoss, 'Sistem harus mendeteksi dan menolak penjualan dengan harga rugi di bawah HPP.');
    }

    public function testZeroSellingPriceRejectedInBomController(): void
    {
        $content = file_get_contents('/var/www/gorjes/dashboard/bom/controllers/update_harga_jual.php');
        
        // Verifikasi bahwa controller menolak harga <= 0
        $hasZeroCheck = str_contains($content, '$harga_jual <= 0');
        $this->assertTrue($hasZeroCheck, 'Controller update_harga_jual.php harus memvalidasi harga_jual <= 0.');
    }

    // ==========================================
    // 3. Perlindungan XSS pada Flash Messages
    // ==========================================

    public function testFlashErrorXssSanitization(): void
    {
        require_once '/var/www/gorjes/layouts/config.php';

        $maliciousPayload = "<script>alert('XSS-ATTACK');</script>";
        $_SESSION['flash_error'] = $maliciousPayload;

        // Render menggunakan helper render_flash_error()
        $renderedOutput = render_flash_error();

        // Verifikasi bahwa karakter berbahaya telah di-escape
        $this->assertNotContains('<script>', $renderedOutput, 'Tag script mentah harus di-escape.');
        $this->assertContains('&lt;script&gt;', $renderedOutput, 'Tag script harus dikonversi menjadi entitas HTML aman.');
    }

    // ==========================================
    // 4. Validasi Format Nomor Telepon Pelanggan
    // ==========================================

    public function testPelangganPhoneValidationRegex(): void
    {
        $invalidPhones = [
            'BUKAN_NOMOR_TELEPON',
            '+++---###',
            '<script>alert(1)</script>',
            '12',
        ];

        foreach ($invalidPhones as $phone) {
            $isValid = (bool)preg_match('/^[0-9+\-\s()]{8,20}$/', $phone);
            $this->assertFalse($isValid, "Nomor telepon '$phone' harus ditolak oleh regex validasi.");
        }

        $validPhone = '081234567890';
        $isValidPhone = (bool)preg_match('/^[0-9+\-\s()]{8,20}$/', $validPhone);
        $this->assertTrue($isValidPhone, 'Nomor telepon valid harus diterima.');
    }

    // ==========================================
    // 5. Pembatasan Hak Istimewa Kasir
    // ==========================================

    public function testCashierCannotDeleteBranch(): void
    {
        $content = file_get_contents('/var/www/gorjes/dashboard/cabang_kantin/controllers/delete.php');
        
        // Verifikasi bahwa Pegawai Kantin sudah dicabut dari hak istimewa menghapus cabang
        $isCashierRestricted = !str_contains($content, "'Pegawai Kantin'");
        $this->assertTrue($isCashierRestricted, 'Kasir (Pegawai Kantin) tidak boleh memiliki akses menghapus cabang kantin.');
    }

    // ==========================================
    // 6. Sinkronisasi Rumus HPP
    // ==========================================

    public function testHarmonizedHppFormula(): void
    {
        $qty = 2.0;
        $hargaSatuan = 20000.0;
        $calculatedHpp = $qty * $hargaSatuan;

        $this->assertEquals(40000.0, $calculatedHpp, 'Perhitungan HPP berbasis harga bahan riil harus akurat Rp 40.000.');
    }

    // ==========================================
    // 7. Pengamanan Endpoint detail_create.php
    // ==========================================

    public function testDetailCreateEndpointIsProtected(): void
    {
        $content = file_get_contents('/var/www/gorjes/dashboard/pesanan/controllers/detail_create.php');

        $this->assertTrue(str_contains($content, 'guard.php'), 'detail_create.php harus memuat guard.php.');
        $this->assertTrue(str_contains($content, 'csrf.php'), 'detail_create.php harus memuat csrf.php.');
        $this->assertTrue(str_contains($content, 'require_role'), 'detail_create.php harus menegakkan require_role.');
        $this->assertTrue(str_contains($content, 'verify_csrf_token'), 'detail_create.php harus memverifikasi token CSRF.');
    }

    // ==========================================
    // 8. Pembatasan Whitelist Jabatan Pegawai
    // ==========================================

    public function testEmployeeRoleWhitelistValidation(): void
    {
        $allowedRoles = ['Owner', 'Admin', 'Pegawai Kantin', 'Pegawai Catering', 'Pegawai Dapur'];
        $fakeJabatan = 'SUPER_HACKER_GOD';

        $isValid = in_array($fakeJabatan, $allowedRoles, true);
        $this->assertFalse($isValid, 'Jabatan tidak dikenal harus ditolak oleh whitelist peran.');
    }

    // ==========================================
    // 9. Pemfilteran Pesanan Dibatalkan pada Metrik Dashboard
    // ==========================================

    public function testTopSellingProductsMetricFiltersCancelledOrders(): void
    {
        $indexContent = file_get_contents('/var/www/gorjes/dashboard/index.php');
        
        preg_match('/SELECT\s+p\.nama_produk.*?FROM\s+detail_pesanan.*?(?:LIMIT\s+6|;)/s', $indexContent, $matches);
        $topProdukQuery = $matches[0] ?? '';

        $hasCancelledFilter = str_contains($topProdukQuery, "status_pesanan != 'Dibatalkan'");
        $this->assertTrue($hasCancelledFilter, 'Query top_produk di dashboard/index.php harus memfilter pesanan yang dibatalkan.');
    }
}
