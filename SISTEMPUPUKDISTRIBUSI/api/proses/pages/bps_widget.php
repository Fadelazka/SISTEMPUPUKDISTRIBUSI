<?php
if (!defined('BPS_API_KEY')) {
    define('BPS_API_KEY',    '44c8474c0c29fe00256a9d27b8da1630');
    define('BPS_BASE_URL',   'https://webapi.bps.go.id/v1/api/');
    define('BPS_DOMAIN_URL', 'https://webapi.bps.go.id/v1/api/domain');
}

/* ── Fetch tunggal server-side ─────────────────────────────────────── */
function bps_fetch(string $ep, array $p = []): ?array {
    $p['key'] = BPS_API_KEY;
    $url = BPS_BASE_URL . $ep . '?' . http_build_query($p);
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);
    return $raw ? json_decode($raw, true) : null;
}

/* ── Fetch PARALEL — beberapa endpoint BPS sekaligus ─────────────── */
function bps_fetch_parallel(array $requests): array {
    $mh = curl_multi_init();
    $handles = [];
    foreach ($requests as $i => $req) {
        $p   = array_merge($req['params'] ?? [], ['key' => BPS_API_KEY]);
        $url = BPS_BASE_URL . $req['ep'] . '?' . http_build_query($p);
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        curl_multi_add_handle($mh, $ch);
        $handles[$i] = $ch;
    }
    $running = null;
    do {
        curl_multi_exec($mh, $running);
        curl_multi_select($mh);
    } while ($running > 0);
    $results = [];
    foreach ($handles as $i => $ch) {
        $raw        = curl_multi_getcontent($ch);
        $results[$i] = $raw ? json_decode($raw, true) : null;
        curl_multi_remove_handle($mh, $ch);
    }
    curl_multi_close($mh);
    return $results;
}

/* ── Helper filter aktif ───────────────────────────────────────────── */
function bps_active_domain(): string  { return !empty($_REQUEST['bps_domain'])  ? trim($_REQUEST['bps_domain'])  : '0000'; }
function bps_active_wilayah(): string { return !empty($_REQUEST['bps_wilayah']) ? trim($_REQUEST['bps_wilayah']) : 'Nasional'; }
function filter_prov(): string { return trim($_REQUEST['filter_prov'] ?? ''); }
function filter_kota(): string { return trim($_REQUEST['filter_kota'] ?? ''); }
function filter_kec(): string  { return trim($_REQUEST['filter_kec']  ?? ''); }
function has_filter(): bool    { return filter_prov() !== '' || filter_kota() !== '' || filter_kec() !== ''; }

/* ── Bangun WHERE SQL untuk filter wilayah ─────────────────────────── */
function filter_where($koneksi, string $prefix = ''): string {
    $parts = [];
    if (filter_prov() !== '') {
        $v = mysqli_real_escape_string($koneksi, filter_prov());
        $parts[] = "{$prefix}provinsi LIKE '%$v%'";
    }
    if (filter_kota() !== '') {
        $v = mysqli_real_escape_string($koneksi, filter_kota());
        $parts[] = "{$prefix}kota LIKE '%$v%'";
    }
    if (filter_kec() !== '') {
        $v = mysqli_real_escape_string($koneksi, filter_kec());
        $parts[] = "{$prefix}kecamatan LIKE '%$v%'";
    }
    return empty($parts) ? '1=1' : implode(' AND ', $parts);
}

