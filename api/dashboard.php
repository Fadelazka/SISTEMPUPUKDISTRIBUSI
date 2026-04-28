<?php
session_start();
require __DIR__ . '/service/koneksi.php';
if (!isset($_COOKIE['id'])) { header("Location: /api/login.php"); exit(); }
$role     = $_COOKIE['role'];
$userName = $_COOKIE['nama'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard | Sistem Distribusi Pupuk Subsidi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: '#1e4a3b', secondary: '#f5e7a4',
                        accent: '#2d6a4f', dark: '#0c322a', light: '#f0f4f8'
                    }
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
       /* Custom scrollbar & Modal base */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .modal { display:none; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); }
        
        /* Perbaikan Dropdown Putih di HP */
        select option {
            color: #1e293b !important;
            background-color: #ffffff !important;
        }
    </style>
</head>
<body class="bg-light text-slate-700 antialiased overflow-x-hidden">

<div class="md:hidden bg-primary text-white flex justify-between items-center p-4 shadow-md fixed top-0 w-full z-50">
    <div class="font-bold text-lg flex items-center gap-2"><i class="fas fa-tractor text-secondary"></i> SubsidiTani</div>
    <button id="mobileMenuBtn" class="text-2xl focus:outline-none"><i class="fas fa-bars"></i></button>
</div>

<div class="flex h-screen pt-[60px] md:pt-0">
    <div id="sidebar" class="fixed md:static inset-y-0 left-0 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out w-72 bg-dark text-white shadow-2xl z-50 flex flex-col h-full">
        <div class="p-6 border-b border-white/10 hidden md:block">
            <h3 class="text-2xl font-extrabold text-secondary flex items-center gap-3"><i class="fas fa-tractor"></i> SubsidiTani</h3>
            <p class="text-xs text-emerald-200 mt-1 opacity-80">Distribusi Pupuk Bersubsidi</p>
        </div>
        
        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-6">
            <div class="bg-white/10 rounded-full p-2 flex items-center gap-3 border border-white/5">
                <img src="https://ui-avatars.com/api/?background=F5E7A4&color=1e4a3b&name=<?= urlencode($userName) ?>&bold=true" class="w-10 h-10 rounded-full border-2 border-secondary">
                <div class="overflow-hidden">
                    <h4 class="font-bold text-sm truncate"><?= htmlspecialchars($userName) ?></h4>
                    <span class="text-[10px] text-emerald-200 uppercase tracking-wider"><?= $role==='admin'?'Administrator':'Petugas' ?></span>
                </div>
            </div>

            <ul class="space-y-1" id="navMenu">
                <li class="nav-item active" data-page="beranda"><a href="#" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all hover:bg-white/10 font-medium text-sm text-emerald-100 hover:text-white"><i class="fas fa-chart-line w-5"></i> Beranda</a></li>
                <li class="nav-item" data-page="dataPetani"><a href="#" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all hover:bg-white/10 font-medium text-sm text-emerald-100 hover:text-white"><i class="fas fa-users w-5"></i> Data Petani</a></li>
                <li class="nav-item" data-page="distribusi"><a href="#" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all hover:bg-white/10 font-medium text-sm text-emerald-100 hover:text-white"><i class="fas fa-truck w-5"></i> Distribusi Pupuk</a></li>
                <li class="nav-item" data-page="laporan"><a href="#" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all hover:bg-white/10 font-medium text-sm text-emerald-100 hover:text-white"><i class="fas fa-chart-simple w-5"></i> Laporan</a></li>
                <?php if ($role==='admin'): ?>
                <li class="nav-item" data-page="kelolaUser"><a href="#" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all hover:bg-white/10 font-medium text-sm text-emerald-100 hover:text-white"><i class="fas fa-user-cog w-5"></i> Kelola User</a></li>
                <?php endif; ?>
                <li class="nav-item" data-page="profile"><a href="#" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all hover:bg-white/10 font-medium text-sm text-emerald-100 hover:text-white"><i class="fas fa-user-circle w-5"></i> Profil Saya</a></li>
            </ul>

            <div class="bg-white/5 border border-secondary/20 rounded-2xl p-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="bg-secondary text-primary text-[9px] font-black px-2 py-0.5 rounded-full">BPS API</span>
                    <span class="text-xs font-bold text-emerald-100">Filter Wilayah</span>
                </div>
                <select id="bpsSelProv" class="w-full bg-white/10 border border-white/20 rounded-lg p-2 text-xs text-white mb-2 outline-none focus:border-secondary"><option value="">Memuat provinsi...</option></select>
                <select id="bpsSelKota" class="w-full bg-white/10 border border-white/20 rounded-lg p-2 text-xs text-white mb-2 outline-none focus:border-secondary" disabled><option value="">Pilih provinsi dulu</option></select>
                <select id="bpsSelKec" class="w-full bg-white/10 border border-white/20 rounded-lg p-2 text-xs text-white mb-3 outline-none focus:border-secondary"><option value="">-- Semua Kecamatan --</option></select>
                <button id="bpsApplyBtn" class="w-full bg-secondary text-primary font-bold text-xs py-2 rounded-lg disabled:opacity-50" disabled>Terapkan</button>
                <button id="bpsResetBtn" class="w-full text-emerald-300 text-[10px] mt-2 hover:text-white">Reset Filter</button>
                <div id="bpsStatusTxt" class="text-[9px] text-emerald-400 text-center mt-2">Menghubungi webapi.bps.go.id...</div>
            </div>
        </div>
    </div>

    <div class="flex-1 flex flex-col h-full overflow-hidden relative">
        <div class="hidden md:flex justify-between items-center p-8 pb-4">
            <h1 id="mainTitle" class="text-3xl font-extrabold text-primary tracking-tight">Beranda</h1>
            <div id="wilayahTopBar" class="hidden items-center gap-2 bg-white px-4 py-2 rounded-full shadow-sm border border-secondary/50">
                <i class="fas fa-map-marker-alt text-accent"></i>
                <div class="text-xs"><div id="wtName" class="font-bold text-primary"></div><div id="wtSub" class="text-slate-400 text-[10px]"></div></div>
            </div>
        </div>

        <div id="dynamicContent" class="flex-1 overflow-y-auto p-4 md:p-8 pt-4">Memuat...</div>
    </div>
