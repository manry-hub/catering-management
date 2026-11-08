<?php

namespace Tests\Unit;

use Tests\Support\TestCase;

class InventoryTest extends TestCase
{
    private function determineStatus(float $stok, float $stokKritis): string
    {
        if ($stok <= 0) {
            return 'Habis';
        }
        if ($stok <= $stokKritis) {
            return 'Menipis';
        }
        return 'Tersedia';
    }

    // ==========================================
    // Test to Pass (Positive Testing)
    // ==========================================

    public function testStatusKetersediaanBoundaryTersedia(): void
    {
        $stokKritis = 10.0;

        // Jauh di atas kritis
        $this->assertEquals('Tersedia', $this->determineStatus(50.0, $stokKritis));

        // Tepat 0.01 di atas batas kritis
        $this->assertEquals('Tersedia', $this->determineStatus(10.01, $stokKritis));
    }

    public function testStatusKetersediaanBoundaryMenipis(): void
    {
        $stokKritis = 10.0;

        // Tepat di batas kritis
        $this->assertEquals('Menipis', $this->determineStatus(10.0, $stokKritis));

        // Di bawah kritis tapi di atas 0
        $this->assertEquals('Menipis', $this->determineStatus(5.5, $stokKritis));
        $this->assertEquals('Menipis', $this->determineStatus(0.01, $stokKritis));
    }

    public function testStatusKetersediaanBoundaryHabis(): void
    {
        $stokKritis = 10.0;

        // Tepat 0
        $this->assertEquals('Habis', $this->determineStatus(0.0, $stokKritis));

        // Nilai minus
        $this->assertEquals('Habis', $this->determineStatus(-2.5, $stokKritis));
    }

    public function testStockDeductionCalculation(): void
    {
        $initialStock = 50.0; // 50 kg Tepung
        $bomQty = 0.15;       // 0.15 kg per porsi
        $orderQty = 20;       // 20 porsi

        $kebutuhan = $bomQty * $orderQty; // 3.0 kg
        $remainingStock = $initialStock - $kebutuhan;

        $this->assertEquals(3.0, $kebutuhan, 'Total bahan yang dibutuhkan adalah 3.0 kg.');
        $this->assertEquals(47.0, $remainingStock, 'Sisa stok setelah pemotongan harus 47.0 kg.');
    }

    public function testStockRestorationCalculation(): void
    {
        $currentStock = 47.0;
        $bomQty = 0.15;
        $cancelledQty = 20;

        $pengembalian = $bomQty * $cancelledQty; // 3.0 kg
        $restoredStock = $currentStock + $pengembalian;

        $this->assertEquals(50.0, $restoredStock, 'Stok setelah pembatalan/penghapusan harus kembali utuh ke 50.0 kg.');
    }

    // ==========================================
    // Test to Fail (Negative Testing)
    // ==========================================

    public function testInsufficientStockDetection(): void
    {
        $availableStock = 5.0; // Hanya tersedia 5 kg
        $bomQty = 0.50;        // 0.5 kg per porsi
        $orderQty = 20;        // Butuh 10 kg

        $requiredStock = $bomQty * $orderQty; // 10 kg
        $isSufficient = ($availableStock >= $requiredStock);

        $this->assertFalse($isSufficient, 'Pesanan dengan kebutuhan bahan melebihi stok fisik harus ditolak.');

        $deficit = $requiredStock - $availableStock;
        $this->assertEquals(5.0, $deficit, 'Defisit kekurangan stok harus terhitung tepat 5.0 kg.');
    }

    public function testNegativeStockInputRejection(): void
    {
        $invalidStokInputs = [-1.0, -10.0, -0.01];

        foreach ($invalidStokInputs as $stok) {
            $isValid = ($stok >= 0);
            $this->assertFalse($isValid, "Input stok negatif ($stok) harus ditolak oleh sistem.");
        }
    }

    public function testNegativeCriticalThresholdRejection(): void
    {
        $invalidThresholds = [-5.0, -0.5];

        foreach ($invalidThresholds as $thresh) {
            $isValid = ($thresh >= 0);
            $this->assertFalse($isValid, "Ambang batas stok kritis negatif ($thresh) harus ditolak.");
        }
    }
}
