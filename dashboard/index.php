<?php
include '../layouts/session.php';
include '../layouts/config.php';
include '../layouts/guard.php';

require_role(['Owner', 'Admin'], match ($_SESSION['jabatan'] ?? '') {
    'Pegawai Kantin', 'Pegawai Catering' => '/gorjes/dashboard/pesanan/pesananlihat.php',
    'Pegawai Dapur' => '/gorjes/dashboard/bahan_baku/bahan_bakulihat.php',
    default => '/gorjes/login.php',
});

include '../layouts/head.php';
include '../layouts/menu.php';

// ===================== GATHER STATISTICS =====================

// 1. Card: Total Order (all time)
$total_order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM pesanan"))['c'];

// 2. Card: Diproses (active orders)
$total_diproses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM pesanan WHERE status_pesanan = 'Diproses'"))['c'];

// 3. Card: Omzet bulan ini (Parameterized)
$bulan_ini = date('Y-m');
$stmt_omzet = mysqli_prepare($conn, "SELECT SUM(total_harga) as s FROM pesanan WHERE DATE_FORMAT(tanggal_pesanan, '%Y-%m') = ? AND status_pesanan != 'Dibatalkan'");
mysqli_stmt_bind_param($stmt_omzet, 's', $bulan_ini);
mysqli_stmt_execute($stmt_omzet);
$omzet_row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_omzet));
$omzet = $omzet_row['s'] ? $omzet_row['s'] : 0;

// 4. Card: Stok Kritis (Menipis + Habis)
$stok_kritis_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM bahan_baku WHERE status_ketersediaan IN ('Menipis', 'Habis')"))['c'];

// ===================== CHART: Order per Hari (14 hari) =====================
$order_daily_labels = [];
$order_daily_data = [];
$stmt_daily = mysqli_prepare($conn, "SELECT COUNT(*) as c FROM pesanan WHERE DATE(tanggal_pesanan) = ?");
for ($i = 13; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $order_daily_labels[] = date('d M', strtotime($d));
    mysqli_stmt_bind_param($stmt_daily, 's', $d);
    mysqli_stmt_execute($stmt_daily);
    $c = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_daily))['c'];
    $order_daily_data[] = intval($c);
}

// ===================== CHART: Status Order Donut =====================
$status_labels = ['Diproses', 'Selesai', 'Dibatalkan'];
$status_data = [];
$stmt_status_chart = mysqli_prepare($conn, "SELECT COUNT(*) as c FROM pesanan WHERE status_pesanan = ?");
foreach ($status_labels as $s) {
    mysqli_stmt_bind_param($stmt_status_chart, 's', $s);
    mysqli_stmt_execute($stmt_status_chart);
    $c = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_status_chart))['c'];
    $status_data[] = intval($c);
}

