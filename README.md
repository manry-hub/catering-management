# Kantin Gorjes - Luxury Dining Management System

Sistem Informasi Manajemen Operasional Kantin, Layanan Catering, Manajemen Inventaris Bahan Baku, Resep Produk (*Bill of Materials* / BOM), dan Sinkronisasi Harga Pokok Penjualan (HPP) berbasis Native PHP 8.x dan MySQL/MariaDB dengan antarmuka bertema *Luxury Editorial*.

---

## 1. Kebutuhan Sistem

Pastikan lingkungan lokal atau server Anda telah memenuhi spesifikasi berikut:

- **PHP:** Versi 8.1 atau lebih baru
- **Ekstensi PHP:**
  - `mysqli` (koneksi database)
  - `session` (autentikasi dan sesi pengguna)
  - `json` (pertukaran data grafis dan PDF)
  - `mbstring` (manipulasi string multibyte)
- **Database Server:** MariaDB 10.5+ atau MySQL 8.0+
- **Web Server:** Apache, Nginx, atau PHP Built-in Web Server
- **Web Browser:** Google Chrome, Mozilla Firefox, Microsoft Edge, atau Safari versi modern

---

## 2. Struktur Direktori Proyek

```text
/var/www/gorjes/
├── assets/                 # Aset gambar, logo, dan ikon
├── dashboard/              # Modul utama aplikasi
│   ├── index.php           # Halaman ringkasan eksekutif (Dashboard)
│   ├── bahan_baku/         # Manajemen inventaris bahan baku
│   ├── bom/                # Manajemen resep BOM & kalkulasi HPP
│   ├── cabang_kantin/      # Manajemen cabang fisik kantin
│   ├── catering/           # Manajemen jadwal dan pesanan catering
│   ├── detail_pesanan/     # Rincian item transaksi & ekspor PDF
│   ├── pegawai/            # Manajemen data akun & jabatan pegawai
│   ├── pelanggan/          # Direktori pelanggan
│   ├── pesanan/            # Pemrosesan pesanan kantin & catering
│   └── produk/             # Katalog menu dan harga jual
├── layouts/                # Komponen antarmuka bersama
│   ├── config.php          # Konfigurasi koneksi database & helper
│   ├── csrf.php            # Proteksi token CSRF
│   ├── footer.php          # Penutup kontainer layout
│   ├── guard.php           # Middleware pembatas hak akses (Role Guard)
│   ├── head.php            # Tag meta, font Google, Tailwind CDN
│   ├── menu.php            # Navigasi desktop & drawer responsif mobile
│   ├── session.php         # Inisialisasi sesi aman
│   └── vendor-scripts.php  # Chart.js dan interaktivitas sidebar
├── sql/
│   ├── ddl.sql             # Skema struktur tabel dan konstrain database
│   ├── seed.sql            # Data awal (pegawai, cabang, produk, bahan)
│   └── migrate_passwords.php # Utilitas migrasi hash password Bcrypt
├── index.php               # Halaman depan / Landing page
├── login.php               # Portal masuk sistem
├── logout.php              # Penghancur sesi & keluar sistem
└── README.md               # Dokumentasi panduan instalasi
```

---

## 3. Langkah Instalasi & Konfigurasi

### Langkah 1: Persiapan Database

