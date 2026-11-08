# Implementation Workflow 3: Remediasi Cacat Sistem dan Bug Adversarial

**Status:** COMPLETE  
**Target Outcome:** Seluruh 24 bug logika, dead code, uncaught exception 500, celah keamanan, dan inkonsistensi data yang ditemukan selama pengujian adversarial diperbaiki secara tuntas tanpa merusak arsitektur Native PHP procedural.  
**Owner:** QA & Core Engineering Team  
**References:**
- `.agent-knowledge/dynamic/workflows/2-comprehensive-software-testing.md`
- `sql/ddl.sql`
- `tests/Unit/AdversarialUnitTest.php`
- `tests/Integration/AdversarialIntegrationTest.php`

---

## 1. Objective and Scope

### Objective
Memperbaiki secara komprehensif seluruh 24 titik kegagalan yang ditemukan melalui pengujian adversarial agar sistem Kantin Gorjes memiliki integritas data yang kokoh, penanganan kesalahan database yang elegan tanpa crash HTTP 500, perhitungan HPP yang sinkron, proteksi keamanan terhadap XSS dan bypass autentikasi, serta pembatasan peran operasional yang aman.

### In Scope
1. Penegakan validasi ketersediaan stok sebelum pemotongan (`stok >= kebutuhan`).
2. Pemulihan stok bahan baku otomatis saat status pesanan diubah ke `'Dibatalkan'`.
3. Pencegahan pemulihan stok fiktif saat menghapus pesanan yang sudah berstatus `'Selesai'`.
4. Penolakan manipulasi item pesanan pada pesanan yang sudah berstatus `'Selesai'` atau `'Dibatalkan'`.
5. Pembersihan atau pencegahan pesanan hantu tanpa item (`total_harga = 0.00`).
6. Penghapusan seluruh referensi kolom imajiner `id_detail_pesanan` pada controller dan tampilan, dialihkan penuh ke composite key `(id_pesanan, id_produk)`.
7. Pembungkusan seluruh eksekusi query database dengan `try...catch (\mysqli_sql_exception $e)` untuk mencegah crash HTTP 500 dan menampilkan pesan ramah.
8. Penanganan input cabang kosong (`""`) agar menjadi `NULL` pada `pesanan/controllers/update.php`.
9. Penyelarasan rumus HPP antara `sync_harga_pokok.php` dan `syncHargaPokok()`, serta pembaruan otomatis HPP produk saat harga bahan baku diubah.
10. Validasi harga jual minimum (`> 0`) dan peringatan margin di bawah HPP.
11. Sanitasi output flash message menggunakan `htmlspecialchars()` di seluruh 22 file view dashboard.
12. Pengamanan endpoint `pesanan/controllers/detail_create.php` dengan `guard.php` dan `csrf.php`.
13. Pengetatan otorisasi peran kasir (menghapus wewenang menghapus cabang kantin).
14. Validasi keunikan `nama_pegawai` dan pembatasan whitelist pada kolom `jabatan`.
15. Pemfilteran pesanan dibatalkan dari query Produk Terlaris di `dashboard/index.php`.

### Out of Scope
- Migrasi ke framework Laravel/Symfony.
- Pengubahan driver MySQLi ke PDO.

### Assumptions and Open Questions
- Aplikasi tetap menggunakan PHP 8.x procedural dengan MySQLi driver.
- Tabel `detail_pesanan` tetap menggunakan composite primary key `(id_pesanan, id_produk)`.

---

## 2. Staged Execution Plan

### Stage 1: Inventaris dan Siklus Hidup Pesanan
**Objective:** Mengamankan integritas stok bahan baku dan siklus status pesanan.
1. Perbarui `detail_pesanan/controllers/create.php` dan `update.php` untuk memvalidasi ketersediaan stok setiap bahan BOM sebelum transaksi pemotongan dijalankan.
2. Tambahkan pengecekan status pesanan (`status_pesanan === 'Diproses'`) sebelum mengizinkan penambahan, perubahan, atau penghapusan item pesanan.
3. Perbarui `pesanan/controllers/update.php` agar saat status pesanan diubah menjadi `'Dibatalkan'`, stok seluruh bahan baku dari item pesanan dikembalikan secara proporsional.
4. Perbarui `pesanan/controllers/delete.php` agar hanya mengembalikan stok jika pesanan belum berstatus `'Selesai'` atau `'Dibatalkan'`.
5. Perbaiki penanganan cabang kosong di `pesanan/controllers/update.php` agar tidak mengikat integer 0 melainkan NULL.

