<?php
$isAdmin = ($_COOKIE['role']==='admin');

$products = [
  ['id'=>1,'emoji'=>'🌾','name'=>'Urea Bersubsidi','type'=>'Nitrogen Tinggi','spec'=>'46% N','price'=>2250,'stok'=>1500,'unit'=>'kg','change'=>2.1,'up'=>true,'sold'=>842,'rating'=>4.8,'seller'=>'Dinas Pertanian Kab. Gresik'],
  ['id'=>2,'emoji'=>'🌿','name'=>'NPK Phonska','type'=>'Majemuk Lengkap','spec'=>'15-15-15','price'=>2300,'stok'=>800,'unit'=>'kg','change'=>0.8,'up'=>true,'sold'=>621,'rating'=>4.9,'seller'=>'PT Petrokimia Gresik'],
  ['id'=>3,'emoji'=>'🌱','name'=>'SP-36','type'=>'Fosfat Tinggi','spec'=>'36% P₂O₅','price'=>1800,'stok'=>80,'unit'=>'kg','change'=>0.3,'up'=>false,'sold'=>310,'rating'=>4.6,'seller'=>'Dinas Pertanian Kab. Gresik'],
  ['id'=>4,'emoji'=>'🌻','name'=>'ZA','type'=>'Sulfur & Nitrogen','spec'=>'21% N + Sulfur','price'=>1700,'stok'=>0,'unit'=>'kg','change'=>1.2,'up'=>true,'sold'=>450,'rating'=>4.7,'seller'=>'PT Petrokimia Gresik'],
  ['id'=>5,'emoji'=>'🍀','name'=>'Petroganik','type'=>'Pupuk Organik','spec'=>'SNI 2803:2012','price'=>800,'stok'=>3200,'unit'=>'kg','change'=>0.5,'up'=>true,'sold'=>1203,'rating'=>4.5,'seller'=>'PT Petrokimia Gresik'],
  ['id'=>6,'emoji'=>'🌴','name'=>'NPK Pelangi','type'=>'Majemuk Khusus','spec'=>'20-10-10','price'=>2100,'stok'=>500,'unit'=>'kg','change'=>0.0,'up'=>true,'sold'=>287,'rating'=>4.4,'seller'=>'Kios Pupuk Kecamatan'],
];
?>

<!-- HEADER -->
<div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-6">
  <div>
    <h2 class="text-xl md:text-2xl font-bold text-primary flex items-center gap-2"><i class="fas fa-store"></i> Pasar Pupuk Digital</h2>
    <p class="text-xs text-slate-400 mt-1">Beli & jual pupuk langsung dari platform — harga transparan, stok terjamin</p>
  </div>
  <?php if($isAdmin): ?>
  <button class="bg-primary hover:bg-accent text-white px-5 py-2.5 rounded-full font-medium shadow-md flex items-center gap-2 text-sm transition-all">
    <i class="fas fa-plus"></i> Tambah Produk
  </button>
  <?php endif; ?>
</div>

<!-- HARGA TICKER -->
<div class="bg-gradient-to-r from-primary to-accent rounded-2xl p-4 mb-6 overflow-hidden">
  <div class="flex items-center gap-3">
    <span class="bg-secondary text-primary text-[9px] font-black px-2 py-0.5 rounded-full flex-shrink-0">LIVE</span>
    <div class="ticker-outer overflow-hidden flex-1">
      <div class="ticker-inner flex gap-8 animate-scroll whitespace-nowrap" style="animation:scrollTicker 20s linear infinite">
        <?php foreach($products as $p): ?>
        <span class="text-xs text-white/90">
          <strong class="text-white"><?= $p['name'] ?></strong>
          Rp <?= number_format($p['price'],0,',','.') ?>/kg
          <span class="<?= $p['up']?'text-green-300':'text-red-300' ?>"><?= $p['up']?'▲':'▼' ?><?= $p['change'] ?>%</span>
        </span>
        <?php endforeach; ?>
        <?php foreach($products as $p): ?>
        <span class="text-xs text-white/90">
          <strong class="text-white"><?= $p['name'] ?></strong>
          Rp <?= number_format($p['price'],0,',','.') ?>/kg
          <span class="<?= $p['up']?'text-green-300':'text-red-300' ?>"><?= $p['up']?'▲':'▼' ?><?= $p['change'] ?>%</span>
        </span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<style>@keyframes scrollTicker{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}</style>

<!-- FILTER BAR -->
<div class="flex gap-3 mb-6 flex-wrap items-center">
  <div class="relative flex-1 min-w-[200px]">
    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-sm"></i>
    <input type="text" placeholder="Cari pupuk..." id="mktSearch" oninput="filterProducts()"
           class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-full text-sm outline-none focus:border-primary transition-all">
  </div>
  <select onchange="sortProducts(this.value)" class="px-4 py-2.5 bg-white border border-slate-200 rounded-full text-sm outline-none focus:border-primary">
    <option value="">Urutkan</option>
    <option value="price-asc">Harga Terendah</option>
    <option value="price-desc">Harga Tertinggi</option>
    <option value="rating">Rating Terbaik</option>
    <option value="sold">Terlaris</option>
  </select>
  <button onclick="filterAvail(this)" class="px-4 py-2.5 bg-white border border-slate-200 rounded-full text-sm text-slate-600 hover:border-primary hover:text-primary transition-all">
    <i class="fas fa-check-circle mr-1"></i> Stok Tersedia
  </button>
