<?php
include '../../layouts/session.php';
include '../../layouts/config.php';
include '../../layouts/guard.php';

require_role(['Owner', 'Admin', 'Pegawai Kantin', 'Pegawai Catering'], '../pesanan/pesananlihat.php');

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ../pesanan/pesananlihat.php');
    exit;
}

$sql = "SELECT p.*, pel.nama_pelanggan, ck.nama_cabang, ck.alamat_cabang, peg.nama_pegawai, cat.lokasi_acara, cat.tanggal_acara AS tgl_acara
        FROM pesanan p 
        LEFT JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan 
        LEFT JOIN cabang_kantin ck ON p.id_cabang_kantin = ck.id_cabang_kantin 
        LEFT JOIN pegawai peg ON p.id_pegawai = peg.id_pegawai 
        LEFT JOIN catering cat ON p.id_catering = cat.id_catering
        WHERE p.id_pesanan = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$data) {
    $_SESSION['flash_error'] = 'Data tidak ditemukan.';
    header('Location: ../pesanan/pesananlihat.php');
    exit;
}

$sql_d = "SELECT dp.*, pr.nama_produk, pr.harga_jual FROM detail_pesanan dp LEFT JOIN produk pr ON dp.id_produk = pr.id_produk WHERE dp.id_pesanan = ?";
$stmt_d = mysqli_prepare($conn, $sql_d);
mysqli_stmt_bind_param($stmt_d, 'i', $id);
mysqli_stmt_execute($stmt_d);
$result_d = mysqli_stmt_get_result($stmt_d);

$detail_rows = [];
while ($item = mysqli_fetch_assoc($result_d)) {
    $detail_rows[] = $item;
}

$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
$autoExport = isset($_GET['export']) && $_GET['export'] === '1';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

include '../../layouts/head.php';
include '../../layouts/menu.php';
?>
<div class="mb-12 relative z-10 flex justify-between items-start">
    <div>
        <h2 class="font-serif text-4xl tracking-tight mb-2">Detail Pesanan #
            <?= htmlspecialchars($data['id_pesanan']) ?>
        </h2>
        <a href="../pesanan/pesananlihat.php"
            class="text-xs tracking-[0.2em] uppercase text-luxury-mutedFg hover:text-luxury-accent transition-colors">←
            Kembali ke Pesanan</a>
    </div>
    <button id="btn-export-pdf" onclick="exportPDF(this)"
        class="h-9 px-4 border border-[#1A1A1A] bg-[#1A1A1A] text-white text-[10px] tracking-[0.15em] uppercase flex items-center gap-2 hover:bg-transparent hover:text-[#1A1A1A] transition-all duration-300 no-print">
        <i class="fas fa-file-pdf"></i> Export PDF
    </button>
</div>

