<?php
require_once __DIR__ . '/bps_widget.php';
$domain  = bps_active_domain();
$wilayah = bps_active_wilayah();

// Filter wilayah untuk database
$where = filter_where($koneksi);

// Total Petani (filtered)
$qPetani = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM petani WHERE $where");
$totalPetani = mysqli_fetch_assoc($qPetani)['total'];

// Total Pupuk Tersalur (kg) dari distribusi (filtered)
$qDist = mysqli_query($koneksi, "SELECT jumlah FROM distribusi WHERE $where");
$totalKg = 0;
while ($r = mysqli_fetch_assoc($qDist)) {
    $totalKg += intval(preg_replace('/[^0-9]/', '', $r['jumlah']));
}
$targetTon = 68; // target nasional contoh
$targetKg   = $targetTon * 1000;
$persen     = ($targetKg > 0) ? round(($totalKg / $targetKg) * 100, 1) : 0;

// Grafik per jenis pupuk (filtered)
$qChart = mysqli_query($koneksi, "
    SELECT pupuk, SUM(CAST(REPLACE(REPLACE(jumlah, ',', ''), ' ', '') AS UNSIGNED)) as total
    FROM distribusi
    WHERE $where
    GROUP BY pupuk
");
$chartLabels = []; $chartValues = [];
while ($row = mysqli_fetch_assoc($qChart)) {
    $chartLabels[] = $row['pupuk'];
    $chartValues[] = (int)$row['total'];
}
if (empty($chartLabels)) { $chartLabels = ['Belum ada data']; $chartValues = [0]; }

// Data realisasi per kecamatan (filtered)
$qKec = mysqli_query($koneksi, "
    SELECT kecamatan, SUM(CAST(REPLACE(REPLACE(jumlah, ',', ''), ' ', '') AS UNSIGNED)) as terealisasi
    FROM distribusi
    WHERE $where
    GROUP BY kecamatan
    ORDER BY terealisasi DESC
");
$kecData = [];
while ($row = mysqli_fetch_assoc($qKec)) {
    $kecData[$row['kecamatan']]['ter'] = $row['terealisasi'];
}
if (!empty($kecData)) {
    $kecNames = array_keys($kecData);
    $kecIn = "'" . implode("','", array_map(function($v) use ($koneksi) { return mysqli_real_escape_string($koneksi, $v); }, $kecNames)) . "'";
    $qAlok = mysqli_query($koneksi, "
        SELECT kecamatan, SUM(alokasi) as total_alokasi
        FROM petani
        WHERE kecamatan IN ($kecIn) AND $where
        GROUP BY kecamatan
    ");
    while ($row = mysqli_fetch_assoc($qAlok)) {
        $kecData[$row['kecamatan']]['alok'] = (int)$row['total_alokasi'];
    }
}
$tabelKec = [];
foreach ($kecData as $kec => $d) {
    $alok = isset($d['alok']) ? $d['alok'] : 0;
    $ter  = $d['ter'];
    $persenKec = ($alok > 0) ? round(($ter / $alok) * 100, 1) : 0;
    $status = ($persenKec >= 100) ? 'Selesai' : (($persenKec > 0) ? 'Progres' : 'Belum');
    $tabelKec[] = [
        'kecamatan' => $kec,
        'alokasi'   => number_format($alok, 0, ',', '.'),
        'terealisasi'=> number_format($ter, 0, ',', '.'),
        'persen'     => $persenKec,
        'status'     => $status
    ];
}

// BPS API (indikator, siaran pers, infografis) tetap menggunakan domain aktif
$bpsInd = bps_fetch('list/', ['model'=>'indicators','domain'=>$domain,'lang'=>'ind']);
$indList = !empty($bpsInd['data'][1]) ? array_slice($bpsInd['data'][1], 0, 4) : [];
$bpsBrs = bps_fetch('list/', ['model'=>'pressrelease','domain'=>$domain,'lang'=>'ind','page'=>1]);
$brsList = !empty($bpsBrs['data'][1]) ? array_slice($bpsBrs['data'][1], 0, 3) : [];
$bpsInf = bps_fetch('list/', ['model'=>'infographic','domain'=>'0000','lang'=>'ind','page'=>1,'keyword'=>'pertanian']);
$infList = !empty($bpsInf['data'][1]) ? array_slice($bpsInf['data'][1], 0, 2) : [];
?>

<?= bps_wilayah_badge() ?>

<?php if (has_filter()): ?>
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:14px;padding:12px 18px;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
    <i class="fas fa-check-circle" style="color:#16a34a;"></i>
    <div style="font-size:14px;color:#166534;">
        Data difilter berdasarkan wilayah:
        <strong><?= htmlspecialchars(implode(' › ', array_filter([filter_prov(), filter_kota(), filter_kec()]))) ?></strong>
        — Semua angka di bawah hanya menghitung data di wilayah tersebut.
    </div>
</div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-title">Total Petani Terdaftar</div>
        <div class="stat-number"><?= number_format($totalPetani, 0, ',', '.') ?></div>
        <div>sistem lokal (wilayah aktif)</div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Pupuk Tersalur</div>
        <div class="stat-number"><?= number_format($totalKg, 0, ',', '.') ?> kg</div>
        <div>Target <?= $targetTon ?> ton</div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Realisasi Subsidi</div>
        <div class="stat-number"><?= $persen ?>%</div>
        <div>dari target nasional</div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Distribusi Aktif</div>
        <div class="stat-number"><?= count($tabelKec) ?></div>
        <div>kecamatan terlayani</div>
    </div>
</div>

<!-- Indikator BPS -->
<div style="display:flex;align-items:center;gap:8px;margin:24px 0 12px;">
    <span style="background:#1e4a3b;color:#f5e7a4;font-size:10px;font-weight:800;padding:2px 10px;border-radius:20px;">BPS</span>
    <h3 style="font-size:18px;color:#1e4a3b;margin:0;">Indikator Strategis — <?= htmlspecialchars($wilayah) ?></h3>
</div>
<?php if (!empty($indList)): ?>
<div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:24px;">
    <?php foreach ($indList as $ind): ?>
    <div class="stat-card" style="border-top:4px solid #f5e7a4;">
        <div style="font-size:13px;color:#64748b;margin-bottom:6px;"><?= htmlspecialchars($ind['title']??'-') ?></div>
        <div class="stat-number" style="font-size:28px;"><?= htmlspecialchars($ind['value']??'-') ?></div>
        <div style="font-size:12px;color:#94a3b8;"><?= htmlspecialchars($ind['unit']??'') ?></div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="info-box" style="margin-bottom:20px;">
    <?= $domain==='0000' ? '⚠️ Indikator strategis BPS tidak dapat dimuat.' : 'ℹ️ Indikator strategis untuk wilayah <strong>'.htmlspecialchars($wilayah).'</strong> tidak tersedia.' ?>
</div>
<?php endif; ?>

<!-- Tabel realisasi per kecamatan (dinamis) -->
<h3 style="margin:20px 0 14px; font-size:20px;">📋 Realisasi per Kecamatan (Wilayah Aktif)</h3>
<?php if (empty($tabelKec)): ?>
    <div class="info-box">Tidak ada data distribusi atau petani di wilayah yang dipilih.</div>
<?php else: ?>
    <div style="overflow-x:auto;">
        <table>
            <thead><tr><th>Kecamatan</th><th>Alokasi (kg)</th><th>Terealisasi (kg)</th><th>Persentase</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($tabelKec as $k): ?>
                <tr>
                    <td><?= htmlspecialchars($k['kecamatan']) ?></td>
                    <td><?= $k['alokasi'] ?></td>
                    <td><?= $k['terealisasi'] ?></td>
                    <td><?= $k['persen'] ?>%</td>
                    <td><span class="badge"><?= $k['status'] ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Grafik penyaluran per jenis pupuk -->
<div style="margin-top:24px;background:white;padding:20px;border-radius:24px;box-shadow:0 4px 12px rgba(0,0,0,0.04);">
    <canvas id="berandaChart" height="150"></canvas>
</div>
<script>
(function() {
    if (typeof Chart === 'undefined') return;
    var canvas = document.getElementById('berandaChart');
    if (!canvas) return;
    var labels = <?= json_encode($chartLabels) ?>;
    var values = <?= json_encode($chartValues) ?>;
    new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: { labels: labels, datasets: [{ label: 'Penyaluran (kg)', data: values, backgroundColor: '#f5e7a4', borderRadius: 12, borderSkipped: false }] },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: { legend: { position: 'top' }, tooltip: { callbacks: { label: function(ctx) { return ctx.dataset.label + ': ' + ctx.raw.toLocaleString('id-ID') + ' kg'; } } } },
            scales: { y: { beginAtZero: true, grid: { color: '#e2e8f0' }, title: { display: true, text: 'Kilogram (kg)' } }, x: { grid: { display: false }, title: { display: true, text: 'Jenis Pupuk' } } }
        }
    });
})();
</script>

