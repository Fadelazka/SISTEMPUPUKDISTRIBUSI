<?php
require_once __DIR__ . '/bps_widget.php';
$domain  = bps_active_domain();
$wilayah = bps_active_wilayah();
$where = filter_where($koneksi);

// Logika database tetap sama seperti aslinya
$qPetani = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM petani WHERE $where");
$totalPetani = mysqli_fetch_assoc($qPetani)['total'];
$qDist = mysqli_query($koneksi, "SELECT jumlah FROM distribusi WHERE $where");
$totalKg = 0;
while ($r = mysqli_fetch_assoc($qDist)) { $totalKg += intval(preg_replace('/[^0-9]/', '', $r['jumlah'])); }
$targetTon = 68; $targetKg = $targetTon * 1000;
$persen = ($targetKg > 0) ? round(($totalKg / $targetKg) * 100, 1) : 0;

// Grafik
$qChart = mysqli_query($koneksi, "SELECT pupuk, SUM(CAST(REPLACE(REPLACE(jumlah, ',', ''), ' ', '') AS UNSIGNED)) as total FROM distribusi WHERE $where GROUP BY pupuk");
$chartLabels = []; $chartValues = [];
while ($row = mysqli_fetch_assoc($qChart)) { $chartLabels[] = $row['pupuk']; $chartValues[] = (int)$row['total']; }
if (empty($chartLabels)) { $chartLabels = ['Belum ada data']; $chartValues = [0]; }

// Data BPS
$bpsInd = bps_fetch('list/', ['model'=>'indicators','domain'=>$domain,'lang'=>'ind']);
$indList = !empty($bpsInd['data'][1]) ? array_slice($bpsInd['data'][1], 0, 4) : [];
?>

<?= bps_wilayah_badge() ?>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
        <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-1">Total Petani</p>
        <h3 class="text-2xl font-black text-primary"><?= number_format($totalPetani, 0, ',', '.') ?></h3>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
        <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-1">Pupuk Tersalur</p>
        <h3 class="text-2xl font-black text-primary"><?= number_format($totalKg, 0, ',', '.') ?> <span class="text-xs font-normal text-slate-400">kg</span></h3>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
        <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-1">Realisasi</p>
        <h3 class="text-2xl font-black text-amber-500"><?= $persen ?>%</h3>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
        <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-1">Target</p>
        <h3 class="text-2xl font-black text-primary"><?= $targetTon ?> <span class="text-xs font-normal text-slate-400">ton</span></h3>
    </div>
</div>

<div class="mb-6 flex items-center gap-2">
    <span class="bg-primary text-secondary text-[10px] font-black px-2.5 py-1 rounded-full">BPS</span>
    <h3 class="font-bold text-slate-800">Indikator Strategis — <?= htmlspecialchars($wilayah) ?></h3>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <?php if(!empty($indList)): foreach ($indList as $ind): ?>
    <div class="bg-white p-5 rounded-2xl shadow-sm border-t-4 border-secondary">
        <p class="text-xs text-slate-500 font-medium leading-tight mb-2"><?= htmlspecialchars($ind['title']??'-') ?></p>
        <h4 class="text-xl font-bold text-primary"><?= htmlspecialchars($ind['value']??'-') ?></h4>
        <p class="text-[10px] text-slate-400 mt-1"><?= htmlspecialchars($ind['unit']??'') ?></p>
    </div>
    <?php endforeach; else: ?>
    <div class="col-span-full bg-slate-100 p-4 rounded-xl text-center text-slate-500 text-sm italic">Indikator tidak tersedia.</div>
    <?php endif; ?>
</div>

<div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 mb-8">
    <canvas id="berandaChart" height="150"></canvas>
</div>

<div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 mb-8">
    <canvas id="berandaChart" height="150"></canvas>
</div>

<div class="mt-8">
    <div class="flex items-center gap-2 mb-4">
        <i class="fas fa-newspaper text-primary text-xl"></i>
        <h3 class="font-bold text-slate-800 text-lg">Warta Subsidi Pupuk Indonesia</h3>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 hover:border-primary transition-all">
            <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-1 rounded-md font-bold">INFO BPS</span>
            <h4 class="font-bold text-slate-800 mt-2 text-sm">Tren Konsumsi Pupuk Subsidi Nasional 2026</h4>
            <p class="text-xs text-slate-500 mt-1 leading-relaxed">BPS mencatat efisiensi distribusi meningkat 12% tahun ini berkat integrasi data NIK petani yang lebih akurat.</p>
            <a href="#" class="text-primary text-[10px] font-bold mt-3 inline-block hover:underline">Baca Selengkapnya →</a>
        </div>

        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 hover:border-primary transition-all">
            <span class="text-[10px] bg-amber-100 text-amber-700 px-2 py-1 rounded-md font-bold">KEMENKEU</span>
            <h4 class="font-bold text-slate-800 mt-2 text-sm">Alokasi Tambahan Subsidi Pupuk Rp 14 Triliun</h4>
            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Pemerintah memastikan ketersediaan pupuk urea dan NPK aman hingga musim tanam kedua di seluruh wilayah Indonesia.</p>
            <a href="#" class="text-primary text-[10px] font-bold mt-3 inline-block hover:underline">Baca Selengkapnya →</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script> <script>
(function() {
    const ctx = document.getElementById('berandaChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [{
                label: 'Total Distribusi (kg)',
                data: <?= json_encode($chartValues) ?>,
                backgroundColor: '#2d6a4f',
                borderRadius: 10,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                datalabels: { // Ini yang bikin muncul angka di atas diagram
                    anchor: 'end',
                    align: 'top',
                    formatter: (value) => value.toLocaleString('id-ID') + ' kg',
                    font: { weight: 'bold' }
                }
            },
            scales: {
                y: { beginAtZero: true, grid: { display: false } },
                x: { grid: { display: false } }
            }
        },
        plugins: [ChartDataLabels]
    });
})();
</script>