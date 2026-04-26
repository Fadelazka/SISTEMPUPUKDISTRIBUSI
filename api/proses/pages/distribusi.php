<?php
require_once __DIR__ . '/bps_widget.php';
$domain  = bps_active_domain();
$wilayah = bps_active_wilayah();
$isAdmin = ($_COOKIE['role']==='admin');
$where   = filter_where($koneksi);

$query = mysqli_query($koneksi,"SELECT * FROM distribusi WHERE $where ORDER BY tgl DESC");
$distribusi=[];
while($r=mysqli_fetch_assoc($query)) $distribusi[]=$r;

$totalKg=0;
foreach($distribusi as $d) $totalKg+=intval(preg_replace('/[^0-9]/','',$d['jumlah']));

$qPupuk=mysqli_query($koneksi,"SELECT pupuk,COUNT(*) as cnt,SUM(CAST(REPLACE(REPLACE(jumlah,',',''),' ','') AS UNSIGNED)) as total FROM distribusi WHERE $where GROUP BY pupuk ORDER BY total DESC");
$pupukStats=[];
while($r=mysqli_fetch_assoc($qPupuk)) $pupukStats[]=$r;

// BPS API aman
$bpsBrs  = bps_fetch('list/',['model'=>'pressrelease','domain'=>$domain,'lang'=>'ind','keyword'=>'pupuk','page'=>1]);
$brsList = (is_array($bpsBrs) && !empty($bpsBrs['data'][1])) ? array_slice($bpsBrs['data'][1],0,3) : [];
if(empty($brsList)){
    $fb = bps_fetch('list/',['model'=>'pressrelease','domain'=>'0000','lang'=>'ind','keyword'=>'pupuk','page'=>1]);
    $brsList = (is_array($fb) && !empty($fb['data'][1])) ? array_slice($fb['data'][1],0,3) : [];
}
$bpsTbl  = bps_fetch('list/',['model'=>'statictable','domain'=>$domain,'lang'=>'ind','keyword'=>'subsidi','page'=>1]);
$tblList = (is_array($bpsTbl) && !empty($bpsTbl['data'][1])) ? array_slice($bpsTbl['data'][1],0,3) : [];
if(empty($tblList)){
    $fb2 = bps_fetch('list/',['model'=>'statictable','domain'=>'0000','lang'=>'ind','keyword'=>'harga','page'=>1]);
    $tblList = (is_array($fb2) && !empty($fb2['data'][1])) ? array_slice($fb2['data'][1],0,3) : [];
}
?>

<?= bps_wilayah_badge() ?>

<?php if($isAdmin): ?>
<div style="margin-bottom:20px;text-align:right;">
    <button id="tambahDistribusiBtn" class="btn-admin" style="font-size:14px;padding:10px 22px;">
        <i class="fas fa-plus"></i> Tambah Distribusi
    </button>
</div>
<?php endif; ?>

<?php if(has_filter()): ?>
<div style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:16px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;gap:12px;">
    <i class="fas fa-filter" style="color:#d97706;font-size:18px;"></i>
    <div>
        <div style="font-size:15px;font-weight:700;color:#92400e;">Filter Aktif</div>
        <div style="font-size:14px;color:#b45309;margin-top:2px;">
            Menampilkan <strong><?= count($distribusi) ?> catatan</strong> distribusi ·
            Total: <strong><?= number_format($totalKg,0,',','.') ?> kg</strong>
            dari: <strong><?= htmlspecialchars(implode(' › ',array_filter([filter_prov(),filter_kota(),filter_kec()]))) ?></strong>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="stats-grid" style="margin-bottom:24px;">
    <div class="stat-card" style="border-top:4px solid #f5e7a4;">
        <div class="stat-title" style="font-size:14px;">📋 Total Catatan</div>
        <div class="stat-number"><?= count($distribusi) ?></div>
        <div style="font-size:14px;color:#64748b;margin-top:4px;">transaksi distribusi<?= has_filter()?' di wilayah ini':'' ?></div>
    </div>
    <div class="stat-card" style="border-top:4px solid #86efac;">
        <div class="stat-title" style="font-size:14px;">⚖️ Total Pupuk Tersalur</div>
        <div class="stat-number" style="font-size:28px;"><?= number_format($totalKg,0,',','.') ?> kg</div>
        <div style="font-size:14px;color:#64748b;margin-top:4px;"><?= number_format($totalKg/1000,2) ?> ton tersalurkan</div>
    </div>
    <?php foreach(array_slice($pupukStats,0,2) as $ps): ?>
    <div class="stat-card">
        <div class="stat-title" style="font-size:14px;">🌱 <?= htmlspecialchars($ps['pupuk']) ?></div>
        <div class="stat-number" style="font-size:28px;"><?= number_format($ps['total'],0,',','.') ?></div>
        <div style="font-size:14px;color:#64748b;margin-top:4px;">kg · <?= $ps['cnt'] ?> transaksi</div>
    </div>
    <?php endforeach; ?>
</div>

<h3 style="margin-bottom:16px;font-size:20px;color:#1e4a3b;font-weight:800;">
    🚛 Log Distribusi Pupuk Subsidi
    <?= has_filter()?'<span style="font-size:14px;font-weight:500;color:#64748b;margin-left:8px;">('.count($distribusi).' catatan)</span>':'' ?>