/* ── Badge wilayah aktif ───────────────────────────────────────────── */
function bps_wilayah_badge(): string {
    $domain = bps_active_domain();
    $nama   = bps_active_wilayah();
    $prov   = filter_prov();
    $kota   = filter_kota();
    $kec    = filter_kec();
    $isNas  = ($domain === '0000' && !has_filter());

    $filterHtml = '';
    if (has_filter()) {
        $tags = '';
        if ($prov) $tags .= '<span style="background:rgba(245,231,164,0.25);color:#f5e7a4;font-size:11px;padding:2px 10px;border-radius:20px;margin-right:4px;">🗺 ' . htmlspecialchars($prov) . '</span>';
        if ($kota) $tags .= '<span style="background:rgba(245,231,164,0.25);color:#f5e7a4;font-size:11px;padding:2px 10px;border-radius:20px;margin-right:4px;">🏙 ' . htmlspecialchars($kota) . '</span>';
        if ($kec)  $tags .= '<span style="background:rgba(245,231,164,0.25);color:#f5e7a4;font-size:11px;padding:2px 10px;border-radius:20px;">🛣 ' . htmlspecialchars($kec) . '</span>';
        $filterHtml = '<div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:4px;align-items:center;"><span style="font-size:10px;color:#9db8b0;">Filter aktif:</span> ' . $tags . '</div>';
    }

    return '
    <div style="display:flex;align-items:flex-start;gap:12px;background:linear-gradient(135deg,#1e4a3b,#2d6a4f);
        border-radius:16px;padding:14px 20px;margin-bottom:22px;flex-wrap:wrap;">
        <i class="fas fa-' . ($isNas ? 'globe-asia' : 'map-marker-alt') . '"
            style="color:#f5e7a4;font-size:18px;margin-top:2px;flex-shrink:0;"></i>
        <div style="flex:1;min-width:0;">
            <div style="font-size:10px;color:#9db8b0;font-weight:700;letter-spacing:0.5px;">'
                . ($isNas ? 'TAMPIL SEMUA WILAYAH — NASIONAL' : 'WILAYAH AKTIF — FILTER BPS') . '
            </div>
            <div style="font-size:16px;font-weight:800;color:#f5e7a4;margin-top:2px;">' . htmlspecialchars($nama) . '</div>
            <div style="font-size:11px;color:#9db8b0;margin-top:1px;">
                BPS Domain: <code style="color:#bdd9ce;">' . htmlspecialchars($domain) . '</code> · webapi.bps.go.id
            </div>
            ' . $filterHtml . '
        </div>
        <div style="text-align:right;flex-shrink:0;">
            <div style="font-size:10px;color:#9db8b0;">Ganti wilayah</div>
            <div style="font-size:12px;color:#f5e7a4;font-weight:700;">sidebar kiri ↩</div>
        </div>
    </div>';
}

/**
 * Render dropdown Provinsi → Kota/Kab → Kecamatan (client-side fetch dari BPS API).
 * Dipakai di semua form modal CRUD (tambah / edit).
 *
 * @param string $prefix  Prefix nama field, e.g. 'petani' → petani_provinsi
 * @param array  $sel     Nilai awal edit: ['prov'=>'...','kota'=>'...','kec'=>'...']
 */
