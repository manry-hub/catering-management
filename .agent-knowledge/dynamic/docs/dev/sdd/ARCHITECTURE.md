# Architecture Plan: Kantin Gorjes

> **Status:** `CURRENT` (Mode: Existing Project)  
> **Output:** `.agent-knowledge/dynamic/docs/dev/sdd/ARCHITECTURE.md`  
> **Purpose:** Cetak biru teknis untuk perancangan sistem, komponen, aliran data, strategi routing, dan keputusan arsitektural Kantin Gorjes.

## Project Type Declaration

| Item | Value |
|------|-------|
| Project Type | Monolith (Procedural PHP Web Application) |
| Git Location | Single repository (`/var/www/gorjes` / `https://github.com/manry-hub/catering-management.git`) |

---

## 1. System Design

### Architecture Pattern

Procedural Transaction Script / Page Controller Pattern (Monolith).

**Rationale:**  
Aplikasi dirancang sebagai sistem monolitik berbasis script PHP prosedural mandiri tanpa framework berat (seperti Laravel atau Symfony). Setiap endpoint tampilan dipasangkan langsung dengan file script controller (`create.php`, `update.php`, `delete.php`) yang mengeksekusi logika transaksi database menggunakan ekstensi `mysqli`. Pola ini dipilih untuk kemudahan deployment langsung pada web server Apache / LAMP stack standar tanpa kebutuhan proses build atau dependency management eksternal.

### High-Level Diagram

```text
[Browser Client]
       │
       ▼ (HTTP Requests)
 [Apache Web Server (mod_php)]
       │
       ├─► [Public Entry: index.php / login.php]
       │
       ├─► [Session & Config Guards: layouts/session.php, layouts/config.php]
       │
       ├─► [Dashboard Views: dashboard/<module>/<view>.php]
       │         │
       │         └─► [Client-side rendering: Tailwind CSS CDN, Chart.js, jsPDF]
       │
       └─► [Action Controllers: dashboard/<module>/controllers/<action>.php]
                 │
                 ▼ (mysqli prepared statements & ACID transactions)
         [(MySQL Database: sistem_pemesanan_kantin_gorjes_db)]
```

---

## 2. Component Breakdown

| Component | Type | Responsibility | Technology |
|-----------|------|----------------|------------|
| `Public Gateway` | Frontend / Page | Menampilkan landing page publik informasi Kantin Gorjes dan gerbang masuk ke dashboard. | PHP 8.x, HTML5, Tailwind CSS |
| `Authentication Module` | Backend / Controller | Menangani validasi kredensial login pegawai, pembuatan sesi, dan pembersihan sesi saat logout. | PHP Native Sessions, MySQL (`pegawai`) |
| `Dashboard Core & Layouts` | View Component | Menyediakan struktur layout bersama (head, navigation sidebar, footer) serta visualisasi agregasi statistik. | PHP Includes, Tailwind CSS, FontAwesome, Chart.js |
| `Inventory & BOM Engine` | Backend & View | Mengelola master bahan baku, penyusunan resep produk, dan sinkronisasi harga pokok produksi (HPP). | PHP Procedural, MySQL (`bahan_baku`, `bom`, `produk`) |
| `Order Processing Module` | Backend & View | Menangani siklus hidup pesanan kantin dan catering, relasi pelanggan, serta pemotongan stok otomatis. | PHP Transactions, MySQL (`pesanan`, `catering`, `detail_pesanan`) |
| `Client PDF Exporter` | Frontend Utility | Merender dan mencetak struk belanja kantin atau lembar invoice catering langsung di sisi browser. | JavaScript, jsPDF library |
| `Database Storage Layer` | Persistence | Menyimpan seluruh entitas bisnis, relasi foreign key, dan integritas transaksi. | MySQL 8.x / MariaDB, InnoDB Engine |

---

## 3. Routing & Middleware Strategy

### Monolith (File-Based Procedural Routing)

Sistem menggunakan mekanisme routing bawaan web server berbasis path berkas fisik (File-based Routing):

- **Tampilan Antarmuka (Views)**:
  - Landing Page: `/index.php`
  - Portal Login: `/login.php`
  - Ringkasan Dashboard: `/dashboard/index.php`
  - Tampilan Modul: `/dashboard/{module}/{module}lihat.php`, `{module}tambah.php`, `{module}ubah.php`
- **Pengendali Aksi (Controllers)**:
  - Form Actions: `/dashboard/{module}/controllers/create.php`, `update.php`, `delete.php`
