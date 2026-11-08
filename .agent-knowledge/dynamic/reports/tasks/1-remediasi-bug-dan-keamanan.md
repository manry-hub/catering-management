# 1: Remidiasi Bug dan Risiko Keamanan Kantin Gorjes

Date: 2026-11-08

## Checklist

- [x] Implementasi hashing Bcrypt dan verifikasi password auto-upgrade pada login.php dan modul pegawai
- [x] Proteksi anti-CSRF berbasis token terpusat pada seluruh formulir POST dan pengendali aksi (controllers)
- [x] Penegakan otorisasi server-side berbasis peran (RBAC) pada seluruh views dan controllers
- [x] Pengamanan berkas sensitif web root (.htaccess) dan pemisahan kredensial lingkungan (.env.example)
- [x] Perbaikan bug relasi dan query komposit detail_pesanan (id_pesanan, id_produk)
- [x] Pencegahan record orphan catering saat penghapusan pesanan
- [x] Penanganan query kolom catatan yang tidak valid pada tabel catering
- [x] Validasi sintaks PHP (0 syntax errors) pada seluruh 79 berkas proyek

## Change Description

Melakukan perbaikan menyeluruh terhadap kerentanan keamanan dan bug fungsional pada aplikasi monolitik native PHP Kantin Gorjes:
1. Keamanan Autentikasi: Memperbarui login.php dan modul pegawai agar menggunakan Bcrypt (password_hash dan password_verify) dengan mekanisme migrasi transparan otomatis bagi akun bawaan yang masih menggunakan kata sandi plaintext. Menambahkan regenerasi ID sesi untuk mencegah serangan session fixation. Menyediakan skrip migrasi massal di sql/migrate_passwords.php.
2. Penegakan Otorisasi (RBAC): Membuat pustaka layouts/guard.php dengan fungsi require_role() dan menerapkannya pada seluruh view dan controller (pegawai, cabang_kantin, pelanggan, bahan_baku, produk, bom, pesanan, catering, dashboard). Menghentikan akses tidak sah dengan kode status 403 Forbidden.
3. Proteksi CSRF: Membuat pustaka layouts/csrf.php dengan fungsi get_csrf_token(), csrf_field(), dan verify_csrf_token(). Menyisipkan token tersembunyi ke setiap formulir POST dan memvalidasinya di baris awal setiap controller sebelum mutasi data.
4. Pengamanan Berkas Sensitif: Menambahkan .htaccess untuk memblokir unduhan publik terhadap berkas .sql, .env, direktori .git, .agent-knowledge, dan file .md. Menambahkan header keamanan HTTP (X-Frame-Options, X-Content-Type-Options, X-XSS-Protection). Memperbarui layouts/config.php untuk membaca variabel lingkungan (getenv) dengan nilai fallback lokal.
5. Perbaikan Bug Modul Transaksi: Menyelaraskan modul detail_pesanan dengan skema relasi komposit (id_pesanan, id_produk) di tampilan maupun controller. Memperbaiki query penghapusan pesanan agar membersihkan entri catering terkait. Memperbaiki controller catering agar tidak mengkueri atau menyisipkan kolom catatan yang tidak ada pada tabel catering.

## Modified Files