// ===================== LEFT: Pesanan Terbaru (5) =====================
$pesanan_terbaru = mysqli_query($conn, "
    SELECT p.id_pesanan, p.tanggal_pesanan, p.total_harga, p.status_pesanan, p.jenis_pesanan,
           pel.nama_pelanggan
    FROM pesanan p
    LEFT JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
    ORDER BY p.id_pesanan DESC
    LIMIT 5
");

// ===================== RIGHT: Stok Kritis =====================
$stok_kritis_list = mysqli_query($conn, "
    SELECT nama_bahan, stok, satuan, stok_kritis, status_ketersediaan
    FROM bahan_baku
    WHERE status_ketersediaan IN ('Menipis', 'Habis')
    ORDER BY stok ASC
    LIMIT 8
");

// ===================== RIGHT: Kebutuhan Bahan (paling banyak dipakai) =====================
$kebutuhan_bahan = mysqli_query($conn, "
    SELECT bb.nama_bahan, bb.satuan, SUM(b.qty * dp.qty) as total_kebutuhan
    FROM detail_pesanan dp
    JOIN bom b ON dp.id_produk = b.id_produk
    JOIN bahan_baku bb ON b.id_bahan_baku = bb.id_bahan_baku
    JOIN pesanan p ON dp.id_pesanan = p.id_pesanan
    WHERE p.status_pesanan = 'Diproses'
    GROUP BY bb.id_bahan_baku
    ORDER BY total_kebutuhan DESC
    LIMIT 5
");

// ===================== BOTTOM: Produk Terlaris =====================
$top_produk = mysqli_query($conn, "
    SELECT p.nama_produk, p.kategori, SUM(dp.qty) as total_terjual, SUM(dp.subtotal) as total_revenue
    FROM detail_pesanan dp
    JOIN produk p ON dp.id_produk = p.id_produk
    GROUP BY dp.id_produk
    ORDER BY total_terjual DESC
    LIMIT 6
");
$top_prod_labels = [];
$top_prod_data = [];
$top_prod_revenue = [];
mysqli_data_seek($top_produk, 0);
while ($r = mysqli_fetch_assoc($top_produk)) {
    $top_prod_labels[] = $r['nama_produk'];
    $top_prod_data[] = intval($r['total_terjual']);
    $top_prod_revenue[] = floatval($r['total_revenue']);
}
mysqli_data_seek($top_produk, 0);
?>

<!-- ==================== HEADER ==================== -->
<div class="mb-10 relative z-10">
    <h2 class="font-serif text-4xl md:text-5xl tracking-tight mb-3 text-luxury-fg">Dashboard</h2>
    <p class="text-sm tracking-[0.1em] text-luxury-mutedFg">Selamat datang,
        <span class="text-luxury-accent font-medium"><?= htmlspecialchars($_SESSION['nama_pegawai']) ?></span>
        <span
            class="ml-2 inline-block px-2 py-0.5 border border-[#1A1A1A] text-[10px] tracking-wider uppercase text-luxury-mutedFg"><?= htmlspecialchars($_SESSION['jabatan'] ?? '') ?></span>
    </p>
</div>

<!-- ==================== CARD SUMMARY ==================== -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-10 relative z-10">

    <!-- Total Order -->
    <div class="glass-panel p-6 hover:-translate-y-1 transition-transform duration-300 relative group">
        <div class="flex justify-between items-start mb-6">
            <h3 class="text-[10px] tracking-[0.25em] uppercase text-luxury-mutedFg font-medium">Total Order</h3>
            <div class="text-luxury-fg opacity-70">
                <i class="fas fa-clipboard-list text-sm"></i>
            </div>
        </div>
        <p class="font-serif text-4xl text-luxury-fg leading-none"><?= number_format($total_order) ?></p>
    </div>

    <!-- Diproses -->
    <div class="glass-panel p-6 hover:-translate-y-1 transition-transform duration-300 relative group">
        <div class="flex justify-between items-start mb-6">
            <h3 class="text-[10px] tracking-[0.25em] uppercase text-luxury-mutedFg font-medium">Diproses</h3>
            <div class="text-luxury-fg opacity-70">
                <i class="fas fa-spinner text-sm"></i>
            </div>
        </div>
        <p class="font-serif text-4xl text-luxury-fg leading-none"><?= number_format($total_diproses) ?></p>
    </div>

    <!-- Omzet -->
    <div class="glass-panel p-6 hover:-translate-y-1 transition-transform duration-300 relative group">
        <div class="flex justify-between items-start mb-6">
            <h3 class="text-[10px] tracking-[0.25em] uppercase text-luxury-mutedFg font-medium">Omzet</h3>
            <div class="text-luxury-accent">
                <i class="fas fa-wallet text-sm"></i>
            </div>
        </div>
        <p class="font-serif text-3xl text-luxury-fg leading-none">Rp <?= number_format($omzet, 0, ',', '.') ?></p>
    </div>

    <!-- Stok Kritis -->
    <div
        class="glass-panel p-6 hover:-translate-y-1 transition-transform duration-300 relative group <?= $stok_kritis_count > 0 ? 'border-t-[#1A1A1A] border-t-2' : '' ?>">
        <div class="flex justify-between items-start mb-6">
            <h3 class="text-[10px] tracking-[0.25em] uppercase text-luxury-mutedFg font-medium">Stok Kritis</h3>
            <div class="<?= $stok_kritis_count > 0 ? 'text-[#1A1A1A]' : 'text-luxury-fg opacity-70' ?>">
                <i class="fas fa-exclamation-triangle text-sm"></i>
            </div>
        </div>
        <p
            class="font-serif text-4xl <?= $stok_kritis_count > 0 ? 'text-luxury-fg' : 'text-luxury-mutedFg' ?> leading-none">
            <?= number_format($stok_kritis_count) ?>
        </p>
    </div>

</div>

<!-- ==================== CHART SECTION ==================== -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10 relative z-10">

    <!-- Chart: Order per Hari -->
    <div class="lg:col-span-2 glass-panel p-6">
        <h3 class="font-serif text-lg text-luxury-fg mb-5">Order per Hari <span
                class="text-xs text-luxury-mutedFg font-sans">(14 hari terakhir)</span></h3>
        <div class="relative h-[280px] w-full">
            <canvas id="orderDailyChart"></canvas>
        </div>
    </div>

    <!-- Chart: Status Order Donut -->
    <div class="glass-panel p-6 flex flex-col">
        <h3 class="font-serif text-lg text-luxury-fg mb-5">Status Order</h3>
        <div class="relative flex-1 w-full flex items-center justify-center min-h-[220px]">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<!-- ==================== MIDDLE: LEFT + RIGHT ==================== -->
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-10 relative z-10">

    <!-- LEFT: Pesanan Terbaru -->
    <div class="lg:col-span-3 glass-panel p-6">
        <div class="flex justify-between items-center mb-5">
            <h3 class="font-serif text-lg text-luxury-fg">Pesanan Terbaru</h3>
            <a href="pesanan/pesananlihat.php"
                class="text-[10px] tracking-[0.2em] uppercase text-luxury-mutedFg hover:text-luxury-accent transition-colors">Lihat
                Semua →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="border-b border-luxury-fg/10 uppercase text-[10px] tracking-[0.2em] text-luxury-mutedFg">
                        <th class="py-3 px-3 font-normal">ID</th>
                        <th class="py-3 px-3 font-normal">Pelanggan</th>
                        <th class="py-3 px-3 font-normal">Jenis</th>
                        <th class="py-3 px-3 font-normal">Total</th>
                        <th class="py-3 px-3 font-normal">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($p = mysqli_fetch_assoc($pesanan_terbaru)): ?>
                        <tr class="border-b border-[#1A1A1A]/10 hover:bg-[#F9F8F6] transition-colors">
                            <td class="py-4 px-3 font-mono text-xs text-luxury-mutedFg">#<?= $p['id_pesanan'] ?></td>
                            <td class="py-4 px-3 font-serif text-sm"><?= htmlspecialchars($p['nama_pelanggan'] ?? '-') ?>
                            </td>
                            <td class="py-4 px-3">
                                <span
                                    class="text-[9px] tracking-[0.2em] uppercase text-luxury-mutedFg"><?= $p['jenis_pesanan'] ?></span>
                            </td>
                            <td class="py-4 px-3 text-sm font-medium">Rp
                                <?= number_format($p['total_harga'], 0, ',', '.') ?>
                            </td>
                            <td class="py-4 px-3">
                                <span
                                    class="text-[9px] tracking-[0.2em] uppercase <?= $p['status_pesanan'] === 'Selesai' ? 'text-luxury-fg font-bold' : ($p['status_pesanan'] === 'Dibatalkan' ? 'text-red-800 line-through' : 'text-luxury-mutedFg') ?>">
                                    <?= $p['status_pesanan'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- RIGHT: Stok Kritis + Kebutuhan Bahan -->
    <div class="lg:col-span-2 space-y-6">

        <!-- Stok Kritis -->
        <div class="glass-panel p-6 <?= $stok_kritis_count > 0 ? 'border-red-100' : '' ?>">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-serif text-lg text-luxury-fg">
                    <i class="fas fa-exclamation-circle text-red-400 mr-2 text-sm"></i>Stok Kritis
                </h3>
                <a href="bahan_baku/bahan_bakulihat.php"
                    class="text-[10px] tracking-[0.2em] uppercase text-luxury-mutedFg hover:text-luxury-accent transition-colors">Detail
                    →</a>
            </div>
            <?php if (mysqli_num_rows($stok_kritis_list) > 0): ?>
                <div class="space-y-0">
                    <?php while ($b = mysqli_fetch_assoc($stok_kritis_list)): ?>
                        <?php $is_habis = $b['status_ketersediaan'] === 'Habis'; ?>
                        <div
                            class="flex items-center justify-between py-3 px-4 border-b border-[#1A1A1A]/10 hover:bg-[#F9F8F6] transition-colors <?= $is_habis ? 'text-red-900' : 'text-luxury-fg' ?>">
                            <div class="flex items-center gap-3">
                                <span
                                    class="text-[8px] uppercase tracking-widest <?= $is_habis ? 'font-bold' : '' ?>"><?= $is_habis ? 'HABIS' : 'KRITIS' ?></span>
                                <span class="text-sm font-serif"><?= htmlspecialchars($b['nama_bahan']) ?></span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-mono"><?= number_format($b['stok'], 1) ?></span>
                                <span
                                    class="text-[9px] tracking-widest uppercase ml-1 opacity-70"><?= htmlspecialchars($b['satuan']) ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-sm text-green-600 py-4 text-center"><i class="fas fa-check-circle mr-2"></i>Semua stok aman!
                </p>
            <?php endif; ?>
        </div>

        <!-- Kebutuhan Bahan (pesanan aktif) -->
        <div class="glass-panel p-6">
            <h3 class="font-serif text-lg text-luxury-fg mb-4">
                <i class="fas fa-box-open text-luxury-accent mr-2 text-sm"></i>Kebutuhan Bahan Aktif
            </h3>
            <p class="text-[10px] text-luxury-mutedFg mb-3 uppercase tracking-wider">Total kebutuhan dari pesanan
                berstatus "Diproses"</p>
            <?php if (mysqli_num_rows($kebutuhan_bahan) > 0): ?>
                <div class="space-y-0">
                    <?php while ($kb = mysqli_fetch_assoc($kebutuhan_bahan)): ?>
                        <div
                            class="flex items-center justify-between py-3 px-4 border-b border-[#1A1A1A]/10 hover:bg-[#F9F8F6] transition-colors">
                            <span class="text-sm font-serif"><?= htmlspecialchars($kb['nama_bahan']) ?></span>
                            <div class="text-right">
                                <span
                                    class="text-sm font-mono text-luxury-accent font-semibold"><?= number_format($kb['total_kebutuhan'], 1) ?></span>
                                <span
                                    class="text-[9px] tracking-widest uppercase text-luxury-mutedFg ml-1"><?= htmlspecialchars($kb['satuan']) ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-sm text-luxury-mutedFg py-4 text-center">Tidak ada pesanan aktif saat ini.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ==================== BOTTOM: Produk Terlaris ==================== -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10 relative z-10">

    <!-- Bar Chart -->
    <div class="lg:col-span-2 glass-panel p-6">
        <h3 class="font-serif text-lg text-luxury-fg mb-5">Produk Terlaris</h3>
        <div class="relative h-[280px] w-full">
            <canvas id="topProdChart"></canvas>
        </div>
    </div>

    <!-- Tabel Produk Terlaris -->
    <div class="glass-panel p-6">
        <h3 class="font-serif text-lg text-luxury-fg mb-5">Detail Produksi</h3>
        <div class="space-y-3">
            <?php $rank = 1;
            while ($tp = mysqli_fetch_assoc($top_produk)): ?>
                <div
                    class="flex items-center gap-4 py-3 border-b border-[#1A1A1A]/10 hover:bg-[#F9F8F6] transition-colors px-2">
                    <span
                        class="w-6 h-6 border border-luxury-accent/30 text-luxury-accent flex items-center justify-center font-mono text-[10px] shrink-0"><?= $rank++ ?></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-serif truncate"><?= htmlspecialchars($tp['nama_produk']) ?></p>
                        <p class="text-[9px] tracking-widest uppercase text-luxury-mutedFg">
                            <?= htmlspecialchars($tp['kategori'] ?? '-') ?>
                        </p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-sm font-mono font-medium"><?= number_format($tp['total_terjual']) ?> pcs</p>
                        <p class="text-[9px] tracking-widest uppercase text-luxury-mutedFg">Rp
                            <?= number_format($tp['total_revenue'], 0, ',', '.') ?>
                        </p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- ==================== CHART SCRIPTS ==================== -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cAccent = '#D4AF37';
        const cMuted = '#6C6863';
        const cGrid = 'rgba(0, 0, 0, 0.05)';

        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = cMuted;
        Chart.defaults.scale.grid.color = cGrid;
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(26, 26, 26, 0.9)';
        Chart.defaults.plugins.tooltip.padding = 12;
        Chart.defaults.plugins.tooltip.cornerRadius = 0;
        Chart.defaults.plugins.tooltip.titleFont = { family: "'Playfair Display', serif", size: 14 };

        // 1. Order per Hari - Area Chart
        new Chart(document.getElementById('orderDailyChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($order_daily_labels) ?>,
                datasets: [{
                    label: 'Jumlah Order',
                    data: <?= json_encode($order_daily_data) ?>,
                    borderColor: cAccent,
                    backgroundColor: 'rgba(212, 175, 55, 0.08)',
                    borderWidth: 2,
                    pointBackgroundColor: cAccent,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 },
                        grid: { borderDash: [3, 3] }
                    },
                    x: { grid: { display: false } }
                }
            }
        });

        // 2. Status Order Donut
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($status_labels) ?>,
                datasets: [{
                    data: <?= json_encode($status_data) ?>,
                    backgroundColor: [
                        '#1A1A1A', // Diproses (Black)
                        '#D4AF37', // Selesai (Gold/Accent)
                        '#E5E5E5'  // Dibatalkan (Light Gray)
                    ],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 16, font: { size: 11 } }
                    }
                }
            }
        });

        // 3. Produk Terlaris Bar Chart
        new Chart(document.getElementById('topProdChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($top_prod_labels) ?>,
                datasets: [{
                    label: 'Terjual',
                    data: <?= json_encode($top_prod_data) ?>,
                    backgroundColor: [
                        'rgba(212, 175, 55, 0.85)',
                        'rgba(212, 175, 55, 0.7)',
                        'rgba(212, 175, 55, 0.55)',
                        'rgba(212, 175, 55, 0.4)',
                        'rgba(212, 175, 55, 0.3)',
                        'rgba(212, 175, 55, 0.2)'
                    ],
                    borderRadius: 0,
                    barThickness: 28
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 },
                        grid: { borderDash: [3, 3] }
                    },
                    y: { grid: { display: false } }
                }
            }
        });
    });
</script>
<?php
include '../layouts/footer.php';
include '../layouts/vendor-scripts.php';
?>