const { test, expect } = require('@playwright/test');

test.describe('E2E Manajemen BOM & HPP — Kantin Gorjes', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto('/login.php');
    await page.fill('input[name="nama_pegawai"]', 'Siska Nur');
    await page.fill('input[name="password"]', '123456');
    await page.click('button[type="submit"]');
  });

  test('PASS: Melihat detail BOM dan menjalankan sinkronisasi HPP', async ({ page }) => {
    await page.goto('/dashboard/bom/bomdetail.php?id=1');
    await expect(page.locator('body')).toContainText('Ayam Goreng Crispy');
    await expect(page.locator('body')).toContainText('Bill of Materials (BOM)');

    // Klik tombol sinkronisasi HPP
    const syncButton = page.locator('form[action*="sync_harga_pokok.php"] button[type="submit"]');
    if (await syncButton.isVisible()) {
      await syncButton.click();
      await expect(page.locator('body')).toContainText('Harga Pokok berhasil disinkronkan');
    }
  });

  test('FAIL: Input kuantitas bahan resep nol ditolak', async ({ page }) => {
    await page.goto('/dashboard/bom/bomdetail.php?id=2');
    const formToggle = page.locator('#btn-toggle-form');
    if (await formToggle.isVisible()) {
      await formToggle.click();
    }
    await page.fill('input[name="qty"]', '0');
    await page.click('#form-tambah-bom button[type="submit"]');

    await expect(page.locator('body')).toContainText('Qty harus lebih dari 0');
  });

});
