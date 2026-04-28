<?php
/**
 * AJAX Handler Lengkap - FULL FIX VERCEL
 */
error_reporting(0);

// Jaring Pengaman Error Database
set_exception_handler(function($e) {
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(['status'=>'error', 'msg'=>'Crash DB: ' . $e->getMessage()]);
    exit();
});

// 1. UBAH SESSION JADI COOKIE DI SINI
if (!isset($_COOKIE['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status'=>'error','msg'=>'Unauthorized']);
    exit();
}

require __DIR__ .  '/../service/koneksi.php';

$action = trim($_POST['action'] ?? '');

// =====================================================================
// 1. GET PAGE
// =====================================================================
if ($action === 'getPage') {
    $page = preg_replace('/[^a-zA-Z0-9_]/','',$_POST['page']??'');
    $_REQUEST['bps_wilayah'] = $_POST['bps_wilayah'] ?? 'Nasional';
    $_REQUEST['bps_domain']  = $_POST['bps_domain']  ?? '0000';
    $_REQUEST['filter_prov'] = $_POST['filter_prov'] ?? '';
    $_REQUEST['filter_kota'] = $_POST['filter_kota'] ?? '';
    $_REQUEST['filter_kec']  = $_POST['filter_kec']  ?? '';

    $file = __DIR__.'/pages/'.$page.'.php';
    if (file_exists($file)) {
        ob_start(); include $file; echo ob_get_clean();
    } else {
        echo "<div style='color:red;padding:20px;font-size:15px;'>Halaman tidak ditemukan: ".htmlspecialchars($page)."</div>";
    }
    exit();
}

// =====================================================================
// 2. GET KECAMATAN dari DB lokal
// =====================================================================
if ($action === 'getKecamatan') {
    header('Content-Type: application/json; charset=utf-8');
    $prov  = mysqli_real_escape_string($koneksi, $_POST['filter_prov'] ?? '');
    $kota  = mysqli_real_escape_string($koneksi, $_POST['filter_kota'] ?? '');

    $sql1 = "SELECT DISTINCT kecamatan FROM distribusi WHERE kecamatan IS NOT NULL AND kecamatan != ''";
    if ($prov) $sql1 .= " AND provinsi LIKE '%$prov%'";
    if ($kota) $sql1 .= " AND kota LIKE '%$kota%'";

    $sql2 = "SELECT DISTINCT kecamatan FROM petani WHERE kecamatan IS NOT NULL AND kecamatan != ''";
    if ($prov) $sql2 .= " AND provinsi LIKE '%$prov%'";
    if ($kota) $sql2 .= " AND kota LIKE '%$kota%'";

    $kecList = [];
    $r1 = mysqli_query($koneksi,$sql1);
    if($r1) while ($row = mysqli_fetch_assoc($r1)) { if (!empty($row['kecamatan'])) $kecList[$row['kecamatan']] = true; }
    $r2 = mysqli_query($koneksi,$sql2);
    if($r2) while ($row = mysqli_fetch_assoc($r2)) { if (!empty($row['kecamatan'])) $kecList[$row['kecamatan']] = true; }
    $result = array_keys($kecList);
    sort($result);
    echo json_encode(['status'=>'success','data'=>$result]);
    exit();
}

// =====================================================================
// 3. GET FORM (HTML)
// =====================================================================
if ($action === 'getForm') {
    $type = preg_replace('/[^a-zA-Z0-9_]/','',$_POST['type']??'');
    $id   = intval($_POST['id']??0);

    $paths = [
        __DIR__.'/forms/'.$type.'_form.php',
        __DIR__.'/form_'.$type.'.php',
        __DIR__.'/'.$type.'_form.php'
    ];
    $formFile = null;
    foreach ($paths as $p) { if (file_exists($p)) { $formFile=$p; break; } }

    if (!$formFile) {
        echo "<div style='color:red;padding:20px;font-size:15px;'>Form tidak ditemukan untuk tipe: ".htmlspecialchars($type)."</div>";
        exit();
    }
    ob_start(); include $formFile; echo ob_get_clean();
    exit();
}

