# Product Requirements Document: Kantin Gorjes

> **Status:** `CURRENT` (Mode: Existing Project)  
> **Purpose:** Sumber kebenaran tunggal untuk visi produk, alur pengguna, dan fitur sistem pemesanan dan manajemen operasional Kantin & Catering Gorjes.

---

## 1. Product Vision

### Elevator Pitch

Kantin Gorjes adalah sistem informasi manajemen operasional kantin dan catering terpadu berbasis web yang mengelola pesanan multi-channel (kantin sekolah dan pesanan catering acara), kalkulasi Bill of Materials (BOM) resep, pengawasan stok bahan baku kritis, serta penetapan harga pokok dan harga jual produk.

### Problem Statement

Operasional kantin multi-cabang dan layanan catering sering menghadapi kendala pencatatan pesanan manual, ketidaksesuaian kalkulasi harga pokok produksi (HPP) terhadap fluktuasi harga bahan baku, pemborosan bahan akibat ketiadaan pelacakan stok otomatis saat pesanan masuk, serta kesulitan pemisahan data transaksi antara unit kantin harian dan pesanan catering skala besar.

### Target Users

| Role | Description | Primary Need |
|------|-------------|--------------|
| `Owner` | Pemilik bisnis Kantin Gorjes | Memantau omzet bulanan, total volume pesanan, produk terlaris, dan performa keseluruhan unit usaha. |
| `Admin` | Administrator operasional sistem | Mengelola akun pegawai, master cabang kantin, data pelanggan, konfigurasi produk, dan audit transaksi. |
| `Pegawai Kantin` | Kasir dan staf operasional cabang kantin sekolah | Mencatat pesanan makan di tempat atau take-away cabang, memproses status pesanan, dan mencetak struk kasir. |
| `Pegawai Catering` | Staf pengelola pesanan catering acara | Mencatat pesanan catering lengkap dengan tanggal dan lokasi acara, memantau detail porsi, dan mencetak invoice A4. |
| `Pegawai Dapur` | Koki dan tim logistik dapur | Memantau ketersediaan bahan baku, mengelola resep (BOM), menyinkronkan HPP produk, dan merespons peringatan stok menipis/habis. |

---

## 2. User Stories / Use Cases

### Epic 1: Autentikasi dan Manajemen Pengguna