**Exit Criteria:**
- [x] Stok bahan baku tidak pernah bernilai negatif.
- [x] Pembatalan pesanan mengembalikan stok ke gudang secara akurat.
- [x] Pesanan selesai tidak dapat dimanipulasi atau mengembalikan stok saat dihapus.

### Stage 2: Penghapusan Kolom Imajiner dan Penanganan Exception Database
**Objective:** Menghilangkan penyebab crash HTTP 500 dan mengaktifkan penanganan kesalahan database yang elegan.
1. Bersihkan seluruh referensi `id_detail_pesanan` pada `detail_pesanan/controllers/update.php`, `delete.php`, `detail_pesananubah.php`, `detail_pesananhapus.php`, `detail_pesananlihat.php`, dan `pesanan/controllers/detail_delete.php`.
2. Bungkus seluruh eksekusi `mysqli_stmt_execute()` di seluruh controller delete/create/update dengan `try...catch (\mysqli_sql_exception $e)` dan alihkan pesan error yang informatif ke sesi flash error.

**Exit Criteria:**
- [x] Penghapusan data berelasi menampilkan pesan error ramah tanpa crash 500.
- [x] Operasi item pesanan berjalan mulus menggunakan composite key `(id_pesanan, id_produk)`.

### Stage 3: Penyelarasan BOM dan HPP Produk
**Objective:** Memastikan perhitungan HPP selalu sinkron dan konsisten.
1. Samakan rumus di `sync_harga_pokok.php` menggunakan `SUM(bom.qty * bb.harga_satuan_bahan)`.
2. Tambahkan pembaruan otomatis HPP produk pada `bahan_baku/controllers/update.php` saat harga satuan bahan baku berubah.
3. Perbarui `bom/controllers/update_harga_jual.php` agar menolak `harga_jual <= 0`.
4. Tambahkan validasi stok dan stok kritis tidak boleh negatif di `bahan_baku/controllers/create.php` dan `update.php`.

**Exit Criteria:**
- [x] HPP selalu akurat dan sinkron di seluruh controller.
- [x] Harga jual 0 ditolak.

### Stage 4: Pengamanan Keamanan dan Peran Pengguna
**Objective:** Menutup celah XSS, endpoint tak terproteksi, dan eskalasi hak istimewa.
1. Bungkus `<?= $_SESSION['flash_error'] ?>` dan `<?= $_SESSION['flash_success'] ?>` dengan `htmlspecialchars()` di seluruh 22 file view.
2. Tambahkan `guard.php`, `csrf.php`, dan verifikasi token/role pada `pesanan/controllers/detail_create.php`.
3. Hapus `Pegawai Kantin` dari hak istimewa menghapus cabang di `cabang_kantin/controllers/delete.php`.
4. Validasi keunikan `nama_pegawai` dan whitelist `jabatan` di `pegawai/controllers/create.php` dan `update.php`.

**Exit Criteria:**
- [x] Seluruh tampilan kebal terhadap reflected/stored XSS.
- [x] Tidak ada endpoint pesanan yang lolos tanpa autentikasi dan CSRF.
- [x] Kasir tidak dapat menghapus cabang.

### Stage 5: Metrik Dashboard dan Optimasi
**Objective:** Menjamin akurasi laporan statistik dashboard.
1. Modifikasi query `top_produk` di `dashboard/index.php` untuk memfilter pesanan yang berstatus `'Dibatalkan'`.

**Exit Criteria:**
- [x] Pesanan dibatalkan tidak masuk dalam perhitungan produk terlaris.

### Stage 6: Verifikasi dan Pengujian Regresi
**Objective:** Memastikan seluruh tes otomatis (104+ test) lulus dengan status hijau.
1. Sesuaikan test assertion di Adversarial tests untuk memastikan sistem berhasil menolak skenario cacat.
2. Jalankan rangkaian tes lengkap `php tests/runner.php`.

**Exit Criteria:**
- [x] 100% tes otomatis lulus.
