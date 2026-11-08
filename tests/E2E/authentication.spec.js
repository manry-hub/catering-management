const { test, expect } = require('@playwright/test');

test.describe('E2E Authentication — Kantin Gorjes', () => {

  test('PASS: Pengguna dapat login dan diarahkan ke dashboard', async ({ page }) => {
    await page.goto('/login.php');
    await expect(page.locator('h1')).toContainText('Gorjes');

    await page.fill('input[name="nama_pegawai"]', 'Indri');
    await page.fill('input[name="password"]', '123456');
    await page.click('button[type="submit"]');

    await expect(page).toHaveURL(/.*dashboard\/index\.php/);
    await expect(page.locator('body')).toContainText('Dashboard');
    await expect(page.locator('body')).toContainText('Total Order');

    // Logout
    await page.goto('/logout.php');
    await expect(page).toHaveURL(/.*login\.php/);
  });

  test('FAIL: Login ditolak dengan password salah', async ({ page }) => {
    await page.goto('/login.php');
    await page.fill('input[name="nama_pegawai"]', 'Indri');
    await page.fill('input[name="password"]', 'PasswordSalahTotal');
    await page.click('button[type="submit"]');

    await expect(page.locator('body')).toContainText('Nama atau password salah.');
    await expect(page).toHaveURL(/.*login\.php/);
  });

  test('FAIL: Pengguna tanpa login diarahkan ke login.php saat akses dashboard', async ({ page }) => {
    await page.goto('/dashboard/index.php');
    await expect(page).toHaveURL(/.*login\.php/);
  });

});
