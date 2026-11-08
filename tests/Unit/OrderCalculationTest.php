<?php

namespace Tests\Unit;

use Tests\Support\TestCase;

class OrderCalculationTest extends TestCase
{
    // ==========================================
    // Test to Pass (Positive Testing)
    // ==========================================

    public function testItemSubtotalCalculation(): void
    {
        $hargaJual = 20000.0;
        $qty = 3;
        $subtotal = $qty * $hargaJual;

        $this->assertEquals(60000.0, $subtotal, 'Subtotal 3 porsi @ Rp 20.000 harus Rp 60.000.');
    }

    public function testOrderTotalAccumulation(): void
    {
        $orderItems = [
            ['id_produk' => 1, 'qty' => 2, 'harga' => 20000.0, 'subtotal' => 40000.0],
            ['id_produk' => 2, 'qty' => 2, 'harga' => 5000.0,  'subtotal' => 10000.0],
            ['id_produk' => 3, 'qty' => 1, 'harga' => 15000.0, 'subtotal' => 15000.0],
        ];

        $totalOrder = array_sum(array_column($orderItems, 'subtotal'));
        $this->assertEquals(65000.0, $totalOrder, 'Akumulasi total nilai pesanan harus tepat Rp 65.000.');
    }

    public function testValidOrderTypesAccepted(): void
    {
        $validTypes = ['Kantin', 'Catering'];

        foreach ($validTypes as $type) {
            $isValid = in_array($type, ['Kantin', 'Catering'], true);
            $this->assertTrue($isValid, "Jenis pesanan '$type' harus diterima oleh validasi.");
        }
    }

    public function testCateringComplementaryFieldsValidationPasses(): void
    {
        $orderData = [
            'jenis_pesanan' => 'Catering',
            'tanggal_acara' => '2026-12-01',
            'lokasi_acara'  => 'Gedung Serbaguna Al Azhar',
        ];

        $errors = [];
        if ($orderData['jenis_pesanan'] === 'Catering') {
            if (empty($orderData['tanggal_acara'])) $errors[] = 'Tanggal acara wajib diisi.';
            if (empty($orderData['lokasi_acara'])) $errors[] = 'Lokasi acara wajib diisi.';
        }

        $this->assertCount(0, $errors, 'Pesanan catering dengan tanggal dan lokasi lengkap harus lolos validasi.');
    }

    public function testCanteenBranchFieldValidationPasses(): void
    {
        $orderData = [
            'jenis_pesanan'    => 'Kantin',
            'id_cabang_kantin' => 1,
        ];

        $errors = [];
        if ($orderData['jenis_pesanan'] === 'Kantin') {
            if (empty($orderData['id_cabang_kantin'])) $errors[] = 'Cabang kantin wajib dipilih.';
        }

        $this->assertCount(0, $errors, 'Pesanan kantin dengan cabang terpilih harus lolos validasi.');
    }

    // ==========================================
    // Test to Fail (Negative Testing)
    // ==========================================

    public function testInvalidOrderTypeRejection(): void
    {
        $invalidTypes = ['Online', 'Delivery', 'Takeaway', '', 'kantin', 'CATERING'];

        foreach ($invalidTypes as $type) {
            $isValid = in_array($type, ['Kantin', 'Catering'], true);
            $this->assertFalse($isValid, "Jenis pesanan tidak valid ('$type') harus ditolak.");
        }
    }

    public function testZeroOrNegativeOrderQuantityRejection(): void
    {
        $invalidQuantities = [0, -1, -50];

        foreach ($invalidQuantities as $qty) {
            $isValid = ($qty > 0);
            $this->assertFalse($isValid, "Kuantitas pesanan tidak valid ($qty) harus ditolak.");
        }
    }

    public function testCateringMissingEventFieldsRejection(): void
    {
        $invalidCateringCases = [
            ['tanggal_acara' => '', 'lokasi_acara' => 'Gedung A'],
            ['tanggal_acara' => '2026-12-01', 'lokasi_acara' => ''],
            ['tanggal_acara' => '', 'lokasi_acara' => ''],
        ];

        foreach ($invalidCateringCases as $idx => $case) {
            $errors = [];
            if (empty($case['tanggal_acara'])) $errors[] = 'Tanggal acara wajib diisi.';
            if (empty($case['lokasi_acara'])) $errors[] = 'Lokasi acara wajib diisi.';

            $this->assertTrue(count($errors) > 0, "Kasus catering #$idx dengan field tidak lengkap harus ditolak.");
        }
    }

    public function testCanteenMissingBranchRejection(): void
    {
        $invalidCanteenCases = [
            ['id_cabang_kantin' => 0],
            ['id_cabang_kantin' => null],
            ['id_cabang_kantin' => ''],
        ];

        foreach ($invalidCanteenCases as $idx => $case) {
            $errors = [];
            if (empty($case['id_cabang_kantin'])) $errors[] = 'Cabang kantin wajib dipilih.';

            $this->assertTrue(count($errors) > 0, "Kasus pesanan kantin #$idx tanpa cabang harus ditolak.");
        }
    }
}
