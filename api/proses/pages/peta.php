<?php
require_once __DIR__ . '/bps_widget.php';
$wilayah = bps_active_wilayah();
?>
<?= bps_wilayah_badge() ?>

<div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-6">
  <div>
    <h2 class="text-xl md:text-2xl font-bold text-primary flex items-center gap-2"><i class="fas fa-map-location-dot"></i> Peta Distribusi Live</h2>
    <p class="text-xs text-slate-400 mt-1">Tracking real-time pengiriman pupuk ke seluruh wilayah</p>
  </div>
  <div class="flex gap-2">
    <div class="bg-white border border-slate-100 rounded-full px-3 py-2 flex items-center gap-2 text-xs">
      <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
      <span class="font-semibold text-slate-600">4 Pengiriman Aktif</span>
    </div>
  </div>
</div>

<!-- STATS ROW -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 text-center">
    <div class="w-8 h-8 bg-green-50 rounded-xl flex items-center justify-center mx-auto mb-2"><i class="fas fa-truck text-green-500 text-sm"></i></div>
    <p class="text-2xl font-black text-primary">4</p>
    <p class="text-[10px] text-slate-400 uppercase font-semibold">Sedang Jalan</p>
  </div>
  <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 text-center">
    <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center mx-auto mb-2"><i class="fas fa-map-pin text-blue-500 text-sm"></i></div>
    <p class="text-2xl font-black text-primary">12</p>
    <p class="text-[10px] text-slate-400 uppercase font-semibold">Lokasi Tujuan</p>
  </div>
  <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 text-center">
    <div class="w-8 h-8 bg-emerald-50 rounded-xl flex items-center justify-center mx-auto mb-2"><i class="fas fa-check-circle text-emerald-500 text-sm"></i></div>
    <p class="text-2xl font-black text-primary">28</p>
    <p class="text-[10px] text-slate-400 uppercase font-semibold">Selesai Hari Ini</p>
  </div>
  <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 text-center">
    <div class="w-8 h-8 bg-amber-50 rounded-xl flex items-center justify-center mx-auto mb-2"><i class="fas fa-clock text-amber-500 text-sm"></i></div>
    <p class="text-2xl font-black text-amber-500">3</p>
    <p class="text-[10px] text-slate-400 uppercase font-semibold">Terlambat</p>
  </div>
</div>

<div class="flex gap-4 flex-col md:flex-row">
  <!-- MAP -->
  <div class="flex-1 bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden" style="min-height:420px">
    <div id="liveMap" style="width:100%;height:420px"></div>
  </div>

  <!-- SIDEBAR pengiriman aktif -->
  <div class="w-full md:w-72 flex flex-col gap-3">
    <h3 class="font-bold text-slate-700 text-sm flex items-center gap-2"><span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span> Pengiriman Aktif</h3>

    <?php
    $deliveries = [
      ['id'=>'DO-2024','name'=>'Kel. Tani Makmur','pupuk'=>'Urea','kg'=>500,'dari'=>'Gudang Gresik','status'=>'Dalam Perjalanan','pct'=>65,'color'=>'#7ec88a','lat'=>-7.1568,'lng'=>112.6508],
      ['id'=>'DO-2023','name'=>'Gapoktan Maju','pupuk'=>'NPK','kg'=>1200,'dari'=>'Gudang Surabaya','status'=>'Hampir Tiba','pct'=>88,'color'=>'#60a5fa','lat'=>-7.2458,'lng'=>112.7383],
      ['id'=>'DO-2022','name'=>'Kel. Sido Mukti','pupuk'=>'SP-36','kg'=>300,'dari'=>'Gudang Gresik','status'=>'Baru Berangkat','pct'=>15,'color'=>'#fb923c','lat'=>-7.0511,'lng'=>112.5122],
      ['id'=>'DO-2021','name'=>'Poktan Sejahtera','pupuk'=>'ZA','kg'=>800,'dari'=>'Gudang Lamongan','status'=>'Dalam Perjalanan','pct'=>42,'color'=>'#a78bfa','lat'=>-7.1213,'lng'=>112.4221],
    ];
    ?>

    <?php foreach($deliveries as $d): ?>
    <div class="bg-white rounded-2xl border border-slate-100 p-4 hover:shadow-md transition-all cursor-pointer hover:border-slate-200"
         onclick="focusMarker(<?= $d['lat'] ?>,<?= $d['lng'] ?>,'<?= $d['name'] ?>')">
      <div class="flex justify-between items-start mb-2">
        <div>
          <p class="font-bold text-slate-800 text-xs"><?= $d['name'] ?></p>
          <p class="text-[10px] text-slate-400"><?= $d['dari'] ?> → Tujuan</p>
        </div>
        <span class="text-[9px] font-black px-2 py-0.5 rounded-full" style="background:<?= $d['color'] ?>20;color:<?= $d['color'] ?>"><?= $d['pupuk'] ?></span>
      </div>
      <div class="flex justify-between text-[10px] text-slate-400 mb-1.5">
        <span><?= $d['status'] ?></span>
        <span class="font-bold" style="color:<?= $d['color'] ?>"><?= $d['pct'] ?>%</span>
      </div>
      <div class="h-1.5 bg-slate-100 rounded-full">
        <div class="h-1.5 rounded-full transition-all" style="width:<?= $d['pct'] ?>%;background:<?= $d['color'] ?>"></div>
      </div>
      <div class="flex justify-between mt-2">
        <span class="text-[10px] text-slate-400"><i class="fas fa-box mr-1"></i><?= number_format($d['kg'],0,',','.') ?> kg</span>
        <span class="text-[10px] font-mono text-slate-500"><?= $d['id'] ?></span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const map = L.map('liveMap').setView([-7.15, 112.65], 10);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
  attribution:'© OpenStreetMap contributors', maxZoom:18
}).addTo(map);

