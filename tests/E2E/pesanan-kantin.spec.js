const { test, expect } = require('@playwright/test');

test.describe('E2E Pemrosesan Pesanan Kantin — Kantin Gorjes', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto('/login.php');
    await page.fill('input[name="nama_pegawai"]', 'Rina Putri');
    await page.fill('input[name="password"]', '123456');
    await page.click('button[type="submit"]');
  });

  test('PASS: Membuat pesanan kantin berhasil', async ({ page }) => {
    await page.goto('/dashboard/pesanan/pesanantambah.php');
    await expect(page.locator('h2')).toContainText('Buat Pesanan Baru');

    await page.selectOption('select[name="id_pelanggan"]', { index: 1 });
    await page.selectOption('select[name="jenis_pesanan"]', 'Kantin');
    await page.selectOption('select[name="id_cabang_kantin"]', { index: 1 });
    await page.fill('textarea[name="catatan"]', 'E2E Playwright Kantin Order');
    await page.click('button[type="submit"]');

    // Diarahkan ke detail pesanan
    await expect(page).toHaveURL(/.*dashboard\/detail_pesanan\/detail_pesananlihat\.php\?id=\d+/);
  });

  test('FAIL: Pesanan kantin ditolak jika cabang tidak dipilih', async ({ page }) => {
    await page.goto('/dashboard/pesanan/pesanantambah.php');
    await page.selectOption('select[name="id_pelanggan"]', { index: 1 });
    await page.selectOption('select[name="jenis_pesanan"]', 'Kantin');
    await page.click('button[type="submit"]');

    await expect(page.locator('body')).toContainText('Cabang Kantin wajib dipilih');
  });

});
