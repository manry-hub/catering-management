# Implementation Workflow 2: Comprehensive Software Testing Kantin Gorjes

> **Status:** `COMPLETE`  
> **Target Outcome:** Implementasi dan eksekusi rangkaian pengujian menyeluruh (Unit Testing, Integration Testing, End-to-End Testing) dengan pendekatan Test to Pass dan Test to Fail pada aplikasi Kantin Gorjes.  
> **Owner:** Senior QA Automation Engineer & Software Test Engineer  
> **References:**
>
> - [.agent-knowledge/dynamic/docs/dev/PRD.md](file:///var/www/gorjes/.agent-knowledge/dynamic/docs/dev/PRD.md)
> - [.agent-knowledge/dynamic/docs/dev/sdd/ARCHITECTURE.md](file:///var/www/gorjes/.agent-knowledge/dynamic/docs/dev/sdd/ARCHITECTURE.md)
> - [.agent-knowledge/dynamic/docs/dev/sdd/DATABASE.md](file:///var/www/gorjes/.agent-knowledge/dynamic/docs/dev/sdd/DATABASE.md)
> - [.agent-knowledge/dynamic/workflows/1-remediasi-bug-dan-keamanan.md](file:///var/www/gorjes/.agent-knowledge/dynamic/workflows/1-remediasi-bug-dan-keamanan.md)

---

## 1. Objective and Scope

### Objective

Membangun infrastruktur pengujian otomatis terisolasi untuk arsitektur PHP native prosedural dan mengeksekusi pengujian bertingkat (Unit, Integrasi, E2E) dengan verifikasi kondisi aktual database dan status respon pengguna.

### In Scope

- **Phase 1: Repository Assessment** (Audit struktur, skema database, business logic, dependensi) — Selesai.
- **Phase 2: Test Infrastructure** (Test runner mandiri, konfigurasi environment testing, fixture, basis data pengujian terisolasi).
- **Phase 3: Unit Testing** (Autentikasi, kalkulasi BOM & HPP, logika mutasi inventaris, kalkulasi total pesanan, Test to Pass & Test to Fail).
- **Phase 4: Integration Testing** (Transaksi pesanan, mutasi stok proporsional, pencegahan record orphan, verifikasi COMMIT dan ROLLBACK).
- **Phase 5: End-to-End Testing** (Skenario browser pengguna untuk login/logout, CRUD bahan baku, pesanan kantin, pesanan katering, detail pesanan).
- **Phase 6: Test Execution and Reporting** (Eksekusi seluruh test suite dan dokumentasi laporan pengujian).

### Out of Scope

- Migrasi framework atau refactoring arsitektur produksi hanya demi pengujian.
- Menjalankan test pada basis data operasional / produksi.

### Assumptions and Open Questions

- Database testing berjalan terisolasi pada MariaDB custom socket `/tmp/gorjes_test.sock` dengan skema `gorjes_test_db`.
- PHP 8.5.12 CLI tersedia untuk mengeksekusi test runner dan PHP built-in server.

---

## 2. Staged Execution Plan

### Phase 1: Repository Assessment

**Status:** `COMPLETE`  
Audit arsitektur, skema DDL, relasi, logika bisnis, dan dependensi lingkungan selesai dilaporkan.

### Phase 2: Test Infrastructure Setup

**Status:** `COMPLETE`  
**Objective:** Menyediakan fondasi pengujian terisolasi tanpa mencemari lingkungan produksi.

1. Jalankan MariaDB testing daemon terisolasi di `/tmp/gorjes_test.sock` (port 3307).
2. Buat skema dan tabel testing `gorjes_test_db` serta migrasi kata sandi ke Bcrypt.
3. Buat direktori struktur pengujian (`tests/Unit`, `tests/Integration`, `tests/E2E`, `tests/Fixtures`, `tests/Support`).
4. Buat pustaka dasar test harness [tests/Support/TestCase.php](file:///var/www/gorjes/tests/Support/TestCase.php) dan runner [tests/runner.php](file:///var/www/gorjes/tests/runner.php).
5. Siapkan berkas konfigurasi lingkungan testing [.env.testing](file:///var/www/gorjes/.env.testing) dan seeder [tests/Fixtures/DatabaseSeeder.php](file:///var/www/gorjes/tests/Fixtures/DatabaseSeeder.php).

**Exit Criteria:**

- [x] Database testing `gorjes_test_db` siap dan terisolasi.
- [x] Runner `php tests/runner.php` dapat mengeksekusi test case dasar dengan sukses (3/3 pass, 17 assertions).

### Phase 3: Unit Testing

**Status:** `COMPLETE`  
**Objective:** Menguji logika bisnis murni secara terisolasi (Test to Pass & Test to Fail).

1. [tests/Unit/AuthenticationTest.php](file:///var/www/gorjes/tests/Unit/AuthenticationTest.php) (Validasi username, password, format hash Bcrypt, kredensial kosong, password salah, CSRF token, auto-upgrade).
2. [tests/Unit/BomCalculationTest.php](file:///var/www/gorjes/tests/Unit/BomCalculationTest.php) (Kalkulasi subtotal, total BOM, kuantitas nol/negatif, penolakan input tidak valid, duplikasi resep).
3. [tests/Unit/HppCalculationTest.php](file:///var/www/gorjes/tests/Unit/HppCalculationTest.php) (Kalkulasi harga pokok per produk, penanganan BOM kosong, sinkronisasi harga baru, margin rugi).
4. [tests/Unit/InventoryTest.php](file:///var/www/gorjes/tests/Unit/InventoryTest.php) (Status ketersediaan Tersedia/Menipis/Habis, pengurangan dan pengembalian stok, presisi desimal, defisit kekurangan stok).
5. [tests/Unit/OrderCalculationTest.php](file:///var/www/gorjes/tests/Unit/OrderCalculationTest.php) (Subtotal item, total pesanan, validasi jenis pesanan Kantin/Catering, kuantitas invalid, kelengkapan field katering/kantin).

**Exit Criteria:**

- [x] Seluruh unit test case dieksekusi dan dilaporkan status kelulusannya (39/39 PASS, 79 assertions, 0 Failures).

### Phase 4: Integration Testing

**Status:** `COMPLETE`  
**Objective:** Menguji interaksi controller dengan basis data nyata dan transaksi ACID.

1. [tests/Integration/AuthenticationIntegrationTest.php](file:///var/www/gorjes/tests/Integration/AuthenticationIntegrationTest.php) (Login dengan user valid dari DB, kegagalan password, pembentukan sesi, auto-upgrade password ke DB, logout).
2. [tests/Integration/BomHppIntegrationTest.php](file:///var/www/gorjes/tests/Integration/BomHppIntegrationTest.php) (Penambahan BOM ke DB, kalkulasi ulang, update HPP produk di DB, hapus item BOM, cegah duplikasi).
3. [tests/Integration/InventoryIntegrationTest.php](file:///var/www/gorjes/tests/Integration/InventoryIntegrationTest.php) (Mutasi stok fisik di DB, update dinamis Tersedia/Menipis/Habis, cascade delete ke BOM).
4. [tests/Integration/OrderIntegrationTest.php](file:///var/www/gorjes/tests/Integration/OrderIntegrationTest.php) (Pembuatan pesanan, pemotongan stok bahan baku via BOM, penghapusan pesanan dan pengembalian stok utuh, pesanan katering dan pembersihan relasi, verifikasi COMMIT dan ROLLBACK atomis saat kegagalan).

**Exit Criteria:**

- [x] Verifikasi data actual database sebelum dan sesudah operasi transaksi (18/18 PASS, 69 assertions, 0 Failures).
- [x] Transaksi database ACID terbukti COMMIT saat sukses dan ROLLBACK utuh saat gagal tanpa perubahan data parsial.

### Phase 5: End-to-End (E2E) Testing

**Status:** `COMPLETE`  
**Objective:** Menguji alur pengguna nyata melalui antarmuka web.

1. [tests/E2E/AuthenticationE2ETest.php](file:///var/www/gorjes/tests/E2E/AuthenticationE2ETest.php) (Alur login/logout, verifikasi sesi, penolakan password salah, empty credentials, CSRF token invalid, akses tanpa login diblokir).
2. [tests/E2E/BahanBakuE2ETest.php](file:///var/www/gorjes/tests/E2E/BahanBakuE2ETest.php) (Form input bahan baku, penolakan nama kosong, penolakan harga negatif, penolakan peran tidak berhak).
3. [tests/E2E/BomE2ETest.php](file:///var/www/gorjes/tests/E2E/BomE2ETest.php) (Alur detail BOM, trigger sinkronisasi HPP, penolakan kuantitas nol, proteksi peran non-dapur).
4. [tests/E2E/PesananKantinE2ETest.php](file:///var/www/gorjes/tests/E2E/PesananKantinE2ETest.php) (Alur buat pesanan kantin, penolakan tanpa cabang, proteksi peran non-transaksi).
5. [tests/E2E/PesananCateringE2ETest.php](file:///var/www/gorjes/tests/E2E/PesananCateringE2ETest.php) (Alur buat pesanan katering, penyimpanan entitas catering, penolakan tanpa tanggal/lokasi acara).
6. [tests/E2E/DetailPesananE2ETest.php](file:///var/www/gorjes/tests/E2E/DetailPesananE2ETest.php) (Alur penambahan produk ke pesanan, pemotongan stok otomatis di DB, penghapusan item dan pemulihan stok utuh, penolakan qty nol/negatif).
7. Konfigurasi dan spec Playwright JS: [playwright.config.js](file:///var/www/gorjes/playwright.config.js), [authentication.spec.js](file:///var/www/gorjes/tests/E2E/authentication.spec.js), [bahan-baku.spec.js](file:///var/www/gorjes/tests/E2E/bahan-baku.spec.js), [bom.spec.js](file:///var/www/gorjes/tests/E2E/bom.spec.js), [pesanan-kantin.spec.js](file:///var/www/gorjes/tests/E2E/pesanan-kantin.spec.js), [pesanan-catering.spec.js](file:///var/www/gorjes/tests/E2E/pesanan-catering.spec.js), [detail-pesanan.spec.js](file:///var/www/gorjes/tests/E2E/detail-pesanan.spec.js).

**Exit Criteria:**

- [x] Seluruh alur browser selesai diuji dan didokumentasikan (20/20 PASS, 27 assertions, 0 Failures).

### Phase 6: Test Execution & Reporting

**Status:** `COMPLETE`  
**Objective:** Menyusun laporan pengujian komprehensif.

1. Eksekusi seluruh rangkaian pengujian: Total 77 test case (39 Unit + 18 Integration + 20 E2E) dengan 175 assertions, 0 Failures, 0 Errors.
2. Catat jumlah test case, pass, fail, dan temuan risiko di `test-results/latest_run.json`.
3. Terbitkan laporan pengujian bernomor di [.agent-knowledge/dynamic/reports/tasks/2-comprehensive-software-testing.md](file:///var/www/gorjes/.agent-knowledge/dynamic/reports/tasks/2-comprehensive-software-testing.md).

---

## 3. Completion Criteria

- [x] Seluruh fase pengujian dieksekusi secara nyata dengan bukti hasil pengujian (77/77 PASS).
- [x] Tidak ada klaim pengujian lulus tanpa eksekusi langsung.
