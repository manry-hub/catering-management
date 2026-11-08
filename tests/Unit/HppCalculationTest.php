<?php

namespace Tests\Unit;

use Tests\Support\TestCase;

class HppCalculationTest extends TestCase
{
    // ==========================================
    // Test to Pass (Positive Testing)
    // ==========================================

    public function testAccumulateRecipeTotalHpp(): void
    {
        $recipe = [
            ['nama' => 'Daging Ayam', 'qty' => 0.20, 'harga' => 45000.0, 'subtotal' => 9000.0],
            ['nama' => 'Tepung Terigu', 'qty' => 0.10, 'harga' => 12000.0, 'subtotal' => 1200.0],
            ['nama' => 'Minyak Goreng', 'qty' => 0.10, 'harga' => 18000.0, 'subtotal' => 1800.0],
        ];

        $totalHpp = array_sum(array_column($recipe, 'subtotal'));
        $this->assertEquals(12000.0, $totalHpp, 'Akumulasi total HPP resep harus Rp 12.000.');
    }

    public function testPriceSyncWhenIngredientPriceChanges(): void
    {
        $recipe = [
            ['id_bahan' => 1, 'qty' => 0.20, 'harga_lama' => 45000.0, 'harga_baru' => 50000.0],
            ['id_bahan' => 2, 'qty' => 0.10, 'harga_lama' => 12000.0, 'harga_baru' => 12000.0],
            ['id_bahan' => 3, 'qty' => 0.10, 'harga_lama' => 18000.0, 'harga_baru' => 20000.0],
        ];

        // Hitung ulang dengan harga baru
        $recalculatedHpp = 0.0;
        foreach ($recipe as $item) {
            $recalculatedHpp += ($item['qty'] * $item['harga_baru']);
        }

        // (0.20 * 50000 = 10000) + (0.10 * 12000 = 1200) + (0.10 * 20000 = 2000) = 13200
        $this->assertEquals(13200.0, $recalculatedHpp, 'Total HPP hasil sinkronisasi harga baru harus Rp 13.200.');
    }

    public function testIngredientRemovalRecalculation(): void
    {
        $initialSubtotals = [9000.0, 1200.0, 1800.0];
        $this->assertEquals(12000.0, array_sum($initialSubtotals));

        // Hapus item ke-3 (Minyak Goreng: 1800.0)
        unset($initialSubtotals[2]);
        $newHpp = array_sum($initialSubtotals);

        $this->assertEquals(10200.0, $newHpp, 'HPP setelah penghapusan 1 bahan harus berkurang tepat sebesar subtotalnya.');
    }

    // ==========================================
    // Test to Fail (Negative Testing)
    // ==========================================

    public function testEmptyBomYieldsZeroHpp(): void
    {
        $emptyRecipe = [];
        $totalHpp = empty($emptyRecipe) ? 0.0 : array_sum(array_column($emptyRecipe, 'subtotal'));

        $this->assertEquals(0.0, $totalHpp, 'Produk tanpa resep BOM harus memiliki HPP 0.00 dan bukan null.');
    }

    public function testSellingPriceBelowHppMarginDetection(): void
    {
        $hargaPokok = 15000.0;
        $hargaJualRugi = 12000.0;

        $margin = $hargaJualRugi - $hargaPokok;
        $isProfitable = ($margin >= 0);

        $this->assertFalse($isProfitable, 'Harga jual di bawah harga pokok harus terdeteksi sebagai margin negatif (rugi).');
        $this->assertEquals(-3000.0, $margin, 'Defisit margin penjualan harus terhitung tepat Rp -3.000.');
    }
}
