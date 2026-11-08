# Implementation Workflow 4: Pengujian dan Penyempurnaan Interaksi UI/UX Kantin Gorjes

> **DO NOT MODIFY THIS FILE DIRECTLY WHEN EXECUTING.** Structure strictly follows `.agent-knowledge/static/templates/WORKFLOW-TEMPLATE.md`.

**Status:** `COMPLETE`  
**Target Outcome:** Seluruh interaksi UI/UX (navigasi, formulir, tabel data, status badge, modal/dialog konfirmasi, responsivitas mobile/desktop, aksesibilitas a11y, dan ekspor PDF) diuji secara teliti dan diperbaiki hingga mencapai standar editorial luxury yang presisi, konsisten, dan bebas cacat interaksi.  
**Owner:** Senior QA Automation Engineer & UI/UX Specialist  
**References:**

- `AGENTS.md`
- `.agent-knowledge/static/rules/antislop/UI.md`
- `.agent-knowledge/static/rules/antislop/LAYOUT-MOBILE.md`
- `.agent-knowledge/static/rules/antislop/COPYWRITING.md`
- `.agent-knowledge/static/rules/SECURITY-BASELINE.md`
- `.agent-knowledge/dynamic/workflows/3-remediasi-cacat-sistem-dan-bug-adversarial.md`

---

## 1. Objective and Scope

### Objective

Melakukan audit mendalam dan pengujian komprehensif terhadap seluruh interaksi UI/UX pada aplikasi Kantin Gorjes (baik di desktop maupun perangkat mobile), mendeteksi cacat interaksi (broken controls, active state error, contrast failure, horizontal overflow, layout clipping, missing feedback), serta mengimplementasikan perbaikan presisi tanpa merusak identitas desain luxury editorial (`#F9F8F6`, `#1A1A1A`, `#D4AF37`, Playfair Display, Inter, 0px border radius, noise overlay, vertical gridlines).

### In Scope

- Audit dan perbaikan state navigasi aktif pada sidebar (`layouts/menu.php`) agar mencerminkan halaman yang sedang diakses secara dinamis.
- Penambahan menu Catering yang sebelumnya hilang pada navigasi sidebar untuk role Pegawai Catering, Owner, dan Admin.
- Audit dan perbaikan interaktivitas drawer mobile navigation (hamburger toggle, overlay backdrop, transisi smooth, touch target minimum 44x44 px, tombol tutup close-btn, dan keyboard dismiss dengan tombol Escape).
- Audit dan perbaikan formulir: konsistensi floating labels, styling select dropdown (`.glass-input`), focus state ring `#D4AF37`, dan visual pesan error form (`render_flash_error` / `render_flash_success`).
- Audit dan perbaikan tabel data di semua halaman list (`*lihat.php`): perataan kolom teks vs numerik (currency rata kanan dengan font monospaced tabular numbers), pembungkus scroll responsif (`overflow-x-auto`) untuk mencegah kebocoran scroll horizontal, dan badge status berstandar kontras tinggi (WCAG AA).
- Perbaikan bug `colspan` pada empty state tabel (`produklihat.php` 6 kolom, `pesananlihat.php` 9 kolom, `bomdetail.php` 6 kolom, `pegawailihat.php` 4 kolom).
- Penyelesaian fitur inline editing kuantitas resep BOM pada `bomdetail.php` dan pembuatan controller `dashboard/bom/controllers/update.php`.
- Audit dan verifikasi fungsionalitas ekspor PDF di detail pesanan (`detail_pesananlihat.php`) dan BOM (`bomdetail.php`) dengan tombol interaktif status loading dan mekanisme unduh fallback jika popup diblokir peramban.
- Pembuatan test suite otomatis UI & Interaction testing (`tests/E2E/UiInteractionE2ETest.php`) dengan 10 skenario pengujian komprehensif.

### Out of Scope

- Penulisan ulang total antarmuka (redesign) atau migrasi ke Tailwind v4 / framework frontend modern.
- Pengubahan arsitektur sistem backend atau database schema di luar kebutuhan tampilan UI/UX.
- Penggunaan library CSS/JS berat baru yang tidak dibutuhkan.

### Assumptions and Open Questions

