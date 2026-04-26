<?php
require_once __DIR__ . '/bps_widget.php';
$domain  = bps_active_domain();
$wilayah = bps_active_wilayah();
$isAdmin = ($_SESSION['role']=='admin');

$where = filter_where($koneksi);
$queryChart = "SELECT pupuk, SUM(CAST(REPLACE(REPLACE(jumlah, ',', ''), ' ', '') AS UNSIGNED)) as total FROM distribusi WHERE $where GROUP BY pupuk";
$resultChart = mysqli_query($koneksi, $queryChart);
$chartLabels = []; $chartValues = []; $totalRealisasiKg = 0;
while ($row = mysqli_fetch_assoc($resultChart)) {
    $chartLabels[] = $row['pupuk'];
    $val = (int)$row['total'];
    $chartValues[] = $val;
    $totalRealisasiKg += $val;
}
if (empty($chartLabels)) { $chartLabels = ['Belum Ada Data']; $chartValues = [0]; }

$targetTon = 68; $targetKg = $targetTon * 1000;
$persentase = ($targetKg > 0) ? round(($totalRealisasiKg / $targetKg) * 100, 1) : 0;

// --- TAMBAHAN: GRAFIK PER PROVINSI (dari file kedua) ---
$qProv = mysqli_query($koneksi,
    "SELECT provinsi,
            SUM(CAST(REPLACE(REPLACE(jumlah,',',''),' ','') AS UNSIGNED)) as total
     FROM distribusi
     WHERE provinsi IS NOT NULL AND provinsi != ''
     GROUP BY provinsi
     ORDER BY total DESC
     LIMIT 15");
$provLabels=[]; $provValues=[];
while($r=mysqli_fetch_assoc($qProv)){
    $provLabels[]=$r['provinsi'];
    $provValues[]=(int)$r['total'];
}
$hasProvData = !empty($provLabels);

// --- TAMBAHAN: GRAFIK TREND BULANAN (dari file kedua) ---
$qBulan = mysqli_query($koneksi,
    "SELECT DATE_FORMAT(tgl,'%Y-%m') as bulan,
            DATE_FORMAT(tgl,'%b %Y') as label_bulan,
            SUM(CAST(REPLACE(REPLACE(jumlah,',',''),' ','') AS UNSIGNED)) as total
     FROM distribusi
     WHERE $where AND tgl IS NOT NULL
     GROUP BY DATE_FORMAT(tgl,'%Y-%m')
     ORDER BY bulan ASC
     LIMIT 12");
$bulanLabels=[]; $bulanValues=[];
while($r=mysqli_fetch_assoc($qBulan)){
    $bulanLabels[]=$r['label_bulan'];
    $bulanValues[]=(int)$r['total'];
}
$hasBulanData=!empty($bulanLabels);
// --- AKHIR TAMBAHAN ---

$queryLaporan = mysqli_query($koneksi, "SELECT * FROM laporan ORDER BY created_at DESC");
$laporan = [];
while($r = mysqli_fetch_assoc($queryLaporan)) $laporan[] = $r;

// BPS API
$bpsTbl = bps_fetch('list/',['model'=>'statictable','domain'=>$domain,'lang'=>'ind','keyword'=>'produksi','page'=>1]);
$tblList = !empty($bpsTbl['data'][1]) ? array_slice($bpsTbl['data'][1],0,4) : [];
if(empty($tblList)){
    $bpsTbl2 = bps_fetch('list/',['model'=>'statictable','domain'=>'0000','lang'=>'ind','keyword'=>'produksi padi','page'=>1]);
    $tblList = !empty($bpsTbl2['data'][1]) ? array_slice($bpsTbl2['data'][1],0,4) : [];
}
$bpsBrs = bps_fetch('list/',['model'=>'pressrelease','domain'=>$domain,'lang'=>'ind','keyword'=>'produksi','page'=>1]);
$brsList = !empty($bpsBrs['data'][1]) ? array_slice($bpsBrs['data'][1],0,3) : [];
if(empty($brsList)){
    $bpsBrs2 = bps_fetch('list/',['model'=>'pressrelease','domain'=>'0000','lang'=>'ind','keyword'=>'produksi','page'=>1]);
    $brsList = !empty($bpsBrs2['data'][1]) ? array_slice($bpsBrs2['data'][1],0,3) : [];
}
$bpsInf = bps_fetch('list/',['model'=>'infographic','domain'=>'0000','lang'=>'ind','page'=>1,'keyword'=>'pupuk']);
$infList = !empty($bpsInf['data'][1]) ? array_slice($bpsInf['data'][1],0,2) : [];
?>

<?= bps_wilayah_badge() ?>

<?php if($isAdmin): ?>
<div style="margin-bottom:18px;text-align:right;">
    <button id="tambahLaporanBtn" class="btn-admin"><i class="fas fa-plus"></i> Tambah Laporan</button>
</div>
<?php endif; ?>

<!-- GRAFIK 1: Realisasi per Jenis Pupuk (sudah ada) -->
<div style="background:white;padding:20px;border-radius:24px;margin-bottom:24px;">
    <h3 style="margin-bottom:16px; font-size:20px;">📊 Grafik Realisasi Pupuk Subsidi — <?= htmlspecialchars($wilayah) ?></h3>
    <canvas id="reportChart" height="160"></canvas>
</div>

<!-- TAMBAHAN: GRAFIK 2 - PERBANDINGAN ANTAR PROVINSI -->
<?php if($hasProvData): ?>
<div style="background:white;padding:24px;border-radius:24px;margin-bottom:24px;box-shadow:0 4px 16px rgba(0,0,0,0.05);">
    <div style="margin-bottom:16px;">
        <h3 style="font-size:20px;color:#1e4a3b;font-weight:800;margin:0;">🗺️ Perbandingan Realisasi Antar Provinsi</h3>
        <div style="font-size:14px;color:#64748b;margin-top:4px;">
            Total distribusi pupuk subsidi dari semua provinsi (data keseluruhan).
            <?php if(filter_prov()): ?>
            <span style="background:#fef3c7;color:#b45309;padding:2px 10px;border-radius:20px;font-size:13px;margin-left:6px;">
                🔍 Provinsi aktif: <?= htmlspecialchars(filter_prov()) ?>
            </span>
            <?php endif; ?>
        </div>
    </div>
    <canvas id="provChart" height="200"></canvas>
</div>
<?php endif; ?>

<!-- TAMBAHAN: GRAFIK 3 - TREND BULANAN -->
<?php if($hasBulanData && count($bulanLabels)>1): ?>
<div style="background:white;padding:24px;border-radius:24px;margin-bottom:24px;box-shadow:0 4px 16px rgba(0,0,0,0.05);">
    <div style="margin-bottom:16px;">
        <h3 style="font-size:20px;color:#1e4a3b;font-weight:800;margin:0;">📈 Trend Distribusi Bulanan</h3>
        <div style="font-size:14px;color:#64748b;margin-top:4px;">
            Perkembangan distribusi pupuk dari waktu ke waktu untuk wilayah: <strong><?= htmlspecialchars($wilayah) ?></strong>
        </div>
    </div>
    <canvas id="trendChart" height="160"></canvas>
</div>
<?php endif; ?>

<!-- Stat Cards (ringkasan) -->
<div class="stats-grid" style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:24px;">
    <div class="stat-card" style="flex:1;text-align:center;"><div class="stat-title">Target (nasional)</div><div class="stat-number" style="font-size:32px;"><?= $targetTon ?> ton</div></div>
    <div class="stat-card" style="flex:1;text-align:center;"><div class="stat-title">Total Realisasi</div><div class="stat-number" style="font-size:32px;color:#2d6a4f;"><?= number_format($totalRealisasiKg/1000, 1) ?> ton</div><div style="font-size:13px;">(<?= number_format($totalRealisasiKg,0,',','.') ?> kg)</div></div>
    <div class="stat-card" style="flex:1;text-align:center;"><div class="stat-title">Persentase</div><div class="stat-number" style="font-size:32px;color:#f59e0b;"><?= $persentase ?>%</div></div>
</div>

<!-- BPS: Tabel Produksi -->
<?php if(!empty($tblList)): ?>
<div style="display:flex;align-items:center;gap:8px;margin:0 0 12px;">
    <span style="background:#1e4a3b;color:#f5e7a4;font-size:10px;font-weight:800;padding:2px 10px;border-radius:20px;">BPS</span>
    <h3 style="font-size:18px;color:#1e4a3b;margin:0;">Tabel Produksi BPS — <?= htmlspecialchars($wilayah) ?></h3>
</div>
<div style="display:flex;flex-direction:column;gap:10px;margin-bottom:24px;">
    <?php foreach($tblList as $tbl): ?>
    <div class="info-box" style="border-left:5px solid #2d6a4f;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;">
        <div><div style="font-weight:700;font-size:15px;"><?= htmlspecialchars($tbl['title']??'-') ?></div><div style="font-size:13px;"><?= htmlspecialchars($tbl['subj']??'-') ?> · <?= !empty($tbl['updt_date'])?date('d M Y',strtotime($tbl['updt_date'])):'-' ?></div></div>
        <?php if(!empty($tbl['excel'])): ?><a href="<?= htmlspecialchars($tbl['excel']) ?>" target="_blank" class="btn-admin" style="padding:5px 14px;"><i class="fas fa-file-excel"></i> Excel</a><?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- BPS: Siaran Pers -->
<?php if(!empty($brsList)): ?>
<div style="display:flex;align-items:center;gap:8px;margin:0 0 12px;">
    <span style="background:#1e4a3b;color:#f5e7a4;font-size:10px;font-weight:800;padding:2px 10px;border-radius:20px;">BPS</span>
    <h3 style="font-size:18px;color:#1e4a3b;margin:0;">Siaran Pers BPS — <?= htmlspecialchars($wilayah) ?></h3>
</div>
<div style="display:flex;flex-direction:column;gap:10px;margin-bottom:24px;">
    <?php foreach($brsList as $b): ?>
    <div class="info-box" style="border-left:5px solid #f5e7a4;"><div style="font-weight:700;font-size:15px;"><?= htmlspecialchars($b['title']??'-') ?></div><div style="font-size:13px;">📅 <?= !empty($b['rl_date'])?date('d M Y',strtotime($b['rl_date'])):'-' ?><?php if(!empty($b['pdf'])): ?> · <a href="<?= htmlspecialchars($b['pdf']) ?>" target="_blank">📥 PDF</a><?php endif; ?></div></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- BPS: Infografis -->
<?php if(!empty($infList)): ?>
<div style="display:flex;align-items:center;gap:8px;margin:0 0 12px;">
    <span style="background:#1e4a3b;color:#f5e7a4;font-size:10px;font-weight:800;padding:2px 10px;border-radius:20px;">BPS</span>
    <h3 style="font-size:18px;color:#1e4a3b;margin:0;">🖼️ Infografis BPS</h3>
</div>
<div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:24px;">
    <?php foreach($infList as $inf): ?>
    <div style="flex:1;min-width:200px;background:white;border-radius:18px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
        <?php if(!empty($inf['img'])): ?><img src="<?= htmlspecialchars($inf['img']) ?>" style="width:100%;height:140px;object-fit:cover;"><?php endif; ?>
        <div style="padding:12px;"><div style="font-size:14px;font-weight:700;"><?= htmlspecialchars($inf['title']??'-') ?></div><?php if(!empty($inf['dl'])): ?><a href="<?= htmlspecialchars($inf['dl']) ?>" target="_blank" style="font-size:12px;">📥 Unduh</a><?php endif; ?></div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Laporan Tertulis -->
<h3 style="margin:10px 0 16px; font-size:20px;">📄 Laporan Tertulis (Lokal)</h3>
<?php if(empty($laporan)): ?>
<div class="info-box" style="text-align:center;">Belum ada laporan. Klik "Tambah Laporan" untuk membuat baru.</div>
<?php else: ?>
    <?php foreach($laporan as $lap): ?>
    <div class="info-box" style="margin-top:14px;">
        <strong style="font-size:16px;"><?= htmlspecialchars($lap['judul']) ?></strong><br>
        <small><?= date('d/m/Y H:i',strtotime($lap['created_at'])) ?></small>
        <p style="margin-top:8px; font-size:14px;"><?= nl2br(htmlspecialchars($lap['deskripsi'])) ?></p>
        <?php if($isAdmin): ?>
        <div style="margin-top:10px;">
            <button class="btn-sm btn-edit-laporan" data-id="<?= $lap['id'] ?>"><i class="fas fa-edit"></i> Edit</button>
            <button class="btn-sm btn-hapus-laporan" data-id="<?= $lap['id'] ?>" style="background:#fee2e2;"><i class="fas fa-trash"></i> Hapus</button>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- SCRIPT CHART (digabung dari kedua file) -->
<script>
(function(){
    if(typeof Chart === 'undefined') return;

    // Chart 1: Realisasi per Jenis Pupuk (dari file pertama)
    var canvas1 = document.getElementById('reportChart');
    if(canvas1){
        var labels1 = <?= json_encode($chartLabels) ?>;
        var values1 = <?= json_encode($chartValues) ?>;
        new Chart(canvas1.getContext('2d'), {
            type: 'bar',
            data: { labels: labels1, datasets: [{ label: 'Penyaluran (kg)', data: values1, backgroundColor: '#f5e7a4', borderRadius: 12, borderSkipped: false }] },
            options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'top' }, tooltip: { callbacks: { label: function(ctx) { return ctx.dataset.label + ': ' + ctx.raw.toLocaleString('id-ID') + ' kg'; } } } }, scales: { y: { beginAtZero: true, title: { display: true, text: 'Kilogram (kg)' } }, x: { title: { display: true, text: 'Jenis Pupuk' } } } }
        });
    }

    // Chart 2: Perbandingan Provinsi (tambahan)
    var canvas2 = document.getElementById('provChart');
    if(canvas2){
        var pLabels = <?= json_encode($provLabels) ?>;
        var pValues = <?= json_encode($provValues) ?>;
        var activeProv = <?= json_encode(filter_prov()) ?>;
        var pColors = pLabels.map(function(l){ return l === activeProv ? '#2d6a4f' : '#b7dfc8'; });
        new Chart(canvas2.getContext('2d'), {
            type: 'bar',
            data: { labels: pLabels, datasets: [{ label: 'Total Distribusi (kg)', data: pValues, backgroundColor: pColors, borderRadius: 10, borderSkipped: false }] },
            options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(ctx) { return ctx.raw.toLocaleString('id-ID') + ' kg'; } } } }, scales: { x: { beginAtZero: true, ticks: { callback: function(v){ return (v/1000).toFixed(0) + ' ton'; } } }, y: { grid: { display: false } } } }
        });
    }

    // Chart 3: Trend Bulanan (tambahan)
    var canvas3 = document.getElementById('trendChart');
    if(canvas3){
        var bLabels = <?= json_encode($bulanLabels) ?>;
        var bValues = <?= json_encode($bulanValues) ?>;
        new Chart(canvas3.getContext('2d'), {
            type: 'line',
            data: { labels: bLabels, datasets: [{ label: 'Distribusi (kg)', data: bValues, borderColor: '#2d6a4f', backgroundColor: 'rgba(45,106,79,0.08)', fill: true, tension: 0.4, pointBackgroundColor: '#2d6a4f', pointRadius: 5 }] },
            options: { responsive: true, plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(ctx) { return ctx.raw.toLocaleString('id-ID') + ' kg'; } } } }, scales: { y: { beginAtZero: true, ticks: { callback: function(v){ return (v/1000).toFixed(1) + ' ton'; } } }, x: { grid: { display: false } } } }
        });
    }
})();
</script>

<div class="info-box">
    <i class="fas fa-chart-simple"></i>
    Data realisasi dihitung dari tabel distribusi sesuai filter wilayah aktif.<br>
    <strong>Filter saat ini:</strong> <?= has_filter() ? htmlspecialchars(implode(' › ', array_filter([filter_prov(), filter_kota(), filter_kec()]))) : 'Semua Wilayah' ?><br>
    <strong>Total realisasi:</strong> <?= number_format($totalRealisasiKg,0,',','.') ?> kg (<?= number_format($totalRealisasiKg/1000,2) ?> ton)<br>
    <strong>BPS Domain:</strong> <?= htmlspecialchars($domain) ?> (<?= htmlspecialchars($wilayah) ?>) &nbsp;|&nbsp; Update: <?= date('d F Y') ?>
</div>