# 3 — Remediasi Cacat Sistem dan Bug Adversarial

Date: 2026-11-08

## Checklist

- [x] Memperbaiki validasi stok sebelum transaksi dan menambahkan konstrain `CHECK (stok >= 0)`
- [x] Mengotomatiskan pemulihan stok saat pesanan dibatalkan
- [x] Mencegah pemulihan stok fiktif saat menghapus pesanan yang sudah berstatus 'Selesai'
- [x] Mencegah manipulasi item pada pesanan yang sudah 'Selesai' atau 'Dibatalkan'
- [x] Menghapus seluruh referensi kolom imajiner `id_detail_pesanan` dan beralih penuh ke composite key `(id_pesanan, id_produk)`
- [x] Membungkus seluruh eksekusi query dengan `try...catch (\mysqli_sql_exception $e)` untuk mencegah crash HTTP 500
- [x] Menangani input cabang kosong agar dievaluasi sebagai `NULL` murni
- [x] Menyelaraskan rumus kalkulasi HPP dan mengotomatiskan update HPP saat harga bahan baku diubah
- [x] Menolak harga jual Rp 0 dan mencegah margin rugi di bawah HPP
- [x] Menutup celah XSS dengan sanitasi `render_flash_error()` di seluruh 23 view dashboard
- [x] Mengamankan endpoint `pesanan/controllers/detail_create.php` dengan CSRF dan Role Guard
- [x] Membatasi wewenang penghapusan cabang kantin hanya untuk Owner dan Admin
- [x] Menegakkan konstrain unik nama pegawai dan whitelist jabatan
- [x] Memfilter pesanan dibatalkan dari query Produk Terlaris di dashboard
- [x] Menjalankan test suite otomatis dengan hasil 98/98 PASS (213 assertions)

## Change Description

Melakukan perbaikan menyeluruh (remediasi) terhadap 24 cacat sistem, dead code, uncaught exception 500, dan kerentanan keamanan yang teridentifikasi selama sesi pengujian adversarial:
1. **Integritas Stok & Inventaris:** Menambahkan konstrain `chk_bahan_baku_stok CHECK (stok >= 0)` dan `chk_bahan_baku_kritis CHECK (stok_kritis >= 0)`. Memastikan controller memeriksa kecukupan stok sebelum pemotongan dijalankan.
2. **Siklus Hidup Pesanan:** Mengotomatiskan pemulihan stok bahan baku saat pesanan diubah ke `'Dibatalkan'`. Mencegah perubahan status ulang pada pesanan yang sudah dibatalkan. Menolak penambahan/pengubahan/penghapusan item pada pesanan yang sudah `'Selesai'` atau `'Dibatalkan'`. Mencegah penambahan stok saat menghapus pesanan berstatus `'Selesai'`.
3. **Pembersihan Kolom Imajiner:** Menghapus seluruh referensi `id_detail_pesanan` pada controller dan tampilan detail pesanan, beralih penuh ke composite key `(id_pesanan, id_produk)`.
4. **Pencegahan Crash HTTP 500:** Membungkus pemanggilan query delete dan update di seluruh modul dengan `try...catch (\mysqli_sql_exception $e)` dan menghubungkannya ke `handle_db_error()` untuk menampilkan pesan ramah.
5. **Kalkulasi BOM & HPP:** Mengotomatiskan sinkronisasi `bom.subtotal` dan `produk.harga_pokok` saat `harga_satuan_bahan` diubah. Menyelaraskan rumus kalkulasi pada `sync_harga_pokok.php` dan `syncHargaPokok()`. Memvalidasi harga jual harus `> 0` dan tidak boleh di bawah HPP. Mencegah penghapusan bahan baku yang masih aktif dipakai di resep BOM.
6. **Keamanan & Otorisasi:** Menambahkan fungsi `render_flash_error()` pada `layouts/config.php` yang secara otomatis meng-escape entitas HTML dan menerapkannya pada seluruh 23 file view. Mengamankan `pesanan/controllers/detail_create.php` dengan CSRF dan guard. Mencabut izin hapus cabang dari peran kasir (`Pegawai Kantin`). Menegakkan konstrain unik pada `pegawai.nama_pegawai` dan whitelist pada `jabatan`.
7. **Metrik Dashboard:** Memperbarui query Produk Terlaris di `dashboard/index.php` agar mengecualikan pesanan berstatus `'Dibatalkan'`.

## Modified Files

