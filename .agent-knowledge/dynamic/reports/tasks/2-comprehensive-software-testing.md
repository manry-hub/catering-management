# 2: Comprehensive Software Testing Kantin Gorjes

Date: 2026-11-08

## Checklist

- [x] Phase 1: Repository assessment (struktur proyek, skema database, logika bisnis, kesiapan lingkungan pengujian)
- [x] Phase 2: Test infrastructure setup (MariaDB isolated test daemon, database seeder, test runner native PHP, .env.testing)
- [x] Phase 3: Unit testing (39 test cases: Authentication, BOM, HPP, Inventory, Order - Positive & Negative)
- [x] Phase 4: Integration testing (18 test cases: Database queries, ACID transaction, COMMIT & ROLLBACK, stock deduction/restoration)
- [x] Phase 5: End-to-End testing (20 test cases: Browser simulation alur login, bahan baku, BOM, pesanan kantin/katering, detail pesanan)
- [x] Phase 6: Full test suite execution (77/77 PASS, 175 assertions, 0 Failures, 0 Errors)

## Change Description

Membangun infrastruktur dan mengimplementasikan rangkaian pengujian otomatis secara komprehensif pada aplikasi Native Procedural PHP Kantin Gorjes:
1. Test Infrastructure: Membangun test harness native PHP di `tests/Support/TestCase.php` dengan assertions lengkap (equality, truth, exceptions, database checking) dan test runner CLI di `tests/runner.php` tanpa dependensi runtime eksternal. Menjalankan MariaDB testing daemon terisolasi di socket `/tmp/gorjes_test.sock` dengan skema database `gorjes_test_db`.
2. Unit Testing: Membuat 5 berkas pengujian unit di `tests/Unit/` mencakup validasi autentikasi, verifikasi password Bcrypt, kalkulasi subtotal dan desimal BOM, akumulasi total HPP dan margin rugi, batas status ketersediaan inventaris (Tersedia, Menipis, Habis), serta kalkulasi total pesanan.
3. Integration Testing: Membuat 4 berkas pengujian integrasi di `tests/Integration/` yang memverifikasi perubahan aktual data di database sebelum dan sesudah mutasi. Menguji pemotongan stok bahan baku proporsional via BOM, pemulihan stok utuh saat pesanan dihapus, pencegahan record orphan catering, serta integritas ACID ROLLBACK saat terjadi kegagalan di tengah transaksi.
4. End-to-End Testing: Membuat 6 berkas pengujian alur pengguna di `tests/E2E/` dan spec Playwright JS (`playwright.config.js`). Menguji form submit, validasi CSRF token, session cookie handling, proteksi akses peran (RBAC), penolakan input tidak valid, dan pemutakhiran tampilan antarmuka.

## Modified Files

- `layouts/config.php` - edit (mendukung parameter $port dan $socket dari environment variables)
- `logout.php` - edit (memastikan `$_SESSION = []` dan `session_unset()` sebelum `session_destroy()`)
- `.env.testing` - add
- `tests/Support/TestCase.php` - add
- `tests/Support/DatabaseTester.php` - add
- `tests/Support/TestRunner.php` - add
- `tests/Support/BrowserClient.php` - add
- `tests/Support/browser_worker.php` - add
- `tests/Fixtures/DatabaseSeeder.php` - add
- `tests/runner.php` - add
- `tests/Unit/AuthenticationTest.php` - add
- `tests/Unit/BomCalculationTest.php` - add
- `tests/Unit/HppCalculationTest.php` - add
- `tests/Unit/InventoryTest.php` - add
- `tests/Unit/OrderCalculationTest.php` - add
- `tests/Integration/AuthenticationIntegrationTest.php` - add
- `tests/Integration/BomHppIntegrationTest.php` - add
- `tests/Integration/InventoryIntegrationTest.php` - add
- `tests/Integration/OrderIntegrationTest.php` - add
- `tests/E2E/AuthenticationE2ETest.php` - add
- `tests/E2E/BahanBakuE2ETest.php` - add
- `tests/E2E/BomE2ETest.php` - add
- `tests/E2E/PesananKantinE2ETest.php` - add
- `tests/E2E/PesananCateringE2ETest.php` - add
- `tests/E2E/DetailPesananE2ETest.php` - add
- `playwright.config.js` - add
- `tests/E2E/authentication.spec.js` - add
- `tests/E2E/bahan-baku.spec.js` - add
- `tests/E2E/bom.spec.js` - add
- `tests/E2E/pesanan-kantin.spec.js` - add
- `tests/E2E/pesanan-catering.spec.js` - add
- `tests/E2E/detail-pesanan.spec.js` - add
- `.agent-knowledge/dynamic/workflows/2-comprehensive-software-testing.md` - add

## Review

- Result: 77 test cases terimplementasi dan dieksekusi secara nyata dengan tingkat kelulusan 100% (77/77 PASS, 175 assertions, 0 Failures, 0 Errors).
- Verification: Eksekusi `php tests/runner.php` menghasilkan laporan terverifikasi dalam waktu 16.0 detik, dengan audit log tersimpan di `test-results/latest_run.json`.
- Follow-up: Seluruh perubahan tetap berada dalam working tree unstaged sesuai aturan keamanan GIT.md sampai ada instruksi pengguna.

## UAT (User Acceptance Test) Verification

| # | Test | Result |
| --- | --- | --- |
| 1 | Unit Testing: Autentikasi, BOM, HPP, Inventaris, Pesanan (39 tests) | Pass |
| 2 | Integration Testing: Basis data, mutasi stok fisik, cascade, ACID rollback (18 tests) | Pass |
| 3 | End-to-End Testing: Alur browser login, bahan baku, BOM, pesanan kantin/katering (20 tests) | Pass |
| 4 | Verifikasi ACID Commit dan Rollback di database testing | Pass |
| 5 | Eksekusi Full Suite `php tests/runner.php` | Pass |