- Identitas desain luxury editorial (Playfair Display, Inter, `#F9F8F6`, `#1A1A1A`, `#D4AF37`, 0px border radius) tetap dipertahankan dan diperkuat presisinya.
- CDN Tailwind CSS dan FontAwesome tetap digunakan untuk kompatibilitas runtime native PHP.

---

## 2. Staged Execution Plan

### Stage 1: Audit & Perbaikan Navigasi, Sidebar, dan Responsivitas Mobile

**Objective:** Memastikan navigasi desktop dan mobile berfungsi tanpa cacat dan memiliki indikator visual halaman aktif yang akurat.

1. Implementasi helper function `nav_link_class()` pada `layouts/menu.php` untuk menyorot menu yang sedang aktif secara dinamis berdasarkan URL request.
2. Hapus hardcoded class `text-luxury-accent` pada menu Produk dan Pesanan.
3. Tambahkan link navigasi Catering untuk role `Pegawai Catering`, `Owner`, dan `Admin`.
4. Berikan tombol `#hamburger-btn` area sentuh minimal 44x44 px (`w-11 h-11`), `aria-label`, dan status `aria-expanded`.
5. Tambahkan tombol tutup `#sidebar-close-btn` di dalam drawer sidebar mobile serta event listener tombol `Escape` pada `layouts/vendor-scripts.php`.

**Exit Criteria:**

- [x] Menu navigasi menyorot halaman yang sedang aktif secara tepat dan tidak ada hardcoded active class di halaman lain.
- [x] Drawer navigasi mobile dapat dibuka dan ditutup dengan responsif, tombol escape, dan overlay backdrop.

### Stage 2: Audit & Perbaikan Formulir, Kontrol Input, dan Flash Feedback

**Objective:** Memastikan seluruh form interaktif memiliki UX yang jelas, terbaca, dan memiliki feedback instan.

1. Periksa elemen form (`input`, `select`, `textarea`) pada seluruh halaman modul.
2. Perbaiki floating labels pada `login.php` agar sejajar dengan baseline underline input (`left-0`, transisi `-top-4`) tanpa background frosted yang mengambang canggung.
3. Pastikan setiap label form terhubung dengan benar ke `id` input (`for` attribute) untuk aksesibilitas screen reader dan touch.
4. Periksa kontras dan tata letak notifikasi flash (`render_flash_error` dan `render_flash_success`).

**Exit Criteria:**

- [x] Form input konsisten dengan styling editorial underline, focus state emas (`#D4AF37`), dan label yang presisi.
- [x] Seluruh notifikasi validasi dan flash message tampil bersih, aman (XSS-safe), dan mudah terbaca.

### Stage 3: Audit & Perbaikan Tabel Data, Badge Status, dan Dialog Konfirmasi

**Objective:** Menyajikan data tabel dengan hierarki visual yang jelas, perataan angka presisi, dan proteksi aksi destruktif.

1. Bungkus seluruh tabel dalam kontainer `overflow-x-auto` dengan scrolling halus untuk mencegah horizontal scroll leak di viewport sempit.
2. Atur perataan kolom: teks rata kiri, status dan aksi rata tengah/kanan, nominal uang/harga rata kanan dengan `tabular-nums` dan `font-mono`.
3. Standarisasi warna status badge memenuhi kontras tinggi WCAG AA:
   - Selesai / Tersedia: `text-emerald-800 bg-emerald-50 border border-emerald-200/80`.
   - Diproses / Menipis: `text-amber-900 bg-amber-50 border border-amber-200/80`.
   - Dibatalkan / Habis: `text-rose-800 bg-rose-50 border border-rose-200/80`.
4. Perbaiki nilai `colspan` pada empty state rows di `produklihat.php` (6), `pesananlihat.php` (9), `bomdetail.php` (6), dan `pegawailihat.php` (4).
5. Selesaikan fitur inline quantity edit pada BOM table di `bomdetail.php` dan bangun controller `dashboard/bom/controllers/update.php`.

**Exit Criteria:**

- [x] Seluruh tabel responsif tanpa merusak gridline halaman.
- [x] Badge status konsisten dan memenuhi standar kontras WCAG AA.
- [x] Nilai colspan akurat di seluruh modul.
- [x] Inline BOM editing beroperasi mulus dan menyinkronkan HPP secara otomatis.

