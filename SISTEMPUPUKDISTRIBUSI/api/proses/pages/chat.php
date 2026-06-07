<?php
$myName = $_COOKIE['nama'] ?? 'Petugas';
$isAdmin = ($_COOKIE['role']==='admin');

$contacts = [
  ['id'=>1,'name'=>'Bpk. Suyono','role'=>'Petani','avatar'=>'SY','unread'=>2,'last'=>'Kapan pupuk Urea kami dikirim?','time'=>'2m','color'=>'#7ec88a'],
  ['id'=>2,'name'=>'Gapoktan Makmur','role'=>'Kelompok Tani','avatar'=>'GM','unread'=>0,'last'=>'Terima kasih Pak sudah dikirim','time'=>'1j','color'=>'#60a5fa'],
  ['id'=>3,'name'=>'Bpk. Wahyudi','role'=>'Petani','avatar'=>'WY','unread'=>1,'last'=>'Ada stok NPK tidak Pak?','time'=>'3j','color'=>'#f5c842'],
  ['id'=>4,'name'=>'Kel. Tani Sejahtera','role'=>'Kelompok Tani','avatar'=>'KT','unread'=>0,'last'=>'Laporan distribusi sudah kami..','time'=>'Kmrn','color'=>'#a78bfa'],
  ['id'=>5,'name'=>'Bpk. Ahmad','role'=>'Petani','avatar'=>'AM','unread'=>0,'last'=>'Siap Pak, kami tunggu','time'=>'2h','color'=>'#fb923c'],
];

$messages = [
  ['from'=>'other','name'=>'Bpk. Suyono','text'=>'Selamat pagi Pak, mau tanya soal distribusi pupuk Urea kami','time'=>'08:30'],
  ['from'=>'other','name'=>'Bpk. Suyono','text'=>'Kami sudah daftar sejak bulan lalu tapi belum dapat jadwal','time'=>'08:31'],
  ['from'=>'me','name'=>$myName,'text'=>'Selamat pagi Bpk. Suyono, kami sedang proses jadwal distribusi untuk wilayah Anda','time'=>'08:45'],
  ['from'=>'me','name'=>$myName,'text'=>'Rencananya minggu ini akan kami distribusikan. Mohon bersiap ya Pak','time'=>'08:46'],
  ['from'=>'other','name'=>'Bpk. Suyono','text'=>'Alhamdulillah, terima kasih Pak. Kami siap menerima','time'=>'09:00'],
  ['from'=>'other','name'=>'Bpk. Suyono','text'=>'Kapan pupuk Urea kami dikirim Pak? Hari ini bisa?','time'=>'10:15'],
];
?>