1. Buka terminal atau konsol database (misal: MySQL CLI, phpMyAdmin, DBeaver, atau HeidiSQL).
2. Buat database baru bernama `sistem_pemesanan_kantin_gorjes_db`:
   ```sql
   CREATE DATABASE sistem_pemesanan_kantin_gorjes_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Import berkas struktur tabel ([`sql/ddl.sql`](file:///var/www/gorjes/sql/ddl.sql)):
   ```bash
   mysql -u root -p sistem_pemesanan_kantin_gorjes_db < sql/ddl.sql
   ```
4. Import berkas data awal ([`sql/seed.sql`](file:///var/www/gorjes/sql/seed.sql)):
   ```bash
   mysql -u root -p sistem_pemesanan_kantin_gorjes_db < sql/seed.sql
   ```

### Langkah 2: Konfigurasi Koneksi Database

Konfigurasi koneksi database berada pada berkas [`layouts/config.php`](file:///var/www/gorjes/layouts/config.php).

Secara default, aplikasi membaca konfigurasi berikut:
- **Host:** `localhost`
- **User:** `root`
- **Password:** `Sorry.240106` (atau kosongkan / sesuaikan dengan kredensial database lokal Anda)
- **Database:** `sistem_pemesanan_kantin_gorjes_db`

Jika Anda menggunakan kredensial database yang berbeda, Anda dapat:
1. Menyesuaikan nilai variabel pada baris 2-6 di berkas [`layouts/config.php`](file:///var/www/gorjes/layouts/config.php):
   ```php
   $host     = getenv('DB_HOST') ?: 'localhost';
   $user     = getenv('DB_USER') ?: 'root';
   $password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'password_database_anda';
   $database = getenv('DB_NAME') ?: 'sistem_pemesanan_kantin_gorjes_db';
   $port     = getenv('DB_PORT') ? intval(getenv('DB_PORT')) : null;
   ```
2. Atau mengatur Environment Variables pada server Anda (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, `DB_PORT`).

---

## 4. Cara Menjalankan Aplikasi

Pilih salah satu cara di bawah ini yang sesuai dengan lingkungan kerja Anda:

### Opsi A: Menggunakan PHP Built-in Server (Direkomendasikan & Tercepat)

Aplikasi ini menggunakan tautan absolut berbasis rute `/gorjes/...`. Untuk pengalaman terbaik dengan built-in server:

1. Buka terminal pada direktori induk tempat folder `gorjes` berada (misalnya `/var/www`):
   ```bash
   cd /var/www
   php -S localhost:8000
   ```
2. Buka peramban (browser) dan akses:
   ```text
   http://localhost:8000/gorjes/
   ```

*(Alternatif: Jika Anda menjalankan perintah `php -S localhost:8000` langsung dari dalam folder `/var/www/gorjes`, pastikan web server atau virtual host mengenali prefix path `/gorjes`).*

### Opsi B: Menggunakan XAMPP / Laragon / WampServer (Windows / macOS)

1. Pindahkan atau salin folder `gorjes` ke dalam direktori publik web server Anda:
   - **XAMPP:** `C:\xampp\htdocs\gorjes`
   - **Laragon:** `C:\laragon\www\gorjes`
2. Jalankan modul **Apache** dan **MySQL** melalui Control Panel.
3. Buka peramban dan kunjungi:
   ```text
   http://localhost/gorjes/
   ```

### Opsi C: Menggunakan Apache / Nginx (Linux Server)

1. Pastikan folder berada di `/var/www/gorjes`.
2. Pastikan permission direktori dapat dibaca oleh user web server (`www-data`):
   ```bash
   sudo chown -R www-data:www-data /var/www/gorjes
   sudo chmod -R 755 /var/www/gorjes
   ```
3. Akses melalui IP server atau domain lokal: `http://localhost/gorjes/`.

---

## 5. Kredensial Akun Pengguna Default

Aplikasi dilengkapi dengan data pegawai awal untuk setiap role / jabatan dengan kata sandi default `123456`:

| Nama Pegawai | Jabatan | Password Default | Hak Akses Utama |
| :--- | :--- | :--- | :--- |
| **Indri** | `Admin` | `123456` | Akses penuh ke seluruh menu (Dashboard analitik, Pegawai, Pelanggan, Cabang, Bahan Baku, Produk & BOM, Pesanan, Catering) |
| **Dewi Lestari** | `Pegawai Kantin` | `123456` | Transaksi Pesanan Kantin, Data Pelanggan, Cabang Kantin |
| **Rina Putri** | `Pegawai Catering` | `123456` | Transaksi Pesanan Catering, Jadwal Acara Catering, Data Pelanggan |
| **Siska Nur** | `Pegawai Dapur` | `123456` | Pengelolaan Bahan Baku, Pantauan Stok, Produk, dan Resep BOM |

> [!NOTE]
> Sistem secara transparan akan meng-upgrade format password plaintext menjadi hash aman Bcrypt (`$2y$`) secara otomatis saat pengguna pertama kali login.

---

## 6. Panduan Alur Pengujian Fitur Utama

Saat menguji aplikasi secara manual, ikuti panduan alur bisnis berikut:

