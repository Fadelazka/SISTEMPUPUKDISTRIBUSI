<?php
if($_COOKIE['role']!='admin'){ echo "<div class='text-red-500 font-bold p-6 text-center'>Akses ditolak. Anda bukan Admin.</div>"; exit(); }
require_once __DIR__ . '/bps_widget.php';
$domain  = bps_active_domain();
$wilayah = bps_active_wilayah();

$query = mysqli_query($koneksi,"SELECT id,nama,email,role FROM users ORDER BY id");
$users = []; while($r = mysqli_fetch_assoc($query)) $users[] = $r;

$bpsNews = bps_fetch('list/',['model'=>'news','domain'=>'0000','lang'=>'ind','page'=>1]);
$newsList = (is_array($bpsNews) && !empty($bpsNews['data'][1])) ? array_slice($bpsNews['data'][1],0,4) : [];
?>

<?= bps_wilayah_badge() ?>

<div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-6 mt-4">
    <h2 class="text-xl md:text-2xl font-bold text-primary flex items-center gap-2"><i class="fas fa-users-cog"></i> Kelola Pengguna</h2>
    <button id="tambahUserBtn" class="bg-primary hover:bg-accent text-white px-5 py-2.5 rounded-full font-medium shadow-md flex items-center justify-center gap-2 text-sm transition-all"><i class="fas fa-user-plus"></i> Tambah User</button>
</div>

<div class="grid grid-cols-1 gap-4 md:hidden mb-8">
    <?php foreach($users as $u): ?>
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex flex-col">
        <div class="flex justify-between items-start mb-2">
            <h4 class="font-extrabold text-slate-800 text-lg"><?= htmlspecialchars($u['nama']) ?></h4>
            <span class="px-2.5 py-1 text-[10px] font-black uppercase rounded-full tracking-wider <?= $u['role']==='admin'?'bg-purple-100 text-purple-700':'bg-emerald-100 text-emerald-700' ?>"><?= $u['role'] ?></span>
        </div>
        <p class="text-sm text-slate-500 mb-4 font-medium"><i class="fas fa-envelope text-slate-300 mr-1.5"></i> <?= $u['email'] ?></p>
        <div class="flex gap-2 mt-auto pt-3 border-t border-slate-100">
            <button class="flex-1 bg-slate-100 text-primary py-2 rounded-xl text-xs font-bold btn-edit-user" data-id="<?= $u['id'] ?>"><i class="fas fa-edit mr-1"></i> Edit</button>
            <?php if($u['role']!='admin'): ?>
            <button class="bg-red-50 text-red-600 px-4 py-2 rounded-xl text-xs font-bold btn-hapus-user" data-id="<?= $u['id'] ?>"><i class="fas fa-trash"></i></button>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="hidden md:block bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-8">
    <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-primary border-b border-slate-100">
            <tr><th class="p-4 font-bold">Nama Lengkap</th><th class="p-4 font-bold">Email</th><th class="p-4 font-bold">Role</th><th class="p-4 font-bold text-center">Aksi</th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php foreach($users as $u): ?>
            <tr class="hover:bg-slate-50">
                <td class="p-4 font-bold text-slate-800"><?= htmlspecialchars($u['nama']) ?></td>
                <td class="p-4 text-slate-600 font-medium"><?= $u['email'] ?></td>
                <td class="p-4"><span class="px-3 py-1 rounded-lg text-xs font-bold <?= $u['role']==='admin'?'bg-purple-100 text-purple-700':'bg-emerald-100 text-emerald-700' ?>"><?= ucfirst($u['role']) ?></span></td>
                <td class="p-4 text-center">
                    <button class="text-primary hover:bg-slate-200 bg-slate-100 px-3 py-1.5 rounded-lg text-xs font-bold mr-1 btn-edit-user" data-id="<?= $u['id'] ?>"><i class="fas fa-edit"></i> Edit</button>
                    <?php if($u['role']!='admin'): ?>
                    <button class="text-red-600 hover:bg-red-100 bg-red-50 px-3 py-1.5 rounded-lg text-xs font-bold btn-hapus-user" data-id="<?= $u['id'] ?>"><i class="fas fa-trash"></i></button>
                    <?php else: ?>
                    <span class="inline-block px-3 py-1.5 text-slate-300 text-xs font-bold">-</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="flex items-center gap-2 mb-4 mt-8">
    <span class="bg-primary text-secondary text-[10px] font-black px-2.5 py-1 rounded-full">BPS API</span>
    <h3 class="font-bold text-slate-800">Status Integrasi & Konfigurasi</h3>
</div>
<div class="bg-gradient-to-br from-emerald-50 to-green-100 border border-green-200 rounded-3xl p-6 mb-8 shadow-sm">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div>
            <p class="text-[10px] uppercase font-bold text-slate-500 mb-1.5">API KEY</p>
            <div class="bg-white border border-green-200 px-3 py-2 rounded-xl text-xs font-mono font-bold text-primary shadow-sm"><?= substr(BPS_API_KEY,0,8) ?>••••••••<?= substr(BPS_API_KEY,-4) ?></div>
        </div>
        <div>
            <p class="text-[10px] uppercase font-bold text-slate-500 mb-1">WILAYAH AKTIF</p>
            <p class="text-sm font-black text-primary"><?= htmlspecialchars($wilayah) ?></p>
            <p class="text-[10px] text-slate-500 mt-0.5">Domain: <?= htmlspecialchars($domain) ?></p>
        </div>
        <div>
            <p class="text-[10px] uppercase font-bold text-slate-500 mb-1">HALAMAN TERINTEGRASI</p>
            <p class="text-xs font-semibold text-primary leading-relaxed">✅ Beranda &nbsp; ✅ Data Petani<br>✅ Distribusi &nbsp; ✅ Laporan<br>✅ Profil &nbsp; ✅ Form Input</p>
        </div>
        <div>
            <p class="text-[10px] uppercase font-bold text-slate-500 mb-1">DATA BPS AKTIF</p>
            <p class="text-xs font-semibold text-primary leading-relaxed">✅ API Domain (Prov/Kab)<br>✅ API SIMDASI (Kecamatan)<br>✅ Indikator & Publikasi</p>
        </div>
    </div>
</div>

<?php if(!empty($newsList)): ?>
<div class="flex items-center gap-2 mb-4">
    <span class="bg-primary text-secondary text-[10px] font-black px-2.5 py-1 rounded-full">BPS</span>
    <h3 class="font-bold text-slate-800">Berita Terbaru BPS</h3>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
    <?php foreach($newsList as $n): ?>
    <div class="bg-white border-l-4 border-secondary rounded-xl p-4 shadow-sm">
        <h4 class="font-bold text-sm text-primary mb-1 leading-snug"><?= htmlspecialchars($n['title']??'-') ?></h4>
        <p class="text-xs text-slate-400 font-medium"><i class="far fa-calendar-alt mr-1"></i> <?= !empty($n['release_date'])?date('d M Y',strtotime($n['release_date'])):'-' ?></p>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>