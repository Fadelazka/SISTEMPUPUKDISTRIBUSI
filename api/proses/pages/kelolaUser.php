<?php
if($_COOKIE['role']!='admin'){ echo "<div class='error'>Akses ditolak.</div>"; exit(); }
require_once __DIR__ . '/bps_widget.php';
$domain  = bps_active_domain();
$wilayah = bps_active_wilayah();

$query = mysqli_query($koneksi,"SELECT id,nama,email,role FROM users ORDER BY id");
$users = [];
while($r = mysqli_fetch_assoc($query)) $users[] = $r;

$bpsNews = bps_fetch('list/',['model'=>'news','domain'=>'0000','lang'=>'ind','page'=>1]);
$newsList = (is_array($bpsNews) && !empty($bpsNews['data'][1])) ? array_slice($bpsNews['data'][1],0,4) : [];
?>

<?= bps_wilayah_badge() ?>

<div style="margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
    <h3 style="margin:0;">👥 Daftar Pengguna Sistem</h3>
    <button id="tambahUserBtn" class="btn-admin"><i class="fas fa-user-plus"></i> Tambah User</button>
</div>

<table>
    <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Aksi</th></tr></thead>
    <tbody>
        <?php foreach($users as $u): ?>
        <tr>
            <td><?= htmlspecialchars($u['nama']) ?></td>
            <td><?= $u['email'] ?></td>
            <td><?= ucfirst($u['role']) ?></td>
            <td>
                <button class="btn-sm btn-edit-user" data-id="<?= $u['id'] ?>"><i class="fas fa-edit"></i> Edit</button>
                <?php if($u['role']!='admin'): ?>
                <button class="btn-sm btn-hapus-user" data-id="<?= $u['id'] ?>" style="background:#fee2e2;"><i class="fas fa-trash"></i> Hapus</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div style="display:flex;align-items:center;gap:8px;margin:28px 0 12px;">
    <span style="background:#1e4a3b;color:#f5e7a4;font-size:10px;font-weight:800;padding:2px 10px;border-radius:20px;">BPS API</span>
    <h3 style="font-size:16px;color:#1e4a3b;margin:0;">Status Integrasi & Konfigurasi</h3>
</div>
<div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #86efac;border-radius:20px;padding:20px 24px;margin-bottom:24px;">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;">
        <div>
            <div style="font-size:11px;color:#64748b;font-weight:600;margin-bottom:4px;">API KEY</div>
            <div style="font-size:12px;color:#1e4a3b;font-weight:700;font-family:monospace;background:white;padding:6px 10px;border-radius:10px;">
                <?= substr(BPS_API_KEY,0,8) ?>••••••••<?= substr(BPS_API_KEY,-4) ?>
            </div>
        </div>
        <div>
            <div style="font-size:11px;color:#64748b;font-weight:600;margin-bottom:4px;">WILAYAH AKTIF</div>
            <div style="font-size:13px;color:#1e4a3b;font-weight:700;"><?= htmlspecialchars($wilayah) ?></div>
            <div style="font-size:11px;color:#64748b;">Domain: <?= htmlspecialchars($domain) ?></div>
        </div>
        <div>
            <div style="font-size:11px;color:#64748b;font-weight:600;margin-bottom:4px;">HALAMAN TERINTEGRASI</div>
            <div style="font-size:12px;color:#1e4a3b;line-height:1.8;">✅ Beranda &nbsp; ✅ Data Petani<br>✅ Distribusi &nbsp; ✅ Laporan &nbsp; ✅ Profil</div>
        </div>
        <div>
            <div style="font-size:11px;color:#64748b;font-weight:600;margin-bottom:4px;">FORM CRUD</div>
            <div style="font-size:12px;color:#1e4a3b;line-height:1.8;">✅ Provinsi (BPS)<br>✅ Kota/Kab (BPS)<br>✅ Kecamatan (BPS SIMDASI)</div>
        </div>
    </div>
</div>

<?php if(!empty($newsList)): ?>
<div style="display:flex;align-items:center;gap:8px;margin:0 0 12px;">
    <span style="background:#1e4a3b;color:#f5e7a4;font-size:10px;font-weight:800;padding:2px 10px;border-radius:20px;">BPS</span>
    <h3 style="font-size:16px;color:#1e4a3b;margin:0;">📰 Berita Terbaru BPS</h3>
</div>
<div style="display:flex;flex-direction:column;gap:10px;margin-bottom:24px;">
    <?php foreach($newsList as $n): ?>
    <div class="info-box" style="border-left:5px solid #2d6a4f;padding:10px 16px;">
        <div style="font-weight:700;font-size:13px;color:#1e4a3b;"><?= htmlspecialchars($n['title']??'-') ?></div>
        <div style="font-size:11px;color:#64748b;margin-top:2px;">📅 <?= !empty($n['release_date'])?date('d M Y',strtotime($n['release_date'])):'-' ?></div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="info-box"><i class="fas fa-cog"></i>
    Manajemen pengguna &nbsp;|&nbsp; <strong>BPS Domain:</strong> <?= htmlspecialchars($domain) ?> &nbsp;|&nbsp; Update: <?= date('d F Y') ?>
</div>