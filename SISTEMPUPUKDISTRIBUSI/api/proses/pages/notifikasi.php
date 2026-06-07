<?php
$isAdmin = ($_COOKIE['role']==='admin');

// Simulasi data notifikasi (dalam implementasi nyata dari tabel DB)
$notifikasi = [
  ['id'=>1,'type'=>'arrived','icon'=>'fa-truck','color'=>'#7ec88a','bg'=>'rgba(126,200,138,.12)',
   'title'=>'Pupuk Urea Sudah Tiba','msg'=>'Kiriman 500 kg Urea ke Kel. Tani Makmur Desa Sidoarjo telah dikonfirmasi tiba.','time'=>'2 menit lalu','read'=>false],
  ['id'=>2,'type'=>'price','icon'=>'fa-tag','color'=>'#f5c842','bg'=>'rgba(245,200,66,.12)',
   'title'=>'Update Harga NPK Phonska','msg'=>'Harga NPK Phonska naik 0.8% menjadi Rp 2.300/kg. Berlaku mulai hari ini.','time'=>'15 menit lalu','read'=>false],
  ['id'=>3,'type'=>'low_stock','icon'=>'fa-triangle-exclamation','color'=>'#fb923c','bg'=>'rgba(251,146,60,.12)',
   'title'=>'Stok ZA Hampir Habis','msg'=>'Stok pupuk ZA gudang Gresik tersisa 120 kg (< 200 kg threshold). Segera lakukan pengisian.','time'=>'1 jam lalu','read'=>false],
  ['id'=>4,'type'=>'chat','icon'=>'fa-comment','color'=>'#60a5fa','bg'=>'rgba(96,165,250,.12)',
   'title'=>'Permintaan dari Bpk. Suyono','msg'=>'Petani Suyono (Kel. Sido Makmur) mengirim permintaan: "Kapan pupuk Urea kami dikirim Pak?"','time'=>'2 jam lalu','read'=>true],
  ['id'=>5,'type'=>'arrived','icon'=>'fa-truck','color'=>'#7ec88a','bg'=>'rgba(126,200,138,.12)',
   'title'=>'NPK Phonska Tiba di Tujuan','msg'=>'Distribusi 1.200 kg NPK Phonska ke Gapoktan Maju Bersama telah selesai.','time'=>'3 jam lalu','read'=>true],
  ['id'=>6,'type'=>'price','icon'=>'fa-chart-line','color'=>'#a78bfa','bg'=>'rgba(167,139,250,.12)',
   'title'=>'Laporan Bulanan Tersedia','msg'=>'Laporan realisasi distribusi bulan Mei 2026 sudah dapat diunduh.','time'=>'5 jam lalu','read'=>true],
  ['id'=>7,'type'=>'low_stock','icon'=>'fa-triangle-exclamation','color'=>'#f87171','bg'=>'rgba(248,113,113,.12)',
   'title'=>'Alert: SP-36 Stok Kritis','msg'=>'Stok SP-36 gudang Surabaya hanya tersisa 80 kg! Di bawah batas minimum 150 kg.','time'=>'Kemarin','read'=>true],
];

$unread = count(array_filter($notifikasi, fn($n)=>!$n['read']));
?>

<div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-6">
  <h2 class="text-xl md:text-2xl font-bold text-primary flex items-center gap-3">
    <i class="fas fa-bell"></i> Notifikasi
    <?php if($unread>0): ?>
    <span class="bg-red-500 text-white text-xs font-black px-2.5 py-1 rounded-full animate-pulse"><?= $unread ?></span>
    <?php endif; ?>
  </h2>
  <div class="flex gap-2">
    <button onclick="filterNotif('all')" id="btn-all" class="notif-filter-btn active px-4 py-2 rounded-full text-xs font-bold border transition-all">Semua</button>
    <button onclick="filterNotif('arrived')" id="btn-arrived" class="notif-filter-btn px-4 py-2 rounded-full text-xs font-bold border border-slate-200 text-slate-500 hover:border-primary hover:text-primary transition-all">Pengiriman</button>
    <button onclick="filterNotif('price')" id="btn-price" class="notif-filter-btn px-4 py-2 rounded-full text-xs font-bold border border-slate-200 text-slate-500 hover:border-primary hover:text-primary transition-all">Harga</button>
    <button onclick="filterNotif('low_stock')" id="btn-low_stock" class="notif-filter-btn px-4 py-2 rounded-full text-xs font-bold border border-slate-200 text-slate-500 hover:border-primary hover:text-primary transition-all">Stok</button>
  </div>
