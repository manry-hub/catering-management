const { test, expect } = require('@playwright/test');

test.describe('E2E Manajemen Detail Pesanan & Stok — Kantin Gorjes', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto('/login.php');
    await page.fill('input[name="nama_pegawai"]', 'Rina Putri');
    await page.fill('input[name="password"]', '123456');
    await page.click('button[type="submit"]');
  });

  test('PASS: Menambah dan menghapus produk pada detail pesanan', async ({ page }) => {
    // 1. Buat pesanan baru
    await page.goto('/dashboard/pesanan/pesanantambah.php');
    await page.selectOption('select[name="id_pelanggan"]', { index: 1 });
    await page.selectOption('select[name="jenis_pesanan"]', 'Kantin');
    await page.selectOption('select[name="id_cabang_kantin"]', { index: 1 });
    await page.click('button[type="submit"]');

    await expect(page).toHaveURL(/.*dashboard\/detail_pesanan\/detail_pesananlihat\.php\?id=\d+/);

    // 2. Klik Tambah Produk
    await page.click('a:has-text("Tambah Produk")');
    await page.selectOption('select[name="id_produk"]', { index: 1 });
    await page.fill('input[name="qty"]', '2');
    await page.click('button[type="submit"]');

    await expect(page.locator('body')).toContainText('Item produk berhasil ditambahkan');

    // 3. Hapus item produk
    const deleteButton = page.locator('form[action*="controllers/delete.php"] button[type="submit"]').first();
    if (await deleteButton.isVisible()) {
      await deleteButton.click();
      await expect(page.locator('body')).toContainText('berhasil dihapus');
    }
  });

  test('FAIL: Menambah item pesanan dengan kuantitas 0 ditolak', async ({ page }) => {
    await page.goto('/dashboard/detail_pesanan/detail_pesanantambah.php?id_pesanan=1');
    await page.selectOption('select[name="id_produk"]', { index: 1 });
    await page.fill('input[name="qty"]', '0');
    await page.click('button[type="submit"]');

    // Form validation or error message
    await expect(page.locator('body')).toContainText('qty tidak valid');
  });

});
