<?php
session_start();
if (!isset($_SESSION['id'])) { header("Location: login.php"); exit(); }
require 'service/koneksi.php';
$role     = $_SESSION['role'];
$userName = $_SESSION['nama'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Dashboard | Sistem Distribusi Pupuk Subsidi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif;}
        body{background:#eef2f8;overflow-x:hidden;}
        .animated-bg{position:fixed;top:0;left:0;width:100%;height:100%;z-index:-2;
            background:radial-gradient(circle at 10% 20%,#e0efe8,#cbdde2);
            animation:slowDrift 20s infinite alternate;}
        @keyframes slowDrift{0%{background:radial-gradient(circle at 10% 20%,#e0efe8,#cbdde2);}
            100%{background:radial-gradient(circle at 90% 80%,#d4e6df,#b9cfc4);}}

        /* ── Sidebar ── */
        .sidebar{position:fixed;left:0;top:0;width:280px;height:100%;
            background:rgba(12,50,42,0.97);backdrop-filter:blur(14px);
            border-right:1px solid rgba(245,231,164,0.2);z-index:100;
            box-shadow:8px 0 32px rgba(0,0,0,0.15);
            display:flex;flex-direction:column;overflow:hidden;
            transition:width 0.35s cubic-bezier(0.2,0.9,0.4,1.1);}
        .sidebar-scroll{flex:1;overflow-y:auto;overflow-x:hidden;}
        .sidebar-scroll::-webkit-scrollbar{width:3px;}
        .sidebar-scroll::-webkit-scrollbar-thumb{background:rgba(245,231,164,0.25);border-radius:4px;}
        .sidebar-header{padding:24px 20px 18px 24px;border-bottom:1px solid rgba(245,231,164,0.15);}
        .sidebar-header h3{color:#f5e7a4;font-size:22px;font-weight:800;display:flex;align-items:center;gap:10px;}
        .sidebar-header p{color:#9db8b0;font-size:11px;margin-top:6px;}
        .profile-sidebar{display:flex;align-items:center;gap:12px;
            background:rgba(255,255,255,0.08);margin:16px 14px 0;
            padding:10px 16px;border-radius:50px;}
        .profile-sidebar img{width:44px;height:44px;border-radius:50%;border:2px solid #f5e7a4;}
        .profile-sidebar .info h4{color:white;font-size:14px;font-weight:700;}
        .profile-sidebar .info span{font-size:11px;color:#9db8b0;}
        .nav-menu{list-style:none;margin:14px 0 0;padding:0 12px;}
        .nav-item{margin-bottom:6px;border-radius:36px;transition:all 0.2s;cursor:pointer;}
        .nav-item a{display:flex;align-items:center;gap:14px;padding:12px 18px;
            color:#cde0d8;text-decoration:none;font-weight:500;border-radius:36px;font-size:14px;}
        .nav-item a i{width:22px;font-size:1.1rem;text-align:center;}
        .nav-item.active{background:#f5e7a4;}
        .nav-item.active a{color:#1a4d3e;font-weight:700;}
        .nav-item:not(.active):hover{background:rgba(245,231,164,0.12);transform:translateX(3px);}
        .sidebar-divider{height:1px;background:rgba(245,231,164,0.1);margin:10px 14px;}

        /* ── Widget Filter Wilayah BPS ── */
        .bps-filter-widget{margin:0 14px 12px;background:rgba(255,255,255,0.05);
            border:1px solid rgba(245,231,164,0.18);border-radius:18px;padding:14px 15px;}
        .bps-filter-widget .w-head{display:flex;align-items:center;gap:8px;margin-bottom:12px;}
        .bps-badge{background:#f5e7a4;color:#1a4d3e;font-size:9px;font-weight:900;
            padding:2px 8px;border-radius:20px;letter-spacing:0.5px;}
        .bps-filter-widget .w-title{color:#c8ddd5;font-size:12px;font-weight:700;}
        .bps-select-label{font-size:10px;color:#9db8b0;display:block;margin-bottom:3px;font-weight:600;letter-spacing:0.3px;}
        .bps-select{width:100%;padding:8px 26px 8px 10px;border-radius:10px;margin-bottom:8px;
            border:1px solid rgba(245,231,164,0.2);background:rgba(255,255,255,0.08);
            color:#e2edf2;font-size:12px;font-family:'Inter',sans-serif;outline:none;
            appearance:none;cursor:pointer;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%23f5e7a4' stroke-width='1.5' stroke-linecap='round' fill='none'/%3E%3C/svg%3E");
            background-repeat:no-repeat;background-position:right 9px center;
            transition:border-color 0.2s;}
        .bps-select:hover:not(:disabled){border-color:rgba(245,231,164,0.45);}
        .bps-select:disabled{opacity:0.38;cursor:not-allowed;}
        .bps-select option{background:#1a3d30;color:#e2edf2;}
        .bps-apply-btn{width:100%;padding:9px;border-radius:11px;border:none;
            background:#f5e7a4;color:#1a4d3e;font-size:12px;font-weight:800;
            cursor:pointer;transition:all 0.2s;display:flex;align-items:center;
            justify-content:center;gap:6px;margin-top:2px;}
        .bps-apply-btn:hover:not(:disabled){background:#ede07a;transform:translateY(-1px);}
        .bps-apply-btn:disabled{background:rgba(245,231,164,0.18);color:#5a7a70;cursor:not-allowed;transform:none;}
        .bps-reset-btn{width:100%;padding:6px;border-radius:11px;
            border:1px solid rgba(245,231,164,0.15);background:transparent;
            color:#7a9e94;font-size:11px;font-weight:600;cursor:pointer;
            transition:all 0.2s;margin-top:6px;}
        .bps-reset-btn:hover{background:rgba(245,231,164,0.07);color:#b0cdc5;}
        .bps-status-txt{font-size:10px;color:#5a7a70;text-align:center;margin-top:6px;min-height:14px;line-height:1.4;}
        .bps-active-pill{margin:0 14px 12px;padding:9px 13px;
            background:rgba(245,231,164,0.08);border:1px solid rgba(245,231,164,0.18);
            border-radius:12px;display:none;line-height:1.6;}
        .bps-active-pill strong{color:#f5e7a4;display:block;font-size:12px;}
        .bps-active-pill span{color:#6b8a7f;font-size:10px;}

        /* ── Main Content ── */
        .main-content{margin-left:280px;padding:28px 36px;transition:margin 0.35s;}
        .top-bar{display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;flex-wrap:wrap;gap:12px;}
        .page-title h1{font-size:30px;font-weight:800;
            background:linear-gradient(135deg,#1e4a3b,#0e382b);
            -webkit-background-clip:text;background-clip:text;color:transparent;letter-spacing:-0.5px;}
        .wilayah-topbar{display:none;align-items:center;gap:10px;background:white;
            border-radius:36px;padding:8px 16px;
            box-shadow:0 4px 12px rgba(0,0,0,0.06);border:2px solid rgba(245,231,164,0.7);}
        .wilayah-topbar i{color:#2d6a4f;font-size:14px;}
        .wilayah-topbar .wt-name{font-size:13px;font-weight:700;color:#1e4a3b;}
        .wilayah-topbar .wt-sub{font-size:11px;color:#64748b;}
        .content-panel{background:rgba(255,255,255,0.94);border-radius:36px;padding:28px 32px;
            box-shadow:0 20px 35px -12px rgba(0,0,0,0.08);
            animation:fadeSlideUp 0.4s cubic-bezier(0.2,0.9,0.4,1.1);}
        @keyframes fadeSlideUp{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}

        .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;margin-bottom:32px;}
        .stat-card{background:white;border-radius:24px;padding:20px;box-shadow:0 4px 16px rgba(0,0,0,0.04);}
        .stat-title{font-size:13px;color:#64748b;margin-bottom:6px;font-weight:600;}
        .stat-number{font-size:34px;font-weight:800;color:#0c3f31;}
        table{width:100%;border-collapse:collapse;}
        th,td{text-align:left;padding:12px 10px;border-bottom:1px solid #e6edf2;}
        th{font-weight:700;color:#1e4a3b;background:#f9fbfd;font-size:13px;}
        td{font-size:14px;color:#374151;}
        .badge{background:#e9f5ef;color:#1e4a3b;padding:4px 12px;border-radius:30px;font-size:12px;font-weight:700;}
        .btn-sm{background:#eef2ff;border:none;padding:6px 14px;border-radius:24px;cursor:pointer;transition:0.2s;font-size:13px;}
        .btn-sm:hover{background:#d9e2ef;}
        .btn-admin{background:#1e4a3b;color:white;border:none;padding:8px 18px;border-radius:30px;cursor:pointer;font-size:13px;font-weight:600;}
        .info-box{background:#f0f6f3;border-radius:20px;padding:16px 20px;margin-top:20px;border-left:4px solid #f5e7a4;font-size:14px;line-height:1.7;}
        .modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;
            background:rgba(0,0,0,0.55);backdrop-filter:blur(6px);z-index:1000;
            align-items:center;justify-content:center;}
        .modal-content{background:white;max-width:580px;width:92%;border-radius:28px;
            padding:28px;box-shadow:0 30px 50px rgba(0,0,0,0.18);
            max-height:90vh;overflow-y:auto;}
        .modal-buttons{display:flex;justify-content:flex-end;gap:10px;margin-top:16px;}
        @media(max-width:800px){
            .sidebar{width:64px;}
            .sidebar-header h3 span,.sidebar-header p,.profile-sidebar .info,
            .nav-item a span,.bps-filter-widget .w-title,.bps-select-label,
            .bps-select,.bps-apply-btn span,.bps-reset-btn,
            .bps-active-pill,.bps-status-txt{display:none;}
            .bps-filter-widget{padding:8px;}
            .main-content{margin-left:64px;padding:16px;}
        }
    </style>
</head>
<body>
<div class="animated-bg"></div>

<div class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-tractor"></i><span>SubsidiTani</span></h3>
        <p><span>Distribusi Pupuk Bersubsidi</span></p>
    </div>
    <div class="sidebar-scroll">
        <div class="profile-sidebar">
            <img src="https://ui-avatars.com/api/?background=F5E7A4&color=1e4a3b&name=<?= urlencode($userName) ?>&bold=true" alt="avatar">
            <div class="info">
                <h4><?= htmlspecialchars($userName) ?></h4>
                <span><?= $role==='admin'?'Administrator':'Petugas Dinas' ?></span>
            </div>
        </div>
        <ul class="nav-menu" id="navMenu">
            <li class="nav-item active" data-page="beranda"><a href="#"><i class="fas fa-chart-line"></i><span>Beranda</span></a></li>
            <li class="nav-item" data-page="dataPetani"><a href="#"><i class="fas fa-users"></i><span>Data Petani</span></a></li>
            <li class="nav-item" data-page="distribusi"><a href="#"><i class="fas fa-truck"></i><span>Distribusi Pupuk</span></a></li>
            <li class="nav-item" data-page="laporan"><a href="#"><i class="fas fa-chart-simple"></i><span>Laporan</span></a></li>
            <?php if ($role==='admin'): ?>
            <li class="nav-item" data-page="kelolaUser"><a href="#"><i class="fas fa-user-cog"></i><span>Kelola User</span></a></li>
            <?php endif; ?>
            <li class="nav-item" data-page="profile"><a href="#"><i class="fas fa-user-circle"></i><span>Profil Saya</span></a></li>
        </ul>

        <div class="sidebar-divider"></div>

        <!-- ===== WIDGET FILTER WILAYAH BPS ===== -->
        <div class="bps-filter-widget">
            <div class="w-head">
                <span class="bps-badge">BPS API</span>
                <span class="w-title">Filter Wilayah</span>
            </div>

            <span class="bps-select-label"><i class="fas fa-map" style="margin-right:3px;font-size:9px;"></i> Provinsi</span>
            <select class="bps-select" id="bpsSelProv">
                <option value="">Memuat provinsi...</option>
            </select>

            <span class="bps-select-label"><i class="fas fa-city" style="margin-right:3px;font-size:9px;"></i> Kota / Kabupaten</span>
            <select class="bps-select" id="bpsSelKota" disabled>
                <option value="">Pilih provinsi dulu</option>
            </select>

            <!-- Kecamatan: diisi dari DB lokal (semua kecamatan yg ada di data) -->
            <span class="bps-select-label"><i class="fas fa-road" style="margin-right:3px;font-size:9px;"></i> Kecamatan</span>
            <select class="bps-select" id="bpsSelKec">
                <option value="">-- Semua Kecamatan --</option>
            </select>

            <button class="bps-apply-btn" id="bpsApplyBtn" disabled>
                <i class="fas fa-filter"></i>
                <span>Terapkan Filter</span>
            </button>
            <button class="bps-reset-btn" id="bpsResetBtn">
                <i class="fas fa-times-circle" style="margin-right:4px;"></i> Reset — Tampilkan Semua
            </button>
            <div class="bps-status-txt" id="bpsStatusTxt">Menghubungi webapi.bps.go.id...</div>
        </div>

        <div class="bps-active-pill" id="bpsActivePill">
            <strong id="bpsActiveName">—</strong>
            <span id="bpsActiveSub"></span>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="top-bar">
        <div class="page-title"><h1 id="mainTitle">Beranda</h1></div>
        <div class="wilayah-topbar" id="wilayahTopBar">
            <i class="fas fa-map-marker-alt"></i>
            <div>
                <div class="wt-name" id="wtName">—</div>
                <div class="wt-sub" id="wtSub">BPS Domain: —</div>
            </div>
            <button onclick="bpsReset()" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:11px;padding:2px 4px;" title="Reset filter"><i class="fas fa-times"></i></button>
        </div>
    </div>
    <div id="dynamicContent" class="content-panel">Memuat...</div>
</div>

<div id="crudModal" class="modal"><div class="modal-content" id="modalContent"></div></div>

<script>
$(function(){
    var currentRole = "<?= $role ?>";
    var BPS_KEY     = '44c8474c0c29fe00256a9d27b8da1630';
    var BPS_DOM_URL = 'https://webapi.bps.go.id/v1/api/domain';

    // ── State ────────────────────────────────────────────────────
    var state = { bpsDomain:'0000', bpsWilayah:'Nasional', filterProv:'', filterKota:'', filterKec:'' };
    try { var saved=JSON.parse(sessionStorage.getItem('bpsState')||'{}'); if(saved.bpsDomain) state=Object.assign(state,saved); } catch(e){}

    function setStatus(msg){ $('#bpsStatusTxt').text(msg); }

    // ── Load kecamatan dari DB lokal via AJAX ─────────────────────
    // Dipanggil pertama kali dan setelah provinsi/kota dipilih
    function loadKecamatanDB(provFilter, kotaFilter) {
        $.ajax({
            url:'proses/ajax_handler.php', type:'POST', dataType:'json',
            data:{ action:'getKecamatan', filter_prov:provFilter||'', filter_kota:kotaFilter||'' },
            success:function(res){
                var list = res.data || [];
                var opts = '<option value="">-- Semua Kecamatan --</option>';
                $.each(list, function(i,k){
                    opts += '<option value="'+k+'"'+(state.filterKec===k?' selected':'')+'>'+k+'</option>';
                });
                $('#bpsSelKec').html(opts);
                if(state.filterKec) $('#bpsSelKec').val(state.filterKec);
                if(list.length) setStatus(list.length+' kecamatan tersedia');
            },
            error:function(){ /* silent */ }
        });
    }

    // ── BPS: Load Provinsi ────────────────────────────────────────
    function loadProvinsi(){
        setStatus('Memuat provinsi dari BPS...');
        $('#bpsSelProv').html('<option value="">Memuat...</option>').prop('disabled',true);
        $.ajax({ url:BPS_DOM_URL, type:'GET', dataType:'json',
            data:{ type:'prov', key:BPS_KEY }, timeout:12000,
            success:function(data){
                var list=(data.data&&data.data[1])?data.data[1]:[];
                var opts='<option value="">-- Semua Provinsi --</option>';
                $.each(list,function(i,p){
                    opts+='<option value="'+p.domain_name+'" data-id="'+p.domain_id+'"'
                        +(state.filterProv===p.domain_name?' selected':'')+'>'+p.domain_name+'</option>';
                });
                $('#bpsSelProv').html(opts).prop('disabled',false);
                setStatus(list.length+' provinsi dimuat');
                if(state.filterProv){ $('#bpsSelProv').trigger('change'); }
                else { loadKecamatanDB('',''); updateApplyBtn(); }
            },
            error:function(){ $('#bpsSelProv').html('<option value="">Gagal memuat</option>'); setStatus('Gagal konek BPS'); }
        });
    }

    // ── Provinsi change → Kota ────────────────────────────────────
    $('#bpsSelProv').on('change',function(){
        var pName=$(this).val();
        var domId=$('option:selected',this).data('id')||'';
        $('#bpsSelKota').html('<option value="">Memuat...</option>').prop('disabled',true);
        updateApplyBtn();

        // Reset kec state saat provinsi ganti
        if(!pName){
            $('#bpsSelKota').html('<option value="">Pilih provinsi dulu</option>');
            loadKecamatanDB('','');
            return;
        }
        setStatus('Memuat kota/kabupaten...');
        $.ajax({ url:BPS_DOM_URL, type:'GET', dataType:'json',
            data:{ type:'kabbyprov', prov:domId, key:BPS_KEY }, timeout:12000,
            success:function(data){
                var list=(data.data&&data.data[1])?data.data[1]:[];
                var opts='<option value="">-- Semua Kota/Kab --</option>';
                $.each(list,function(i,k){
                    opts+='<option value="'+k.domain_name+'" data-id="'+k.domain_id+'"'
                        +(state.filterKota===k.domain_name?' selected':'')+'>'+k.domain_name+'</option>';
                });
                $('#bpsSelKota').html(opts).prop('disabled',false);
                setStatus(list.length+' kota/kab dimuat');
                if(state.filterKota){ $('#bpsSelKota').trigger('change'); }
                else { loadKecamatanDB(pName,''); }
                updateApplyBtn();
            },
            error:function(){ $('#bpsSelKota').html('<option value="">Gagal</option>').prop('disabled',false); }
        });
    });

    // ── Kota change → filter kecamatan dari DB ────────────────────
    $('#bpsSelKota').on('change',function(){
        var kName=$(this).val();
        var pName=$('#bpsSelProv').val();
        loadKecamatanDB(pName, kName);
        updateApplyBtn();
    });

    // ── Kecamatan change ──────────────────────────────────────────
    $('#bpsSelKec').on('change', function(){ updateApplyBtn(); });

    // ── Aktifkan tombol Apply ─────────────────────────────────────
    function updateApplyBtn(){
        var hasSel = $('#bpsSelProv').val()!=='' || $('#bpsSelKec').val()!=='';
        $('#bpsApplyBtn').prop('disabled',!hasSel);
    }

    // ── Terapkan Filter ───────────────────────────────────────────
    $('#bpsApplyBtn').on('click',function(){
        var provOpt=$('#bpsSelProv option:selected');
        var kotaOpt=$('#bpsSelKota option:selected');
        var kecVal =$('#bpsSelKec').val();
        var pName  = provOpt.val();
        var kName  = kotaOpt.val();
        var kotaDomId = kotaOpt.data('id')||provOpt.data('id')||'0000';
        var bpsDomain = kotaDomId||'0000';
        var parts  = [pName,kName,kecVal].filter(Boolean);
        var wilayah= parts.join(' › ')||'Nasional';

        state = { bpsDomain:bpsDomain, bpsWilayah:wilayah,
                  filterProv:pName, filterKota:kName, filterKec:kecVal };
        sessionStorage.setItem('bpsState',JSON.stringify(state));
        updateUI();
        loadPage($('.nav-item.active').data('page'));
    });

    // ── Reset ─────────────────────────────────────────────────────
    window.bpsReset=function(){
        state={bpsDomain:'0000',bpsWilayah:'Nasional',filterProv:'',filterKota:'',filterKec:''};
        sessionStorage.removeItem('bpsState');
        $('#bpsSelProv').val('').trigger('change');
        $('#bpsSelKec').val('');
        updateUI();
        $('#bpsApplyBtn').prop('disabled',true);
        loadPage($('.nav-item.active').data('page'));
    };
    $('#bpsResetBtn').on('click',bpsReset);

    // ── Update UI pill & topbar ───────────────────────────────────
    function updateUI(){
        var has=state.filterProv!==''||state.filterKec!=='';
        if(has){
            var parts=[state.filterProv,state.filterKota,state.filterKec].filter(Boolean);
            var label=parts.join(' › ');
            $('#bpsActiveName').text(label);
            $('#bpsActiveSub').text('BPS Domain: '+state.bpsDomain);
            $('#bpsActivePill').show();
            $('#wilayahTopBar').css('display','flex');
            $('#wtName').text(label);
            $('#wtSub').text('BPS Domain: '+state.bpsDomain);
            setStatus('✓ Filter aktif');
        } else {
            $('#bpsActivePill').hide();
            $('#wilayahTopBar').hide();
            setStatus('Menampilkan semua wilayah');
        }
    }

    // ── Load Page AJAX ────────────────────────────────────────────
    var titleMap={
        beranda:'🏠 Beranda', dataPetani:'👨‍🌾 Data Petani',
        distribusi:'🚛 Distribusi', laporan:'📈 Laporan',
        kelolaUser:'👥 Kelola User', profile:'👤 Profil'
    };
    window.loadPage=function(page){
        $('#mainTitle').text(titleMap[page]||page);
        $('#dynamicContent').html(
            '<div style="text-align:center;padding:60px;color:#94a3b8;">'
            +'<i class="fas fa-spinner fa-pulse fa-2x"></i>'
            +'<div style="font-size:14px;margin-top:14px;">Memuat data wilayah...</div></div>'
        );
        $.ajax({
            url:'proses/ajax_handler.php', type:'POST', dataType:'html', timeout:20000,
            data:{ action:'getPage', page:page, role:currentRole,
                   bps_domain:state.bpsDomain, bps_wilayah:state.bpsWilayah,
                   filter_prov:state.filterProv, filter_kota:state.filterKota, filter_kec:state.filterKec },
            success:function(html){ $('#dynamicContent').html(html); attachEventHandlers(page); },
            error:function(xhr,s,err){
                var msg=s==='timeout'?'Waktu habis.':(xhr.status===404?'File tidak ditemukan.':err);
                $('#dynamicContent').html('<div style="color:red;padding:24px;text-align:center;font-size:15px;">Gagal memuat: '+msg
                    +'<br><button onclick="location.reload()" style="margin-top:12px;padding:8px 20px;border-radius:20px;border:none;background:#1e4a3b;color:white;cursor:pointer;">Refresh</button></div>');
            }
        });
    };

    // ── Event Handler CRUD ────────────────────────────────────────
    window.attachEventHandlers=function(page){
        if(page==='dataPetani'){
            $('.btn-edit-petani').off('click').on('click',function(){ openModal('petani',$(this).data('id')); });
            $('.btn-hapus-petani').off('click').on('click',function(){ if(confirm('Yakin hapus data petani ini?')) deleteData('petani',$(this).data('id')); });
            if(currentRole==='admin') $('#tambahPetaniBtn').off('click').on('click',function(){ openModal('petani',null); });
        }else if(page==='distribusi'){
            $('.btn-edit-distribusi').off('click').on('click',function(){ openModal('distribusi',$(this).data('id')); });
            $('.btn-hapus-distribusi').off('click').on('click',function(){ if(confirm('Yakin hapus data distribusi ini?')) deleteData('distribusi',$(this).data('id')); });
            if(currentRole==='admin') $('#tambahDistribusiBtn').off('click').on('click',function(){ openModal('distribusi',null); });
        }else if(page==='laporan'){
            $('.btn-edit-laporan').off('click').on('click',function(){ openModal('laporan',$(this).data('id')); });
            $('.btn-hapus-laporan').off('click').on('click',function(){ if(confirm('Yakin hapus laporan ini?')) deleteData('laporan',$(this).data('id')); });
            if(currentRole==='admin') $('#tambahLaporanBtn').off('click').on('click',function(){ openModal('laporan',null); });
        }else if(page==='kelolaUser'&&currentRole==='admin'){
            $('.btn-edit-user').off('click').on('click',function(){ openModal('user',$(this).data('id')); });
            $('.btn-hapus-user').off('click').on('click',function(){ if(confirm('Yakin hapus user ini?')) deleteData('user',$(this).data('id')); });
            $('#tambahUserBtn').off('click').on('click',function(){ openModal('user',null); });
        }
    };

    // ── Modal ─────────────────────────────────────────────────────
    window.openModal=function(type,id){
        $.ajax({ url:'proses/ajax_handler.php', type:'POST', dataType:'html', timeout:15000,
            data:{ action:'getForm', type:type, id:id },
            success:function(html){
                $('#modalContent').html(html);
                $('#crudModal').css('display','flex');
                $('#saveCrudBtn').off('click').on('click',function(){
                    var fd=$('#crudForm').serialize()+'&action=save&type='+type;
                    $.post('proses/ajax_handler.php',fd,function(res){
                        if(res.status==='success'){
                            $('#crudModal').hide();
                            loadPage($('.nav-item.active').data('page'));
                        }else{ alert('Gagal menyimpan: '+(res.msg||'Terjadi kesalahan')); }
                    },'json').fail(function(){ alert('Koneksi error saat menyimpan.'); });
                });
            },
            error:function(xhr,s,e){ alert('Gagal memuat form: '+e); }
        });
    };
    window.deleteData=function(type,id){
        $.post('proses/ajax_handler.php',{action:'delete',type:type,id:id},function(res){
            if(res.status==='success') loadPage($('.nav-item.active').data('page'));
            else alert('Gagal hapus: '+(res.msg||'Error'));
        },'json').fail(function(){ alert('Koneksi error.'); });
    };

    // ── Menu Klik ─────────────────────────────────────────────────
    $('.nav-item').on('click',function(e){
        e.preventDefault();
        var page=$(this).data('page');
        $('.nav-item').removeClass('active');
        $(this).addClass('active');
        loadPage(page);
    });
    $('#crudModal').on('click',function(e){ if($(e.target).is('#crudModal')) $(this).hide(); });

    // ── Init ──────────────────────────────────────────────────────
    updateUI();
    loadProvinsi();
    loadPage('beranda');
});
if(typeof Chart==='undefined'){var s=document.createElement('script');s.src='https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';document.head.appendChild(s);}
</script>
</body>
</html>
