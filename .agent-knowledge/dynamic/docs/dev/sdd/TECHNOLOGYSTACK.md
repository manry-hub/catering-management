# Technology Stack: Kantin Gorjes

> **Status:** `CURRENT` (Mode: Existing Project)  
> **Output:** `.agent-knowledge/dynamic/docs/dev/sdd/TECHNOLOGYSTACK.md`  
> **Purpose:** Daftar kanonikal teknologi, pustaka, versi, dan peranannya dalam sistem Kantin Gorjes.

| Layer | Technology | Version | Role |
|-------|------------|---------|------|
| Backend Runtime | PHP | 8.x (Native Procedural) | Runtime bahasa server, penanganan sesi, routing berkas, dan pemrosesan logika bisnis transaksi. |
| Database Engine | MySQL / MariaDB | 8.0+ / 10.4+ | Sistem manajemen basis data relasional utama, penegakan foreign key, dan transaksi ACID dengan engine InnoDB. |
| Database Connector | PHP mysqli Extension | PHP Core Bundled | Ekstensi penghubung database dengan dukungan prepared statements dan kontrol transaksi (`begin_transaction`, `commit`, `rollback`). |
| Web Server | Apache HTTP Server | 2.4+ (dengan `mod_php`) | Penangan permintaan HTTP, perutean direktori statis, dan eksekutor script PHP server. |
| Frontend CSS Engine | Tailwind CSS | 3.x (CDN: `cdn.tailwindcss.com`) | Framework utilitas CSS untuk tata letak antarmuka responsif dan kustomisasi tema palet warna luxury. |
| UI Typography | Google Fonts (Playfair Display & Inter) | Web Font Service | Tipografi editorial serif untuk judul elegan dan sans-serif untuk keterbacaan data tabel. |
| Iconography | Font Awesome | 6.4.0 (CDN cdnjs) | Pustaka ikon antarmuka untuk navigasi menu, aksi tombol, dan indikator status. |
| Client-Side Charts | Chart.js | Latest (CDN jsdelivr) | Pustaka visualisasi grafik donat status pesanan dan grafik garis transaksi harian pada dashboard. |
| Document Export | jsPDF | Latest (CDN cdnjs) | Pustaka JavaScript sisi browser untuk menyusun dan mengunduh struk kasir serta invoice catering dalam format PDF. |
| Version Control | Git & GitHub | Git 2.x / GitHub Repository | Pengelolaan repositori kode sumber dan pelacakan riwayat versi pengembangan. |