</div>

<!-- PRODUCTS GRID -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5" id="productsGrid">
  <?php foreach($products as $p): ?>
  <?php $habis = $p['stok']===0; $menipis = $p['stok']>0 && $p['stok']<150; ?>
  <div class="product-card bg-white rounded-2xl border border-slate-100 overflow-hidden hover:shadow-lg hover:border-slate-200 transition-all group"
       data-name="<?= strtolower($p['name']) ?>" data-price="<?= $p['price'] ?>" data-rating="<?= $p['rating'] ?>" data-sold="<?= $p['sold'] ?>" data-stok="<?= $p['stok'] ?>">
    <!-- Card Top -->
    <div class="relative p-5 pb-3">
      <?php if($habis): ?>
      <div class="absolute top-3 right-3 bg-red-100 text-red-600 text-[9px] font-black px-2 py-1 rounded-lg uppercase">Habis</div>
      <?php elseif($menipis): ?>
      <div class="absolute top-3 right-3 bg-orange-100 text-orange-600 text-[9px] font-black px-2 py-1 rounded-lg uppercase animate-pulse">Segera Habis</div>
      <?php else: ?>
      <div class="absolute top-3 right-3 bg-emerald-50 text-emerald-600 text-[9px] font-black px-2 py-1 rounded-lg uppercase">Tersedia</div>
      <?php endif; ?>

      <div class="w-full h-28 rounded-xl bg-gradient-to-br from-primary/10 to-emerald-50 flex items-center justify-center text-5xl mb-4 group-hover:scale-105 transition-transform">
        <?= $p['emoji'] ?>
      </div>

      <div class="flex items-start justify-between gap-2">
        <div>
          <h3 class="font-extrabold text-slate-800 text-sm"><?= $p['name'] ?></h3>
          <p class="text-[11px] text-slate-400 mt-0.5"><?= $p['type'] ?> • <?= $p['spec'] ?></p>
        </div>
      </div>
    </div>

    <!-- Rating & Seller -->
    <div class="px-5 pb-3">
      <div class="flex items-center gap-1 text-[11px] text-slate-400">
        <i class="fas fa-star text-amber-400 text-[10px]"></i>
        <span class="font-bold text-slate-600"><?= $p['rating'] ?></span>
        <span>• <?= number_format($p['sold'],0,',','.') ?> terjual</span>
      </div>
      <p class="text-[10px] text-slate-400 mt-1 truncate"><i class="fas fa-store mr-1"></i><?= $p['seller'] ?></p>
    </div>

    <!-- Stok bar -->
    <div class="px-5 pb-3">
      <div class="flex justify-between text-[10px] text-slate-400 mb-1">
        <span>Stok: <strong class="text-slate-600"><?= number_format($p['stok'],0,',','.') ?> kg</strong></span>
        <?php if($menipis): ?><span class="text-orange-500 font-bold">⚠ Hampir habis</span><?php endif; ?>
      </div>
      <?php if($p['stok']>0): ?>
      <?php $maxStok=3200; $pct=min(100,round($p['stok']/$maxStok*100)); ?>
      <div class="h-1.5 bg-slate-100 rounded-full">
        <div class="h-1.5 rounded-full <?= $menipis?'bg-orange-400':($habis?'bg-red-300':'bg-emerald-400') ?>" style="width:<?= $pct ?>%"></div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Price & CTA -->
    <div class="px-5 pb-5">
      <div class="flex items-center justify-between mb-3">
        <div>
          <p class="text-[10px] text-slate-400">Harga/kg</p>
          <p class="text-xl font-extrabold text-primary">Rp <?= number_format($p['price'],0,',','.') ?></p>
        </div>
        <div class="text-right">
          <p class="text-[10px] <?= $p['up']?'text-emerald-600':'text-red-500' ?> font-bold">
            <?= $p['up']?'▲':'▼' ?> <?= $p['change'] ?>%
          </p>
          <p class="text-[9px] text-slate-400">vs kemarin</p>
        </div>
      </div>
      <div class="flex gap-2">
        <?php if(!$habis): ?>
        <button onclick="openOrder(<?= htmlspecialchars(json_encode($p)) ?>)"
                class="flex-1 bg-primary hover:bg-accent text-white text-xs font-bold py-2.5 rounded-xl transition-all shadow-sm hover:shadow-md">
          <i class="fas fa-cart-plus mr-1"></i> Pesan
        </button>
        <button class="w-10 h-10 border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 hover:border-primary hover:text-primary transition-all text-xs">
          <i class="fas fa-heart"></i>
        </button>
        <?php else: ?>
        <button class="flex-1 bg-slate-100 text-slate-400 text-xs font-bold py-2.5 rounded-xl cursor-not-allowed">Stok Habis</button>
        <button class="flex-1 border border-primary text-primary text-xs font-bold py-2.5 rounded-xl hover:bg-primary hover:text-white transition-all">
          <i class="fas fa-bell mr-1"></i> Notif Stok
        </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ORDER MODAL -->