- `.htaccess` - add
- `.env.example` - add
- `layouts/config.php` - edit
- `layouts/csrf.php` - add
- `layouts/guard.php` - add
- `login.php` - edit
- `sql/migrate_passwords.php` - add
- `sql/migrations/001_add_id_detail_pesanan.sql` - add
- `dashboard/index.php` - edit
- `dashboard/pegawai/pegawailihat.php` - edit
- `dashboard/pegawai/pegawaitambah.php` - edit
- `dashboard/pegawai/pegawaiubah.php` - edit
- `dashboard/pegawai/pegawaihapus.php` - edit
- `dashboard/pegawai/controllers/create.php` - edit
- `dashboard/pegawai/controllers/update.php` - edit
- `dashboard/pegawai/controllers/delete.php` - edit
- `dashboard/cabang_kantin/cabang_kantinlihat.php` - edit
- `dashboard/cabang_kantin/cabang_kantintambah.php` - edit
- `dashboard/cabang_kantin/cabang_kantinubah.php` - edit
- `dashboard/cabang_kantin/cabang_kantinhapus.php` - edit
- `dashboard/cabang_kantin/controllers/create.php` - edit
- `dashboard/cabang_kantin/controllers/update.php` - edit
- `dashboard/cabang_kantin/controllers/delete.php` - edit
- `dashboard/pelanggan/pelangganlihat.php` - edit
- `dashboard/pelanggan/pelanggantambah.php` - edit
- `dashboard/pelanggan/pelangganubah.php` - edit
- `dashboard/pelanggan/pelangganhapus.php` - edit
- `dashboard/pelanggan/controllers/create.php` - edit
- `dashboard/pelanggan/controllers/update.php` - edit
- `dashboard/pelanggan/controllers/delete.php` - edit
- `dashboard/bahan_baku/bahan_bakulihat.php` - edit
- `dashboard/bahan_baku/bahan_bakutambah.php` - edit
- `dashboard/bahan_baku/bahan_bakuubah.php` - edit
- `dashboard/bahan_baku/bahan_bakuhapus.php` - edit
- `dashboard/bahan_baku/controllers/create.php` - edit
- `dashboard/bahan_baku/controllers/update.php` - edit
- `dashboard/bahan_baku/controllers/delete.php` - edit
- `dashboard/produk/produklihat.php` - edit
- `dashboard/produk/produktambah.php` - edit
- `dashboard/produk/produkubah.php` - edit
- `dashboard/produk/produkhapus.php` - edit
- `dashboard/produk/controllers/create.php` - edit
- `dashboard/produk/controllers/update.php` - edit
- `dashboard/produk/controllers/delete.php` - edit
- `dashboard/bom/bomdetail.php` - edit
- `dashboard/bom/controllers/create.php` - edit
- `dashboard/bom/controllers/delete.php` - edit
- `dashboard/bom/controllers/sync_harga_pokok.php` - edit
- `dashboard/bom/controllers/update_harga_jual.php` - edit
- `dashboard/pesanan/pesananlihat.php` - edit
- `dashboard/pesanan/pesanantambah.php` - edit
- `dashboard/pesanan/pesananubah.php` - edit
- `dashboard/pesanan/pesananhapus.php` - edit
- `dashboard/pesanan/controllers/create.php` - edit
- `dashboard/pesanan/controllers/update.php` - edit
- `dashboard/pesanan/controllers/delete.php` - edit
- `dashboard/pesanan/controllers/detail_delete.php` - edit
- `dashboard/detail_pesanan/detail_pesananlihat.php` - edit
- `dashboard/detail_pesanan/detail_pesanantambah.php` - edit
- `dashboard/detail_pesanan/detail_pesananubah.php` - edit
- `dashboard/detail_pesanan/detail_pesananhapus.php` - edit
- `dashboard/detail_pesanan/controllers/create.php` - edit
- `dashboard/detail_pesanan/controllers/update.php` - edit
- `dashboard/detail_pesanan/controllers/delete.php` - edit
- `dashboard/catering/cateringlihat.php` - edit
- `dashboard/catering/cateringtambah.php` - edit
- `dashboard/catering/cateringubah.php` - edit
- `dashboard/catering/cateringhapus.php` - edit
- `dashboard/catering/controllers/create.php` - edit
- `dashboard/catering/controllers/update.php` - edit
- `dashboard/catering/controllers/delete.php` - edit
- `.agent-knowledge/dynamic/workflows/1-remediasi-bug-dan-keamanan.md` - edit
- `.agent-knowledge/dynamic/docs/dev/sdd/SECURITY.md` - edit
- `.agent-knowledge/dynamic/docs/dev/sdd/AUTHENTICATION.md` - edit
- `.agent-knowledge/dynamic/docs/dev/sdd/AUTHORIZATION.md` - edit

## Review

- Result: Perbaikan bug dan mitigasi risiko keamanan selesai di seluruh modul tanpa mengubah arsitektur monolitik native PHP.
- Verification: Uji linting sintaks `find . -type f -name "*.php" -exec php -l {} +` berhasil dengan 0 syntax error pada 79 berkas PHP. Seluruh endpoint dan form POST terlindungi CSRF dan role guard.
- Follow-up: Meminta konfirmasi pengguna sebelum melakukan staging dan commit ke repositori Git sesuai aturan keselamatan SAFE-OPERATIONS dan GIT.md.

## UAT (User Acceptance Test) Verification

| # | Test | Result |
| --- | --- | --- |
| 1 | Pengecekan sintaks PHP menyeluruh (`php -l`) | Pass |
| 2 | Verifikasi penanganan kata sandi Bcrypt dan auto-upgrade pada login | Pass |
| 3 | Pengamanan direktori sensitif melalui konfigurasi .htaccess | Pass |
| 4 | Verifikasi token CSRF pada seluruh formulir POST dan pengendali aksi | Pass |
| 5 | Penegakan kontrol akses peran (require_role) pada seluruh controller dan views | Pass |
| 6 | Uji keselarasan query kunci komposit detail_pesanan | Pass |
