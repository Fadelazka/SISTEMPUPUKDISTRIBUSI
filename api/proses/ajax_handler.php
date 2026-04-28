<?php
/**
 * AJAX Handler Lengkap - FINAL FIX
 */
error_reporting(0);

// Jaring Pengaman Error Database
set_exception_handler(function($e) {
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(['status'=>'error', 'msg'=>'Crash DB: ' . $e->getMessage()]);
    exit();
});

// 1. CEK COOKIE LOGIN
if (!isset($_COOKIE['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status'=>'error','msg'=>'Unauthorized']);
    exit();
}

require __DIR__ .  '/../service/koneksi.php';

$action = trim($_POST['action'] ?? '');
$type   = $_POST['type'] ?? '';

// =====================================================================
// 1. GET PAGE
// =====================================================================
if ($action === 'getPage') {
    $page = preg_replace('/[^a-zA-Z0-9_]/','',$_POST['page']??'');
    include __DIR__ . "/../../views/" . $page . ".php";
    exit();
}

// =====================================================================
// 2. GET FORM (MODAL)
// =====================================================================
if ($action === 'getForm') {
    $id = intval($_POST['id'] ?? 0);
    include __DIR__ . "/../../views/forms/form_" . $type . ".php";
    exit();
}

// =====================================================================
// 3. LOGIKA SIMPAN (SAVE) - UNTUK PETANI, DISTRIBUSI, USER
// =====================================================================
if ($action === 'save') {
    header('Content-Type: application/json');
    $id = intval($_POST['id'] ?? 0);

    // --- A. PETANI ---
    if ($type === 'petani') {
        $nama    = mysqli_real_escape_string($koneksi, $_POST['nama'] ?? '');
        $desa    = mysqli_real_escape_string($koneksi, $_POST['desa'] ?? '');
        $luas    = mysqli_real_escape_string($koneksi, $_POST['luas_lahan'] ?? '');
        $alokasi = mysqli_real_escape_string($koneksi, $_POST['alokasi'] ?? '');

        if ($id > 0) {
            $sql = "UPDATE petani SET nama='$nama', desa='$desa', luas_lahan='$luas', alokasi='$alokasi' WHERE id=$id";
        } else {
            $sql = "INSERT INTO petani (nama, desa, luas_lahan, alokasi) VALUES ('$nama', '$desa', '$luas', '$alokasi')";
        }
    } 
    // --- B. DISTRIBUSI ---
    elseif ($type === 'distribusi') {
        $tgl      = mysqli_real_escape_string($koneksi, $_POST['tgl'] ?? '');
        $kelompok = mysqli_real_escape_string($koneksi, $_POST['kelompok'] ?? '');
        $pupuk    = mysqli_real_escape_string($koneksi, $_POST['pupuk'] ?? '');
        $jumlah   = mysqli_real_escape_string($koneksi, $_POST['jumlah'] ?? '');
        $no_do    = mysqli_real_escape_string($koneksi, $_POST['no_do'] ?? '');

        if ($id > 0) {
            $sql = "UPDATE distribusi SET tgl='$tgl', kelompok='$kelompok', pupuk='$pupuk', jumlah='$jumlah', no_do='$no_do' WHERE id=$id";
        } else {
            $sql = "INSERT INTO distribusi (tgl, kelompok, pupuk, jumlah, no_do) VALUES ('$tgl', '$kelompok', '$pupuk', '$jumlah', '$no_do')";
        }
    }
    // --- C. KELOLA USER (ADMIN ONLY) ---
    elseif ($type === 'user' && $_COOKIE['role'] === 'admin') {
        $nama  = mysqli_real_escape_string($koneksi, $_POST['nama'] ?? '');
        $email = mysqli_real_escape_string($koneksi, $_POST['email'] ?? '');
        $role  = mysqli_real_escape_string($koneksi, $_POST['role'] ?? 'user');
        $pass  = $_POST['password'] ?? '';

        if ($id > 0) {
            $passSql = !empty($pass) ? ", password='".password_hash($pass, PASSWORD_DEFAULT)."'" : "";
            $sql = "UPDATE users SET nama='$nama', email='$email', role='$role' $passSql WHERE id=$id";
        } else {
            $hashed = password_hash(!empty($pass) ? $pass : '123456', PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (nama, email, role, password) VALUES ('$nama', '$email', '$role', '$hashed')";
        }
    }

    if (isset($sql) && mysqli_query($koneksi, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => mysqli_error($koneksi)]);
    }
    exit();
}

// =====================================================================
// 4. DELETE DATA
// =====================================================================
if ($action === 'delete') {
    header('Content-Type: application/json');
    $id = intval($_POST['id'] ?? 0);
    $table = ($type === 'petani' || $type === 'distribusi' || $type === 'user') ? $type : '';
    
    if ($table) {
        $sql = "DELETE FROM $table WHERE id=$id";
        if ($table === 'user') $sql .= " AND role != 'admin'"; // Jangan hapus admin utama
        
        mysqli_query($koneksi, $sql);
        echo json_encode(['status' => 'success']);
    }
    exit();
}

// =====================================================================
// 5. UPDATE PROFILE (DARI HALAMAN PROFIL)
// =====================================================================
if ($action === 'updateProfile') {
    header('Content-Type: application/json');
    $uid = intval($_COOKIE['id'] ?? 0);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama'] ?? '');
    $email = mysqli_real_escape_string($koneksi, $_POST['email'] ?? '');

    $sql = "UPDATE users SET nama='$nama', email='$email' WHERE id=$uid";

    if (mysqli_query($koneksi, $sql)) {
        setcookie('nama', $nama, time() + (86400 * 30), "/");
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => mysqli_error($koneksi)]);
    }
    exit();
}