<!-- BPS: Press Release -->
<div style="display:flex;align-items:center;gap:8px;margin:28px 0 12px;">
    <span style="background:#1e4a3b;color:#f5e7a4;font-size:10px;font-weight:800;padding:2px 10px;border-radius:20px;">BPS</span>
    <h3 style="font-size:18px;color:#1e4a3b;margin:0;">Siaran Pers BPS — <?= htmlspecialchars($wilayah) ?></h3>
</div>
<?php if (!empty($brsList)): ?>
<div style="display:flex;flex-direction:column;gap:10px;margin-bottom:24px;">
    <?php foreach ($brsList as $b): ?>
    <div class="info-box" style="border-left:5px solid #2d6a4f;padding:12px 18px;">
        <div style="font-weight:700;font-size:15px;color:#1e4a3b;"><?= htmlspecialchars($b['title']??'-') ?></div>
        <div style="font-size:13px;color:#64748b;margin-top:3px;">📅 <?= !empty($b['rl_date']) ? date('d M Y', strtotime($b['rl_date'])) : '-' ?><?php if(!empty($b['pdf'])): ?> · <a href="<?= htmlspecialchars($b['pdf']) ?>" target="_blank" style="color:#2d6a4f;">📥 PDF</a><?php endif; ?></div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="info-box">ℹ️ Siaran pers BPS tidak tersedia untuk wilayah ini.</div>
