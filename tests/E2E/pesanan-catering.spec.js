const { test, expect } = require('@playwright/test');

test.describe('E2E Pemrosesan Pesanan Catering — Kantin Gorjes', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto('/login.php');
    await page.fill('input[name="nama_pegawai"]', 'Dewi Lestari');
    await page.fill('input[name="password"]', '123456');
    await page.click('button[type="submit"]');
  });

  test('PASS: Membuat pesanan catering dengan tanggal dan lokasi acara', async ({ page }) => {
    await page.goto('/dashboard/pesanan/pesanantambah.php');
    await page.selectOption('select[name="id_pelanggan"]', { index: 1 });
    await page.selectOption('select[name="jenis_pesanan"]', 'Catering');
    await page.fill('input[name="tanggal_acara"]', '2026-12-30');
    await page.fill('textarea[name="lokasi_acara"]', 'Gedung Kartika Ekstrim Jakarta');
    await page.fill('textarea[name="catatan"]', 'Catering 150 Porsi Box');
    await page.click('button[type="submit"]');

    await expect(page).toHaveURL(/.*dashboard\/detail_pesanan\/detail_pesananlihat\.php\?id=\d+/);
  });

  test('FAIL: Pesanan catering ditolak jika tanggal atau lokasi acara kosong', async ({ page }) => {
    await page.goto('/dashboard/pesanan/pesanantambah.php');
    await page.selectOption('select[name="id_pelanggan"]', { index: 1 });
    await page.selectOption('select[name="jenis_pesanan"]', 'Catering');
    await page.click('button[type="submit"]');

    await expect(page.locator('body')).toContainText('Tanggal acara wajib diisi');
  });

});