<?php if ($flash_success): ?>
    <div class="mb-8 p-4 rounded-lg border border-green-200 text-green-800 text-sm bg-green-50/80 relative z-10">
        <?= htmlspecialchars($flash_success, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>
<?php if ($flash_error): ?>
    <div class="mb-8 p-4 rounded-lg border border-red-200 text-red-800 text-sm bg-red-50/80 relative z-10">
        <?php foreach (preg_split('/<br\s*\/?>|\n/i', (string)$flash_error) as $l): if (trim($l) !== ''): ?>
            <div><?= htmlspecialchars(trim($l), ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; endforeach; ?>
    </div>
<?php endif; ?>

<div class="pdf-content">
    <div class="relative z-10 glass-panel p-8 mb-8 max-w-4xl">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <p class="text-[10px] tracking-[0.2em] uppercase text-luxury-mutedFg mb-1">Pelanggan</p>
                <p class="text-sm font-medium"><?= htmlspecialchars($data['nama_pelanggan'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-[10px] tracking-[0.2em] uppercase text-luxury-mutedFg mb-1">Jenis</p>
                <span
                    class="text-[9px] tracking-[0.2em] uppercase text-luxury-mutedFg"><?= htmlspecialchars($data['jenis_pesanan']) ?></span>
            </div>
            <div>
                <?php if ($data['jenis_pesanan'] === 'Kantin'): ?>
                    <p class="text-[10px] tracking-[0.2em] uppercase text-luxury-mutedFg mb-1">Cabang Kantin</p>
                    <p class="text-sm font-medium"><?= htmlspecialchars($data['nama_cabang'] ?? '-') ?></p>


                <?php endif; ?>
            </div>
            <div>
                <p class="text-[10px] tracking-[0.2em] uppercase text-luxury-mutedFg mb-1">Nama Pegawai</p>
                <p class="text-sm font-medium"><?= htmlspecialchars($data['nama_pegawai'] ?? '-') ?></p>
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-6 pt-6 border-t border-luxury-fg/5">
            <div>
                <p class="text-[10px] tracking-[0.2em] uppercase text-luxury-mutedFg mb-1">Tanggal</p>
                <p class="text-sm font-medium"><?= date('d M Y, H:i', strtotime($data['tanggal_pesanan'])) ?></p>
            </div>
            <div>
                <?php
                if ($data['status_pesanan'] === 'Selesai') {
                    $statusBadge = 'text-emerald-800 bg-emerald-50 border border-emerald-200/80';
                } elseif ($data['status_pesanan'] === 'Dibatalkan') {
                    $statusBadge = 'text-rose-800 bg-rose-50 border border-rose-200/80 line-through';
                } else {
                    $statusBadge = 'text-amber-900 bg-amber-50 border border-amber-200/80';
                }
                ?>
                <p class="text-[10px] tracking-[0.2em] uppercase text-luxury-mutedFg mb-1">Status</p>
                <span class="inline-block px-2.5 py-1 text-[9px] tracking-[0.15em] uppercase font-semibold <?= $statusBadge ?>">
                    <?= htmlspecialchars($data['status_pesanan']) ?>
                </span>
            </div>
            <div class="col-span-2">
                <p class="text-[10px] tracking-[0.2em] uppercase text-luxury-mutedFg mb-1">Total Harga</p>
                <p class="text-lg font-serif font-semibold text-luxury-accent">Rp
                    <?= number_format($data['total_harga'], 0, ',', '.') ?>
                </p>
            </div>
        </div>
        <div class="mt-6 pt-6 border-t border-luxury-fg/5">
            <p class="text-[10px] tracking-[0.2em] uppercase text-luxury-mutedFg mb-1">Catatan</p>
            <p class="text-sm font-medium">
                <?= !empty($data['catatan']) ? nl2br(htmlspecialchars($data['catatan'])) : '-' ?>
            </p>
        </div>
    </div>

    <?php if ($data['jenis_pesanan'] === 'Catering' && !empty($data['id_catering'])): ?>
        <div class="relative z-10 glass-panel p-6 mb-8 max-w-4xl">
            <h4 class="font-serif text-lg text-luxury-fg mb-4">Informasi Catering</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-[10px] tracking-[0.2em] uppercase text-purple-800/60 mb-1">Tanggal Acara</p>
                    <p class="text-sm font-medium text-purple-900">
                        <?= !empty($data['tgl_acara']) ? date('d M Y, H:i', strtotime($data['tgl_acara'])) : '-' ?>
                    </p>
                </div>
                <div>
                    <p class="text-[10px] tracking-[0.2em] uppercase text-purple-800/60 mb-1">Lokasi Acara</p>
                    <p class="text-sm font-medium text-purple-900"><?= htmlspecialchars($data['lokasi_acara'] ?? '-') ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="overflow-x-auto relative z-10 max-w-4xl mb-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-serif text-lg">Item Pesanan</h3>
            <a href="detail_pesanantambah.php?id_pesanan=<?= $id ?>"
                class="h-9 px-4 border border-[#1A1A1A] bg-[#1A1A1A] text-white text-[10px] tracking-[0.15em] uppercase flex items-center gap-2 hover:bg-transparent hover:text-[#1A1A1A] transition-all duration-300 no-print">
                <i class="fas fa-plus"></i> Tambah Produk
            </a>
        </div>

        <table
            class="w-full text-left text-sm whitespace-nowrap bg-white/20 backdrop-blur-sm rounded-lg overflow-hidden">
            <thead>
                <tr
                    class="border-b border-luxury-fg/10 uppercase text-[10px] tracking-[0.2em] text-luxury-mutedFg bg-black/5">
                    <th class="py-4 px-4 font-normal">#</th>
                    <th class="py-4 px-4 font-normal">Produk</th>
                    <th class="py-4 px-4 font-normal">Harga Jual</th>
                    <th class="py-4 px-4 font-normal">Qty</th>
                    <th class="py-4 px-4 font-normal text-right">Subtotal</th>
                    <th class="py-4 px-4 font-normal text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                foreach ($detail_rows as $item): ?>
                    <tr class="border-b border-[#1A1A1A]/10 hover:bg-[#F9F8F6] transition-colors duration-300">
                        <td class="py-4 px-4 text-luxury-mutedFg"><?= $no++ ?></td>
                        <td class="py-4 px-4 font-serif"><?= htmlspecialchars($item['nama_produk'] ?? '-') ?></td>
                        <td class="py-4 px-4 font-mono text-luxury-mutedFg">Rp
                            <?= number_format($item['harga_jual'], 0, ',', '.') ?>
                        </td>
                        <td class="py-4 px-4 font-mono"><?= htmlspecialchars($item['qty']) ?></td>
                        <td class="py-4 px-4 text-right font-mono font-medium">Rp
                            <?= number_format($item['subtotal'], 0, ',', '.') ?>
                        </td>
                        <td class="py-4 px-4 text-right space-x-2">
                            <?php
                            $item_url_params = 'id_pesanan=' . urlencode($id) . '&id_produk=' . urlencode($item['id_produk']);
                            ?>
                            <a href="detail_pesananubah.php?<?= $item_url_params ?>"
                                class="text-[10px] uppercase tracking-widest text-luxury-mutedFg hover:text-luxury-accent transition-colors">Ubah</a>
                            <a href="detail_pesananhapus.php?<?= $item_url_params ?>"
                                class="text-[10px] uppercase tracking-widest text-luxury-mutedFg hover:text-red-700 transition-colors">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="bg-black/5">
                    <td colspan="4"
                        class="py-4 px-4 text-right font-serif font-semibold text-sm uppercase tracking-wider">
                        Total Keseluruhan</td>
                    <td class="py-4 px-4 text-right font-serif font-semibold text-lg text-luxury-accent">Rp
                        <?= number_format($data['total_harga'], 0, ',', '.') ?>
                    </td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    const shouldAutoExport = <?= $autoExport ? 'true' : 'false' ?>;
    const orderData = {
        id: <?= json_encode($data['id_pesanan']) ?>,
        pelanggan: <?= json_encode($data['nama_pelanggan'] ?? '-') ?>,
        jenis: <?= json_encode($data['jenis_pesanan']) ?>,
        cabang: <?= json_encode($data['nama_cabang'] ?? '-') ?>,
        alamat_cabang: <?= json_encode($data['alamat_cabang'] ?? '-') ?>,
        tanggal: <?= json_encode(date('d M Y, H:i', strtotime($data['tanggal_pesanan']))) ?>,
        status: <?= json_encode($data['status_pesanan']) ?>,
        total_harga: <?= json_encode($data['total_harga']) ?>,
        catatan: <?= json_encode(!empty($data['catatan']) ? $data['catatan'] : '-') ?>,
        pegawai: <?= json_encode($data['nama_pegawai'] ?? '-') ?>,
        // catering specific
        tgl_acara: <?= json_encode(!empty($data['tgl_acara']) ? date('d M Y, H:i', strtotime($data['tgl_acara'])) : '-') ?>,
        lokasi_acara: <?= json_encode($data['lokasi_acara'] ?? '-') ?>
    };
    const itemRows = <?= json_encode($detail_rows, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    function formatRupiah(value) {
        return 'Rp ' + Number(value).toLocaleString('id-ID');
    }

    function drawHeader(pdf, logoData = null) {
        const pageWidth = pdf.internal.pageSize.getWidth();
        const margin = 15;
        // Compact header for cleaner PDF
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(12);
        pdf.text('kantin gorjes', margin, 16);

        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(8);
        pdf.text('+62 821-1162-2287', margin, 22);
        pdf.text('Jl. Ketapang No.8, RT.2/RW.10, Jati Padang, Ps. Minggu', margin, 27);
        pdf.text('Kota Jakarta Selatan, DKI Jakarta 12540', margin, 32);

        const logoSize = 42;
        const logoX = pageWidth - margin - logoSize;
        const logoY = 8;

        if (logoData) {
            try {
                pdf.addImage(logoData, 'PNG', logoX, logoY, logoSize, logoSize);
                return;
            } catch (e) {
                // fallback to text if image fails to render
            }
        }

        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(12);
        pdf.text('Gorjes', logoX + logoSize / 2, logoY + 18, { align: 'center' });
        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(7);
        pdf.text('Delicious Home Food', logoX + logoSize / 2, logoY + 25, { align: 'center' });
    }

    function drawOrderInfo(pdf, yStart) {
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(10);
        pdf.text('id pesanan =', 15, yStart);
        pdf.text('nama pelanggan =', 110, yStart);

        pdf.setFont('helvetica', 'normal');
        pdf.text(String(orderData.id), 45, yStart);
        pdf.text(orderData.pelanggan, 150, yStart);

        pdf.setFont('helvetica', 'bold');
        pdf.text('jenis =', 15, yStart + 8);
        pdf.text('cabang kantin =', 110, yStart + 8);

        pdf.setFont('helvetica', 'normal');
        pdf.text(orderData.jenis, 40, yStart + 8);
        pdf.text(orderData.cabang, 150, yStart + 8);

        pdf.setFont('helvetica', 'bold');
        pdf.text('tanggal =', 15, yStart + 16);
        pdf.text('alamat cabang =', 110, yStart + 16);

        pdf.setFont('helvetica', 'normal');
        pdf.text(orderData.tanggal, 44, yStart + 16);
        pdf.text(orderData.alamat_cabang || '-', 150, yStart + 16);

        pdf.setFont('helvetica', 'bold');
        pdf.text('status =', 15, yStart + 24);
        pdf.setFont('helvetica', 'normal');
        pdf.text(orderData.status, 35, yStart + 24);

        // tighten vertical spacing to keep layout compact
        return yStart + 30;
    }

    function drawItemTable(pdf, yStart) {
        const pageWidth = pdf.internal.pageSize.getWidth();
        const margin = 15;
        const tableRight = pageWidth - margin;
        const cols = {
            no: margin + 2,
            produk: margin + 20,
            harga: margin + 86,
            qty: tableRight - 48,
            subtotal: tableRight - 2
        };

        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(12);
        pdf.text('detail pesanan', pageWidth / 2, yStart, { align: 'center' });
        yStart += 6;
        pdf.setLineWidth(0.4);
        pdf.line(margin, yStart, tableRight, yStart);
        yStart += 8;

        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(9);
        pdf.text('no', cols.no, yStart);
        pdf.text('produk', cols.produk, yStart);
        pdf.text('harga jual', cols.harga, yStart);
        pdf.text('qty', cols.qty, yStart, { align: 'right' });
        pdf.text('subtotal', cols.subtotal, yStart, { align: 'right' });
        yStart += 3;
        pdf.line(margin, yStart, tableRight, yStart);
        yStart += 6;

        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(9);

        itemRows.forEach((item, index) => {
            const nameLines = pdf.splitTextToSize(String(item.nama_produk || '-'), cols.qty - cols.produk - 6);
            const rowHeight = Math.max(1, nameLines.length) * 6;

            if (yStart + rowHeight > pdf.internal.pageSize.getHeight() - 40) {
                pdf.addPage();
                yStart = 20;
            }

            pdf.text(String(index + 1), cols.no, yStart);
            pdf.text(nameLines, cols.produk, yStart);
            // right-align price column but keep it near produk column
            pdf.text(formatRupiah(item.harga_jual), cols.harga, yStart, { align: 'left' });
            pdf.text(String(item.qty), cols.qty, yStart, { align: 'right' });
            pdf.text(formatRupiah(item.subtotal), cols.subtotal, yStart, { align: 'right' });
            yStart += rowHeight;
        });

        yStart += 6;
        pdf.line(margin, yStart, tableRight, yStart);
        return yStart + 8;
    }

    function drawFooter(pdf, yStart) {
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(10);
        const pageWidth = pdf.internal.pageSize.getWidth();
        const margin = 15;
        const tableRight = pageWidth - margin;
        pdf.text('total keseluruhan =', 15, yStart);
        pdf.setFont('helvetica', 'normal');
        pdf.text(formatRupiah(orderData.total_harga), tableRight, yStart, { align: 'right' });

        yStart += 12;
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(9);
        pdf.text('catatan', 15, yStart);
        pdf.text('nama pegawai', 110, yStart);

        yStart += 4;
        pdf.setLineWidth(0.3);
        pdf.rect(15, yStart, 78, 30);
        pdf.rect(110, yStart, 78, 30);
        yStart += 6;

        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(9);
        const noteLines = pdf.splitTextToSize(orderData.catatan || '-', 72);
        pdf.text(noteLines, 18, yStart);
        pdf.text(String(orderData.pegawai), 113, yStart);
    }

    async function exportKantinPDF() {
        const logoPaths = [
            '/gorjes/logo.png',
            '/gorjes/logo.jpg',
            '/gorjes/logo.jpeg',
            '/gorjes/assets/gorjes_logo.png',
            '/gorjes/assets/gorjes_logo.jpg',
            '/gorjes/assets/gorjes_logo.jpeg',
            '/gorjes/assets/logo.png',
            '/gorjes/assets/logo.jpg',
            '/gorjes/assets/logo.jpeg',
            '../../logo.png',
            '../../logo.jpg',
            '../../logo.jpeg',
            '../../assets/gorjes_logo.png',
            '../../assets/gorjes_logo.jpg',
            '../../assets/gorjes_logo.jpeg',
            '../../assets/logo.png',
            '../../assets/logo.jpg',
            '../../assets/logo.jpeg'
        ];
        let logoData = null;
        for (const path of logoPaths) {
            logoData = await loadImageAsDataURL(path);
            if (logoData) break;
        }

        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');

        drawHeader(pdf, logoData);
        let currentY = 48; // start a bit higher after compact header
        currentY = drawOrderInfo(pdf, currentY);
        currentY = drawItemTable(pdf, currentY);
        drawFooter(pdf, currentY + 6);

        const fileName = 'Pesanan_' + String(orderData.id).replace(/\s+/g, '_') + '_' + new Date().toISOString().slice(0, 10).replace(/-/g, '') + '.pdf';
        const blobUrl = pdf.output('bloburl');
        const opened = window.open(blobUrl, '_blank');
        if (!opened) {
            pdf.save(fileName);
        }
    }

    // Utility: load image URL -> dataURL (returns null on failure)
    function loadImageAsDataURL(url) {
        return fetch(url)
            .then(resp => {
                if (!resp.ok) throw new Error('Failed to fetch image');
                return resp.blob();
            })
            .then(blob => new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onloadend = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsDataURL(blob);
            }))
            .catch(() => null);
    }

    async function exportCateringPDF() {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');
        const pageWidth = pdf.internal.pageSize.getWidth();
        const margin = 15;

        // Try to load logo from common paths (project root assets)
        const possibleLogoPaths = [
            '/gorjes/logo.png',
            '/gorjes/logo.jpg',
            '/gorjes/logo.jpeg',
            '/gorjes/assets/gorjes_logo.png',
            '/gorjes/assets/gorjes_logo.jpg',
            '/gorjes/assets/gorjes_logo.jpeg',
            '/gorjes/assets/logo.png',
            '/gorjes/assets/logo.jpg',
            '/gorjes/assets/logo.jpeg',
            '/assets/gorjes_logo.png',
            '/assets/gorjes_logo.jpg',
            '/assets/gorjes_logo.jpeg',
            '/assets/logo.png',
            '/assets/logo.jpg',
            '/assets/logo.jpeg'
        ];

        let logoData = null;
        for (const p of possibleLogoPaths) {
            // eslint-disable-next-line no-await-in-loop
            logoData = await loadImageAsDataURL(p);
            if (logoData) break;
        }

        drawHeader(pdf, logoData);

        // Order info block (include acara fields)
        let y = 48;
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(10);
        pdf.text('id pesanan =', 15, y);
        pdf.text('nama pelanggan =', 110, y);
        pdf.setFont('helvetica', 'normal');
        pdf.text(String(orderData.id), 45, y);
        pdf.text(orderData.pelanggan, 150, y);

        pdf.setFont('helvetica', 'bold');
        pdf.text('jenis =', 15, y + 8);
        pdf.text('tanggal acara =', 110, y + 8);
        pdf.setFont('helvetica', 'normal');
        pdf.text(orderData.jenis, 40, y + 8);
        pdf.text(orderData.tgl_acara || '-', 150, y + 8);

        pdf.setFont('helvetica', 'bold');
        pdf.text('tanggal =', 15, y + 16);
        pdf.text('lokasi acara =', 110, y + 16);
        pdf.setFont('helvetica', 'normal');
        pdf.text(orderData.tanggal, 44, y + 16);
        pdf.text(orderData.lokasi_acara || '-', 150, y + 16);

        pdf.setFont('helvetica', 'bold');
        pdf.text('status =', 15, y + 24);
        pdf.setFont('helvetica', 'normal');
        pdf.text(orderData.status, 35, y + 24);

        // Items table
        let currentY = y + 40;
        currentY = drawItemTable(pdf, currentY);

        // Footer with total and notes
        drawFooter(pdf, currentY + 4);

        const fileName = 'Pesanan_Catering_' + String(orderData.id).replace(/\s+/g, '_') + '_' + new Date().toISOString().slice(0, 10).replace(/-/g, '') + '.pdf';
        const blobUrl = pdf.output('bloburl');
        const opened = window.open(blobUrl, '_blank');
        if (!opened) {
            pdf.save(fileName);
        }
    }

    async function exportPDF(btn = document.getElementById('btn-export-pdf')) {
        let originalHtml = '';
        if (btn) {
            btn.disabled = true;
            originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        }

        try {
            if (orderData.jenis === 'Kantin') {
                await exportKantinPDF();
                return;
            }
            if (orderData.jenis === 'Catering') {
                await exportCateringPDF();
                return;
            }

            document.querySelectorAll('.no-print').forEach(el => el.style.display = 'none');

            const { jsPDF } = window.jspdf;
            const element = document.querySelector('.pdf-content') || document.body;
            const canvas = await html2canvas(element, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff'
            });

            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF('p', 'mm', 'a4');
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            const imgRatio = canvas.height / canvas.width;
            const imgHeight = pageWidth * imgRatio;

            let posY = 0;
            while (posY < imgHeight) {
                pdf.addImage(imgData, 'PNG', 0, -posY, pageWidth, imgHeight);
                posY += pageHeight;
                if (posY < imgHeight) pdf.addPage();
            }

            const fileName = 'Pesanan_<?= htmlspecialchars($data['id_pesanan']) ?>_' + new Date().toISOString().slice(0, 10).replace(/-/g, '') + '.pdf';
            const blobUrl = pdf.output('bloburl');
            const opened = window.open(blobUrl, '_blank');
            if (!opened) {
                pdf.save(fileName);
            }
        } catch (err) {
            console.error('PDF Export error:', err);
            alert('Gagal mengekspor PDF: ' + err.message);
        } finally {
            document.querySelectorAll('.no-print').forEach(el => el.style.display = '');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    }

    if (shouldAutoExport) {
        window.addEventListener('load', exportPDF);
    }
</script>
<?php include '../../layouts/footer.php';
include '../../layouts/vendor-scripts.php'; ?>