<div class="flex h-[calc(100vh-200px)] md:h-[600px] bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">

  <!-- CONTACT LIST -->
  <div class="w-72 flex-shrink-0 border-r border-slate-100 flex flex-col hidden md:flex">
    <div class="p-4 border-b border-slate-100">
      <h3 class="font-bold text-primary text-sm mb-3 flex items-center gap-2"><i class="fas fa-comments"></i> Pesan</h3>
      <div class="relative">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-xs"></i>
        <input type="text" placeholder="Cari kontak..." class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-100 rounded-xl text-xs outline-none focus:border-primary transition-all">
      </div>
    </div>
    <div class="flex-1 overflow-y-auto">
      <?php foreach($contacts as $c): ?>
      <div class="contact-item flex items-center gap-3 p-4 hover:bg-slate-50 cursor-pointer border-b border-slate-50 transition-all <?= $c['id']===1?'bg-primary/5 border-l-2 border-l-primary':'' ?>"
           onclick="loadChat(<?= $c['id'] ?>,this)">
        <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center font-bold text-xs text-white" style="background:<?= $c['color'] ?>"><?= $c['avatar'] ?></div>
        <div class="flex-1 min-w-0">
          <div class="flex justify-between items-center">
            <p class="font-bold text-slate-800 text-xs truncate"><?= $c['name'] ?></p>
            <span class="text-[10px] text-slate-400"><?= $c['time'] ?></span>
          </div>
          <div class="flex justify-between items-center mt-0.5">
            <p class="text-[11px] text-slate-400 truncate"><?= $c['last'] ?></p>
            <?php if($c['unread']>0): ?>
            <span class="bg-primary text-white text-[9px] font-black px-1.5 py-0.5 rounded-full ml-1"><?= $c['unread'] ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- CHAT AREA -->
  <div class="flex-1 flex flex-col">
    <!-- Chat header -->
    <div class="flex items-center gap-3 p-4 border-b border-slate-100 bg-slate-50/50">
      <div class="w-9 h-9 rounded-full bg-emerald-400 flex items-center justify-center text-white font-bold text-xs">SY</div>
      <div>
        <p class="font-bold text-slate-800 text-sm">Bpk. Suyono</p>
        <p class="text-[11px] text-emerald-500 flex items-center gap-1"><span class="w-1.5 h-1.5 bg-emerald-400 rounded-full"></span> Online</p>
      </div>
      <div class="ml-auto flex gap-2">
        <button class="w-8 h-8 rounded-lg bg-white border border-slate-100 flex items-center justify-center text-slate-400 hover:text-primary hover:border-primary transition-all text-xs">
          <i class="fas fa-phone"></i>
        </button>
        <button class="w-8 h-8 rounded-lg bg-white border border-slate-100 flex items-center justify-center text-slate-400 hover:text-primary hover:border-primary transition-all text-xs">
          <i class="fas fa-ellipsis-v"></i>
        </button>
      </div>
    </div>

    <!-- Messages -->
    <div class="flex-1 overflow-y-auto p-4 space-y-3" id="chatMessages">
      <div class="text-center"><span class="bg-slate-100 text-slate-400 text-[10px] px-3 py-1 rounded-full">Hari ini</span></div>
      <?php foreach($messages as $m): ?>
        <?php if($m['from']==='me'): ?>
        <div class="flex justify-end">
          <div class="max-w-[75%]">
            <div class="bg-primary text-white text-sm px-4 py-2.5 rounded-2xl rounded-tr-sm shadow-sm"><?= $m['text'] ?></div>
            <p class="text-[10px] text-slate-300 text-right mt-1"><?= $m['time'] ?> <i class="fas fa-check-double text-primary/50"></i></p>
          </div>
        </div>
        <?php else: ?>
        <div class="flex gap-2 items-end">
          <div class="w-7 h-7 rounded-full bg-emerald-400 flex-shrink-0 flex items-center justify-center text-white text-[10px] font-bold">SY</div>
          <div class="max-w-[75%]">
            <div class="bg-slate-100 text-slate-800 text-sm px-4 py-2.5 rounded-2xl rounded-tl-sm"><?= $m['text'] ?></div>
            <p class="text-[10px] text-slate-300 mt-1"><?= $m['time'] ?></p>
          </div>
        </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <!-- Input -->
    <div class="p-4 border-t border-slate-100">
      <!-- Quick replies -->
      <div class="flex gap-2 mb-3 flex-wrap">
        <button onclick="quickReply(this)" class="text-[10px] bg-slate-100 hover:bg-primary hover:text-white text-slate-600 px-3 py-1.5 rounded-full transition-all">✅ Siap dikirim hari ini</button>
        <button onclick="quickReply(this)" class="text-[10px] bg-slate-100 hover:bg-primary hover:text-white text-slate-600 px-3 py-1.5 rounded-full transition-all">📅 Jadwal besok</button>
        <button onclick="quickReply(this)" class="text-[10px] bg-slate-100 hover:bg-primary hover:text-white text-slate-600 px-3 py-1.5 rounded-full transition-all">⏳ Sedang diproses</button>
      </div>
      <div class="flex gap-2 items-center">
        <div class="flex-1 flex items-center bg-slate-50 border border-slate-200 rounded-2xl px-4 gap-2 focus-within:border-primary transition-all">
          <input type="text" id="chatInput" placeholder="Tulis pesan..." class="flex-1 py-3 text-sm bg-transparent outline-none text-slate-700 placeholder-slate-400">
          <button class="text-slate-300 hover:text-primary"><i class="fas fa-paperclip"></i></button>
        </div>
        <button onclick="sendMsg()" class="w-11 h-11 bg-primary hover:bg-accent rounded-2xl flex items-center justify-center text-white transition-all shadow-md hover:shadow-lg">
          <i class="fas fa-paper-plane text-sm"></i>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function sendMsg(){
  const inp=document.getElementById('chatInput');
  const txt=inp.value.trim();
  if(!txt) return;
  const msgs=document.getElementById('chatMessages');
  const now=new Date().toLocaleTimeString('id',{hour:'2-digit',minute:'2-digit'});
  msgs.innerHTML+=`<div class="flex justify-end"><div class="max-w-[75%]"><div class="bg-primary text-white text-sm px-4 py-2.5 rounded-2xl rounded-tr-sm shadow-sm">${txt}</div><p class="text-[10px] text-slate-300 text-right mt-1">${now} <i class="fas fa-check-double text-primary/50"></i></p></div></div>`;
  inp.value='';
  msgs.scrollTop=msgs.scrollHeight;
}
function quickReply(btn){
  document.getElementById('chatInput').value=btn.textContent.trim();
  document.getElementById('chatInput').focus();
}
document.getElementById('chatInput').addEventListener('keydown',e=>{ if(e.key==='Enter') sendMsg(); });
document.getElementById('chatMessages').scrollTop=99999;
function loadChat(id,el){
  document.querySelectorAll('.contact-item').forEach(c=>c.classList.remove('bg-primary/5','border-l-2','border-l-primary'));
  el.classList.add('bg-primary/5','border-l-2','border-l-primary');
}
</script>