<div id="orderModal" class="fixed inset-0 z-[9999] items-center justify-content-center p-4" style="display:none;background:rgba(0,0,0,.6);backdrop-filter:blur(4px)">
  <div class="bg-white rounded-3xl max-w-md w-full mx-auto p-6 shadow-2xl mt-20" id="orderBox">
    <div class="flex justify-between items-start mb-5">
      <div>
        <h3 class="text-lg font-extrabold text-slate-800" id="orderTitle">Pesan Pupuk</h3>
        <p class="text-xs text-slate-400" id="orderSub">Isi detail pesanan Anda</p>
      </div>
      <button onclick="closeOrder()" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-red-50 hover:text-red-500"><i class="fas fa-times text-xs"></i></button>
    </div>
    <div class="bg-slate-50 rounded-2xl p-4 mb-5 flex items-center gap-3">
      <div class="text-3xl" id="orderEmoji">🌾</div>
      <div><p class="font-bold text-slate-800 text-sm" id="orderName">Urea Bersubsidi</p><p class="text-xs text-slate-400" id="orderPrice">Rp 2.250/kg</p></div>
    </div>
    <div class="space-y-4">
      <div>
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wide block mb-1.5">Jumlah (kg)</label>
        <div class="flex items-center gap-2">
          <button onclick="changeQty(-50)" class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 font-bold hover:bg-primary hover:text-white transition-all">-</button>
          <input type="number" id="orderQty" value="100" min="50" step="50" class="flex-1 text-center py-2.5 border border-slate-200 rounded-xl font-bold text-primary outline-none focus:border-primary">
          <button onclick="changeQty(50)" class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 font-bold hover:bg-primary hover:text-white transition-all">+</button>
        </div>
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wide block mb-1.5">Alamat Pengiriman</label>
        <input type="text" placeholder="Desa / Kecamatan / Kabupaten..." class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary">
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wide block mb-1.5">Catatan</label>
        <textarea placeholder="Catatan tambahan..." class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary h-20 resize-none"></textarea>
      </div>
    </div>
    <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-3 mt-4 flex justify-between items-center">
      <span class="text-sm font-medium text-slate-600">Total Estimasi</span>
      <span class="text-lg font-extrabold text-primary" id="orderTotal">Rp 225.000</span>
    </div>
    <button onclick="submitOrder()" class="w-full bg-primary hover:bg-accent text-white font-bold py-3 rounded-2xl mt-4 transition-all shadow-md hover:shadow-lg">
      <i class="fas fa-check mr-2"></i> Konfirmasi Pesanan
    </button>
  </div>
</div>

<script>
let currentPrice=0;
function openOrder(p){
  currentPrice=p.price;
  document.getElementById('orderTitle').textContent='Pesan '+p.name;
  document.getElementById('orderEmoji').textContent=p.emoji;
  document.getElementById('orderName').textContent=p.name;
  document.getElementById('orderPrice').textContent='Rp '+p.price.toLocaleString('id')+'/kg';
  updateTotal();
  document.getElementById('orderModal').style.display='flex';
}
function closeOrder(){document.getElementById('orderModal').style.display='none';}
function changeQty(d){
  const inp=document.getElementById('orderQty');
  inp.value=Math.max(50,parseInt(inp.value||100)+d);
  updateTotal();
}
function updateTotal(){
  const qty=parseInt(document.getElementById('orderQty').value)||0;
  document.getElementById('orderTotal').textContent='Rp '+(qty*currentPrice).toLocaleString('id');
}
document.getElementById('orderQty').addEventListener('input',updateTotal);
function submitOrder(){
  alert('Pesanan berhasil dikirim! Petugas akan menghubungi Anda segera.');
  closeOrder();
}
function filterProducts(){
  const q=document.getElementById('mktSearch').value.toLowerCase();
  document.querySelectorAll('.product-card').forEach(c=>{c.style.display=c.dataset.name.includes(q)?'':'none';});
}
function sortProducts(v){
  const grid=document.getElementById('productsGrid');
  const cards=[...grid.querySelectorAll('.product-card')];
  if(v==='price-asc') cards.sort((a,b)=>a.dataset.price-b.dataset.price);
  else if(v==='price-desc') cards.sort((a,b)=>b.dataset.price-a.dataset.price);
  else if(v==='rating') cards.sort((a,b)=>b.dataset.rating-a.dataset.rating);
  else if(v==='sold') cards.sort((a,b)=>b.dataset.sold-a.dataset.sold);
  cards.forEach(c=>grid.appendChild(c));
}
function filterAvail(btn){
  const active=btn.classList.toggle('bg-primary');
  btn.classList.toggle('text-white',active);
  btn.classList.toggle('border-primary',active);
  document.querySelectorAll('.product-card').forEach(c=>{
    c.style.display=(!active||parseInt(c.dataset.stok)>0)?'':'none';
  });
}
document.getElementById('orderModal').addEventListener('click',function(e){if(e.target===this)closeOrder();});
</script>
