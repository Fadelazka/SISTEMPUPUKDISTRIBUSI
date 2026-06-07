<?php
require_once __DIR__ . '/bps_widget.php';
$domain  = bps_active_domain();
$wilayah = bps_active_wilayah();
$isAdmin = ($_COOKIE['role']==='admin');
$where   = filter_where($koneksi);

$query = mysqli_query($koneksi,"SELECT * FROM petani WHERE $where ORDER BY id DESC");
$petani=[]; while($r=mysqli_fetch_assoc($query)) $petani[]=$r;

$statusCount = [];
foreach($petani as $p){ $st = $p['status']??'Belum'; $statusCount[$st]=($statusCount[$st]??0)+1; }
$totalAlokasi = array_sum(array_column($petani,'alokasi'));

// Data BPS
$bpsTbl  = bps_fetch('list/',['model'=>'statictable','domain'=>$domain,'lang'=>'ind','keyword'=>'petani','page'=>1]);
$tblList = (is_array($bpsTbl) && !empty($bpsTbl['data'][1])) ? array_slice($bpsTbl['data'][1],0,3) : [];
?>

<?= bps_wilayah_badge() ?>

<div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-6">
    <h2 class="text-xl md:text-2xl font-bold text-primary flex items-center gap-2"><i class="fas fa-users"></i> Daftar Petani</h2>
    <?php if($isAdmin): ?>
    <button id="tambahPetaniBtn" class="bg-primary hover:bg-accent text-white px-5 py-2.5 rounded-full font-medium shadow-md transition-all flex items-center gap-2 text-sm"><i class="fas fa-plus"></i> Tambah Petani</button>
    <?php endif; ?>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <p class="text-xs text-slate-500 font-semibold mb-1">Total Petani</p>
        <h3 class="text-3xl font-black text-primary"><?= count($petani) ?></h3>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <p class="text-xs text-slate-500 font-semibold mb-1">Terealisasi</p>
        <h3 class="text-3xl font-black text-green-600"><?= $statusCount['Terealisasi']??0 ?></h3>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <p class="text-xs text-slate-500 font-semibold mb-1">Pending</p>
        <h3 class="text-3xl font-black text-amber-500"><?= ($statusCount['Pending']??0)+($statusCount['Proses']??0) ?></h3>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <p class="text-xs text-slate-500 font-semibold mb-1">Total Alokasi (kg)</p>
        <h3 class="text-3xl font-black text-primary"><?= number_format($totalAlokasi,0,',','.') ?></h3>
    </div>
</div>

<div class="hidden md:block bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-8">
    <table class="w-full text-left text-sm text-slate-600">
        <thead class="bg-slate-50 text-primary border-b border-slate-100">
            <tr><th class="p-4 font-semibold">Nama</th><th class="p-4 font-semibold">Desa / Kecamatan</th><th class="p-4 font-semibold">Luas (Ha)</th><th class="p-4 font-semibold">Alokasi</th><th class="p-4 font-semibold">Status</th><th class="p-4 font-semibold">Aksi</th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php if(empty($petani)): ?>
            <tr><td colspan="6" class="p-8 text-center text-slate-400">Belum ada data.</td></tr>
            <?php else: foreach($petani as $p): ?>
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="p-4 font-bold text-slate-800"><?= htmlspecialchars($p['nama']) ?></td>
                <td class="p-4">
                    <div class="font-medium"><?= htmlspecialchars($p['desa']) ?></div>
                    <div class="text-xs text-slate-400"><?= htmlspecialchars($p['kecamatan']??'-') ?></div>
                </td>
                <td class="p-4"><?= $p['luas_lahan'] ?></td>
                <td class="p-4 font-semibold text-primary"><?= $p['alokasi'] ?> kg</td>
                <td class="p-4">
                    <span class="px-3 py-1 text-xs font-bold rounded-full <?= $p['status']==='Terealisasi'?'bg-green-100 text-green-700':($p['status']==='Proses'?'bg-yellow-100 text-yellow-700':'bg-red-100 text-red-700') ?>">
                        <?= $p['status'] ?>
                    </span>
                </td>
                <td class="p-4">
                    <?php if($isAdmin): ?>
                    <button class="bg-slate-100 hover:bg-slate-200 text-primary px-3 py-1.5 rounded-lg text-xs font-medium mr-1 btn-edit-petani" data-id="<?= $p['id'] ?>"><i class="fas fa-edit"></i> Edit</button>
                    <button class="bg-red-50 hover:bg-red-100 text-red-600 px-3 py-1.5 rounded-lg text-xs font-medium btn-hapus-petani" data-id="<?= $p['id'] ?>"><i class="fas fa-trash"></i></button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div class="grid grid-cols-1 gap-4 md:hidden mb-8">
    <?php if(empty($petani)): ?>
    <div class="bg-white p-6 rounded-2xl text-center text-slate-400 text-sm">Belum ada data.</div>
    <?php else: foreach($petani as $p): ?>
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex justify-between items-start mb-3">
            <div>
                <h4 class="font-extrabold text-primary text-lg"><?= htmlspecialchars($p['nama']) ?></h4>
                <p class="text-xs text-slate-500 mt-1"><i class="fas fa-map-marker-alt text-slate-400 mr-1"></i> <?= htmlspecialchars($p['desa']) ?>, <?= htmlspecialchars($p['kecamatan']??'-') ?></p>
            </div>
            <span class="px-2.5 py-1 text-[10px] font-bold rounded-md <?= $p['status']==='Terealisasi'?'bg-green-100 text-green-700':($p['status']==='Proses'?'bg-yellow-100 text-yellow-700':'bg-red-100 text-red-700') ?>">
                <?= $p['status'] ?>
            </span>
        </div>
        
        <div class="flex gap-4 mb-4 bg-slate-50 p-3 rounded-xl border border-slate-100">
            <div class="flex-1">
                <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Luas Lahan</p>
                <p class="text-sm font-semibold text-slate-700"><?= $p['luas_lahan'] ?> Ha</p>
            </div>
            <div class="flex-1 border-l border-slate-200 pl-4">
                <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Alokasi Pupuk</p>
                <p class="text-sm font-bold text-primary"><?= $p['alokasi'] ?> kg</p>
            </div>
        </div>

        <?php if($isAdmin): ?>
        <div class="flex gap-2 border-t border-slate-100 pt-3">
            <button class="flex-1 bg-slate-100 hover:bg-slate-200 text-primary py-2 rounded-xl text-xs font-bold transition-colors btn-edit-petani" data-id="<?= $p['id'] ?>"><i class="fas fa-edit mr-1"></i> Edit Data</button>
            <button class="bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-xl text-xs font-bold transition-colors btn-hapus-petani" data-id="<?= $p['id'] ?>"><i class="fas fa-trash"></i></button>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; endif; ?>
</div>

<div class="bg-blue-50 border border-blue-100 rounded-2xl p-5 flex items-start gap-4">
    <i class="fas fa-info-circle text-blue-500 text-xl mt-0.5"></i>
    <div class="text-sm text-blue-800">
        <p class="font-bold mb-1">Keterangan Data:</p>
        <p class="text-blue-600/80 leading-relaxed">
            Data real-time disinkronisasi dengan BPS Domain <?= htmlspecialchars($domain) ?>. Update terakhir: <?= date('d F Y') ?>.
        </p>
    </div>
</div>