</div>

<div id="crudModal" class="modal fixed inset-0 z-[100] flex items-center justify-center"><div class="bg-white w-[90%] max-w-2xl rounded-3xl p-6 shadow-2xl max-h-[90vh] overflow-y-auto" id="modalContent"></div></div>

<script>
// ... (Masukkan SELURUH KODE JAVASCRIPT BAWAAN dari file dashboard.php kamu yang lama di sini, 
// TANPA ADA YANG DIUBAH SAMA SEKALI, mulai dari $(function(){ sampai ujung script)
$(function(){
    var currentRole = "<?= $role ?>";
    var BPS_KEY     = '44c8474c0c29fe00256a9d27b8da1630';
    var BPS_DOM_URL = 'https://webapi.bps.go.id/v1/api/domain';

    // State & Script BPS dari file aslimu taruh semua di bawah ini...
    var state = { bpsDomain:'0000', bpsWilayah:'Nasional', filterProv:'', filterKota:'', filterKec:'' };
    try { var saved=JSON.parse(sessionStorage.getItem('bpsState')||'{}'); if(saved.bpsDomain) state=Object.assign(state,saved); } catch(e){}

    function setStatus(msg){ $('#bpsStatusTxt').text(msg); }
    function loadKecamatanDB(provFilter, kotaFilter) {
        $.ajax({
            url:'/api/proses/ajax_handler.php', type:'POST', dataType:'json',
            data:{ action:'getKecamatan', filter_prov:provFilter||'', filter_kota:kotaFilter||'' },
            success:function(res){
                var list = res.data || [];
                var opts = '<option value="">-- Semua Kecamatan --</option>';
                $.each(list, function(i,k){ opts += '<option value="'+k+'"'+(state.filterKec===k?' selected':'')+'>'+k+'</option>'; });
                $('#bpsSelKec').html(opts);
                if(state.filterKec) $('#bpsSelKec').val(state.filterKec);
                if(list.length) setStatus(list.length+' kecamatan tersedia');
            }
        });
    }

    function loadProvinsi(){
        $('#bpsSelProv').html('<option value="">Memuat...</option>').prop('disabled',true);
        $.ajax({ url:BPS_DOM_URL, type:'GET', dataType:'json', data:{ type:'prov', key:BPS_KEY },
            success:function(data){
                var list=(data.data&&data.data[1])?data.data[1]:[];
                var opts='<option value="">-- Semua Provinsi --</option>';
                $.each(list,function(i,p){ opts+='<option value="'+p.domain_name+'" data-id="'+p.domain_id+'"'+(state.filterProv===p.domain_name?' selected':'')+'>'+p.domain_name+'</option>'; });
                $('#bpsSelProv').html(opts).prop('disabled',false);
                if(state.filterProv){ $('#bpsSelProv').trigger('change'); } else { loadKecamatanDB('',''); updateApplyBtn(); }
            }
        });
    }

    $('#bpsSelProv').on('change',function(){
        var pName=$(this).val(); var domId=$('option:selected',this).data('id')||'';
        $('#bpsSelKota').html('<option value="">Memuat...</option>').prop('disabled',true); updateApplyBtn();
        if(!pName){ $('#bpsSelKota').html('<option value="">Pilih provinsi dulu</option>'); loadKecamatanDB('',''); return; }
        $.ajax({ url:BPS_DOM_URL, type:'GET', dataType:'json', data:{ type:'kabbyprov', prov:domId, key:BPS_KEY },
            success:function(data){
                var list=(data.data&&data.data[1])?data.data[1]:[];
                var opts='<option value="">-- Semua Kota/Kab --</option>';
                $.each(list,function(i,k){ opts+='<option value="'+k.domain_name+'" data-id="'+k.domain_id+'"'+(state.filterKota===k.domain_name?' selected':'')+'>'+k.domain_name+'</option>'; });
                $('#bpsSelKota').html(opts).prop('disabled',false);
                if(state.filterKota){ $('#bpsSelKota').trigger('change'); } else { loadKecamatanDB(pName,''); }
                updateApplyBtn();
            }
        });
    });

    $('#bpsSelKota').on('change',function(){ loadKecamatanDB($('#bpsSelProv').val(), $(this).val()); updateApplyBtn(); });
    $('#bpsSelKec').on('change', function(){ updateApplyBtn(); });

    function updateApplyBtn(){ $('#bpsApplyBtn').prop('disabled', !($('#bpsSelProv').val()!=='' || $('#bpsSelKec').val()!=='')); }

    $('#bpsApplyBtn').on('click',function(){
        var provOpt=$('#bpsSelProv option:selected'), kotaOpt=$('#bpsSelKota option:selected'), kecVal=$('#bpsSelKec').val();
        var pName=provOpt.val(), kName=kotaOpt.val(), bpsDomain=kotaOpt.data('id')||provOpt.data('id')||'0000';
        var wilayah=[pName,kName,kecVal].filter(Boolean).join(' › ')||'Nasional';
        state = { bpsDomain:bpsDomain, bpsWilayah:wilayah, filterProv:pName, filterKota:kName, filterKec:kecVal };
        sessionStorage.setItem('bpsState',JSON.stringify(state)); updateUI(); loadPage($('.nav-item.active').data('page'));
        $('#sidebar').addClass('-translate-x-full'); // Tutup sidebar di HP saat apply
    });

    window.bpsReset=function(){
        state={bpsDomain:'0000',bpsWilayah:'Nasional',filterProv:'',filterKota:'',filterKec:''}; sessionStorage.removeItem('bpsState');
        $('#bpsSelProv').val('').trigger('change'); $('#bpsSelKec').val(''); updateUI(); $('#bpsApplyBtn').prop('disabled',true);
        loadPage($('.nav-item.active').data('page'));
    };
    $('#bpsResetBtn').on('click',bpsReset);

    function updateUI(){
        if(state.filterProv!==''||state.filterKec!==''){
            $('#wilayahTopBar').removeClass('hidden').addClass('flex');
            $('#wtName').text([state.filterProv,state.filterKota,state.filterKec].filter(Boolean).join(' › '));
            $('#wtSub').text('BPS Domain: '+state.bpsDomain);
        } else { $('#wilayahTopBar').addClass('hidden').removeClass('flex'); }
    }

    var titleMap={ beranda:'Beranda', dataPetani:'Data Petani', distribusi:'Distribusi Pupuk', laporan:'Laporan', kelolaUser:'Kelola User', profile:'Profil Saya' };
    window.loadPage=function(page){
        $('#mainTitle').text(titleMap[page]||page);
        $('#dynamicContent').html('<div class="flex justify-center items-center h-40 text-primary"><i class="fas fa-spinner fa-pulse fa-2x"></i></div>');
        $.ajax({
            url:'/api/proses/ajax_handler.php', type:'POST', dataType:'html',
            data:{ action:'getPage', page:page, role:currentRole, bps_domain:state.bpsDomain, bps_wilayah:state.bpsWilayah, filter_prov:state.filterProv, filter_kota:state.filterKota, filter_kec:state.filterKec },
            success:function(html){ $('#dynamicContent').html(html); attachEventHandlers(page); },
            error:function(){ $('#dynamicContent').html('<div class="text-red-500 text-center">Gagal memuat halaman</div>'); }
        });
    };

    window.attachEventHandlers=function(page){
        // Event handler CRUD aslimu (TIDAK ADA YANG DIUBAH)
        if(page==='dataPetani'){
            $('.btn-edit-petani').off('click').on('click',function(){ openModal('petani',$(this).data('id')); });
            $('.btn-hapus-petani').off('click').on('click',function(){ if(confirm('Yakin hapus?')) deleteData('petani',$(this).data('id')); });
            if(currentRole==='admin') $('#tambahPetaniBtn').off('click').on('click',function(){ openModal('petani',null); });
        }else if(page==='distribusi'){
            $('.btn-edit-distribusi').off('click').on('click',function(){ openModal('distribusi',$(this).data('id')); });
            $('.btn-hapus-distribusi').off('click').on('click',function(){ if(confirm('Yakin hapus?')) deleteData('distribusi',$(this).data('id')); });
            if(currentRole==='admin') $('#tambahDistribusiBtn').off('click').on('click',function(){ openModal('distribusi',null); });
        }else if(page==='laporan'){
            $('.btn-edit-laporan').off('click').on('click',function(){ openModal('laporan',$(this).data('id')); });
            $('.btn-hapus-laporan').off('click').on('click',function(){ if(confirm('Yakin hapus?')) deleteData('laporan',$(this).data('id')); });
            if(currentRole==='admin') $('#tambahLaporanBtn').off('click').on('click',function(){ openModal('laporan',null); });
        }else if(page==='kelolaUser'&&currentRole==='admin'){
            $('.btn-edit-user').off('click').on('click',function(){ openModal('user',$(this).data('id')); });
            $('.btn-hapus-user').off('click').on('click',function(){ if(confirm('Yakin hapus?')) deleteData('user',$(this).data('id')); });
            $('#tambahUserBtn').off('click').on('click',function(){ openModal('user',null); });
        }
    };

   window.openModal = function(type, id) {
    $.post('/api/proses/ajax_handler.php', { action: 'getForm', type: type, id: id }, function(html) {
        $('#modalContent').html(html);
        $('#crudModal').fadeIn(200).css('display', 'flex');
        
        // Klik tombol simpan akan memicu submit form
        $('#saveCrudBtn').off('click').on('click', function() {
            $('#crudForm').submit();
        });
    });
};

// Bagian ini diletakkan di LUAR openModal (sejajar dengan window.openModal)
$(document).on('submit', '#crudForm', function(e) {
    e.preventDefault();
    
    let fd = $(this).serialize();
    fd += "&action=save"; // Menambahkan perintah simpan

    $.post('/api/proses/ajax_handler.php', fd, function(res) {
        if (res.status === 'success') {
            $('#crudModal').fadeOut(200);
            loadPage($('.nav-item.active').data('page'));
        } else {
            alert('Gagal menyimpan: ' + (res.msg || 'Error Unknown'));
        }
    }, 'json');
});
    window.deleteData=function(type,id){ $.post('/api/proses/ajax_handler.php',{action:'delete',type:type,id:id},function(res){ if(res.status==='success') loadPage($('.nav-item.active').data('page')); },'json'); };

    // Klik menu & toggle sidebar di mobile
    $('.nav-item').on('click',function(e){
        e.preventDefault();
        $('.nav-item a').removeClass('bg-white/10 text-white').addClass('text-emerald-100');
        $(this).find('a').addClass('bg-white/10 text-white').removeClass('text-emerald-100');
        $('.nav-item').removeClass('active'); $(this).addClass('active');
        loadPage($(this).data('page'));
        $('#sidebar').addClass('-translate-x-full'); // Auto close di HP
    });
    
    // Toggle Mobile Menu
    $('#mobileMenuBtn').on('click', function() { $('#sidebar').toggleClass('-translate-x-full'); });
    $('#crudModal').on('click',function(e){ if($(e.target).is('#crudModal')) $(this).fadeOut(200); });

    // Init
    $('.nav-item.active a').addClass('bg-white/10 text-white').removeClass('text-emerald-100');
    updateUI(); loadProvinsi(); loadPage('beranda');
});
</script>
</body>
</html>