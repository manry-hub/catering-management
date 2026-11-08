# Authentication Plan: Kantin Gorjes

> **Status:** `CURRENT` (Mode: Existing Project)  
> **Output:** `.agent-knowledge/dynamic/docs/dev/sdd/AUTHENTICATION.md`  
> **Purpose:** Dokumentasi model identitas, alur otentikasi, manajemen sesi, dan siklus kredensial sistem Kantin Gorjes.

---

## 1. Identity Model

Sistem mengelola identitas tunggal internal yaitu entitas **Pegawai** yang tersimpan pada tabel `pegawai`:

- **Pengenal Identitas (Identity Identifier)**: `nama_pegawai` (kolom bertipe `varchar(100)`).
- **Pengenal Unik Sesi**: `id_pegawai` (`int auto_increment`).
- **Atribut Peran**: `jabatan` (`varchar(50)`), mendefinisikan otoritas tugas (misal: 'Owner', 'Admin', 'Pegawai Kantin', 'Pegawai Catering', 'Pegawai Dapur').
- **Entitas Eksternal**: Entitas `pelanggan` pada sistem saat ini tidak memiliki akun login atau kredensial mandiri; pelanggan dicatat sebagai subjek data transaksi oleh pegawai.

---

## 2. Authentication Flow

Alur otentikasi diproses secara langsung oleh skrip [login.php](file:///var/www/gorjes/login.php):

```text
[User / Browser]
       │
       ├─► 1. Akses GET /gorjes/login.php
       │        └─► Jika $_SESSION['id_pegawai'] sudah ada, redirect ke /gorjes/dashboard/index.php
       │
       ├─► 2. Submit Formulir POST (nama_pegawai, password)
       │
       ▼
 [login.php Handler]
       │
       ├─► 3. Sanitasi & Prepared Statement Query:
       │        SELECT * FROM pegawai WHERE nama_pegawai = ? LIMIT 1
       │
       ├─► 4. Evaluasi Kredensial:
       │        if ($user && $password === $user['password'])
       │
       ├─► 5a. [GAGAL]
       │         Set pesan $error = 'Nama atau password salah.'
       │         Render ulang form login dengan alert error
       │
       └─► 5b. [BERHASIL]
                 $_SESSION['id_pegawai']   = $user['id_pegawai'];
                 $_SESSION['nama_pegawai'] = $user['nama_pegawai'];
                 $_SESSION['jabatan']      = $user['jabatan'];
                 header('Location: /gorjes/dashboard/index.php');
```

---

## 3. Session Management & Lifecycle

- **Mekanisme Penyimpanan Sesi**: Native PHP File Session Storage pada sisi server, terhubung dengan peramban melalui cookie HTTP standar `PHPSESSID`.
- **Pengawasan Sesi (Session Guard)**:
  - Berkas [layouts/session.php](file:///var/www/gorjes/layouts/session.php) menginisiasi `session_start()` jika belum aktif.
  - Memeriksa keberadaan `$_SESSION['id_pegawai']`. Jika tidak ditemukan, eksekusi dihentikan dan peramban dialihkan langsung ke `/login.php`.
- **Terminasi Sesi (Logout)**:
  - Diproses oleh [logout.php](file:///var/www/gorjes/logout.php).
  - Mengeksekusi `session_unset()`, `session_destroy()`, dan mengalihkan pengguna kembali ke `/gorjes/login.php`.

---

## 4. Credential Storage & Implementation Status

| Aspek | Standar Implementasi Terkini | Status / Evaluasi |
|-------|------------------------------|-------------------|
| Format Penyimpanan Kata Sandi | Hashing satu arah menggunakan algoritma Bcrypt via `password_hash($password, PASSWORD_BCRYPT)`. | **Selesai (Remediated)**. Seluruh penambahan dan pengubahan akun pegawai di-hash dengan Bcrypt. |
| Verifikasi Login & Auto-Upgrade | Menggunakan `password_verify($password, $user['password'])`. Jika verifikasi langsung berhasil (akun modern), proses lanjut. Jika password masih plaintext lama (`$password === $user['password']`), kata sandi seketika di-hash ulang dan disimpan ke database secara transparan (auto-upgrade). | **Selesai (Remediated)**. Akun legacy otomatis termigrasi tanpa disrupsi operasional. |
| Proteksi Session Fixation | `session_regenerate_id(true)` dipanggil tepat saat login berhasil sebelum menetapkan data sesi dan pengalihan landing page. | **Selesai (Remediated)**. |
| Skrip Migrasi Massal | Tersedia skrip [sql/migrate_passwords.php](file:///var/www/gorjes/sql/migrate_passwords.php) untuk mengonversi seluruh akun seed/lama secara massal. | **Tersedia**. |

---

## 5. Open Questions & Future Hardening

1. **Session Hijacking & Cookie Flags**: Konfigurasi cookie `PHPSESSID` saat ini masih menggunakan konfigurasi bawaan `php.ini` tanpa penegakan eksplisit flag `HttpOnly`, `Secure`, dan `SameSite=Strict`.
2. **Brute-Force Protection**: Belum terdapat pembatasan frekuensi percobaan login (rate limiting) atau penundaan waktu (exponential backoff) pada [login.php](file:///var/www/gorjes/login.php).
3. **Session Timeout**: Belum ada mekanisme kedaluwarsa sesi otomatis (idle timeout) saat pengguna tidak melakukan aktivitas dalam rentang waktu tertentu.
