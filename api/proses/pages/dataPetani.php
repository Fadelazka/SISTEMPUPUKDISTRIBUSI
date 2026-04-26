<?php
require_once __DIR__ . '/bps_widget.php';
$domain  = bps_active_domain();
$wilayah = bps_active_wilayah();
$isAdmin = ($_SESSION['role']==='admin');
$where   = filter_where($koneksi);

$query = mysqli_query($koneksi,"SELECT * FROM petani WHERE $where ORDER BY id DESC");
$petani=[];
while($r=mysqli_fetch_assoc($query)) $petani[]=$r;

$statusCount = [];
foreach($petani as $p){
    $st = $p['status']??'Belum';
    $statusCount[$st]=($statusCount[$st]??0)+1;
}
$totalAlokasi = array_sum(array_column($petani,'alokasi'));

// BPS API dengan pengecekan aman
$bpsTbl  = bps_fetch('list/',['model'=>'statictable','domain'=>$domain,'lang'=>'ind','keyword'=>'petani','page'=>1]);
$tblList = (is_array($bpsTbl) && !empty($bpsTbl['data'][1])) ? array_slice($bpsTbl['data'][1],0,3) : [];
if(empty($tblList)){
    $fb = bps_fetch('list/',['model'=>'statictable','domain'=>'0000','lang'=>'ind','keyword'=>'petani','page'=>1]);
    $tblList = (is_array($fb) && !empty($fb['data'][1])) ? array_slice($fb['data'][1],0,3) : [];
}
$bpsPub  = bps_fetch('list/',['model'=>'publication','domain'=>$domain,'lang'=>'ind','keyword'=>'pertanian','page'=>1]);
$pubList = (is_array($bpsPub) && !empty($bpsPub['data'][1])) ? array_slice($bpsPub['data'][1],0,3) : [];
if(empty($pubList)){
    $fb2 = bps_fetch('list/',['model'=>'publication','domain'=>'0000','lang'=>'ind','keyword'=>'pertanian','page'=>1]);
    $pubList = (is_array($fb2) && !empty($fb2['data'][1])) ? array_slice($fb2['data'][1],0,3) : [];
}
?>

<?= bps_wilayah_badge() ?>

<?php if($isAdmin): ?>
<div style="margin-bottom:20px;text-align:right;">
    <button id="tambahPetaniBtn" class="btn-admin" style="font-size:14px;padding:10px 22px;">
        <i class="fas fa-plus"></i> Tambah Petani
    </button>
</div>
<?php endif; ?>

<?php if(has_filter()): ?>
<div style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:16px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;gap:12px;">
    <i class="fas fa-filter" style="color:#d97706;font-size:18px;"></i>
    <div>
        <div style="font-size:15px;font-weight:700;color:#92400e;">Filter Aktif</div>
        <div style="font-size:14px;color:#b45309;margin-top:2px;">
            Menampilkan <strong><?= count($petani) ?> petani</strong> dari wilayah:
            <strong><?= htmlspecialchars(implode(' › ',array_filter([filter_prov(),filter_kota(),filter_kec()]))) ?></strong>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="stats-grid" style="margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-title" style="font-size:14px;">👨‍🌾 Total Petani</div>
        <div class="stat-number"><?= count($petani) ?></div>
        <div style="font-size:14px;color:#64748b;margin-top:4px;"><?= has_filter()?'di wilayah yang dipilih':'terdaftar di sistem' ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-title" style="font-size:14px;">✅ Sudah Terealisasi</div>
        <div class="stat-number" style="color:#16a34a;"><?= $statusCount['Terealisasi']??0 ?></div>
        <div style="font-size:14px;color:#64748b;margin-top:4px;">petani sudah menerima pupuk</div>
    </div>
    <div class="stat-card">
        <div class="stat-title" style="font-size:14px;">⏳ Masih Pending</div>
        <div class="stat-number" style="color:#f59e0b;"><?= ($statusCount['Pending']??0)+($statusCount['Proses']??0) ?></div>
        <div style="font-size:14px;color:#64748b;margin-top:4px;">petani menunggu realisasi</div>
    </div>
    <div class="stat-card">
        <div class="stat-title" style="font-size:14px;">🌾 Total Alokasi</div>
        <div class="stat-number" style="font-size:28px;"><?= number_format($totalAlokasi,0,',','.') ?></div>
        <div style="font-size:14px;color:#64748b;margin-top:4px;">kg alokasi pupuk terdaftar</div>
    </div>
</div>

<h3 style="margin-bottom:16px;font-size:20px;color:#1e4a3b;font-weight:800;">
    👨‍🌾 Daftar Petani Penerima Subsidi
    <?= has_filter()?'<span style="font-size:14px;font-weight:500;color:#64748b;margin-left:10px;">('.count($petani).' data ditemukan)</span>':'' ?>