function bps_wilayah_fields(string $prefix = 'f', array $sel = []): string {
    $uid   = 'bw' . substr(md5($prefix . mt_rand()), 0, 7);
    $sProv = htmlspecialchars($sel['prov'] ?? '');
    $sKota = htmlspecialchars($sel['kota'] ?? '');
    $sKec  = htmlspecialchars($sel['kec']  ?? '');
    $KEY   = BPS_API_KEY;

    /* ── HTML ─────────────────────────────────────────────────────── */
    $h  = '<div style="background:#f0f9f5;border-radius:16px;padding:16px 18px;margin-bottom:14px;border:1.5px solid #b7dfc8;">';

    /* Header */
    $h .= '<div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">';
    $h .= '<span style="background:#1e4a3b;color:#f5e7a4;font-size:10px;font-weight:800;padding:2px 10px;border-radius:20px;letter-spacing:0.5px;">BPS API</span>';
    $h .= '<span style="font-size:13px;font-weight:700;color:#1e4a3b;">Wilayah Administratif — Live dari BPS</span>';
    $h .= '</div>';

    /* Provinsi */
    $h .= '<label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">'
        . '<i class="fas fa-map" style="color:#2d6a4f;margin-right:4px;"></i> Provinsi <span style="color:#dc2626;">*</span></label>';
    $h .= '<select name="' . $prefix . '_provinsi" id="' . $uid . '_prov"'
        . ' style="width:100%;padding:10px 12px;border-radius:12px;border:1.5px solid #c8e6d0;font-size:13px;background:white;margin-bottom:10px;outline:none;cursor:pointer;">';
    $h .= '<option value="">Memuat provinsi...</option>';
    if ($sProv) $h .= '<option value="' . $sProv . '" selected>' . $sProv . '</option>';
    $h .= '</select>';

    /* Kota */
    $h .= '<label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">'
        . '<i class="fas fa-city" style="color:#2d6a4f;margin-right:4px;"></i> Kota / Kabupaten <span style="color:#dc2626;">*</span></label>';
    $h .= '<select name="' . $prefix . '_kota" id="' . $uid . '_kota"'
        . ' style="width:100%;padding:10px 12px;border-radius:12px;border:1.5px solid #c8e6d0;font-size:13px;background:white;margin-bottom:10px;outline:none;cursor:pointer;"'
        . ($sProv ? '' : ' disabled') . '>';
    $h .= '<option value="">-- Pilih provinsi dulu --</option>';
    if ($sKota) $h .= '<option value="' . $sKota . '" selected>' . $sKota . '</option>';
    $h .= '</select>';

    /* Kecamatan */
    $h .= '<label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">'
        . '<i class="fas fa-road" style="color:#2d6a4f;margin-right:4px;"></i> Kecamatan</label>';
    $h .= '<select name="' . $prefix . '_kecamatan" id="' . $uid . '_kec"'
        . ' style="width:100%;padding:10px 12px;border-radius:12px;border:1.5px solid #c8e6d0;font-size:13px;background:white;margin-bottom:6px;outline:none;cursor:pointer;"'
        . ($sKota ? '' : ' disabled') . '>';
    $h .= '<option value="">-- Pilih kota dulu --</option>';
    if ($sKec) $h .= '<option value="' . $sKec . '" selected>' . $sKec . '</option>';
    $h .= '</select>';

    /* Status */
    $h .= '<div id="' . $uid . '_sta" style="font-size:11px;color:#6b7280;min-height:16px;margin-top:2px;display:flex;align-items:center;gap:4px;">'
        . '<i class="fas fa-spinner fa-spin" style="font-size:9px;color:#2d6a4f;"></i>'
        . '<span>Menghubungi webapi.bps.go.id...</span></div>';
    $h .= '</div>';

    /* ── JavaScript ───────────────────────────────────────────────── */
    $h .= '<script>(function(){';
    $h .= 'var KEY="' . $KEY . '",U="' . $uid . '";';
    $h .= 'var sp=document.getElementById(U+"_prov"),sk=document.getElementById(U+"_kota"),sc=document.getElementById(U+"_kec"),sta=document.getElementById(U+"_sta");';

    /* status helpers */
    $h .= 'function ok(m){sta.innerHTML=\'<i class="fas fa-check-circle" style="font-size:9px;color:#16a34a;"></i><span style="color:#16a34a;margin-left:3px;">\'+m+"</span>";}';
    $h .= 'function ld(m){sta.innerHTML=\'<i class="fas fa-spinner fa-spin" style="font-size:9px;color:#2d6a4f;"></i><span style="color:#6b7280;margin-left:3px;">\'+m+"</span>";}';
    $h .= 'function er(m){sta.innerHTML=\'<i class="fas fa-exclamation-circle" style="font-size:9px;color:#dc2626;"></i><span style="color:#dc2626;margin-left:3px;">\'+m+"</span>";}';

    /* fetch helper */
    $h .= 'function go(url,cb){fetch(url).then(function(r){return r.json();}).then(cb).catch(function(){er("Gagal konek BPS");});}';

    /* build options */
    $h .= 'function build(sel,list,vk,nk,dk,ph,auto){';
    $h .= 'sel.innerHTML="<option value=\\"\\">"+ph+"</option>";';
    $h .= 'list.forEach(function(x){var o=document.createElement("option");o.value=x[vk];o.textContent=x[nk];if(dk&&x[dk])o.setAttribute("data-id",x[dk]);sel.appendChild(o);});';
    $h .= 'sel.disabled=false;if(auto&&auto!==""){sel.value=auto;if(sel.value===auto)sel.dispatchEvent(new Event("change"));}}';

    /* Load provinsi */
    $h .= 'ld("Memuat provinsi...");';
    $h .= 'go("https://webapi.bps.go.id/v1/api/domain?type=prov&key="+KEY,function(d){';
    $h .= 'var list=(d.data&&d.data[1])?d.data[1]:[];';
    $h .= 'build(sp,list,"domain_name","domain_name","domain_id","-- Pilih Provinsi --","' . $sProv . '");';
    $h .= 'ok(list.length+" provinsi dimuat dari BPS");});';

    /* Provinsi → Kota */
    $h .= 'sp.addEventListener("change",function(){';
    $h .= 'var opt=sp.options[sp.selectedIndex];var pid=opt?opt.getAttribute("data-id")||"":"";';
    $h .= 'sk.innerHTML="<option value=\\"\\">Memuat kota...</option>";sk.disabled=true;';
    $h .= 'sc.innerHTML="<option value=\\"\\">-- Pilih kota dulu --</option>";sc.disabled=true;';
    $h .= 'if(!this.value){sk.innerHTML="<option value=\\"\\">-- Pilih provinsi dulu --</option>";return;}';
    $h .= 'ld("Memuat kota/kabupaten...");';
    /* jika pid belum ada, cari dulu dari list prov */
    $h .= 'function doLoadKota(domId){';
    $h .= 'go("https://webapi.bps.go.id/v1/api/domain?type=kabbyprov&prov="+domId+"&key="+KEY,function(d){';
    $h .= 'var list=(d.data&&d.data[1])?d.data[1]:[];';
    $h .= 'build(sk,list,"domain_name","domain_name","domain_id","-- Pilih Kota/Kabupaten --","' . $sKota . '");';
    $h .= 'ok(list.length+" kota/kab dimuat");});}';
    $h .= 'if(pid){doLoadKota(pid);}else{';
    $h .= 'go("https://webapi.bps.go.id/v1/api/domain?type=prov&key="+KEY,function(d){';
    $h .= 'var list=(d.data&&d.data[1])?d.data[1]:[];var found="";var pName=sp.value;';
    $h .= 'list.forEach(function(x){if(x.domain_name===pName)found=x.domain_id;});';
    $h .= 'if(found)doLoadKota(found);else er("Domain provinsi tidak ditemukan");});}';
    $h .= '});';

    /* Kota → Kecamatan */
    $h .= 'sk.addEventListener("change",function(){';
    $h .= 'sc.innerHTML="<option value=\\"\\">Memuat kecamatan...</option>";sc.disabled=true;';
    $h .= 'if(!this.value){sc.innerHTML="<option value=\\"\\">-- Pilih kota dulu --</option>";return;}';
    $h .= 'var opt=sk.options[sk.selectedIndex];var kid=opt?opt.getAttribute("data-id")||"":"";';
    $h .= 'if(!kid){sc.innerHTML="<option value=\\"\\">Kecamatan tidak tersedia</option>";sc.disabled=false;ok("Kecamatan tidak tersedia");return;}';
    $h .= 'ld("Memuat kecamatan...");';
    $h .= 'var mfd=kid.length===4?kid+"000":kid;';
    $h .= 'go("https://webapi.bps.go.id/v1/api/interoperabilitas/datasource/simdasi/id/28/?parent="+mfd+"&key="+KEY,function(d){';
    $h .= 'var list=(d.data&&d.data[1])?d.data[1]:[];';
    $h .= 'sc.innerHTML="<option value=\\"\\">-- Pilih Kecamatan --</option>";';
    $h .= 'if(!list.length){sc.innerHTML="<option value=\\"\\">Tidak tersedia untuk kota ini</option>";sc.disabled=false;ok("Kecamatan tidak tersedia");return;}';
    $h .= 'list.forEach(function(x){var nm=x.nama||x.domain_name||"";var o=document.createElement("option");o.value=nm;o.textContent=nm;sc.appendChild(o);});';
    $h .= 'sc.disabled=false;if("' . $sKec . '")sc.value="' . $sKec . '";ok(list.length+" kecamatan dimuat");});';
    $h .= '});';

    /* auto-trigger untuk mode edit */
    $h .= 'if(sp.value)setTimeout(function(){sp.dispatchEvent(new Event("change"));},400);';
    $h .= '})();</script>';

    return $h;
}
