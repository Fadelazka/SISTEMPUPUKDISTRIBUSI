<?php
/**
 * AJAX Handler Lengkap
 */
error_reporting(0);
session_start();

iif (!isset($_COOKIE['id'])) {
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
    while ($row = mysqli_fetch_assoc($r1)) {
        if (!empty($row['kecamatan'])) $kecList[$row['kecamatan']] = true;
    }
    $r2 = mysqli_query($koneksi,$sql2);
    while ($row = mysqli_fetch_assoc($r2)) {
        if (!empty($row['kecamatan'])) $kecList[$row['kecamatan']] = true;
    }
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

    // Perbaikan path: tambahkan kemungkinan file langsung di folder forms
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

    // PETANI
    if ($type === 'petani') {
        $id        = intval($_POST['id']??0);
        $nama      = esc('nama');
        $desa      = esc('desa');
        $luas      = esc('luas_lahan');
        $alokasi   = esc('alokasi');
        $status    = esc('status');
        $tgl       = !empty($_POST['tgl_terima'])?"'".escv($_POST['tgl_terima'])."'":"NULL";
        $provinsi  = esc('petani_provinsi');
        $kota      = esc('petani_kota');
        $kecamatan = esc('petani_kecamatan');

        if ($id>0) {
            $sql = "UPDATE petani SET nama='$nama',desa='$desa',luas_lahan='$luas',
                    alokasi='$alokasi',status='$status',tgl_terima=$tgl,
                    provinsi='$provinsi',kota='$kota',kecamatan='$kecamatan' WHERE id=$id";
        } else {
            $sql = "INSERT INTO petani(nama,desa,luas_lahan,alokasi,status,tgl_terima,provinsi,kota,kecamatan)
                    VALUES('$nama','$desa','$luas','$alokasi','$status',$tgl,'$provinsi','$kota','$kecamatan')";
        }
        $response = mysqli_query($koneksi,$sql) ? ['status'=>'success'] : ['status'=>'error','msg'=>mysqli_error($koneksi)];
    }

    // DISTRIBUSI
    elseif ($type === 'distribusi') {
        $id        = intval($_POST['id']??0);
        $tgl       = esc('tgl');
        $kelompok  = esc('kelompok');
        $pupuk     = esc('pupuk');
        $jumlah    = esc('jumlah');
        $tujuan    = esc('tujuan');
        $no_do     = esc('no_do');
        $provinsi  = esc('dist_provinsi');
        $kota      = esc('dist_kota');
        $kecamatan = esc('dist_kecamatan');

        if ($id>0) {
            $sql = "UPDATE distribusi SET tgl='$tgl',kelompok='$kelompok',pupuk='$pupuk',
                    jumlah='$jumlah',tujuan='$tujuan',no_do='$no_do',
                    provinsi='$provinsi',kota='$kota',kecamatan='$kecamatan' WHERE id=$id";
        } else {
            $sql = "INSERT INTO distribusi(tgl,kelompok,pupuk,jumlah,tujuan,no_do,provinsi,kota,kecamatan)
                    VALUES('$tgl','$kelompok','$pupuk','$jumlah','$tujuan','$no_do','$provinsi','$kota','$kecamatan')";
        }
        $response = mysqli_query($koneksi,$sql) ? ['status'=>'success'] : ['status'=>'error','msg'=>mysqli_error($koneksi)];
    }

    // LAPORAN
    elseif ($type === 'laporan') {
        $id        = intval($_POST['id']??0);
        $judul     = esc('judul');
        $deskripsi = esc('deskripsi');
        $provinsi  = esc('lap_provinsi');
        $kota      = esc('lap_kota');
        $kecamatan = esc('lap_kecamatan');

        if ($id>0) {
            $sql = "UPDATE laporan SET judul='$judul',deskripsi='$deskripsi',
                    provinsi='$provinsi',kota='$kota',kecamatan='$kecamatan' WHERE id=$id";
        } else {
            $sql = "INSERT INTO laporan(judul,deskripsi,provinsi,kota,kecamatan)
                    VALUES('$judul','$deskripsi','$provinsi','$kota','$kecamatan')";
        }
        $response = mysqli_query($koneksi,$sql) ? ['status'=>'success'] : ['status'=>'error','msg'=>mysqli_error($koneksi)];
    }

    // USER
    elseif ($type === 'user' && $_SESSION['role']==='admin') {
        $id    = intval($_POST['id']??0);
        $nama  = esc('nama');
        $email = esc('email');
        $role  = esc('role');
        $prov  = esc('user_provinsi');
        $kota2 = esc('user_kota');
        $kec   = esc('user_kecamatan');

        if ($id>0) {
            $pwSql='';
            if(!empty($_POST['password'])){
                $pw=escv(password_hash($_POST['password'],PASSWORD_DEFAULT));
                $pwSql=",password='$pw'";
            }
            $sql="UPDATE users SET nama='$nama',email='$email',role='$role'$pwSql,
                  provinsi='$prov',kota='$kota2',kecamatan='$kec' WHERE id=$id";
        } else {
            $pw=escv(password_hash($_POST['password']??'password123',PASSWORD_DEFAULT));
            $sql="INSERT INTO users(nama,email,password,role,provinsi,kota,kecamatan)
                  VALUES('$nama','$email','$pw','$role','$prov','$kota2','$kec')";
        }
        $response = mysqli_query($koneksi,$sql) ? ['status'=>'success'] : ['status'=>'error','msg'=>mysqli_error($koneksi)];
    }

    echo json_encode($response);
    exit();
}

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
    } elseif ($type==='user' && $_SESSION['role']==='admin') {
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
    header('Content-Type: application/json; charset=utf-8');
    $uid    = $_SESSION['id'];
    $fields = ['nama','bio','email','phone','nip','instansi','address','provinsi','kota','kecamatan'];
    $parts  = [];
    foreach ($fields as $f) {
        $v=mysqli_real_escape_string($koneksi,$_POST[$f]??'');
        $parts[]="$f='$v'";
    }
    $tgl = !empty($_POST['tgl_lahir'])
           ? "tgl_lahir='".mysqli_real_escape_string($koneksi,$_POST['tgl_lahir'])."'"
           : "tgl_lahir=NULL";
    $parts[]=$tgl;
    $sql="UPDATE users SET ".implode(',',$parts)." WHERE id=$uid";
    if(mysqli_query($koneksi,$sql)){
        $_SESSION['nama']=$_POST['nama']??$_SESSION['nama'];
        echo json_encode(['status'=>'success']);
    } else {
        echo json_encode(['status'=>'error','msg'=>mysqli_error($koneksi)]);
    }
    exit();
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['status'=>'error','msg'=>"Action tidak dikenal: '$action'"]);