### 1. Autentikasi & Otorisasi Berbasis Peran (RBAC)
- Buka halaman login di `http://localhost:8000/gorjes/login.php`.
- Masuk menggunakan salah satu akun di atas.
- Perhatikan bahwa menu navigasi di bilah samping (sidebar) hanya menampilkan modul yang berhak diakses oleh jabatan tersebut.
- Uji upaya membuka URL terlarang secara manual di address bar (misal login sebagai `Pegawai Dapur` lalu membuka `/dashboard/pegawai/pegawailihat.php`). Sistem akan menolak dan mengarahkan kembali secara aman.

### 2. Manajemen Inventaris Bahan Baku
- Masuk sebagai `Admin` atau `Pegawai Dapur`.
- Buka menu **Bahan Baku** (`/dashboard/bahan_baku/bahan_bakulihat.php`).
- Tambahkan atau ubah data bahan baku.
- Perhatikan indikator status ketersediaan:
  - **Tersedia:** Stok berada di atas batas kritis.
  - **Menipis:** Stok berada di bawah atau sama dengan stok kritis.
  - **Habis:** Stok bernilai 0.
- Database dilengkapi konstrain `CHECK (stok >= 0)` untuk mencegah nilai negatif.

### 3. Resep Produk (BOM) & Sinkronisasi HPP
- Buka menu **Produk** (`/dashboard/produk/produklihat.php`).
- Klik **Detail** pada salah satu produk untuk membuka halaman BOM (`/dashboard/bom/bomdetail.php`).
- Anda dapat menambahkan bahan baku ke dalam resep, mengubah kuantitas secara inline, atau menghapusnya.
- Sistem akan menghitung subtotal bahan dan menyinkronkan nilai **Harga Pokok Penjualan (HPP)** produk secara otomatis.

### 4. Transaksi Pesanan & Pemotongan Stok Otomatis
- Masuk sebagai `Admin`, `Pegawai Kantin`, atau `Pegawai Catering`.
- Buka menu **Pesanan** (`/dashboard/pesanan/pesanantambah.php`).
- Pilih jenis pesanan (*Kantin* atau *Catering*):
  - Form akan menyesuaikan input secara dinamis (memilih Cabang untuk Kantin, atau mengisi Tanggal & Lokasi Acara untuk Catering).
- Setelah pesanan dibuat, masuk ke **Detail Pesanan** untuk menambahkan produk dan kuantitas porsi.
- Sistem akan secara otomatis memotong stok bahan baku terkait sesuai komposisi resep BOM.
- Jika stok tidak mencukupi, sistem akan menolak transaksi dan menampilkan pesan kesalahan informatif.

### 5. Pembatalan Pesanan & Pemulihan Stok
- Jika status pesanan diubah menjadi **Dibatalkan**, sistem secara otomatis mengembalikan seluruh stok bahan baku yang sempat terpotong ke saldo inventaris.

### 6. Ekspor Faktur / Invoice PDF
- Buka halaman **Detail Pesanan** (`/dashboard/detail_pesanan/detail_pesananlihat.php?id=...`).
- Klik tombol **Export PDF** di pojok kanan atas.
- Tombol akan menampilkan indikator visual *Memproses...*, menghasilkan berkas PDF yang rapi, dan otomatis mengunduh berkas jika popup browser terblokir.

---

## 7. Troubleshooting Umum

1. **Pesan "Koneksi gagal: Access denied for user 'root'@'localhost'"**
   - Buka [`layouts/config.php`](file:///var/www/gorjes/layouts/config.php).
   - Sesuaikan nilai `$password` dan `$user` dengan kredensial MySQL lokal Anda.

2. **Tampilan CSS / Font Tidak Muncul**
   - Pastikan perangkat Anda terhubung ke internet saat pertama kali memuat halaman, karena aplikasi memuat Tailwind CSS dan font Google melalui CDN.

3. **Link Navigasi Mengarah ke 404 Not Found**
   - Pastikan web server menyajikan aplikasi dengan path basis `/gorjes/`.
   - Jika menggunakan PHP Built-in Server, jalankan server dari folder parent (`cd /var/www && php -S localhost:8000`), bukan dari dalam folder `gorjes`.