| ID | User Story | Priority | Acceptance Criteria |
|----|-----------|----------|-------------------|
| `US-001` | Sebagai Pegawai, saya ingin masuk ke sistem menggunakan nama dan kata sandi agar dapat mengakses menu kerja sesuai jabatan saya. | `P0` | Berhasil login via [login.php](file:///var/www/gorjes/login.php) dan diarahkan ke dashboard jika valid; menampilkan pesan kesalahan jika salah. |
| `US-002` | Sebagai Admin/Owner, saya ingin mengelola data akun pegawai (tambah, lihat, ubah, hapus) agar akses staf operasional tetap mutakhir. | `P1` | CRUD data pegawai pada [dashboard/pegawai/](file:///var/www/gorjes/dashboard/pegawai/) berfungsi; pencegahan penghapusan akun yang sedang aktif login. |
| `US-003` | Sebagai Pegawai, saya ingin keluar dari sesi kerja (logout) untuk mencegah penyalahgunaan akun di perangkat bersama. | `P0` | Sesi dihancurkan via [logout.php](file:///var/www/gorjes/logout.php) dan pengguna dialihkan ke halaman login. |

### Epic 2: Manajemen Bahan Baku dan Bill of Materials (BOM)

| ID | User Story | Priority | Acceptance Criteria |
|----|-----------|----------|-------------------|
| `US-004` | Sebagai Pegawai Dapur, saya ingin mendata master bahan baku beserta satuan, harga satuan, dan batas stok kritis. | `P0` | CRUD bahan baku pada [dashboard/bahan_baku/](file:///var/www/gorjes/dashboard/bahan_baku/) tersimpan di tabel `bahan_baku`. |
| `US-005` | Sebagai Pegawai Dapur, saya ingin menyusun komposisi resep (BOM) per produk dengan kuantitas bahan yang tepat. | `P0` | Tambah dan hapus item BOM pada [dashboard/bom/bomdetail.php](file:///var/www/gorjes/dashboard/bom/bomdetail.php) menghitung subtotal bahan secara otomatis. |
| `US-006` | Sebagai Pegawai Dapur, saya ingin menyinkronkan total biaya BOM ke Harga Pokok Penjualan (HPP) produk dengan satu klik. | `P0` | Aksi POST ke [dashboard/bom/controllers/sync_harga_pokok.php](file:///var/www/gorjes/dashboard/bom/controllers/sync_harga_pokok.php) memperbarui kolom `harga_pokok` di tabel `produk`. |
| `US-007` | Sebagai Pegawai Dapur, saya ingin mengekspor lembar spesifikasi resep produk ke format PDF. | `P2` | Tombol Export PDF di [dashboard/bom/bomdetail.php](file:///var/www/gorjes/dashboard/bom/bomdetail.php) menghasilkan dokumen cetak spesifikasi produk. |

### Epic 3: Manajemen Produk dan Menu

| ID | User Story | Priority | Acceptance Criteria |
|----|-----------|----------|-------------------|
| `US-008` | Sebagai Admin/Pegawai Dapur, saya ingin mengelola katalog produk (nama, kategori, harga jual, harga pokok). | `P0` | CRUD produk pada [dashboard/produk/](file:///var/www/gorjes/dashboard/produk/) berjalan dengan validasi kelengkapan data. |
| `US-009` | Sebagai Pengunjung Publik, saya ingin melihat halaman beranda elegan yang menampilkan identitas dan tautan portal Kantin Gorjes. | `P2` | Halaman [index.php](file:///var/www/gorjes/index.php) dapat diakses publik tanpa sesi login. |

### Epic 4: Pemrosesan Pesanan (Kantin & Catering)

| ID | User Story | Priority | Acceptance Criteria |
|----|-----------|----------|-------------------|
| `US-010` | Sebagai Pegawai Kantin/Catering, saya ingin membuat transaksi pesanan baru dengan memilih pelanggan dan jenis pesanan. | `P0` | Transaksi pesanan tersimpan di tabel `pesanan` via [dashboard/pesanan/controllers/create.php](file:///var/www/gorjes/dashboard/pesanan/controllers/create.php); jika jenis Catering, entri jadwal dan lokasi tersimpan di tabel `catering`. |
| `US-011` | Sebagai Pegawai, saya ingin menambahkan item produk dan kuantitas ke dalam pesanan, di mana stok bahan baku otomatis terpotong sesuai rasio BOM. | `P0` | Penambahan item di [dashboard/detail_pesanan/controllers/create.php](file:///var/www/gorjes/dashboard/detail_pesanan/controllers/create.php) memotong stok di `bahan_baku`, memperbarui status ketersediaan (Tersedia, Menipis, Habis), dan menambahkan total harga pesanan dalam transaksi database ACID. |
| `US-012` | Sebagai Pegawai, saya ingin menghapus item produk dari pesanan dan mengembalikan kuantitas stok bahan baku secara proporsional. | `P0` | Penghapusan item memulihkan kuantitas di `bahan_baku` dan mengurangi `total_harga` pesanan. |
| `US-013` | Sebagai Pegawai, saya ingin mencetak struk kasir untuk pesanan kantin dan invoice resmi A4 untuk pesanan catering. | `P1` | Fitur cetak PDF berbasis browser/jsPDF di [dashboard/detail_pesanan/detail_pesananlihat.php](file:///var/www/gorjes/dashboard/detail_pesanan/detail_pesananlihat.php) menghasilkan layout struk belanja kantin atau invoice catering. |

### Epic 5: Dashboard dan Analitik Operasional

| ID | User Story | Priority | Acceptance Criteria |
|----|-----------|----------|-------------------|
| `US-014` | Sebagai Owner/Admin, saya ingin melihat ringkasan metrik (total pesanan, pesanan aktif diproses, omzet bulanan, dan total stok kritis). | `P1` | Card statistik di [dashboard/index.php](file:///var/www/gorjes/dashboard/index.php) menampilkan data agregasi SQL secara real-time. |
| `US-015` | Sebagai Owner/Admin, saya ingin melihat grafik tren transaksi 14 hari terakhir dan distribusi status pesanan. | `P1` | Grafik Chart.js merender data harian dan chart donat status pesanan. |
| `US-016` | Sebagai Tim Dapur, saya ingin melihat proyeksi kebutuhan bahan baku yang harus disiapkan untuk pesanan yang berstatus 'Diproses'. | `P1` | Tabel kebutuhan bahan di dashboard menghitung akumulasi `BOM qty * detail_pesanan qty` untuk seluruh pesanan aktif. |

---

## 3. Feature List

### MVP (Fitur yang Telah Diimplementasikan)

| # | Feature | Description | Epic |
|---|---------|-------------|------|
| 1 | Autentikasi Pengguna Tunggal | Login menggunakan nama pegawai dan password berbasis sesi PHP standar | Epic 1 |
| 2 | Pembatasan Navigasi Peran | Filter tampilan menu samping (Sidebar) berdasarkan jabatan di sesi | Epic 1 |
| 3 | CRUD Bahan Baku | Pengelolaan inventaris bahan mentah, batas kritis, dan status ketersediaan | Epic 2 |
| 4 | Manajemen BOM Produk | Penyusunan daftar kebutuhan bahan dan rasio penggunaan per produk | Epic 2 |
| 5 | Sinkronisasi HPP Otomatis | Tombol kalkulasi ulang dan pembaruan `harga_pokok` berdasarkan total BOM | Epic 2 |
| 6 | Katalog Produk & Kategori | Manajemen produk makanan, dessert, snack, paket nasi box, dan catering harian | Epic 3 |
| 7 | Master Cabang Kantin | Pendataan unit lokasi kantin sekolah (SMP Islam Al Azhar 1 & 2) | Epic 4 |
| 8 | Master Pelanggan | Pendataan kontak dan alamat pemesan | Epic 4 |
| 9 | Transaksi Pesanan Kantin & Catering | Pembuatan header order dengan relasi dinamis ke cabang atau detail acara catering | Epic 4 |
| 10 | Pengurangan Stok Otomatis Berbasis BOM | Pengurangan stok bahan baku saat item pesanan ditambahkan dalam transaksi database | Epic 4 |
| 11 | Export Dokumen PDF Sisi Klien | Pembuatan struk kantin dan invoice catering berbasis jsPDF di browser | Epic 4 |
| 12 | Dashboard Statistik & Grafik | Agregasi omzet, pemantauan stok menipis, proyeksi kebutuhan dapur, dan chart tren transaksi | Epic 5 |

### Post-MVP / Rencana Pengembangan Masa Depan

| # | Feature | Description | Priority |
|---|---------|-------------|----------|
| 1 | Hashing Password Bcrypt/Argon2 | Mengganti verifikasi string plaintext dengan `password_hash()` dan `password_verify()` | High |
| 2 | Middleware Otorisasi Controller | Penegakan validasi peran langsung pada controller POST/GET untuk mencegah IDOR | High |
| 3 | CSRF Protection | Implementasi token anti-CSRF pada setiap formulir pengiriman data | High |
| 4 | Manajemen Pembayaran & Gateway | Integrasi QRIS / Payment Gateway dan pencatatan metode bayar (Tunai, Transfer) | Medium |
| 5 | Portal Self-Order Pelanggan | Akses pemesanan langsung untuk pelanggan melalui QR Code meja | Low |
| 6 | Laporan Keuangan & Laba Rugi | Rekapitulasi margin kotor (Omzet dikurangi HPP BOM) per periode | Medium |

---

## 4. Constraints & Assumptions

### Constraints

- **Teknis**: Berjalan pada arsitektur PHP 8.x prosedural dengan ekstensi `mysqli`, basis data MySQL/MariaDB, tanpa framework backend (native script) dan tanpa Composer package manager di lingkungan runtime utama.
- **Frontend**: Menggunakan Tailwind CSS via CDN publik (`cdn.tailwindcss.com`) dan pustaka FontAwesome serta Chart.js dari CDN, membutuhkan koneksi internet saat merender antarmuka pengguna.
- **Infrastruktur**: Mengasumsikan web server Apache dengan document root `/var/www/gorjes` atau akses berbasis subdirektori `/gorjes/`.

### Assumptions

- Setiap pegawai yang memiliki hak akses sistem dapat dipercaya dalam batas navigasi yang disediakan antarmuka.
- Produk yang memiliki status BOM aktif selalu memiliki komposisi bahan baku yang valid di database.
- Kuantitas stok bahan baku dapat bernilai pecahan desimal (`decimal(10,2)`), sedangkan kuantitas produk dalam pesanan bernilai bilangan bulat (`int`).

---

## 5. Success Metrics

| Metric | Target | Measurement Method |
|--------|--------|--------------------|
| Integritas Transaksi Stok | 100% konsisten | Tidak ada perbedaan antara selisih stok bahan baku dengan akumulasi BOM pesanan yang diproses |
| Ketersediaan Kalkulasi HPP | Real-time | Kolom `harga_pokok` terbarui seketika saat tombol sinkronisasi BOM ditekan |
| Waktu Cetak Dokumen | < 3 detik | Dokumen struk kasir atau invoice catering terbit di browser via jsPDF tanpa lag server |
| Akurasi Agregasi Dashboard | 100% akurat | Angka omzet dan status pesanan pada kartu metrik sesuai dengan query agregasi database |

---

## 6. Out of Scope

**Fitur berikut secara eksplisit tidak termasuk dalam ruang lingkup versi ini:**

- Integrasi sistem pembayaran online otomatis (Payment Gateway seperti Midtrans atau Xendit).
- Otentikasi multi-faktor (MFA / 2FA) dan pendaftaran mandiri pelanggan umum.
- Multi-bahasa (i18n), sistem hanya mendukung antarmuka Bahasa Indonesia.
- Sistem notifikasi SMS/WhatsApp otomatis ke pelanggan terkait status pesanan.
- Aplikasi mobile native (iOS / Android).