- **Mekanisme Guard / Middleware Tiruan**:
  - Pengecekan Sesi Terpusat: Berkas [layouts/session.php](file:///var/www/gorjes/layouts/session.php) diikutsertakan (`include`) pada setiap halaman tampilan dan berkas controller. Jika `$_SESSION['id_pegawai']` belum terdefinisi, pengguna dialihkan otomatis ke `/login.php`.
  - Pembatasan Menu Navigasi: Berkas [layouts/menu.php](file:///var/www/gorjes/layouts/menu.php) memfilter visibilitas tautan sidebar berdasarkan nilai `$_SESSION['jabatan']`.
  - Penanganan Kesalahan Database: Fungsi terpusat `handle_db_error()` pada [layouts/config.php](file:///var/www/gorjes/layouts/config.php) menerjemahkan kode error MySQL (1451, 1062, 1452) ke pesan ramah pengguna.

---

## 4. Data Flow

### Alur Penambahan Item Pesanan dan Pemotongan Stok Bahan Baku (BOM)

```text
[User Submit Item Form]
         │ (POST id_pesanan, id_produk, qty)
         ▼
[dashboard/detail_pesanan/controllers/create.php]
         │
         ├─► Validasi parameter input (id_pesanan > 0, qty > 0)
         │
         ├─► mysqli_begin_transaction($conn)
         │
         ├─► 1. SELECT harga_jual FROM produk WHERE id_produk = ?
         │        └─► Hitung subtotal = harga_jual * qty
         │
         ├─► 2. INSERT INTO detail_pesanan (id_pesanan, id_produk, qty, subtotal)
         │
         ├─► 3. UPDATE pesanan SET total_harga = total_harga + subtotal WHERE id_pesanan = ?
         │
         ├─► 4. SELECT id_bahan_baku, qty FROM bom WHERE id_produk = ?
         │        │
         │        ├─► Looping setiap bahan:
         │        │     pengurangan = qty_bom * qty_order
         │        ├─► UPDATE bahan_baku SET stok = stok - pengurangan WHERE id_bahan_baku = ?
         │        └─► Evaluasi stok terhadap stok_kritis -> UPDATE status_ketersediaan
         │
         ├─► mysqli_commit($conn)
         │
         └─► Redirect ke ../detail_pesananlihat.php?id={id_pesanan} dengan pesan sukses
```

---

## 5. Security Plan

| Area | Strategy | Rujukan Detail |
|------|----------|----------------|
| Authentication | Sesi server-side PHP berbasis cookie `PHPSESSID`. Kredensial diverifikasi dari tabel `pegawai`. | [AUTHENTICATION.md](file:///var/www/gorjes/.agent-knowledge/dynamic/docs/dev/sdd/AUTHENTICATION.md) |
| Authorization | Role-Based Access Control (RBAC) pada antarmuka menu navigasi untuk peran Owner, Admin, Pegawai Kantin, Pegawai Catering, dan Pegawai Dapur. | [AUTHORIZATION.md](file:///var/www/gorjes/.agent-knowledge/dynamic/docs/dev/sdd/AUTHORIZATION.md) |
| SQL Injection | Parameterized queries menggunakan `mysqli_prepare`, `mysqli_stmt_bind_param`, dan casting tipe data integer/float pada controller utama. | [SECURITY.md](file:///var/www/gorjes/.agent-knowledge/dynamic/docs/dev/sdd/SECURITY.md) |
| Output Escaping (XSS) | Penggunaan fungsi `htmlspecialchars()` pada output data teks dinamis di berkas view. | [SECURITY.md](file:///var/www/gorjes/.agent-knowledge/dynamic/docs/dev/sdd/SECURITY.md) |
| CSRF | *Current Gap*: Formulir POST belum dilengkapi token CSRF acak. Direkomendasikan untuk fase pengerasan berikutnya. | [SECURITY.md](file:///var/www/gorjes/.agent-knowledge/dynamic/docs/dev/sdd/SECURITY.md) |
| File Upload | Tidak ada fitur unggah berkas publik pada versi saat ini (logo dimuat statis). | [SECURITY.md](file:///var/www/gorjes/.agent-knowledge/dynamic/docs/dev/sdd/SECURITY.md) |

---

## 6. Deployment Architecture

```text
[Local Development] ──► [Git Repository: GitHub (origin/main)] ──► [Production Server: /var/www/gorjes]
```

| Environment | Server | Domain / Path | Branch |
|------------|--------|---------------|--------|
| Local | Apache / MariaDB Localhost | `http://localhost/gorjes/` atau `http://localhost/` | `main` |
| Staging | Belum dikonfigurasi mandiri | — | — |
| Production | Linux Web Server (Apache/Nginx + PHP-FPM) | `http://<server-ip>/gorjes/` | `main` |

---

## 7. Key Decisions

| # | Decision | Rationale | ADR |
|---|----------|-----------|-----|
| 1 | Penggunaan Arsitektur Script Prosedural Native PHP | Mengurangi kompleksitas dependensi, meniadakan tahapan kompilasi/build, dan menjamin portabilitas tinggi pada berbagai hosting LAMP standar. | Arsitektur Historis |
| 2 | Pemotongan Stok Bahan Baku Otomatis Berbasis BOM pada Database Transaction | Menjamin integritas data inventaris saat pesanan masuk, mencegah inkonsistensi data jika salah satu tahapan kalkulasi gagal melalui mekanisme rollback. | Arsitektur Historis |
| 3 | Pembangkitan PDF Dokumen di Sisi Klien Menggunakan jsPDF | Menghemat utilisasi memori dan pemrosesan CPU web server dengan mendelegasikan perenderan struk/invoice ke browser pengguna. | Arsitektur Historis |