</h3>
<div style="overflow-x:auto;">
<table>
    <thead>
        <tr><th>Nama</th><th>Desa</th><th>Provinsi</th><th>Kota/Kab</th><th>Kecamatan</th><th>Luas (Ha)</th><th>Alokasi</th><th>Status</th><th>Tgl Terima</th><th>Aksi</th></tr>
    </thead>
    <tbody>
        <?php if(empty($petani)): ?>
        <tr><td colspan="10" style="text-align:center;padding:30px;font-size:15px;color:#94a3b8;">
            <?= has_filter()?'Tidak ada petani di wilayah yang dipilih. Coba ubah filter atau reset.':'Belum ada data petani. Klik "Tambah Petani" untuk menambahkan.' ?>
        </td></tr>
        <?php else: foreach($petani as $p): ?>
        <tr>
            <td><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
            <td><?= htmlspecialchars($p['desa']) ?></td>
            <td><?= htmlspecialchars($p['provinsi']??'-') ?></td>
            <td><?= htmlspecialchars($p['kota']??'-') ?></td>
            <td><?= htmlspecialchars($p['kecamatan']??'-') ?></td>
            <td><?= $p['luas_lahan'] ?></td>
            <td><?= $p['alokasi'] ?></td>
            <td><span class="badge" style="background:<?= $p['status']==='Terealisasi'?'#dcfce7':($p['status']==='Proses'?'#fef9c3':'#fee2e2') ?>;"><?= $p['status'] ?></span></td>
            <td><?= $p['tgl_terima']??'-' ?></td>
            <td>
                <?php if($isAdmin): ?>
                <button class="btn-sm btn-edit-petani" data-id="<?= $p['id'] ?>"><i class="fas fa-edit"></i></button>
                <button class="btn-sm btn-hapus-petani" data-id="<?= $p['id'] ?>" style="background:#fee2e2;"><i class="fas fa-trash"></i></button>
                <?php else: ?><button class="btn-sm"><i class="fas fa-eye"></i></button><?php endif; ?>
            </td>
        </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>
</div>

<div class="info-box" style="font-size:14px;padding:18px 24px;line-height:1.9;">
    <i class="fas fa-info-circle" style="color:#2d6a4f;margin-right:6px;"></i>
    <strong>Keterangan Data Petani:</strong><br>
    • Menampilkan <strong><?= count($petani) ?> petani</strong> <?= has_filter()?'dari wilayah: <strong>'.htmlspecialchars(implode(' › ',array_filter([filter_prov(),filter_kota(),filter_kec()]))).'</strong>':'dari seluruh wilayah' ?><br>
    • <strong><?= $statusCount['Terealisasi']??0 ?> petani</strong> sudah menerima pupuk, <strong><?= ($statusCount['Pending']??0)+($statusCount['Proses']??0) ?> petani</strong> masih dalam proses<br>
    • Total alokasi terdaftar: <strong><?= number_format($totalAlokasi,0,',','.') ?> kg</strong><br>
    • BPS Domain: <?= htmlspecialchars($domain) ?> (<?= htmlspecialchars($wilayah) ?>) &nbsp;|&nbsp; Update: <?= date('d F Y') ?>
</div>

<?php if(!empty($tblList)): ?>
<div style="display:flex;align-items:center;gap:10px;margin:28px 0 14px;">
    <span style="background:#1e4a3b;color:#f5e7a4;font-size:10px;font-weight:800;padding:3px 12px;border-radius:20px;">BPS</span>
    <h3 style="font-size:18px;color:#1e4a3b;margin:0;">Statistik Petani — <?= htmlspecialchars($wilayah) ?></h3>
</div>
<div style="display:flex;flex-direction:column;gap:10px;margin-bottom:24px;">
    <?php foreach($tblList as $tbl): ?>
    <div class="info-box" style="border-left:5px solid #f5e7a4;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;padding:14px 20px;">
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

<?php if(!empty($pubList)): ?>
<div style="display:flex;align-items:center;gap:10px;margin:0 0 14px;">
    <span style="background:#1e4a3b;color:#f5e7a4;font-size:10px;font-weight:800;padding:3px 12px;border-radius:20px;">BPS</span>
    <h3 style="font-size:18px;color:#1e4a3b;margin:0;">Publikasi BPS — Pertanian</h3>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px;">
    <?php foreach($pubList as $pub): ?>
    <div style="background:white;border-radius:18px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
        <?php if(!empty($pub['cover'])): ?><img src="<?= htmlspecialchars($pub['cover']) ?>" style="width:100%;height:130px;object-fit:cover;" loading="lazy"><?php endif; ?>
        <div style="padding:14px;">
            <div style="font-size:13px;font-weight:700;color:#1e4a3b;line-height:1.4;"><?= htmlspecialchars($pub['title']??'-') ?></div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;"><?= !empty($pub['rl_date'])?date('Y',strtotime($pub['rl_date'])):'-' ?></div>
            <?php if(!empty($pub['pdf'])): ?><a href="<?= htmlspecialchars($pub['pdf']) ?>" target="_blank" style="font-size:12px;color:white;background:#2d6a4f;padding:5px 12px;border-radius:20px;text-decoration:none;display:inline-block;margin-top:8px;">📥 PDF</a><?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>