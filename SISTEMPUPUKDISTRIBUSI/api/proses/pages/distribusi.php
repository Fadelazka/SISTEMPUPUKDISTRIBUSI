<?php
require_once __DIR__ . '/bps_widget.php';
$isAdmin = ($_COOKIE['role']==='admin');
$where   = filter_where($koneksi);
$query = mysqli_query($koneksi,"SELECT * FROM distribusi WHERE $where ORDER BY tgl DESC");
$distribusi=[]; while($r=mysqli_fetch_assoc($query)) $distribusi[]=$r;
?>

<?= bps_wilayah_badge() ?>

<div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-6">
    <h2 class="text-xl md:text-2xl font-bold text-primary flex items-center gap-2"><i class="fas fa-truck"></i> Log Distribusi</h2>
    <?php if($isAdmin): ?>
    <button id="tambahDistribusiBtn" class="bg-primary hover:bg-accent text-white px-5 py-2.5 rounded-full font-medium shadow-md flex items-center gap-2 text-sm transition-all"><i class="fas fa-plus"></i> Tambah Log</button>
    <?php endif; ?>
</div>

<div class="grid grid-cols-1 gap-4 md:hidden mb-8">
    <?php if(empty($distribusi)): ?>
    <div class="bg-white p-6 rounded-2xl text-center text-slate-400 text-sm font-medium">Belum ada data distribusi.</div>
    <?php else: foreach($distribusi as $d): ?>
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex justify-between items-center mb-3">
            <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-1 rounded"><?= date('d M Y',strtotime($d['tgl'])) ?></span>
            <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-1 rounded-full uppercase tracking-tighter"><?= htmlspecialchars($d['pupuk']) ?></span>
        </div>
        <h4 class="font-extrabold text-primary text-lg mb-1 leading-tight"><?= htmlspecialchars($d['kelompok']) ?></h4>
        <p class="text-xs text-slate-500 mb-4 flex items-center gap-1"><i class="fas fa-map-pin text-slate-300"></i> <?= htmlspecialchars($d['tujuan']) ?></p>
        
        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 flex justify-between items-center">
            <div><p class="text-[9px] uppercase font-bold text-slate-400">Jumlah</p><p class="text-sm font-black text-primary"><?= htmlspecialchars($d['jumlah']) ?></p></div>
            <div class="text-right"><p class="text-[9px] uppercase font-bold text-slate-400">No. DO</p><p class="text-[11px] font-mono font-bold text-slate-600"><?= htmlspecialchars($d['no_do']) ?></p></div>
        </div>

        <?php if($isAdmin): ?>
        <div class="flex gap-2 mt-4 pt-3 border-t border-slate-100">
            <button class="flex-1 bg-slate-100 text-primary py-2 rounded-lg text-xs font-bold btn-edit-distribusi" data-id="<?= $d['id'] ?>"><i class="fas fa-edit mr-1"></i> Edit</button>
            <button class="bg-red-50 text-red-600 px-4 py-2 rounded-lg text-xs font-bold btn-hapus-distribusi" data-id="<?= $d['id'] ?>"><i class="fas fa-trash"></i></button>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; endif; ?>
</div>

<div class="hidden md:block bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-8">
    <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-primary border-b border-slate-100">
            <tr><th class="p-4 font-bold">Tanggal</th><th class="p-4 font-bold">Kelompok Tani</th><th class="p-4 font-bold">Jenis</th><th class="p-4 font-bold">Jumlah</th><th class="p-4 font-bold">Tujuan</th><th class="p-4 font-bold text-center">Aksi</th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php foreach($distribusi as $d): ?>
            <tr class="hover:bg-slate-50">
                <td class="p-4 text-slate-500 font-medium"><?= date('d/m/Y',strtotime($d['tgl'])) ?></td>
                <td class="p-4 font-bold text-slate-800"><?= htmlspecialchars($d['kelompok']) ?></td>
                <td class="p-4"><span class="bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-md text-[10px] font-black uppercase"><?= htmlspecialchars($d['pupuk']) ?></span></td>
                <td class="p-4 font-bold text-primary"><?= htmlspecialchars($d['jumlah']) ?></td>
                <td class="p-4 text-slate-600"><?= htmlspecialchars($d['tujuan']) ?></td>
                <td class="p-4 text-center">
                    <?php if($isAdmin): ?>
                    <button class="text-primary hover:bg-slate-100 p-2 rounded-lg btn-edit-distribusi" data-id="<?= $d['id'] ?>"><i class="fas fa-edit"></i></button>
                    <button class="text-red-500 hover:bg-red-50 p-2 rounded-lg btn-hapus-distribusi" data-id="<?= $d['id'] ?>"><i class="fas fa-trash"></i></button>
                    <?php else: ?>-<?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>