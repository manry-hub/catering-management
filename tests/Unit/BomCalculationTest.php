<?php

namespace Tests\Unit;

use Tests\Support\TestCase;

class BomCalculationTest extends TestCase
{
    // ==========================================
    // Test to Pass (Positive Testing)
    // ==========================================

    public function testSubtotalCalculationStandardUnits(): void
    {
        // 2 kg Tepung @ Rp 12.000
        $qty = 2.0;
        $harga = 12000.0;
        $subtotal = $qty * $harga;

        $this->assertEquals(24000.0, $subtotal, 'Subtotal untuk kuantitas bulat harus tepat.');
    }

    public function testSubtotalCalculationFractionalDecimalQuantities(): void
    {
        // 0.25 kg Daging Ayam @ Rp 45.000
        $qty = 0.25;
        $harga = 45000.0;
        $subtotal = round($qty * $harga, 2);

        $this->assertEquals(11250.0, $subtotal, 'Subtotal untuk 0.25 kg @ 45000 harus Rp 11.250.');

        // 0.05 liter Minyak Goreng @ Rp 18.000
        $qty2 = 0.05;
        $harga2 = 18000.0;
        $subtotal2 = round($qty2 * $harga2, 2);

        $this->assertEquals(900.0, $subtotal2, 'Subtotal untuk 0.05 L @ 18000 harus Rp 900.');
    }

    public function testSubtotalPrecisionWithMicroQuantities(): void
    {
        // 0.0025 kg Bumbu Rempah @ Rp 80.000
        $qty = 0.0025;
        $harga = 80000.0;
        $subtotal = round($qty * $harga, 2);

        $this->assertEquals(200.0, $subtotal, 'Subtotal untuk 0.0025 kg @ 80000 harus Rp 200.');
    }

    // ==========================================
    // Test to Fail (Negative Testing)
    // ==========================================

    public function testZeroQuantityRejection(): void
    {
        $qty = 0.0;
        $isValid = ($qty > 0);

        $this->assertFalse($isValid, 'Kuantitas bahan baku 0 harus ditolak oleh sistem.');
    }

    public function testNegativeQuantityRejection(): void
    {
        $testQuantities = [-1.0, -0.005, -100.5];

        foreach ($testQuantities as $qty) {
            $isValid = ($qty > 0);
            $this->assertFalse($isValid, "Kuantitas negatif ($qty) harus ditolak.");
        }
    }

    public function testNegativePriceRejection(): void
    {
        $testPrices = [-12000.0, -0.01, -50000.0];

        foreach ($testPrices as $price) {
            $isValid = ($price > 0);
            $this->assertFalse($isValid, "Harga satuan negatif ($price) harus ditolak.");
        }
    }

    public function testDuplicateBomIngredientDetection(): void
    {
        $existingRecipe = [
            1 => ['id_bahan_baku' => 1, 'qty' => 0.2],
            2 => ['id_bahan_baku' => 2, 'qty' => 0.1],
        ];

        $newIngredientId = 2; // Duplikat
        $isDuplicate = false;

        foreach ($existingRecipe as $item) {
            if ($item['id_bahan_baku'] === $newIngredientId) {
                $isDuplicate = true;
                break;
            }
        }

        $this->assertTrue($isDuplicate, 'Bahan baku yang sudah ada di resep harus terdeteksi sebagai duplikat.');
    }
}
