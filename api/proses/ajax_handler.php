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

// --- BAGIAN PETANI ---
if ($type === 'petani') {
    $id = intval($_POST['id'] ?? 0);
    $nama = esc('nama');
    $desa = esc('desa');
    $luas = esc('luas_lahan');
    $alokasi = esc('alokasi');
    $status = esc('status');

    if ($id > 0) {
        $sql = "UPDATE petani SET nama='$nama', desa='$desa', luas_lahan='$luas', alokasi='$alokasi', status='$status' WHERE id=$id";
    } else {
        $sql = "INSERT INTO petani (nama, desa, luas_lahan, alokasi, status) VALUES ('$nama', '$desa', '$luas', '$alokasi', '$status')";
    }
    $response = mysqli_query($koneksi, $sql) ? ['status'=>'success'] : ['status'=>'error', 'msg'=>mysqli_error($koneksi)];

// --- BAGIAN DISTRIBUSI ---
} elseif ($type === 'distribusi') {
    $id = intval($_POST['id'] ?? 0);
    $tgl = esc('tgl');
    $kelompok = esc('kelompok');
    $pupuk = esc('pupuk');
    $jumlah = esc('jumlah');
    $no_do = esc('no_do');

    if ($id > 0) {
        $sql = "UPDATE distribusi SET tgl='$tgl', kelompok='$kelompok', pupuk='$pupuk', jumlah='$jumlah', no_do='$no_do' WHERE id=$id";
    } else {
        $sql = "INSERT INTO distribusi (tgl, kelompok, pupuk, jumlah, no_do) VALUES ('$tgl', '$kelompok', '$pupuk', '$jumlah', '$no_do')";
    }
    $response = mysqli_query($koneksi, $sql) ? ['status'=>'success'] : ['status'=>'error', 'msg'=>mysqli_error($koneksi)];

// --- BAGIAN KELOLA USER ---
} elseif ($type === 'user') {
    $id = intval($_POST['id'] ?? 0);
    $nama = esc('nama');
    $email = esc('email');
    $role = esc('role');
    $pass = $_POST['password'] ?? '';

    if ($id > 0) {
        if (!empty($pass)) {
            $sql = "UPDATE users SET nama='$nama', email='$email', role='$role', password='$pass' WHERE id=$id";
        } else {
            $sql = "UPDATE users SET nama='$nama', email='$email', role='$role' WHERE id=$id";
        }
    } else {
        $sql = "INSERT INTO users (nama, email, role, password) VALUES ('$nama', '$email', '$role', '$pass')";
    }
    $response = mysqli_query($koneksi, $sql) ? ['status'=>'success'] : ['status'=>'error', 'msg'=>mysqli_error($koneksi)];

// --- BAGIAN PROFIL ---
} elseif ($type === 'profile') {
    $id = $_COOKIE['id'];
    $nama = esc('nama');
    $email = esc('email');
    
    // PERHATIKAN: Hapus tgl_lahir karena tidak ada di tabel users
    $sql = "UPDATE users SET nama='$nama', email='$email' WHERE id=$id";
    
    if (mysqli_query($koneksi, $sql)) {
        setcookie('nama', $nama, time() + (86400 * 30), "/");
        $response = ['status'=>'success'];
    } else {
        $response = ['status'=>'error', 'msg'=>mysqli_error($koneksi)];
    }
}

echo json_encode($response);
exit;