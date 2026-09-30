<?php
include '../../layouts/session.php';
include '../../layouts/config.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ../produk/produklihat.php');
    exit;
}

// Fetch product
$stmt = mysqli_prepare($conn, "SELECT * FROM produk WHERE id_produk = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$produk = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$produk) {
    $_SESSION['flash_error'] = 'Produk tidak ditemukan.';
    header('Location: ../produk/produklihat.php');
    exit;
}

// Fetch BOM for this product (including harga_satuan_bahan)
$bom_q = mysqli_prepare($conn, "
    SELECT bom.*, bb.nama_bahan, bb.satuan, bb.harga_satuan_bahan 
    FROM bom 
    JOIN bahan_baku bb ON bom.id_bahan_baku = bb.id_bahan_baku 
    WHERE bom.id_produk = ? 
    ORDER BY bb.nama_bahan ASC
");
mysqli_stmt_bind_param($bom_q, 'i', $id);
mysqli_stmt_execute($bom_q);
$bom_result = mysqli_stmt_get_result($bom_q);

// Store BOM rows for calculation
$bom_rows = [];
while ($r = mysqli_fetch_assoc($bom_result)) {
    $bom_rows[] = $r;
}
$total_bom = array_sum(array_column($bom_rows, 'subtotal'));

// Fetch all bahan_baku for the add-BOM form
$bahan_all = mysqli_query($conn, "SELECT * FROM bahan_baku ORDER BY nama_bahan ASC");

include '../../layouts/head.php';
include '../../layouts/menu.php';
?>
<!-- Header — ubah div mb-12 menjadi: -->
<div class="mb-12 relative z-10 flex justify-between items-start">
    <div>
        <h2 class="font-serif text-4xl tracking-tight mb-2">Detail Produk</h2>
        <a href="../produk/produklihat.php"
            class="text-xs tracking-[0.2em] uppercase text-luxury-mutedFg hover:text-luxury-accent transition-colors">←
            Kembali ke Daftar Produk</a>
    </div>
    <button onclick="exportPDF()"
        class="h-9 px-4 border border-[#1A1A1A] bg-[#1A1A1A] text-white text-[10px] tracking-[0.15em] uppercase flex items-center gap-2 hover:bg-transparent hover:text-[#1A1A1A] transition-all duration-300 no-print">
        <i class="fas fa-file-pdf"></i> Export PDF
    </button>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="mb-8 p-4 rounded-lg border border-green-200 text-green-800 text-sm bg-green-50/80 relative z-10">
        <?= htmlspecialchars($_SESSION['flash_success']) ?>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="mb-8 p-4 rounded-lg border border-red-200 text-red-800 text-sm bg-red-50/80 relative z-10">
        <?= $_SESSION['flash_error'] ?>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Product Info Card -->
<div class="relative z-10 glass-panel p-8 mb-10 max-w-4xl">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div>
            <p class="text-[10px] tracking-[0.2em] uppercase text-luxury-mutedFg mb-1">Nama Produk</p>
            <p class="text-sm font-medium">
                <?= htmlspecialchars($produk['nama_produk']) ?>
            </p>
        </div>
        <div>
            <p class="text-[10px] tracking-[0.2em] uppercase text-luxury-mutedFg mb-1">Kategori</p>
            <p class="text-sm font-medium">
                <?= htmlspecialchars($produk['kategori'] ?? '-') ?>
            </p>
        </div>
        <div>
            <p class="text-[10px] tracking-[0.2em] uppercase text-luxury-mutedFg mb-1">Harga Jual</p>

            <div id="harga-jual-display" class="flex items-center gap-3">
                <p class="text-sm font-medium text-luxury-accent">Rp
                    <?= number_format($produk['harga_jual'], 0, ',', '.') ?>
                </p>
                <button type="button" onclick="toggleHargaJualEdit()"
                    class="text-luxury-mutedFg hover:text-luxury-accent transition-colors" title="Ubah Harga Jual">
                    <i class="fas fa-pencil-alt text-[10px]"></i>
                </button>
            </div>

            <div id="harga-jual-form" class="hidden mt-1">
                <form action="controllers/update_harga_jual.php" method="POST" class="flex items-center gap-2">
                    <input type="hidden" name="id_produk" value="<?= $produk['id_produk'] ?>">
                    <input type="number" name="harga_jual" min="0" step="0.01"
                        value="<?= htmlspecialchars($produk['harga_jual']) ?>" required
                        class="w-32 text-sm px-2 py-1 h-8 bg-transparent border-b border-[#1A1A1A]/30 focus:outline-none focus:border-luxury-accent transition-colors duration-300">
                    <button type="submit" class="text-luxury-fg hover:text-luxury-accent transition-colors">
                        <i class="fas fa-check text-[10px]"></i>
                    </button>
                    <button type="button" onclick="toggleHargaJualEdit()"
                        class="text-red-500 hover:text-red-700 transition-colors">
                        <i class="fas fa-times text-[10px]"></i>
                    </button>
                </form>
            </div>
        </div>
        <div>
            <p class="text-[10px] tracking-[0.2em] uppercase text-luxury-mutedFg mb-1">Harga Pokok</p>
            <p class="text-sm font-medium">Rp
                <?= number_format($produk['harga_pokok'], 0, ',', '.') ?>
            </p>
        </div>
    </div>
</div>

<!-- BOM Section -->
<div class="relative z-10 max-w-4xl">
    <div class="flex justify-between items-end mb-6">
        <div>
            <h3 class="font-serif text-2xl tracking-tight">Bill of Materials (BOM)</h3>
            <p class="text-xs text-luxury-mutedFg mt-1">Resep bahan baku yang dibutuhkan untuk memproduksi 1 porsi
                produk ini.</p>
        </div>
        <button type="button" id="btn-toggle-form"
            class="h-9 px-5 border border-[#1A1A1A] bg-[#1A1A1A] text-white text-[10px] tracking-[0.2em] uppercase flex items-center gap-2 hover:bg-transparent hover:text-[#1A1A1A] transition-all duration-300">
            <i class="fas fa-plus"></i> Tambah Bahan
        </button>
    </div>

    <!-- Add BOM Form (hidden by default) -->
    <div id="bom-form" class="hidden mb-8 glass-panel p-6 border-l-4 border-luxury-accent">
        <form action="controllers/create.php" method="POST" class="flex flex-col sm:flex-row items-end gap-4">
            <input type="hidden" name="id_produk" value="<?= $produk['id_produk'] ?>">
            <div class="flex-1 w-full">
                <label class="block text-[10px] tracking-[0.2em] uppercase text-luxury-mutedFg mb-2">Bahan Baku</label>
                <select name="id_bahan_baku" required class="w-full glass-input text-sm cursor-pointer">
                    <option value="">-- Pilih Bahan --</option>
                    <?php while ($bb = mysqli_fetch_assoc($bahan_all)): ?>
                        <option value="<?= $bb['id_bahan_baku'] ?>">
                            <?= htmlspecialchars($bb['nama_bahan']) ?> (
                            <?= htmlspecialchars($bb['satuan']) ?>) — Rp
                            <?= number_format($bb['harga_satuan_bahan'], 0, ',', '.') ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="w-full sm:w-36">
                <label class="block text-[10px] tracking-[0.2em] uppercase text-luxury-mutedFg mb-2">Qty / Porsi</label>
                <input type="number" name="qty" min="0.01" step="0.01" required
                    class="w-full glass-input text-sm text-center" placeholder="0.00">
            </div>
            <button type="submit"
                class="h-[42px] px-6 border border-[#1A1A1A] bg-[#1A1A1A] text-white text-[10px] tracking-[0.15em] uppercase hover:bg-transparent hover:text-[#1A1A1A] transition-all duration-300 shrink-0">
                Simpan
            </button>
        </form>
    </div>

    <!-- BOM Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-luxury-fg/10 uppercase text-[10px] tracking-[0.2em] text-luxury-mutedFg">
                    <th class="py-4 px-4 font-normal">#</th>
                    <th class="py-4 px-4 font-normal">Bahan Baku</th>
                    <th class="py-4 px-4 font-normal">Harga / Satuan</th>
                    <th class="py-4 px-4 font-normal">Qty</th>
                    <th class="py-4 px-4 font-normal">Subtotal</th>
                    <th class="py-4 px-4 font-normal text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                foreach ($bom_rows as $bom): ?>
                    <tr class="border-b border-[#1A1A1A]/10 hover:bg-[#F9F8F6] transition-colors duration-300"
                        id="bom-row-<?= $bom['id_bahan_baku'] ?>">
                        <td class="py-4 px-4 font-mono text-xs text-luxury-mutedFg">
                            <?= $no++ ?>
                        </td>
                        <td class="py-4 px-4 font-serif">
                            <?= htmlspecialchars($bom['nama_bahan']) ?>
                        </td>
                        <td class="py-4 px-4 font-mono text-sm text-luxury-mutedFg">Rp
                            <?= number_format($bom['harga_satuan_bahan'], 0, ',', '.') ?> /
                            <?= htmlspecialchars($bom['satuan']) ?>
                        </td>

                        <td class="py-4 px-4 font-mono text-sm text-luxury-mutedFg bom-qty-display">
                            <?= htmlspecialchars($bom['qty']) ?>
                        </td>
                        <td class="py-4 px-4 font-mono text-sm font-medium">Rp
                            <?= number_format($bom['subtotal'], 0, ',', '.') ?>
                        </td>
                        <td class="py-4 px-4 text-right space-x-3">
                            <!-- Inline Edit Form -->

                            <form action="controllers/delete.php" method="POST" class="inline"
                                onsubmit="return confirm('Hapus bahan ini dari resep?')">
                                <input type="hidden" name="id_produk" value="<?= $produk['id_produk'] ?>">
                                <input type="hidden" name="id_bahan_baku" value="<?= $bom['id_bahan_baku'] ?>">
                                <button type="submit"
                                    class="text-[10px] uppercase tracking-widest text-luxury-mutedFg hover:text-red-700 transition-colors">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($bom_rows)): ?>
                    <tr>
                        <td colspan="7" class="py-8 text-center text-luxury-mutedFg text-sm italic">Belum ada bahan baku.
                            Klik "Tambah Bahan" untuk menambahkan resep.</td>
                    </tr>
                <?php else: ?>
                    <!-- Total Row = Harga Pokok -->
                    <tr class="bg-white/30">
                        <td colspan="5"
                            class="py-4 px-4 text-right font-serif font-semibold text-sm uppercase tracking-wider">Total
                            Harga Pokok (BOM)</td>
                        <td class="py-4 px-4 font-serif font-semibold text-lg text-luxury-accent">Rp
                            <?= number_format($total_bom, 0, ',', '.') ?>
                        </td>
                        <td></td>
                    </tr>
                    <?php if (abs($total_bom - $produk['harga_pokok']) > 0.01): ?>
                        <tr>
                            <td colspan="7" class="py-3 px-4">
                                <div
                                    class="flex items-center justify-between p-3 border-l-4 border-amber-500 bg-amber-50/50 text-amber-900 text-xs font-mono">
                                    <span><i class="fas fa-exclamation-triangle mr-2"></i>Total BOM (Rp
                                        <?= number_format($total_bom, 0, ',', '.') ?>) berbeda dengan Harga Pokok produk (Rp
                                        <?= number_format($produk['harga_pokok'], 0, ',', '.') ?>).
                                    </span>
                                    <form action="controllers/sync_harga_pokok.php" method="POST" class="inline">
                                        <input type="hidden" name="id_produk" value="<?= $produk['id_produk'] ?>">
                                        <input type="hidden" name="total_bom" value="<?= $total_bom ?>">
                                        <button type="submit"
                                            class="ml-4 px-4 py-2 border border-amber-700 bg-transparent text-amber-900 text-[10px] tracking-[0.15em] uppercase hover:bg-amber-700 hover:text-white transition-all duration-300">
                                            Sinkronkan
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
    const produkData = {
        id: <?= json_encode($produk['id_produk'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        nama: <?= json_encode($produk['nama_produk'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        kategori: <?= json_encode($produk['kategori'] ?? '-', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        harga_jual: <?= json_encode($produk['harga_jual']) ?>,
        harga_pokok: <?= json_encode($produk['harga_pokok']) ?>,
        total_bom: <?= json_encode($total_bom) ?>
    };

    const bomData = <?= json_encode($bom_rows, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    function formatRupiah(value) {
        return 'Rp ' + Number(value).toLocaleString('id-ID');
    }

    function wrapText(pdf, text, maxWidth) {
        return pdf.splitTextToSize(String(text), maxWidth);
    }

    function drawHeader(pdf, logoData) {
        const pageWidth = pdf.internal.pageSize.getWidth();
        const margin = 15;

        // Company info
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(14);
        pdf.text('kantin gorjes', margin, 18);

        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(8);
        pdf.text('+62 821-1162-2287', margin, 24);
        pdf.text('Jl. Ketapang No.8, RT.2/RW.10, Jati Padang, Ps. Minggu', margin, 30);
        pdf.text('Kota Jakarta Selatan, DKI Jakarta 12540', margin, 36);

        const logoSize = 42;
        const logoX = pageWidth - margin - logoSize;
        const logoY = 8;

        if (logoData) {
            try {
                pdf.addImage(logoData, 'PNG', logoX, logoY, logoSize, logoSize);
                return;
            } catch (e) {
                // fallback to text if image fails
            }
        }

        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(12);
        pdf.text('Gorjes', logoX + logoSize / 2, logoY + 18, { align: 'center' });
        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(7);
        pdf.text('Delicious Home Food', logoX + logoSize / 2, logoY + 25, { align: 'center' });
    }

    function drawProductInfo(pdf, yStart) {
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(10);
        pdf.text('id produk =', 15, yStart);
        pdf.text('kategori =', 110, yStart);
        pdf.setFont('helvetica', 'normal');
        pdf.text(String(produkData.id), 42, yStart);
        pdf.text(String(produkData.kategori), 140, yStart);

        pdf.setFont('helvetica', 'bold');
        pdf.text('nama produk =', 15, yStart + 8);
        pdf.text('harga jual =', 110, yStart + 8);
        pdf.setFont('helvetica', 'normal');
        pdf.text(String(produkData.nama), 47, yStart + 8);
        pdf.text(formatRupiah(produkData.harga_jual), 143, yStart + 8);

        return yStart + 18;
    }

    function drawBomTable(pdf, yStart, rows) {
        const pageWidth = pdf.internal.pageSize.getWidth();
        const margin = 15;
        const tableRight = pageWidth - margin;
        const rowHeight = 7;
        const cols = {
            no: margin + 2,
            bahan: margin + 15,
            harga: margin + 85,
            qty: tableRight - 45,
            subtotal: tableRight - 2
        };

        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(12);
        pdf.text('bill of material', pageWidth / 2, yStart, { align: 'center' });
        yStart += 6;
        pdf.setDrawColor(0, 0, 0);
        pdf.setLineWidth(0.4);
        pdf.line(margin, yStart, tableRight, yStart);
        yStart += 8;

        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(9);
        pdf.text('no', cols.no, yStart);
        pdf.text('bahan baku', cols.bahan, yStart);
        pdf.text('harga / satuan', cols.harga, yStart);
        pdf.text('qty', cols.qty, yStart, { align: 'right' });
        pdf.text('subtotal', cols.subtotal, yStart, { align: 'right' });
        yStart += 3;
        pdf.line(margin, yStart, tableRight, yStart);
        yStart += 5;

        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(9);

        rows.forEach((row, index) => {
            const nameLines = wrapText(pdf, row.nama_bahan, cols.qty - cols.harga - 8);
            const cellHeight = Math.max(1, nameLines.length) * rowHeight;

            if (yStart + cellHeight > pdf.internal.pageSize.getHeight() - 25) {
                pdf.addPage();
                yStart = 20;
            }

            pdf.text(String(index + 1), cols.no, yStart);
            pdf.text(nameLines, cols.bahan, yStart);
            pdf.text(formatRupiah(row.harga_satuan_bahan) + ' / ' + row.satuan, cols.harga, yStart);
            pdf.text(String(row.qty), cols.qty, yStart, { align: 'right' });
            pdf.text(formatRupiah(row.subtotal), cols.subtotal, yStart, { align: 'right' });
            yStart += cellHeight;
        });

        pdf.line(margin, yStart, tableRight, yStart);
        yStart += 8;

        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(10);
        pdf.text('harga pokok = ' + formatRupiah(produkData.total_bom), margin, yStart);

        return yStart;
    }

    async function loadLogoDataUrl() {
        const paths = [
            '/gorjes/logo.jpg',
            '/gorjes/logo.jpeg',
            '/gorjes/logo.png',
            '/gorjes/assets/gorjes_logo.png',
            '/gorjes/assets/gorjes_logo.jpg',
            '/gorjes/assets/gorjes_logo.jpeg',
            '/gorjes/assets/logo.png',
            '/gorjes/assets/logo.jpg',
            '/gorjes/assets/logo.jpeg',
            '../../logo.jpg',
            '../../logo.jpeg',
            '../../logo.png',
            '../../assets/gorjes_logo.png',
            '../../assets/gorjes_logo.jpg',
            '../../assets/gorjes_logo.jpeg',
            '../../assets/logo.png',
            '../../assets/logo.jpg',
            '../../assets/logo.jpeg'
        ];

        for (const path of paths) {
            try {
                const response = await fetch(path);
                if (!response.ok) continue;
                const blob = await response.blob();
                const dataUrl = await new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onloadend = () => resolve(reader.result);
                    reader.onerror = reject;
                    reader.readAsDataURL(blob);
                });
                return dataUrl;
            } catch (err) {
                continue;
            }
        }
        return null;
    }

    async function exportPDF() {
        const logoData = await loadLogoDataUrl();
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');

        drawHeader(pdf, logoData);
        let currentY = 50;
        currentY = drawProductInfo(pdf, currentY);
        currentY = drawBomTable(pdf, currentY + 6, bomData);

        // Preview PDF
        window.open(pdf.output('bloburl'), '_blank');
    }

    // Toggle add-BOM form
    document.getElementById('btn-toggle-form').addEventListener('click', function () {
        const form = document.getElementById('bom-form');
        form.classList.toggle('hidden');
        if (!form.classList.contains('hidden')) {
            form.querySelector('select').focus();
        }
    });

    // Inline edit toggle
    function toggleEdit(btn, bahanId) {
        const row = document.getElementById('bom-row-' + bahanId);
        row.querySelector('.bom-qty-display').classList.add('hidden');
        row.querySelector('.bom-edit-input').classList.remove('hidden');
        row.querySelector('.bom-edit-btn').classList.add('hidden');
        row.querySelector('.bom-save-btn').classList.remove('hidden');
        row.querySelector('.bom-cancel-btn').classList.remove('hidden');
        row.querySelector('.bom-edit-input').focus();
    }

    function cancelEdit(bahanId) {
        const row = document.getElementById('bom-row-' + bahanId);
        row.querySelector('.bom-qty-display').classList.remove('hidden');
        row.querySelector('.bom-edit-input').classList.add('hidden');
        row.querySelector('.bom-edit-btn').classList.remove('hidden');
        row.querySelector('.bom-save-btn').classList.add('hidden');
        row.querySelector('.bom-cancel-btn').classList.add('hidden');
    }

    // Toggle Harga Jual edit
    function toggleHargaJualEdit() {
        const display = document.getElementById('harga-jual-display');
        const form = document.getElementById('harga-jual-form');
        display.classList.toggle('hidden');
        form.classList.toggle('hidden');
        if (!form.classList.contains('hidden')) {
            form.querySelector('input[name="harga_jual"]').focus();
        }
    }

    const shouldAutoExport = <?= isset($_GET['export']) && $_GET['export'] === '1' ? 'true' : 'false' ?>;
    if (shouldAutoExport) {
        window.addEventListener('load', exportPDF);
    }
</script>
<?php include '../../layouts/footer.php';
include '../../layouts/vendor-scripts.php'; ?>