### Stage 4: Audit & Perbaikan Interaksi Fitur Khusus (Ekspor PDF & Detail Pesanan)

**Objective:** Menjamin fitur interaktif khusus seperti Ekspor PDF pada `detail_pesananlihat.php` dan `bomdetail.php` berjalan mulus dan memberikan visual feedback.

1. Hubungkan tombol `#btn-export-pdf` dengan visual loading feedback `<i class="fas fa-spinner fa-spin"></i> Memproses...` dan disabled state selama rendering berjalan.
2. Tambahkan mekanisme fallback ke `pdf.save(...)` bila popup browser diblokir otomatis oleh peramban pengguna.
3. Kembalikan state tombol di blok `finally` agar tombol selalu dapat digunakan kembali.

**Exit Criteria:**

- [x] Tombol Ekspor PDF memberikan feedback visual saat diklik dan mengunduh berkas PDF yang rapi dengan fallback andal.

### Stage 5: Pengujian Otomatis UI/UX dan Verifikasi E2E

**Objective:** Memvalidasi seluruh peningkatan UI/UX dengan test suite otomatis dan checklist anti-slop.

1. Buat test suite otomatis `tests/E2E/UiInteractionE2ETest.php` dengan 10 skenario pengujian komprehensif.
2. Jalankan seluruh test suite (`php tests/runner.php`) dan pastikan 100% lulus.

**Exit Criteria:**

- [x] Test suite UI/UX interaction berjalan dan lulus 100% (10/10 tests, 41 assertions).
- [x] Seluruh test suite (108/108 tests, 254 assertions) lulus 100% tanpa kegagalan atau error.

---

## 3. Dependencies, Risks, and Rollback

### Dependencies

- Isolated MariaDB testing server (`task-695`) untuk eksekusi runner test suite.
- CDN Tailwind CSS, FontAwesome, Chart.js, JSPDF, HTML2Canvas.

### Risks and Mitigations

| Risk | Impact | Mitigation |
| --- | --- | --- |
| Perubahan active class menu merusak rendering di subpath URL yang berbeda | Navigasi tidak menyorot menu dengan benar | Gunakan helper function PHP `nav_link_class()` yang mengecek pola path URL secara fleksibel |
| `overflow-x-auto` pada tabel memotong dropdown atau tooltip | Interaksi dropdown terpotong | Gunakan padding dan positioning aman pada elemen tabel |
| Render PDF canvas lambat pada faktur pesanan panjang | Pengguna mengira aplikasi hang | Berikan indikator loading pada tombol export PDF dan fallback download |

### Rollback or Recovery

Setiap perubahan dilakukan per file dengan diff terisolasi. Jika terjadi inkonsistensi styling, kembalikan ke snapshot git sebelumnya dengan aman tanpa merusak logic backend.

---

## 4. Verification and Acceptance Criteria

| Check | Method | Acceptance Criteria |
| --- | --- | --- |
| Menu Active State | Test runner & visual inspection | Menu item yang cocok dengan path aktif mendapatkan highlight `#D4AF37`, menu lain muted |
| Mobile Hamburger | Test runner & inspection | Tombol memiliki tap target >= 44x44 px, drawer sidebar terbuka/tutup dengan transisi |
| Table Responsiveness | DOM inspection & regex check | Semua tabel di `*lihat.php` berada di dalam wrapper `overflow-x-auto` |
| Monospace & Numeric Alignment | Table markup check | Kolom uang dan kuantitas rata kanan dan menggunakan font yang konsisten |
| Delete Confirmation | Action link inspection | Semua tombol/link hapus memiliki proteksi konfirmasi yang jelas |
| UI E2E Automated Tests | `php tests/runner.php` | Seluruh test suite UI interaction dan regresi test lulus 100% (108/108 tests) |

---

## 5. Completion and Handoff

- [x] All staged exit criteria and acceptance criteria are met.
- [x] Test suite mencatat 100% kelulusan seluruh tes (108/108 tests, 254 assertions).
- [x] Task report `4-pengujian-interaksi-ui-ux.md` disusun secara komprehensif.
- [x] Laporan hasil pengujian dan penyempurnaan UI/UX dipresentasikan ke pengguna.