<?php endif; ?>

<!-- Infografis Nasional -->
<?php if (!empty($infList)): ?>
<div style="display:flex;align-items:center;gap:8px;margin:0 0 12px;">
    <span style="background:#1e4a3b;color:#f5e7a4;font-size:10px;font-weight:800;padding:2px 10px;border-radius:20px;">BPS</span>
    <h3 style="font-size:18px;color:#1e4a3b;margin:0;">Infografis Pertanian Nasional</h3>
</div>
<div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:24px;">
    <?php foreach ($infList as $inf): ?>
    <div style="flex:1;min-width:200px;background:white;border-radius:20px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
        <?php if (!empty($inf['img'])): ?><img src="<?= htmlspecialchars($inf['img']) ?>" style="width:100%;height:150px;object-fit:cover;"><?php endif; ?>
        <div style="padding:12px;">
            <div style="font-size:14px;font-weight:600;color:#1e4a3b;"><?= htmlspecialchars($inf['title']??'-') ?></div>
            <?php if (!empty($inf['dl'])): ?><a href="<?= htmlspecialchars($inf['dl']) ?>" target="_blank" style="font-size:12px;color:#2d6a4f;">📥 Unduh</a><?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="info-box">
    <i class="fas fa-database"></i>
    Data lokal: e-RDKK & i-Pubers &nbsp;|&nbsp;
    <strong>BPS Domain:</strong> <?= htmlspecialchars($domain) ?> (<?= htmlspecialchars($wilayah) ?>) &nbsp;|&nbsp;
    Filter aktif: <?= has_filter() ? htmlspecialchars(implode(' › ', array_filter([filter_prov(), filter_kota(), filter_kec()]))) : 'Semua Wilayah' ?> &nbsp;|&nbsp;
    Update: <?= date('d F Y') ?>
</div>