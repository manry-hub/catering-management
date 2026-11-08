# Implementation Workflow 1: Remidiasi Bug dan Risiko Keamanan Kantin Gorjes

> **Status:** `COMPLETE`  
> **Target Outcome:** Perbaikan menyeluruh terhadap kerentanan keamanan (hashing password Bcrypt, proteksi CSRF, penegakan otorisasi tingkat controller, pengamanan berkas sensitif web root) dan perbaikan bug fungsional inkonsistensi skema detail pesanan.  
> **Owner:** Security Architect & Code Reviewer  
> **References:**
>
> - [.agent-knowledge/dynamic/docs/dev/PRD.md](file:///var/www/gorjes/.agent-knowledge/dynamic/docs/dev/PRD.md)
> - [.agent-knowledge/dynamic/docs/dev/sdd/ARCHITECTURE.md](file:///var/www/gorjes/.agent-knowledge/dynamic/docs/dev/sdd/ARCHITECTURE.md)
> - [.agent-knowledge/dynamic/docs/dev/sdd/SECURITY.md](file:///var/www/gorjes/.agent-knowledge/dynamic/docs/dev/sdd/SECURITY.md)
> - [.agent-knowledge/dynamic/docs/dev/sdd/AUTHENTICATION.md](file:///var/www/gorjes/.agent-knowledge/dynamic/docs/dev/sdd/AUTHENTICATION.md)
> - [.agent-knowledge/dynamic/docs/dev/sdd/AUTHORIZATION.md](file:///var/www/gorjes/.agent-knowledge/dynamic/docs/dev/sdd/AUTHORIZATION.md)
> - [.agent-knowledge/dynamic/docs/dev/sdd/DATABASE.md](file:///var/www/gorjes/.agent-knowledge/dynamic/docs/dev/sdd/DATABASE.md)

---

## 1. Objective and Scope

### Objective

Mengamankan seluruh permukaan serangan aplikasi Kantin Gorjes sesuai prinsip *Defense-in-Depth* dan memperbaiki bug logika pada modul transaksi pemesanan tanpa merusak arsitektur monolitik native PHP yang ada.

### In Scope

- **Keamanan Kredensial & Autentikasi**: Implementasi `password_hash()` (Bcrypt) dan `password_verify()` pada `login.php` dan modul CRUD pegawai, dengan dukungan auto-upgrade transparan untuk kata sandi plaintext lama.
- **Proteksi CSRF**: Modul pembangkitan dan verifikasi token CSRF terpusat pada seluruh formulir POST dan pengendali aksi.
- **Otorisasi Controller & IDOR Defense**: Penegakan validasi hak akses peran (`$_SESSION['jabatan']`) langsung di sisi server controller pada seluruh modul (`pegawai`, `cabang_kantin`, `bahan_baku`, `bom`, `produk`, `pesanan`, `detail_pesanan`, `pelanggan`, `catering`).
- **Penyembunyian Berkas Sensitif & Abstraksi Konfigurasi**: Pembuatan `.htaccess` untuk memblokir akses HTTP publik ke berkas `.sql`, `.env`, dan `.git`, serta penambahan pembacaan variabel lingkungan pada konfigurasi database.
- **Perbaikan Bug Detail Pesanan**: Penyesuaian antarmuka dan controller detail pesanan agar mendukung primary key komposit `(id_pesanan, id_produk)` serta fleksibilitas kolom `id_detail_pesanan`.

### Out of Scope

- Migrasi framework ke Laravel/Symfony atau penulisan ulang ke arsitektur microservices.
- Perubahan tema visual antarmuka atau perancangan ulang desain grafis.
- Integrasi payment gateway eksternal pihak ketiga (Midtrans/Xendit).

### Assumptions and Open Questions

- Database lokal dan runtime server menjalankan PHP 8.x dengan ekstensi `mysqli` dan `session`.
- Akun pegawai yang ada saat ini menggunakan kata sandi bawaan `123456` dan dapat diperbarui otomatis saat login pertama kali.

---

## 2. Staged Execution Plan

### Stage 1: Fondasi Keamanan Terpusat (CSRF, Guard Otorisasi, dan Proteksi Web Root)

**Objective:** Menyediakan pustaka pembantu modular untuk kontrol akses peran, proteksi token CSRF, dan konfigurasi server.

1. Buat berkas proteksi web server `.htaccess` untuk memblokir unduhan langsung berkas `.sql`, direktori `.git`, dan dokumen rahasia.
2. Buat pustaka pembantu [layouts/csrf.php](file:///var/www/gorjes/layouts/csrf.php) untuk manajemen token CSRF.
3. Buat pustaka pembantu [layouts/guard.php](file:///var/www/gorjes/layouts/guard.php) untuk fungsi penegakan peran `require_role(array $allowed_roles)`.
4. Perbarui [layouts/config.php](file:///var/www/gorjes/layouts/config.php) untuk mendukung variabel lingkungan (`getenv`) dengan nilai fallback yang aman.

**Exit Criteria:**

- [x] Berkas `.htaccess` aktif melindungi berkas sensitif dari akses web langsung.
- [x] Modul CSRF dan Guard tersedia untuk di-include oleh controller dan views.

### Stage 2: Remidiasi Autentikasi dan Hashing Kata Sandi

**Objective:** Mencegah kebocoran kata sandi dengan menerapkan hashing satu arah dan mekanisme auto-upgrade.

1. Perbarui [login.php](file:///var/www/gorjes/login.php) agar menggunakan `password_verify()`. Jika kata sandi cocok dengan string plaintext lama, sistem meng-upgrade dan menyimpan hash Bcrypt baru ke database seketika.
2. Perbarui [dashboard/pegawai/controllers/create.php](file:///var/www/gorjes/dashboard/pegawai/controllers/create.php) untuk mengenkripsi kata sandi pegawai baru menggunakan `password_hash($pwd, PASSWORD_BCRYPT)`.
3. Perbarui [dashboard/pegawai/controllers/update.php](file:///var/www/gorjes/dashboard/pegawai/controllers/update.php) agar hanya memperbarui kata sandi jika diisi, dan menyimpannya dalam format hash Bcrypt.
4. Buat skrip migrasi sekali jalan [sql/migrate_passwords.php](file:///var/www/gorjes/sql/migrate_passwords.php) untuk mengenkripsi seluruh akun seed bawaan di database.

**Exit Criteria:**

- [x] Tidak ada lagi penyimpanan kata sandi plaintext baru di tabel `pegawai`.
- [x] Pengguna lama dengan kata sandi plaintext tetap dapat login dan otomatis di-upgrade ke hash Bcrypt.

### Stage 3: Penegakan Otorisasi RBAC dan Proteksi CSRF di Seluruh Controller

**Objective:** Menutup celah Broken Access Control (IDOR) dan CSRF pada setiap endpoint form.

1. Sisipkan `require_role(['Owner', 'Admin'])` dan `verify_csrf_token()` pada seluruh controller pegawai.
2. Sisipkan verifikasi peran dan token CSRF pada controller cabang kantin, bahan baku, produk, BOM, pesanan, dan catering.
3. Tambahkan elemen formulir tersembunyi `<input type="hidden" name="csrf_token" value="...">` pada setiap form aksi di modul-modul terkait.

**Exit Criteria:**

- [x] Setiap form POST memiliki token CSRF.
- [x] Permintaan POST langsung dari pengguna yang tidak berhak ditolak dengan kode status 403 Forbidden.

### Stage 4: Perbaikan Bug Logika Modul Detail Pesanan

**Objective:** Memperbaiki bug query pada `detail_pesanan` agar selaras dengan skema database riil.

1. Perbarui [dashboard/detail_pesanan/detail_pesananlihat.php](file:///var/www/gorjes/dashboard/detail_pesanan/detail_pesananlihat.php) agar tautan hapus dan ubah menggunakan parameter komposit `id_pesanan` dan `id_produk`.
2. Perbarui [dashboard/detail_pesanan/detail_pesananhapus.php](file:///var/www/gorjes/dashboard/detail_pesanan/detail_pesananhapus.php) dan [detail_pesananubah.php](file:///var/www/gorjes/dashboard/detail_pesanan/detail_pesananubah.php) agar mengambil data berdasarkan relasi komposit `(id_pesanan, id_produk)`.
3. Sesuaikan controller [dashboard/detail_pesanan/controllers/delete.php](file:///var/www/gorjes/dashboard/detail_pesanan/controllers/delete.php), `update.php`, dan [dashboard/pesanan/controllers/detail_delete.php](file:///var/www/gorjes/dashboard/pesanan/controllers/detail_delete.php) agar mengeksekusi query yang valid tanpa error 'Unknown column id_detail_pesanan'.
4. Perbarui [dashboard/pesanan/controllers/delete.php](file:///var/www/gorjes/dashboard/pesanan/controllers/delete.php) agar membersihkan record `catering` terkait saat pesanan katering dihapus untuk mencegah record yatim (orphaned record).

**Exit Criteria:**

- [x] Operasi tambah, ubah jumlah, dan hapus item produk pada pesanan berjalan mulus tanpa error kolom.
- [x] Stok bahan baku terpotong dan terpulihkan secara akurat sesuai rasio BOM.

### Stage 5: Verifikasi Menyeluruh & Pembaruan Dokumentasi

**Objective:** Memastikan seluruh berkas bebas syntax error dan memvalidasi integritas sistem.

1. Jalankan pemeriksaan sintaks PHP (`php -l`) pada setiap berkas yang dimodifikasi.
2. Periksa kembali seluruh kesesuaian living documentation ([PRD.md](file:///var/www/gorjes/.agent-knowledge/dynamic/docs/dev/PRD.md) dan SDD).
3. Buat laporan pelaksanaan tugas bernomor di `.agent-knowledge/dynamic/reports/tasks/`.

**Exit Criteria:**

- [x] Seluruh berkas PHP lolos pengecekan sintaks (0 syntax errors).
- [x] Laporan tugas selesai mendokumentasikan rincian perubahan.

---

## 3. Dependencies, Risks, and Rollback

### Dependencies

- Web server mendukung modul Apache `mod_rewrite` atau parsing `.htaccess`.
- Dukungan fungsi native `password_hash` pada PHP 8.x.

### Risks and Mitigations

| Risk | Impact | Mitigation |
| --- | --- | --- |
| Pengguna lama terkunci karena perubahan hashing kata sandi | Staf tidak dapat login ke sistem | Implementasikan auto-upgrade fallback: verifikasi plaintext terlebih dahulu, kemudian langsung hash dan timpa ke database jika cocok. |
| Token CSRF kedaluwarsa atau hilang saat tab terbuka lama | Formulir ditolak saat submit | Buat pesan kesalahan flash yang informatif dan arahkan pengguna untuk mengulangi submit dengan token baru. |
| Error pada penghapusan item pesanan | Stok bahan baku menjadi tidak sinkron | Seluruh mutasi database dibungkus dalam blok `mysqli_begin_transaction` dengan `rollback` jika terjadi kegagalan. |

### Rollback or Recovery

Setiap perubahan dilakukan secara bedah pada berkas-berkas terkait. Git status memantau seluruh diff berkas; jika terjadi kegagalan, pemulihan dapat dilakukan melalui `git checkout` / `git restore` pada berkas bersangkutan.

---

## 4. Verification and Acceptance Criteria

| Check | Method | Acceptance Criteria |
| --- | --- | --- |
| Syntax Integrity | `php -l <file>` | Tidak ada kesalahan sintaks atau parse error pada seluruh berkas PHP yang diedit. |
| Password Hashing | Kode inspeksi & simulasi verifikasi | Password baru tersimpan sebagai hash Bcrypt (`$2y$...`); login lama berhasil di-upgrade. |
| CSRF Enforcement | Pengujian formulir POST | Permintaan POST tanpa `csrf_token` atau dengan token salah ditolak dengan pesan kesalahan. |
| RBAC Enforcement | Inspeksi peran controller | Peran yang tidak sesuai dialihkan dengan status 403 atau pesan peringatan. |
| Detail Pesanan Fix | Alur CRUD item | Aksi ubah qty dan hapus item pesanan berjalan sukses dan mengembalikan stok BOM. |

---

## 5. Completion and Handoff

- [x] All staged exit criteria and acceptance criteria are met.
- [x] Relevant living documentation, ADRs, and specifications are updated or linked.
- [x] The task report records actual changes and verification results.

Next transition: Selesai. Pengujian statis dan sintaks selesai 100%. Laporan tugas diterbitkan.
