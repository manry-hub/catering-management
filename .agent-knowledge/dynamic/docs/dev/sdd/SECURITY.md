# Security Plan: Kantin Gorjes

> **Status:** `CURRENT` (Mode: Existing Project)  
> **Output:** `.agent-knowledge/dynamic/docs/dev/sdd/SECURITY.md`  
> **Purpose:** Dokumentasi baseline keamanan, penanganan rahasia, audit, pemantauan, dan manajemen akses istimewa sistem Kantin Gorjes.

---

## 1. Security Baseline Overview

Sistem saat ini berada pada tahap operasional aplikasi internal terdistribusi lokal. Dokumen ini mengidentifikasi status kontrol keamanan eksisting, potensi risiko eksploitasi, dan panduan pengerasan sistem berdasarkan prinsip *Defense-in-Depth*.

---

## 2. Secret Handling & Credentials

| Aspek | Kondisi Eksisting (Current State) | Risiko / Kerentanan | Status Mitigasi |
|-------|-----------------------------------|---------------------|-----------------|
| **Kredensial Basis Data** | Berkas [layouts/config.php](file:///var/www/gorjes/layouts/config.php) telah mendukung variabel lingkungan (`getenv('DB_HOST')`, `getenv('DB_USER')`, `getenv('DB_PASS')`, `getenv('DB_NAME')`, `getenv('DB_PORT')`) dengan fallback konfigurasi lokal aman. Disediakan berkas [.env.example](file:///var/www/gorjes/.env.example). | Mencegah kebocoran kredensial produksi pada repositori publik. | **Terimplementasi** |
| **Penyimpanan Password Pengguna** | Password pegawai di-hash menggunakan algoritma Bcrypt via `password_hash($password, PASSWORD_BCRYPT)`. Mekanisme login pada [login.php](file:///var/www/gorjes/login.php) secara otomatis mendeteksi kata sandi plaintext lama dan meng-upgradenya ke hash Bcrypt secara instan saat login berhasil. Disediakan pula skrip migrasi [sql/migrate_passwords.php](file:///var/www/gorjes/sql/migrate_passwords.php). | Mencegah kebocoran kredensial jika database terekspos. | **Terimplementasi** |

---

## 3. Vulnerability Surface & Controls

### 3.1 SQL Injection Prevention

- **Status Terkini**: Seluruh query controller pada modul transaksi, inventaris bahan baku, BOM, cabang kantin, pelanggan, pegawai, dan agregasi statistik di [dashboard/index.php](file:///var/www/gorjes/dashboard/index.php) telah menggunakan `mysqli_prepare()` dan `mysqli_stmt_bind_param()` dengan tipe data eksplisit (`intval`, `floatval`, `trim`).
- **Aturan**: Dilarang menginterpolasi variabel POST/GET langsung ke dalam string query SQL.

### 3.2 Cross-Site Scripting (XSS)

- **Status Terkini**: Pesan flash (`flash_error`, `flash_success`) dan seluruh output teks dinamis dibungkus dengan sanitasi `htmlspecialchars($str, ENT_QUOTES, 'UTF-8')`.
- **Aturan**: Pertahankan sanitasi output pada setiap titik rendering HTML.

### 3.3 Cross-Site Request Forgery (CSRF)

- **Status Terkini**: Pustaka pembantu terpusat [layouts/csrf.php](file:///var/www/gorjes/layouts/csrf.php) menyediakan token anti-CSRF berbasis sesi (`get_csrf_token()`, `csrf_field()`, `verify_csrf_token()`).
- **Penegakan**: Seluruh form POST di aplikasi menyertakan `<?= csrf_field() ?>` dan seluruh controller aksi menjalankan `verify_csrf_token()` di baris awal eksekusi sebelum memproses mutasi basis data.

### 3.4 Proteksi Berkas Sensitif pada Web Server

- **Status Terkini**: Berkas [.htaccess](file:///var/www/gorjes/.htaccess) aktif di root direktori `/var/www/gorjes` dengan konfigurasi:
  - Memblokir akses langsung HTTP (403 Forbidden) untuk berkas berekstensi `.sql`, `.env*`, `.git*`, direktori `.agent-knowledge/`, dan berkas dokumentasi `.md`.
  - Mengaktifkan `Options -Indexes` untuk mematikan directory browsing.
  - Menetapkan HTTP Security Headers: `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `X-XSS-Protection: "1; mode=block"`, dan `Referrer-Policy: "strict-origin-when-cross-origin"`.

---

## 4. Audit Logging & Privileged Access

- **Privileged Access**: Peran `Owner` dan `Admin` memiliki wewenang administratif tak terbatas terhadap data pegawai dan transaksi. Namun saat ini sistem belum menerapkan pemisahan tugas formal (separation of duties) atau verifikasi bertingkat untuk tindakan destruktif (seperti penghapusan pesanan dan bahan baku).
- **Audit Logging**: Belum terdapat tabel pencatatan riwayat audit (Audit Trail) untuk mencatat waktu, identitas pegawai, dan rincian perubahan data ketika terjadi penghapusan atau pembaruan transaksi.
- **Rencana Peningkatan**: Tambahkan tabel `log_aktivitas` untuk mencatat aktivitas penting (misal: perubahan harga pokok, penghapusan pesanan, pembaruan stok manual).