// =====================================================================
// 4. SAVE DATA
// =====================================================================
if ($action === 'save') {
    header('Content-Type: application/json; charset=utf-8');
    $type     = $_POST['type'] ?? '';
    $response = ['status'=>'error','msg'=>'Unknown error'];

    function esc($k){ global $koneksi; return mysqli_real_escape_string($koneksi,$_POST[$k]??''); }
    function escv($v){ global $koneksi; return mysqli_real_escape_string($koneksi,$v); }

    /**
     * Ambil field wilayah dengan fallback prefix BPS widget.
     * bps_widget.php menghasilkan name="PREFIX_provinsi", bukan "provinsi".
     * Fungsi ini coba semua kemungkinan prefix agar selalu dapat nilainya.
     */
    function escWilayah($field) {
        global $koneksi;
        // Coba tanpa prefix dulu
        if (!empty($_POST[$field])) {
            return mysqli_real_escape_string($koneksi, $_POST[$field]);
        }
        // Cari semua key yang diakhiri dengan _provinsi / _kota / _kecamatan
        foreach ($_POST as $k => $v) {
            if (!empty($v) && preg_match('/_' . preg_quote($field, '/') . '$/', $k)) {
                return mysqli_real_escape_string($koneksi, $v);
            }
        }
        return '';
    }

    // PETANI
    if ($type === 'petani') {
        $id        = intval($_POST['id']??0);
        $nama      = esc('nama');
        $desa      = esc('desa');
        $luas      = $_POST['luas_lahan'] ? escv($_POST['luas_lahan']) : '0';
        $alokasi   = $_POST['alokasi'] ? escv($_POST['alokasi']) : '0';
        $status    = esc('status');
        $tgl       = !empty($_POST['tgl_terima'])?"'".escv($_POST['tgl_terima'])."'":"NULL";
        $provinsi  = escWilayah('provinsi');
        $kota      = escWilayah('kota');
        $kecamatan = escWilayah('kecamatan');
    
        if ($id>0) {
            $sql = "UPDATE petani SET nama='$nama',desa='$desa',luas_lahan='$luas', alokasi='$alokasi',status='$status',tgl_terima=$tgl, provinsi='$provinsi',kota='$kota',kecamatan='$kecamatan' WHERE id=$id";
        } else {
            $sql = "INSERT INTO petani(nama,desa,luas_lahan,alokasi,status,tgl_terima,provinsi,kota,kecamatan) VALUES('$nama','$desa','$luas','$alokasi','$status',$tgl,'$provinsi','$kota','$kecamatan')";
        }
        $response = mysqli_query($koneksi,$sql) ? ['status'=>'success'] : ['status'=>'error','msg'=>mysqli_error($koneksi)];
    }

    // DISTRIBUSI
    elseif ($type === 'distribusi') {
        $id        = intval($_POST['id']??0);
        $tgl       = esc('tgl');
        $kelompok  = esc('kelompok');
        $pupuk     = esc('pupuk');
        $jumlah    = $_POST['jumlah'] ? escv($_POST['jumlah']) : '0';
        $tujuan    = esc('tujuan');
        $no_do     = esc('no_do');
        $provinsi  = escWilayah('provinsi');
        $kota      = escWilayah('kota');
        $kecamatan = escWilayah('kecamatan');

        if ($id>0) {
            $sql = "UPDATE distribusi SET tgl='$tgl',kelompok='$kelompok',pupuk='$pupuk', jumlah='$jumlah',tujuan='$tujuan',no_do='$no_do', provinsi='$provinsi',kota='$kota',kecamatan='$kecamatan' WHERE id=$id";
        } else {
            $sql = "INSERT INTO distribusi(tgl,kelompok,pupuk,jumlah,tujuan,no_do,provinsi,kota,kecamatan) VALUES('$tgl','$kelompok','$pupuk','$jumlah','$tujuan','$no_do','$provinsi','$kota','$kecamatan')";
        }
        $response = mysqli_query($koneksi,$sql) ? ['status'=>'success'] : ['status'=>'error','msg'=>mysqli_error($koneksi)];
    }

    // LAPORAN
    elseif ($type === 'laporan') {
        $id        = intval($_POST['id']??0);
        $judul     = esc('judul');
        $deskripsi = esc('deskripsi');
        $provinsi  = escWilayah('provinsi');
        $kota      = escWilayah('kota');
        $kecamatan = escWilayah('kecamatan');

        if ($id>0) {
            $sql = "UPDATE laporan SET judul='$judul',deskripsi='$deskripsi', provinsi='$provinsi',kota='$kota',kecamatan='$kecamatan' WHERE id=$id";
        } else {
            $sql = "INSERT INTO laporan(judul,deskripsi,provinsi,kota,kecamatan) VALUES('$judul','$deskripsi','$provinsi','$kota','$kecamatan')";
        }
        $response = mysqli_query($koneksi,$sql) ? ['status'=>'success'] : ['status'=>'error','msg'=>mysqli_error($koneksi)];
    }

    // USER (Telah ditambahkan kurung kurawal '{' dan diubah menjadi elseif)
    elseif ($type === 'user') { 
        $id = intval($_POST['id'] ?? 0);
        $nama = mysqli_real_escape_string($koneksi, $_POST['nama'] ?? '');
        $email = mysqli_real_escape_string($koneksi, $_POST['email'] ?? '');
        $role = mysqli_real_escape_string($koneksi, $_POST['role'] ?? 'user');
        
        $password = $_POST['password'] ?? '';
        $passQuery = "";
        
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $passQuery = ", password='$hashed'";
        }

        if ($id > 0) {
            $sql = "UPDATE users SET nama='$nama', email='$email', role='$role' $passQuery WHERE id=$id";
        } else {
            if (empty($password)) {
                $hashed = password_hash('123456', PASSWORD_DEFAULT);
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
            }
            $sql = "INSERT INTO users (nama, email, role, password) VALUES ('$nama', '$email', '$role', '$hashed')";
        }

        $res = mysqli_query($koneksi, $sql);
        $response = $res ? ['status' => 'success'] : ['status' => 'error', 'msg' => mysqli_error($koneksi)];
    } // Kurung kurawal penutup untuk blok user

    // Kunci penyelesaian masalah (Mengirim respons status success secara terpusat)
    echo json_encode($response);
    exit();
} // Ini adalah kurung penutup asli dari fungsi if ($action === 'save')


