# Authorization Plan: Kantin Gorjes

> **Status:** `CURRENT` (Mode: Existing Project)  
> **Output:** `.agent-knowledge/dynamic/docs/dev/sdd/AUTHORIZATION.md`  
> **Purpose:** Dokumentasi kebijakan kontrol akses berbasis peran (RBAC), cakupan sumber daya, dan penegakan otorisasi sistem Kantin Gorjes.

---

## 1. Access Control Model

Sistem menerapkan model **Role-Based Access Control (RBAC)** berbasis nilai string kolom `jabatan` pada akun pegawai yang tersimpan di sesi peramban (`$_SESSION['jabatan']`).

### Definisi Peran (Roles)

1. `Owner`: Pemilik usaha dengan hak melihat performa bisnis agregat dan seluruh modul operasional.
2. `Admin`: Administrator teknis dan manajerial dengan kewenangan penuh atas master data, pegawai, cabang, dan transaksi.
3. `Pegawai Kantin`: Staf operasional kasir cabang kantin fisik dengan kewenangan transaksi pesanan dan data pelanggan.
4. `Pegawai Catering`: Staf layanan catering acara dengan kewenangan transaksi pesanan catering dan data pelanggan.
5. `Pegawai Dapur`: Staf produksi dan gudang dengan kewenangan inventaris bahan baku, resep BOM, dan katalog produk.

---

## 2. Resource Scope & Permission Matrix

Tabel berikut memetakan matriks kewenangan akses berdasarkan logika antarmuka pada [layouts/menu.php](file:///var/www/gorjes/layouts/menu.php):

| Modul / Sumber Daya | Endpoint Tampilan & Pengendali | Owner | Admin | Pegawai Kantin | Pegawai Catering | Pegawai Dapur |
|---------------------|--------------------------------|:-----:|:-----:|:--------------:|:----------------:|:-------------:|
| **Dashboard** | `dashboard/index.php` | READ | READ | — | — | — |
| **Pegawai** | `dashboard/pegawai/*` | CRUD | CRUD | — | — | — |
| **Cabang Kantin** | `dashboard/cabang_kantin/*` | CRUD | CRUD | CRUD | — | — |
| **Pelanggan** | `dashboard/pelanggan/*` | CRUD | CRUD | CRUD | CRUD | — |
| **Bahan Baku** | `dashboard/bahan_baku/*` | CRUD | CRUD | — | — | CRUD |
| **Produk & BOM** | `dashboard/produk/*`, `dashboard/bom/*` | CRUD | CRUD | — | — | CRUD |
| **Pesanan** | `dashboard/pesanan/*`, `dashboard/detail_pesanan/*` | CRUD | CRUD | CRUD | CRUD | — |

*Keterangan:* `CRUD` = Create, Read, Update, Delete; `READ` = Hanya Lihat; `—` = Tidak Berhak.

---

## 3. Enforcement Mechanisms & Implemented Architecture

### 3.1 Penyaringan Sisi Tampilan (UI Filter)

Navigasi menu utama diatur melalui [layouts/menu.php](file:///var/www/gorjes/layouts/menu.php) menggunakan validasi `in_array($_SESSION['jabatan'] ?? '', [...])`. Tautan yang tidak diizinkan disembunyikan dari antarmuka pengguna.

### 3.2 Penegakan Tingkat Pengendali dan Tampilan (Server-Side Controller & View Guards)

Seluruh modul telah diamankan menggunakan pustaka pembantu terpusat [layouts/guard.php](file:///var/www/gorjes/layouts/guard.php):

```php
function require_role(array $allowed_roles) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['id_pegawai'])) {
        header('Location: /gorjes/login.php');
        exit;
    }
    $current_role = $_SESSION['jabatan'] ?? '';
    if (!in_array($current_role, $allowed_roles, true)) {
        http_response_code(403);
        $_SESSION['flash_error'] = 'Akses ditolak: Anda tidak memiliki izin untuk halaman/tindakan ini.';
        header('Location: /gorjes/dashboard/index.php');
        exit;
    }
}
```

### 3.3 Pemetaan Guard per Modul

Setiap berkas view dan controller aksi mengeksekusi `require_role()` sebelum memproses request:

- **Pegawai** (`dashboard/pegawai/*`): `require_role(['Owner', 'Admin'])`
- **Cabang Kantin** (`dashboard/cabang_kantin/*`): `require_role(['Owner', 'Admin', 'Pegawai Kantin'])`
- **Pelanggan** (`dashboard/pelanggan/*`): `require_role(['Owner', 'Admin', 'Pegawai Kantin', 'Pegawai Catering'])`
- **Bahan Baku** (`dashboard/bahan_baku/*`): `require_role(['Owner', 'Admin', 'Pegawai Dapur'])`
- **Produk & BOM** (`dashboard/produk/*`, `dashboard/bom/*`): `require_role(['Owner', 'Admin', 'Pegawai Dapur'])`
- **Pesanan & Detail Pesanan** (`dashboard/pesanan/*`, `dashboard/detail_pesanan/*`): `require_role(['Owner', 'Admin', 'Pegawai Kantin', 'Pegawai Catering'])`
- **Catering** (`dashboard/catering/*`): `require_role(['Owner', 'Admin', 'Pegawai Catering'])`
- **Dashboard Ringkasan** (`dashboard/index.php`): `require_role(['Owner', 'Admin'])` (peran non-manajerial dialihkan ke halaman operasional masing-masing saat login).
