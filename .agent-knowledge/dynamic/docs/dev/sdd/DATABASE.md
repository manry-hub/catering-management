# Database Plan: Kantin Gorjes

> **Status:** `CURRENT` (Mode: Existing Project)  
> **Output:** `.agent-knowledge/dynamic/docs/dev/sdd/DATABASE.md`  
> **Purpose:** Cetak biru basis data: topologi, skema tabel terverifikasi, relasi, strategi indeks, dan data awal Kantin Gorjes.

---

## 1. Database Topology

| Connection | DB Engine | Database Name | Purpose |
|------------|-----------|---------------|---------|
| `default` (`$conn` via [layouts/config.php](file:///var/www/gorjes/layouts/config.php)) | MySQL 8.x / MariaDB (InnoDB) | `sistem_pemesanan_kantin_gorjes_db` | Penyimpanan data operasional utama: transaksi, inventaris bahan baku, resep BOM, master produk, pelanggan, dan pengguna. |

---

## 2. Table Schemas

### `bahan_baku`
Menyimpan data master bahan mentah inventaris dapur.

| Column | Type | Constraints | Index | Notes |
|--------|------|-------------|-------|-------|
| `id_bahan_baku` | `int` | PK, auto-increment | PRIMARY | Pengenal unik bahan baku |
| `nama_bahan` | `varchar(100)` | NOT NULL | None | Nama bahan mentah (misal: Tepung Terigu, Daging Ayam) |
| `satuan` | `varchar(30)` | NOT NULL | None | Satuan ukur (kg, gr, liter, ml, pcs) |
| `harga_satuan_bahan` | `decimal(12,2)` | NOT NULL | None | Biaya per satu satuan bahan baku |
| `stok` | `decimal(10,2)` | DEFAULT 0.00 | None | Jumlah stok fisik yang tersedia di gudang/dapur |
| `stok_kritis` | `decimal(10,2)` | DEFAULT 0.00 | None | Batas ambang peringatan persediaan menipis |
| `status_ketersediaan` | `enum('Tersedia','Menipis','Habis')` | Nullable | None | Status ketersediaan yang dikalkulasi berdasarkan nilai stok vs stok_kritis |

---

### `cabang_kantin`
Menyimpan lokasi unit fisik kantin sekolah tempat operasional berlangsung.

| Column | Type | Constraints | Index | Notes |
|--------|------|-------------|-------|-------|
| `id_cabang_kantin` | `int` | PK, auto-increment | PRIMARY | Pengenal unik cabang kantin |
| `nama_cabang` | `varchar(100)` | NOT NULL | None | Nama cabang (misal: SMP Islam Al Azhar 2 Jakarta Selatan) |
| `alamat_cabang` | `text` | Nullable | None | Alamat lengkap lokasi cabang |

---

### `catering`
Menyimpan atribut khusus untuk pesanan bertipe catering (acara eksternal).

| Column | Type | Constraints | Index | Notes |
|--------|------|-------------|-------|-------|
| `id_catering` | `int` | PK, auto-increment | PRIMARY | Pengenal unik pesanan catering |
| `tanggal_acara` | `date` | NOT NULL | None | Tanggal pelaksanaan acara pemesan |
| `lokasi_acara` | `text` | NOT NULL | None | Alamat atau tempat pelaksanaan acara catering |

---

### `pegawai`
Menyimpan data staf dan kredensial akses sistem.

| Column | Type | Constraints | Index | Notes |
|--------|------|-------------|-------|-------|
| `id_pegawai` | `int` | PK, auto-increment | PRIMARY | Pengenal unik pegawai |
| `nama_pegawai` | `varchar(100)` | NOT NULL | None | Nama lengkap pegawai (digunakan untuk login) |
| `jabatan` | `varchar(50)` | Nullable | None | Peran staf: 'Owner', 'Admin', 'Pegawai Kantin', 'Pegawai Catering', 'Pegawai Dapur' |
| `password` | `varchar(255)` | NOT NULL | None | Kata sandi akun (saat ini tersimpan sebagai teks biasa/plaintext) |

---

### `pelanggan`
Menyimpan data kontak pelanggan pemesan makanan atau layanan catering.

| Column | Type | Constraints | Index | Notes |
|--------|------|-------------|-------|-------|
| `id_pelanggan` | `int` | PK, auto-increment | PRIMARY | Pengenal unik pelanggan |
| `nama_pelanggan` | `varchar(100)` | NOT NULL | None | Nama pelanggan perorangan atau instansi |
| `no_telp` | `varchar(20)` | Nullable | None | Nomor kontak telepon / WhatsApp |
| `alamat_pelanggan` | `text` | Nullable | None | Alamat tempat tinggal atau kantor pemesan |

---

### `produk`
Menyimpan katalog menu makanan, minuman, cemilan, dan paket katering.

| Column | Type | Constraints | Index | Notes |
|--------|------|-------------|-------|-------|
| `id_produk` | `int` | PK, auto-increment | PRIMARY | Pengenal unik produk |
| `nama_produk` | `varchar(100)` | NOT NULL | None | Nama item produk yang dijual |
| `kategori` | `varchar(50)` | Nullable | None | Kategori menu (Makanan, Dessert, Snack, Paket Nasi Box, Catering Harian) |
| `harga_jual` | `decimal(12,2)` | NOT NULL | None | Harga jual ke pelanggan per porsi/satuan |
| `harga_pokok` | `decimal(12,2)` | NOT NULL | None | Harga pokok produksi (HPP) hasil kalkulasi total BOM |

---

### `bom` (Bill of Materials)
Tabel relasi many-to-many yang mendefinisikan komposisi resep per produk.

| Column | Type | Constraints | Index | Notes |
|--------|------|-------------|-------|-------|
| `id_produk` | `int` | PK (Composite), FK | PRIMARY (Composite) | Relasi ke `produk.id_produk` (ON UPDATE CASCADE ON DELETE CASCADE) |
| `id_bahan_baku` | `int` | PK (Composite), FK | PRIMARY (Composite) | Relasi ke `bahan_baku.id_bahan_baku` (ON UPDATE CASCADE ON DELETE CASCADE) |
| `qty` | `decimal(10,4)` | NOT NULL | None | Kuantitas bahan baku yang diperlukan per satu porsi produk |
| `subtotal` | `decimal(12,2)` | NOT NULL | None | Nilai subtotal biaya bahan (`qty * harga_satuan_bahan`) |

---

### `pesanan`
Tabel header transaksi penjualan harian kantin maupun catering.

| Column | Type | Constraints | Index | Notes |
|--------|------|-------------|-------|-------|
| `id_pesanan` | `int` | PK, auto-increment | PRIMARY | Nomor tiket transaksi pesanan |
| `tanggal_pesanan` | `datetime` | NOT NULL | None | Waktu pencatatan pesanan dibuat |
| `status_pesanan` | `varchar(50)` | NOT NULL | None | Siklus transaksi: 'Diproses', 'Selesai', 'Dibatalkan' |
| `total_harga` | `decimal(12,2)` | DEFAULT 0.00 | None | Akumulasi nilai penjualan seluruh item dalam pesanan |
| `jenis_pesanan` | `enum('Kantin','Catering')` | NOT NULL | None | Saluran transaksi pemesanan |
| `id_pelanggan` | `int` | FK, NOT NULL | fk_pesanan_pelanggan | Relasi ke `pelanggan.id_pelanggan` (ON UPDATE CASCADE) |
| `id_pegawai` | `int` | FK, NOT NULL | fk_pesanan_pegawai | Relasi ke `pegawai.id_pegawai` (kasir/pencatat) |
| `id_cabang_kantin` | `int` | FK, Nullable | fk_pesanan_cabang | Relasi ke `cabang_kantin` (hanya terisi jika jenis 'Kantin') |
| `id_catering` | `int` | FK, Nullable | fk_pesanan_catering | Relasi ke `catering` (hanya terisi jika jenis 'Catering') |
| `catatan` | `text` | Nullable | None | Catatan khusus pesanan atau instruksi pengiriman |

---

### `detail_pesanan`
Tabel baris item produk yang dipesan dalam sebuah transaksi.

| Column | Type | Constraints | Index | Notes |
|--------|------|-------------|-------|-------|
| `id_pesanan` | `int` | PK (Composite), FK | PRIMARY (Composite) | Relasi ke `pesanan.id_pesanan` (ON UPDATE CASCADE ON DELETE CASCADE) |
| `id_produk` | `int` | PK (Composite), FK | PRIMARY (Composite) | Relasi ke `produk.id_produk` (ON UPDATE CASCADE) |
| `qty` | `int` | NOT NULL | None | Jumlah porsi produk yang dibeli |
| `subtotal` | `decimal(12,2)` | NOT NULL | None | Nilai total baris (`qty * harga_jual`) |

> *Catatan Diskrepansi Kode*: Beberapa controller lama (misal: [dashboard/detail_pesanan/controllers/delete.php](file:///var/www/gorjes/dashboard/detail_pesanan/controllers/delete.php)) mengasumsikan adanya kolom tunggal `id_detail_pesanan`, sedangkan skema basis data resmi di [sql/ddl.sql](file:///var/www/gorjes/sql/ddl.sql) dan [sistem_pemesanan_kantin_gorjes_db.sql](file:///var/www/gorjes/sistem_pemesanan_kantin_gorjes_db.sql) menggunakan composite primary key `(id_pesanan, id_produk)`.

---

## 3. Relationships Summary

| Table A | Relation | Table B | Foreign Key | Cascade Behavior |
|---------|----------|---------|-------------|------------------|
| `pesanan` | `belongsTo` | `pelanggan` | `id_pelanggan` | ON UPDATE CASCADE |
| `pesanan` | `belongsTo` | `pegawai` | `id_pegawai` | ON UPDATE CASCADE |
| `pesanan` | `belongsTo` | `cabang_kantin` | `id_cabang_kantin` | ON UPDATE CASCADE ON DELETE SET NULL |
| `pesanan` | `belongsTo` | `catering` | `id_catering` | ON UPDATE CASCADE ON DELETE SET NULL |
| `bom` | `belongsTo` | `produk` | `id_produk` | ON UPDATE CASCADE ON DELETE CASCADE |
| `bom` | `belongsTo` | `bahan_baku` | `id_bahan_baku` | ON UPDATE CASCADE ON DELETE CASCADE |
| `detail_pesanan` | `belongsTo` | `pesanan` | `id_pesanan` | ON UPDATE CASCADE ON DELETE CASCADE |
| `detail_pesanan` | `belongsTo` | `produk` | `id_produk` | ON UPDATE CASCADE |

---

## 4. Indexing Strategy

| Table | Index | Columns | Type | Reason |
|-------|-------|---------|------|--------|
| `bahan_baku` | `PRIMARY` | `id_bahan_baku` | BTREE | Kunci primer identitas bahan baku |
| `cabang_kantin` | `PRIMARY` | `id_cabang_kantin` | BTREE | Kunci primer cabang kantin |
| `catering` | `PRIMARY` | `id_catering` | BTREE | Kunci primer entitas katering |
| `pegawai` | `PRIMARY` | `id_pegawai` | BTREE | Kunci primer identitas pegawai |
| `pelanggan` | `PRIMARY` | `id_pelanggan` | BTREE | Kunci primer pelanggan |
| `produk` | `PRIMARY` | `id_produk` | BTREE | Kunci primer produk |
| `bom` | `PRIMARY` | `id_produk, id_bahan_baku` | BTREE (Composite) | Menjamin keunikan bahan per produk dan efisiensi join resep |
| `pesanan` | `PRIMARY` | `id_pesanan` | BTREE | Kunci primer transaksi pesanan |
| `pesanan` | `fk_pesanan_pelanggan` | `id_pelanggan` | BTREE | Mempercepat join query pesanan dan pelanggan |
| `pesanan` | `fk_pesanan_pegawai` | `id_pegawai` | BTREE | Mempercepat join query pelacakan staf kasir |
| `pesanan` | `fk_pesanan_cabang` | `id_cabang_kantin` | BTREE | Mempercepat filter pesanan berdasarkan lokasi kantin |
| `pesanan` | `fk_pesanan_catering` | `id_catering` | BTREE | Mempercepat lookup detail acara katering |
| `detail_pesanan` | `PRIMARY` | `id_pesanan, id_produk` | BTREE (Composite) | Menjamin item produk tidak duplikat dalam satu nomor pesanan |

---

## 5. Migration Plan

Karena aplikasi beroperasi secara native tanpa migration framework (seperti Phinx atau Laravel Migrations), skema dikelola melalui script SQL terpusat:

| # | Step / Script | Target File | Domain | Notes |
|---|---------------|-------------|--------|-------|
| 1 | Inisialisasi Skema DDL | [sql/ddl.sql](file:///var/www/gorjes/sql/ddl.sql) | Core | Membuat seluruh struktur 9 tabel, constraint primary key, foreign key, dan aturan cascade. |
| 2 | Inisialisasi Data Master & Transaksi | [sql/seed.sql](file:///var/www/gorjes/sql/seed.sql) | Data | Mengisi data awal cabang, pegawai default, produk katalog awal, dan relasi BOM. |
| 3 | Dump Penuh Cadangan | [sistem_pemesanan_kantin_gorjes_db.sql](file:///var/www/gorjes/sistem_pemesanan_kantin_gorjes_db.sql) | Full Backup | Cadangan lengkap struktur dan data transaksi historis. |

---

## 6. Seed Data

| Entity / Domain | Purpose | Data Source | Sample Records |
|-----------------|---------|-------------|----------------|
| `cabang_kantin` | Lokasi unit kantin awal | [sql/seed.sql](file:///var/www/gorjes/sql/seed.sql) | SMP Islam Al Azhar 2 Jakarta Selatan, SMP Islam Al Azhar 1 Kebayoran |
| `pegawai` | Akun awal perwakilan tiap peran | [sql/seed.sql](file:///var/www/gorjes/sql/seed.sql) | Indri (Admin), Rina Putri (Pegawai Catering), Dewi Lestari (Pegawai Kantin), Siska Nur (Pegawai Dapur) |
| `pelanggan` | Data awal pelanggan terdaftar | [sql/seed.sql](file:///var/www/gorjes/sql/seed.sql) | Ahmad Fauzi, Siti Aminah, Rizky Maulana, Dina Maharani, Andi Saputra |
| `produk` | Katalog produk menu kantin & catering | [sql/seed.sql](file:///var/www/gorjes/sql/seed.sql) | Dimsum Keju Lumer, Fettuccine Carbonara, Siomay, Paket Nasi Box 1, Chicken Basil Thailand |
