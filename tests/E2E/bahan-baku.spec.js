const { test, expect } = require('@playwright/test');

test.describe('E2E Manajemen Bahan Baku — Kantin Gorjes', () => {

  test.beforeEach(async ({ page }) => {
    // Login as Pegawai Dapur
    await page.goto('/login.php');
    await page.fill('input[name="nama_pegawai"]', 'Siska Nur');
    await page.fill('input[name="password"]', '123456');
    await page.click('button[type="submit"]');
  });

  test('PASS: Menambah dan melihat bahan baku tersimpan', async ({ page }) => {
    await page.goto('/dashboard/bahan_baku/bahan_bakutambah.php');
    await page.fill('input[name="nama_bahan"]', 'Garam Halus Dapur');
    await page.fill('input[name="satuan"]', 'kg');
    await page.fill('input[name="harga_satuan_bahan"]', '8000');
    await page.fill('input[name="stok"]', '15');
    await page.fill('input[name="stok_kritis"]', '3');
    await page.click('button[type="submit"]');

    await expect(page).toHaveURL(/.*dashboard\/bahan_baku\/bahan_bakulihat\.php/);
    await expect(page.locator('body')).toContainText('Garam Halus Dapur');
  });

  test('FAIL: Menambah bahan baku ditolak jika nama kosong', async ({ page }) => {
    await page.goto('/dashboard/bahan_baku/bahan_bakutambah.php');
    await page.fill('input[name="nama_bahan"]', '');
    await page.fill('input[name="satuan"]', 'kg');
    await page.fill('input[name="harga_satuan_bahan"]', '5000');

    // HTML5 validation or form rejection
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/.*dashboard\/bahan_baku\/bahan_bakutambah\.php/);
  });

});