</div>

<!-- STOK ALERT BANNER -->
<div class="bg-gradient-to-r from-orange-50 to-red-50 border border-orange-200 rounded-2xl p-5 mb-6 flex items-start gap-4">
  <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
    <i class="fas fa-triangle-exclamation text-orange-500"></i>
  </div>
  <div class="flex-1">
    <p class="font-bold text-orange-800 text-sm mb-1">⚠️ Alert Stok Menipis — 2 Item Perlu Perhatian</p>
    <div class="flex flex-wrap gap-3 mt-2">
      <div class="bg-white border border-orange-200 rounded-xl px-3 py-2 text-xs">
        <span class="font-bold text-red-600">ZA</span> — Sisa <strong>120 kg</strong> <span class="text-slate-400">(min. 200 kg)</span>
        <div class="mt-1 bg-slate-100 rounded h-1.5"><div class="h-1.5 rounded bg-red-400" style="width:60%"></div></div>
      </div>
      <div class="bg-white border border-orange-200 rounded-xl px-3 py-2 text-xs">
        <span class="font-bold text-orange-600">SP-36</span> — Sisa <strong>80 kg</strong> <span class="text-slate-400">(min. 150 kg)</span>
        <div class="mt-1 bg-slate-100 rounded h-1.5"><div class="h-1.5 rounded bg-orange-400" style="width:53%"></div></div>
      </div>
    </div>
  </div>
</div>

<!-- NOTIFIKASI LIST -->
<div class="space-y-3" id="notifList">
  <?php foreach($notifikasi as $n): ?>
  <div class="notif-card bg-white rounded-2xl border border-slate-100 p-5 flex gap-4 items-start transition-all hover:shadow-md hover:border-slate-200 cursor-pointer <?= !$n['read']?'border-l-4 border-l-primary':'' ?>"
       data-type="<?= $n['type'] ?>"
       onclick="markRead(this)">
    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background:<?= $n['bg'] ?>">
      <i class="fas <?= $n['icon'] ?>" style="color:<?= $n['color'] ?>"></i>
    </div>
    <div class="flex-1 min-w-0">
      <div class="flex justify-between items-start gap-2">
        <p class="font-bold text-slate-800 text-sm <?= !$n['read']?'':'font-semibold text-slate-600' ?>"><?= $n['title'] ?></p>
        <?php if(!$n['read']): ?>
        <div class="w-2.5 h-2.5 bg-primary rounded-full flex-shrink-0 mt-1"></div>
        <?php endif; ?>
      </div>
      <p class="text-xs text-slate-500 mt-1 leading-relaxed"><?= $n['msg'] ?></p>
      <p class="text-[11px] text-slate-300 mt-2 flex items-center gap-1"><i class="fas fa-clock"></i> <?= $n['time'] ?></p>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- EMPTY STATE (hidden by default) -->
<div id="emptyState" class="hidden text-center py-16">
  <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
    <i class="fas fa-bell-slash text-slate-300 text-2xl"></i>
  </div>
  <p class="text-slate-400 font-medium">Tidak ada notifikasi</p>
</div>

<script>
function filterNotif(type){
  document.querySelectorAll('.notif-filter-btn').forEach(b=>{
    b.classList.remove('active','bg-primary','text-white','border-primary');
    b.classList.add('border-slate-200','text-slate-500');
  });
  const btn = document.getElementById('btn-'+type);
  btn.classList.add('active','bg-primary','text-white','border-primary');
  btn.classList.remove('border-slate-200','text-slate-500');

  const cards = document.querySelectorAll('.notif-card');
  let visible = 0;
  cards.forEach(c=>{
    const show = type==='all'||c.dataset.type===type;
    c.style.display = show?'flex':'none';
    if(show) visible++;
  });
  document.getElementById('emptyState').classList.toggle('hidden', visible>0);
}
function markRead(el){
  el.classList.remove('border-l-4','border-l-primary');
  const dot = el.querySelector('.bg-primary.rounded-full');
  if(dot) dot.remove();
}
// init active style
document.getElementById('btn-all').classList.add('bg-primary','text-white','border-primary');
document.getElementById('btn-all').classList.remove('border-slate-200','text-slate-500');
</script>