- `layouts/config.php` — edit (tambahkan penanganan exception dan fungsi sanitasi flash)
- `sql/ddl.sql` — edit (tambahkan konstrain stok dan unik pegawai)
- `dashboard/detail_pesanan/controllers/create.php` — edit (validasi ketersediaan stok dan status pesanan)
- `dashboard/detail_pesanan/controllers/update.php` — edit (composite key, validasi delta stok)
- `dashboard/detail_pesanan/controllers/delete.php` — edit (composite key, verifikasi status)
- `dashboard/detail_pesanan/detail_pesananubah.php` — edit (hapus id_detail_pesanan)
- `dashboard/detail_pesanan/detail_pesananhapus.php` — edit (hapus id_detail_pesanan)
- `dashboard/detail_pesanan/detail_pesananlihat.php` — edit (sanitasi flash dan perbaikan link)
- `dashboard/pesanan/controllers/create.php` — edit (validasi sesi pegawai dan tanggal acara)
- `dashboard/pesanan/controllers/update.php` — edit (pemulihan stok pembatalan dan NULL cabang)
- `dashboard/pesanan/controllers/delete.php` — edit (pencegahan pemulihan stok pesanan selesai)
- `dashboard/pesanan/controllers/detail_create.php` — edit (proteksi guard, csrf, stok pre-check)
- `dashboard/pesanan/controllers/detail_delete.php` — edit (composite key, verifikasi status)
- `dashboard/bom/controllers/create.php` — edit (try-catch exception)
- `dashboard/bom/controllers/delete.php` — edit (try-catch exception)
- `dashboard/bom/controllers/sync_harga_pokok.php` — edit (penyelarasan rumus HPP dan subtotal)
- `dashboard/bom/controllers/update_harga_jual.php` — edit (tolak harga <= 0 dan margin rugi)
- `dashboard/bahan_baku/controllers/create.php` — edit (validasi stok non-negatif)
- `dashboard/bahan_baku/controllers/update.php` — edit (otomatisasi pembaruan HPP produk terkait)
- `dashboard/bahan_baku/controllers/delete.php` — edit (cegah hapus bahan dalam resep aktif)
- `dashboard/produk/controllers/create.php` — edit (validasi margin terhadap HPP)
- `dashboard/produk/controllers/update.php` — edit (validasi margin terhadap HPP)
- `dashboard/produk/controllers/delete.php` — edit (try-catch exception)
- `dashboard/pelanggan/controllers/create.php` — edit (validasi regex no telepon)
- `dashboard/pelanggan/controllers/update.php` — edit (validasi regex no telepon)
- `dashboard/pelanggan/controllers/delete.php` — edit (try-catch exception)
- `dashboard/cabang_kantin/controllers/create.php` — edit (pembatasan hak kasir)
- `dashboard/cabang_kantin/controllers/update.php` — edit (pembatasan hak kasir)
- `dashboard/cabang_kantin/controllers/delete.php` — edit (pembatasan hak kasir dan try-catch)
- `dashboard/catering/controllers/create.php` — edit (validasi format tanggal)
- `dashboard/catering/controllers/update.php` — edit (validasi format tanggal)
- `dashboard/catering/controllers/delete.php` — edit (try-catch exception)
- `dashboard/pegawai/controllers/create.php` — edit (whitelist jabatan, hirarki owner, cek keunikan nama)
- `dashboard/pegawai/controllers/update.php` — edit (whitelist jabatan, proteksi owner, cek keunikan nama)
- `dashboard/pegawai/controllers/delete.php` — edit (proteksi owner, try-catch)
- `dashboard/index.php` — edit (filter pesanan dibatalkan dari produk terlaris)
- 23 file view dashboard — edit (sanitasi output flash error menggunakan `render_flash_error()`)
- `tests/Support/TestCase.php` — edit (tambahkan `assertNotContains` dan penanganan null pada `assertDatabaseHas`)
- `tests/Unit/AdversarialUnitTest.php` — edit (unit regression tests)
- `tests/Integration/AdversarialIntegrationTest.php` — edit (integration regression tests)

## Review

- Result: Seluruh 24 cacat sistem berhasil diperbaiki tanpa mengubah arsitektur native prosedural PHP.
- Verification: 98 tests otomatis lulus (213 assertions) pada runner PHP terisolasi.
- Follow-up: None.

## UAT (User Acceptance Test) Verification

| # | Test | Result |
| --- | --- | --- |
| 1 | Konstrain database menolak stok bahan baku negatif | Pass |
| 2 | Pemotongan stok pesanan ditolak jika stok bahan baku tidak mencukupi | Pass |
| 3 | Pembatalan pesanan memulihkan stok fisik bahan baku ke database | Pass |
| 4 | Penghapusan pesanan berstatus Selesai tidak membangkitkan stok fiktif | Pass |
| 5 | Manipulasi item pada pesanan Selesai atau Dibatalkan ditolak | Pass |
| 6 | Operasi detail pesanan menggunakan composite key tanpa error kolom id_detail_pesanan | Pass |
| 7 | Hapus entitas master berelasi ditangani dengan pesan ramah tanpa crash 500 | Pass |
| 8 | Input cabang kosong pada update pesanan tersimpan sebagai NULL tanpa crash FK | Pass |
| 9 | Perubahan harga bahan baku otomatis menyinkronkan subtotal BOM dan HPP produk | Pass |
| 10 | Harga jual 0 atau di bawah HPP ditolak | Pass |
| 11 | Pesan flash error disanitasi bebas dari injeksi script XSS | Pass |
| 12 | Endpoint detail_create.php terproteksi CSRF dan otorisasi peran | Pass |
| 13 | Kasir ditolak saat mencoba menghapus cabang kantin | Pass |
| 14 | Duplikasi nama pegawai ditolak oleh database | Pass |
| 15 | Produk dari pesanan dibatalkan tidak masuk dalam ranking Produk Terlaris di dashboard | Pass |