</h3>
<div style="overflow-x:auto;">
<table>
    <thead>
        <tr><th>Tanggal</th><th>Kelompok Tani</th><th>Provinsi</th><th>Kota/Kab</th><th>Kecamatan</th><th>Jenis Pupuk</th><th>Jumlah</th><th>Tujuan</th><th>No. DO</th><th>Aksi</th></tr>
    </thead>
    <tbody>
        <?php if(empty($distribusi)): ?>
        <tr><td colspan="10" style="text-align:center;padding:30px;font-size:15px;color:#94a3b8;">
            <?= has_filter()?'Tidak ada distribusi di wilayah yang dipilih. Coba ubah atau reset filter.':'Belum ada data distribusi.' ?>
        </td></tr>
        <?php else: foreach($distribusi as $d): ?>
        <tr>
            <td><?= date('d/m/Y',strtotime($d['tgl'])) ?></td>
            <td><?= htmlspecialchars($d['kelompok']) ?></td>
            <td><?= htmlspecialchars($d['provinsi']??'-') ?></td>
            <td><?= htmlspecialchars($d['kota']??'-') ?></td>
            <td><?= htmlspecialchars($d['kecamatan']??'-') ?></td>
            <td><span class="badge"><?= htmlspecialchars($d['pupuk']) ?></span></td>
            <td><?= htmlspecialchars($d['jumlah']) ?></td>
            <td><?= htmlspecialchars($d['tujuan']) ?></td>
            <td><?= htmlspecialchars($d['no_do']) ?></td>
            <td>
                <?php if($isAdmin): ?>
                <button class="btn-sm btn-edit-distribusi" data-id="<?= $d['id'] ?>"><i class="fas fa-edit"></i></button>
                <button class="btn-sm btn-hapus-distribusi" data-id="<?= $d['id'] ?>" style="background:#fee2e2;"><i class="fas fa-trash"></i></button>
                <?php else: ?><span style="color:#94a3b8;">—</span><?php endif; ?>
            </td>
        </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>
</div>

<div class="info-box" style="font-size:14px;padding:18px 24px;line-height:1.9;">
    <i class="fas fa-map-marked-alt" style="color:#2d6a4f;margin-right:6px;"></i>
    <strong>Keterangan Data Distribusi:</strong><br>
    • Menampilkan <strong><?= count($distribusi) ?> catatan distribusi</strong> <?= has_filter()?'dari wilayah: <strong>'.htmlspecialchars(implode(' › ',array_filter([filter_prov(),filter_kota(),filter_kec()]))).'</strong>':'dari seluruh wilayah' ?><br>
    • Total pupuk tersalur: <strong><?= number_format($totalKg,0,',','.') ?> kg</strong> (<?= number_format($totalKg/1000,2) ?> ton)<br>
    <?php foreach($pupukStats as $ps): ?>
    • <?= htmlspecialchars($ps['pupuk']) ?>: <strong><?= number_format($ps['total'],0,',','.') ?> kg</strong> (<?= $ps['cnt'] ?> transaksi)<br>
    <?php endforeach; ?>
    • BPS Domain: <?= htmlspecialchars($domain) ?> (<?= htmlspecialchars($wilayah) ?>) &nbsp;|&nbsp; Update: <?= date('d F Y') ?>
</div>

<?php if(!empty($brsList)): ?>
<div style="display:flex;align-items:center;gap:10px;margin:28px 0 14px;">
    <span style="background:#1e4a3b;color:#f5e7a4;font-size:10px;font-weight:800;padding:3px 12px;border-radius:20px;">BPS</span>
    <h3 style="font-size:18px;color:#1e4a3b;margin:0;">Siaran Pers BPS — Pupuk & Subsidi</h3>
</div>
<div style="display:flex;flex-direction:column;gap:10px;margin-bottom:24px;">
    <?php foreach($brsList as $b): ?>
    <div class="info-box" style="border-left:5px solid #f5e7a4;padding:14px 20px;">
        <div style="font-weight:700;font-size:15px;color:#1e4a3b;"><?= htmlspecialchars($b['title']??'-') ?></div>
        <div style="font-size:14px;color:#64748b;margin-top:4px;">
            📅 <?= !empty($b['rl_date'])?date('d M Y',strtotime($b['rl_date'])):'-' ?>
            <?php if(!empty($b['pdf'])): ?> · <a href="<?= htmlspecialchars($b['pdf']) ?>" target="_blank" style="color:#2d6a4f;font-weight:600;">📥 PDF</a><?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if(!empty($tblList)): ?>
<div style="display:flex;align-items:center;gap:10px;margin:0 0 14px;">
    <span style="background:#1e4a3b;color:#f5e7a4;font-size:10px;font-weight:800;padding:3px 12px;border-radius:20px;">BPS</span>
    <h3 style="font-size:18px;color:#1e4a3b;margin:0;">Referensi Tabel BPS — <?= htmlspecialchars($wilayah) ?></h3>
</div>
<div style="display:flex;flex-direction:column;gap:10px;margin-bottom:24px;">
    <?php foreach($tblList as $tbl): ?>
    <div class="info-box" style="border-left:5px solid #2d6a4f;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;padding:14px 20px;">
        <div>
            <div style="font-weight:700;font-size:15px;color:#1e4a3b;"><?= htmlspecialchars($tbl['title']??'-') ?></div>
            <div style="font-size:13px;color:#64748b;margin-top:3px;"><?= htmlspecialchars($tbl['subj']??'-') ?> · <?= !empty($tbl['updt_date'])?date('d M Y',strtotime($tbl['updt_date'])):'-' ?></div>
        </div>
        <?php if(!empty($tbl['excel'])): ?>
        <a href="<?= htmlspecialchars($tbl['excel']) ?>" target="_blank" class="btn-admin" style="text-decoration:none;font-size:13px;padding:6px 16px;"><i class="fas fa-file-excel"></i> Excel</a>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>