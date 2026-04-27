<?php
try {
    require_once __DIR__ . '/bps_widget.php';
    $domain  = bps_active_domain();
    $wilayah = bps_active_wilayah();
    $isAdmin = (isset($_COOKIE['role']) && $_COOKIE['role'] === 'admin');
    $where = filter_where($koneksi);
    
    // Grafik 1
    $queryChart = "SELECT pupuk, SUM(CAST(REPLACE(REPLACE(jumlah, ',', ''), ' ', '') AS UNSIGNED)) as total FROM distribusi WHERE $where GROUP BY pupuk";
    $resultChart = mysqli_query($koneksi, $queryChart);
    $chartLabels = []; $chartValues = []; $totalRealisasiKg = 0;
    if ($resultChart) { while ($row = mysqli_fetch_assoc($resultChart)) { $chartLabels[] = $row['pupuk']; $val = (int)$row['total']; $chartValues[] = $val; $totalRealisasiKg += $val; } }
    if (empty($chartLabels)) { $chartLabels = ['Belum Ada Data']; $chartValues = [0]; }
    $targetTon = 68; $targetKg = $targetTon * 1000;
    $persentase = ($targetKg > 0) ? round(($totalRealisasiKg / $targetKg) * 100, 1) : 0;

    // Grafik 2 & 3
    $qProv = mysqli_query($koneksi, "SELECT provinsi, SUM(CAST(REPLACE(REPLACE(jumlah,',',''),' ','') AS UNSIGNED)) as total FROM distribusi WHERE provinsi IS NOT NULL AND provinsi != '' GROUP BY provinsi ORDER BY total DESC LIMIT 15");
    $provLabels=[]; $provValues=[]; if ($qProv) { while($r=mysqli_fetch_assoc($qProv)){ $provLabels[]=$r['provinsi']; $provValues[]=(int)$r['total']; } }
    $hasProvData = !empty($provLabels);

    $qBulan = mysqli_query($koneksi, "SELECT DATE_FORMAT(tgl,'%Y-%m') as bulan, DATE_FORMAT(tgl,'%b %Y') as label_bulan, SUM(CAST(REPLACE(REPLACE(jumlah,',',''),' ','') AS UNSIGNED)) as total FROM distribusi WHERE $where AND tgl IS NOT NULL GROUP BY DATE_FORMAT(tgl,'%Y-%m'), DATE_FORMAT(tgl,'%b %Y') ORDER BY bulan ASC LIMIT 12");
    $bulanLabels=[]; $bulanValues=[]; if ($qBulan) { while($r=mysqli_fetch_assoc($qBulan)){ $bulanLabels[]=$r['label_bulan']; $bulanValues[]=(int)$r['total']; } }
    $hasBulanData=!empty($bulanLabels);

    // Data Laporan Lokal & BPS
    $queryLaporan = mysqli_query($koneksi, "SELECT * FROM laporan ORDER BY created_at DESC");
    $laporan = []; if ($queryLaporan) { while($r = mysqli_fetch_assoc($queryLaporan)) $laporan[] = $r; }

    $bpsTbl = bps_fetch('list/',['model'=>'statictable','domain'=>$domain,'lang'=>'ind','keyword'=>'produksi','page'=>1]);
    $tblList = (is_array($bpsTbl) && !empty($bpsTbl['data'][1])) ? array_slice($bpsTbl['data'][1],0,4) : [];
    $bpsBrs = bps_fetch('list/',['model'=>'pressrelease','domain'=>$domain,'lang'=>'ind','keyword'=>'produksi','page'=>1]);
    $brsList = (is_array($bpsBrs) && !empty($bpsBrs['data'][1])) ? array_slice($bpsBrs['data'][1],0,3) : [];
    $bpsInf = bps_fetch('list/',['model'=>'infographic','domain'=>'0000','lang'=>'ind','page'=>1,'keyword'=>'pupuk']);
    $infList = (is_array($bpsInf) && !empty($bpsInf['data'][1])) ? array_slice($bpsInf['data'][1],0,2) : [];
?>

<?= bps_wilayah_badge() ?>

<div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-6 mt-4">
    <h2 class="text-xl md:text-2xl font-bold text-primary flex items-center gap-2"><i class="fas fa-chart-pie"></i> Laporan Realisasi</h2>
    <?php if($isAdmin): ?>
    <button id="tambahLaporanBtn" class="bg-primary hover:bg-accent text-white px-5 py-2.5 rounded-full font-medium shadow-md flex items-center justify-center gap-2 text-sm transition-all"><i class="fas fa-plus"></i> Tambah Laporan</button>
    <?php endif; ?>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 text-center">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Target Nasional</p>
        <h3 class="text-2xl font-black text-slate-700"><?= $targetTon ?> <span class="text-sm font-normal">ton</span></h3>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 text-center border-b-4 border-b-primary">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Realisasi</p>
        <h3 class="text-2xl font-black text-primary"><?= number_format($totalRealisasiKg/1000, 1) ?> <span class="text-sm font-normal">ton</span></h3>
        <p class="text-[10px] text-slate-400 mt-1"><?= number_format($totalRealisasiKg,0,',','.') ?> kg</p>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 text-center border-b-4 border-b-amber-400">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Persentase</p>
        <h3 class="text-2xl font-black text-amber-500"><?= $persentase ?>%</h3>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col">
        <h3 class="text-base font-extrabold text-primary mb-4 flex items-center gap-2">📊 Pupuk Subsidi — <?= htmlspecialchars($wilayah) ?></h3>
        <div class="relative w-full h-64 sm:h-72">
            <canvas id="reportChart"></canvas>
        </div>
    </div>
    <?php if($hasBulanData && count($bulanLabels)>1): ?>
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col">
        <h3 class="text-base font-extrabold text-primary mb-4 flex items-center gap-2">📈 Trend Distribusi Bulanan</h3>
        <div class="relative w-full h-64 sm:h-72">
            <canvas id="trendChart"></canvas>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if($hasProvData): ?>
<div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 mb-8">
    <div class="mb-4">
        <h3 class="text-base font-extrabold text-primary flex items-center gap-2">🗺️ Perbandingan Antar Provinsi</h3>
        <p class="text-xs text-slate-500 mt-1">Distribusi pupuk di seluruh provinsi database. <?php if(filter_prov()): ?><span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded font-bold ml-2">🔍 Provinsi aktif: <?= htmlspecialchars(filter_prov()) ?></span><?php endif; ?></p>
    </div>
    <div class="relative w-full h-72 md:h-80">
        <canvas id="provChart"></canvas>
    </div>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <div>
        <?php if(!empty($tblList)): ?>
        <div class="flex items-center gap-2 mb-4"><span class="bg-primary text-secondary text-[10px] font-black px-2.5 py-1 rounded-full">BPS</span><h3 class="font-bold text-slate-800">Tabel Produksi</h3></div>
        <div class="space-y-3">
            <?php foreach($tblList as $tbl): ?>
            <div class="bg-white border-l-4 border-primary p-4 rounded-xl shadow-sm flex flex-col sm:flex-row justify-between gap-3">
                <div>
                    <h4 class="font-bold text-sm text-slate-800 leading-snug"><?= htmlspecialchars($tbl['title']??'-') ?></h4>
                    <p class="text-[11px] text-slate-500 mt-1"><?= htmlspecialchars($tbl['subj']??'-') ?> · <?= !empty($tbl['updt_date'])?date('d M Y',strtotime($tbl['updt_date'])):'-' ?></p>
                </div>
                <?php if(!empty($tbl['excel'])): ?><a href="<?= htmlspecialchars($tbl['excel']) ?>" target="_blank" class="bg-slate-100 hover:bg-slate-200 text-primary px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap self-start sm:self-center"><i class="fas fa-file-excel text-green-600 mr-1"></i> Excel</a><?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <div>
        <?php if(!empty($brsList)): ?>
        <div class="flex items-center gap-2 mb-4"><span class="bg-primary text-secondary text-[10px] font-black px-2.5 py-1 rounded-full">BPS</span><h3 class="font-bold text-slate-800">Siaran Pers</h3></div>
        <div class="space-y-3">
            <?php foreach($brsList as $b): ?>
            <div class="bg-white border-l-4 border-secondary p-4 rounded-xl shadow-sm">
                <h4 class="font-bold text-sm text-slate-800 leading-snug"><?= htmlspecialchars($b['title']??'-') ?></h4>
                <div class="text-[11px] text-slate-500 mt-1.5 flex items-center justify-between">
                    <span>📅 <?= !empty($b['rl_date'])?date('d M Y',strtotime($b['rl_date'])):'-' ?></span>
                    <?php if(!empty($b['pdf'])): ?><a href="<?= htmlspecialchars($b['pdf']) ?>" target="_blank" class="text-primary font-bold hover:underline"><i class="fas fa-download mr-1"></i> PDF</a><?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<h3 class="text-xl font-extrabold text-primary mb-4 flex items-center gap-2 mt-10"><i class="fas fa-file-alt text-secondary"></i> Laporan Tertulis (Lokal)</h3>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
    <?php if(empty($laporan)): ?>
    <div class="col-span-full bg-slate-50 border border-dashed border-slate-300 p-8 rounded-2xl text-center text-slate-400 font-medium">Belum ada laporan. Klik "Tambah Laporan" untuk membuat baru.</div>
    <?php else: foreach($laporan as $lap): ?>
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex flex-col">
        <h4 class="font-extrabold text-lg text-primary leading-snug"><?= htmlspecialchars($lap['judul']) ?></h4>
        <p class="text-xs text-slate-400 font-medium mb-3 mt-1"><i class="far fa-clock mr-1"></i> <?= date('d M Y H:i',strtotime($lap['created_at'])) ?></p>
        <p class="text-sm text-slate-600 mb-4 bg-slate-50 p-3 rounded-xl flex-1"><?= nl2br(htmlspecialchars($lap['deskripsi'])) ?></p>
        
        <?php if($isAdmin): ?>
        <div class="flex gap-2 pt-3 border-t border-slate-100">
            <button class="flex-1 bg-slate-100 text-primary py-2 rounded-lg text-xs font-bold btn-edit-laporan hover:bg-slate-200" data-id="<?= $lap['id'] ?>"><i class="fas fa-edit mr-1"></i> Edit</button>
            <button class="bg-red-50 text-red-600 px-4 py-2 rounded-lg text-xs font-bold btn-hapus-laporan hover:bg-red-100" data-id="<?= $lap['id'] ?>"><i class="fas fa-trash"></i></button>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; endif; ?>
</div>

<script>
(function(){
    if(typeof Chart === 'undefined') return;
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#64748b';

    var c1 = document.getElementById('reportChart');
    if(c1) new Chart(c1.getContext('2d'), { type: 'bar', data: { labels: <?= json_encode($chartLabels) ?>, datasets: [{ label: 'Penyaluran (kg)', data: <?= json_encode($chartValues) ?>, backgroundColor: '#f5e7a4', borderRadius: 6 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } } });
    
    var c2 = document.getElementById('provChart');
    if(c2) {
        var pLabels = <?= json_encode($provLabels) ?>, pValues = <?= json_encode($provValues) ?>, act = <?= json_encode(filter_prov()) ?>;
        var cols = pLabels.map(function(l){ return l===act?'#1e4a3b':'#b7dfc8'; });
        new Chart(c2.getContext('2d'), { type: 'bar', data: { labels: pLabels, datasets: [{ label: 'Distribusi (kg)', data: pValues, backgroundColor: cols, borderRadius: 4 }] }, options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } } });
    }
    
    var c3 = document.getElementById('trendChart');
    if(c3) new Chart(c3.getContext('2d'), { type: 'line', data: { labels: <?= json_encode($bulanLabels) ?>, datasets: [{ label: 'Distribusi (kg)', data: <?= json_encode($bulanValues) ?>, borderColor: '#2d6a4f', backgroundColor: 'rgba(45,106,79,0.1)', fill: true, tension: 0.4 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } } });
})();
</script>

<?php
} catch (\Throwable $e) {
    echo "<div class='bg-red-50 border-l-4 border-red-500 p-6 rounded-2xl mt-4'>";
    echo "<h3 class='text-red-700 font-bold text-lg mb-2'><i class='fas fa-exclamation-triangle'></i> Terjadi Kesalahan DB</h3>";
    echo "<p class='text-red-600 text-sm'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>