const deliveries = [
  {lat:-7.1568,lng:112.6508,name:'Kel. Tani Makmur',pupuk:'Urea 500kg',status:'Dalam Perjalanan',color:'#7ec88a',pct:65},
  {lat:-7.2458,lng:112.7383,name:'Gapoktan Maju',pupuk:'NPK 1200kg',status:'Hampir Tiba',color:'#60a5fa',pct:88},
  {lat:-7.0511,lng:112.5122,name:'Kel. Sido Mukti',pupuk:'SP-36 300kg',status:'Baru Berangkat',color:'#fb923c',pct:15},
  {lat:-7.1213,lng:112.4221,name:'Poktan Sejahtera',pupuk:'ZA 800kg',status:'Dalam Perjalanan',color:'#a78bfa',pct:42},
];

const markers = [];
deliveries.forEach(d=>{
  const icon = L.divIcon({
    className:'',
    html:`<div style="background:${d.color};width:32px;height:32px;border-radius:50%;border:3px solid white;box-shadow:0 3px 10px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;animation:pulse 2s infinite">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="white"><path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.7 1.3 3 3 3s3-1.3 3-3h6c0 1.7 1.3 3 3 3s3-1.3 3-3h2v-5l-3-4zm-5 7.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm-3.5-7l1.96 2.5H11V8.5h1.5zm-6 7.5c-.83 0-1.5-.67-1.5-1.5S4.67 14 5.5 14s1.5.67 1.5 1.5S6.33 16 5.5 16z"/></svg>
    </div>`,
    iconSize:[32,32], iconAnchor:[16,16]
  });
  const m = L.marker([d.lat,d.lng],{icon}).addTo(map);
  m.bindPopup(`<div style="min-width:180px"><strong style="font-size:13px">${d.name}</strong><br><small style="color:#64748b">${d.pupuk}</small><br><div style="margin-top:6px;background:#f1f5f9;border-radius:8px;height:6px"><div style="background:${d.color};height:6px;border-radius:8px;width:${d.pct}%"></div></div><small style="color:${d.color};font-weight:700">${d.pct}% — ${d.status}</small></div>`);
  markers.push({m,lat:d.lat,lng:d.lng});
});

// Animate truck movement (demo)
let tick=0;
setInterval(()=>{
  tick++;
  markers.forEach((mk,i)=>{
    const offset=Math.sin(tick*0.05+i)*0.002;
    mk.lat+=offset*0.01;
    mk.lng+=offset*0.008;
    mk.m.setLatLng([mk.lat,mk.lng]);
  });
},2000);

function focusMarker(lat,lng,name){
  map.flyTo([lat,lng],13,{duration:1});
  markers.forEach(mk=>{
    if(Math.abs(mk.lat-lat)<0.01&&Math.abs(mk.lng-lng)<0.01) mk.m.openPopup();
  });
}
</script>