// =====================================================================
// 5. DELETE
// =====================================================================
if ($action === 'delete') {
    header('Content-Type: application/json; charset=utf-8');
    $type = $_POST['type']??'';
    $id   = intval($_POST['id']??0);
    $map  = ['petani'=>'petani','distribusi'=>'distribusi','laporan'=>'laporan'];
    if (isset($map[$type])) {
        $tbl=$map[$type];
        mysqli_query($koneksi,"DELETE FROM $tbl WHERE id=$id");
        echo json_encode(['status'=>'success']);
    } elseif ($type==='user' && isset($_COOKIE['role']) && $_COOKIE['role']==='admin') {
        mysqli_query($koneksi,"DELETE FROM users WHERE id=$id AND role!='admin'");
        echo json_encode(['status'=>'success']);
    } else {
        echo json_encode(['status'=>'error','msg'=>'Tipe tidak valid']);
    }
    exit();
}

// =====================================================================
// 6. UPDATE PROFILE
// =====================================================================
if ($action === 'updateProfile') {
    $uid      = intval($_COOKIE['id'] ?? 0);
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']     ?? '');
    $email    = mysqli_real_escape_string($koneksi, $_POST['email']    ?? '');
    $bio      = mysqli_real_escape_string($koneksi, $_POST['bio']      ?? '');
    $phone    = mysqli_real_escape_string($koneksi, $_POST['phone']    ?? '');
    $nip      = mysqli_real_escape_string($koneksi, $_POST['nip']      ?? '');
    $instansi = mysqli_real_escape_string($koneksi, $_POST['instansi'] ?? '');
    $address  = mysqli_real_escape_string($koneksi, $_POST['address']  ?? '');

    $sql = "UPDATE users SET 
        nama='$nama', 
        email='$email',
        bio='$bio',
        phone='$phone',
        nip='$nip',
        instansi='$instansi',
        address='$address'
        WHERE id=$uid";

    if (mysqli_query($koneksi, $sql)) {
        setcookie('nama', $nama, time() + (86400 * 30), "/");
        echo json_encode(['status' => 'success']);
    } else {
        // Jika kolom belum ada di DB, coba fallback update nama & email saja
        $sqlFallback = "UPDATE users SET nama='$nama', email='$email' WHERE id=$uid";
        if (mysqli_query($koneksi, $sqlFallback)) {
            setcookie('nama', $nama, time() + (86400 * 30), "/");
            echo json_encode(['status' => 'success', 'msg' => 'Tersimpan sebagian (kolom bio/phone/nip belum ada di DB)']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => mysqli_error($koneksi)]);
        }
    }
    exit();
}

// =====================================================================
// FALLBACK: Action tidak dikenal (Pindahkan ke paling akhir)
// =====================================================================
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['status'=>'error','msg'=>"Action tidak dikenal: '$action'"]);