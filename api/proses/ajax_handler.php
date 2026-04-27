<?php
/**
 * AJAX Handler - FIX TOTAL DATA HILANG
 */
error_reporting(0);
require __DIR__ .  '/../service/koneksi.php';

// Set header JSON agar browser paham
header('Content-Type: application/json; charset=utf-8');

// Ambil parameter utama
$action = $_POST['action'] ?? '';
$type   = $_POST['type'] ?? '';
$uid    = isset($_COOKIE['id']) ? intval($_COOKIE['id']) : 0;

// Fungsi pembersih input
function esc($koneksi, $key) {
    return mysqli_real_escape_string($koneksi, $_POST[$key] ?? '');
}

// 1. LOGIKA UNTUK PETANI
if ($type === 'petani') {
    $id = intval($_POST['id'] ?? 0);
    $nama = esc($koneksi, 'nama');
    $desa = esc($koneksi, 'desa');
    $luas = esc($koneksi, 'luas_lahan');
    $alokasi = esc($koneksi, 'alokasi');
    $status = esc($koneksi, 'status');

    if ($action === 'delete') {
        $sql = "DELETE FROM petani WHERE id=$id";
    } elseif ($id > 0) {
        $sql = "UPDATE petani SET nama='$nama', desa='$desa', luas_lahan='$luas', alokasi='$alokasi', status='$status' WHERE id=$id";
    } else {
        $sql = "INSERT INTO petani (nama, desa, luas_lahan, alokasi, status) VALUES ('$nama', '$desa', '$luas', '$alokasi', '$status')";
    }
    
    $res = mysqli_query($koneksi, $sql);
    echo json_encode($res ? ['status'=>'success'] : ['status'=>'error', 'msg'=>mysqli_error($koneksi)]);
    exit();
}

// 2. LOGIKA UNTUK DISTRIBUSI
if ($type === 'distribusi') {
    $id = intval($_POST['id'] ?? 0);
    $tgl = esc($koneksi, 'tgl');
    $kelompok = esc($koneksi, 'kelompok');
    $pupuk = esc($koneksi, 'pupuk');
    $jumlah = esc($koneksi, 'jumlah');
    $no_do = esc($koneksi, 'no_do');

    if ($action === 'delete') {
        $sql = "DELETE FROM distribusi WHERE id=$id";
    } elseif ($id > 0) {
        $sql = "UPDATE distribusi SET tgl='$tgl', kelompok='$kelompok', pupuk='$pupuk', jumlah='$jumlah', no_do='$no_do' WHERE id=$id";
    } else {
        $sql = "INSERT INTO distribusi (tgl, kelompok, pupuk, jumlah, no_do) VALUES ('$tgl', '$kelompok', '$pupuk', '$jumlah', '$no_do')";
    }

    $res = mysqli_query($koneksi, $sql);
    echo json_encode($res ? ['status'=>'success'] : ['status'=>'error', 'msg'=>mysqli_error($koneksi)]);
    exit();
}

// 3. LOGIKA UNTUK UPDATE PROFILE
if ($action === 'updateProfile') {
    $nama = esc($koneksi, 'nama');
    $email = esc($koneksi, 'email');
    
    // Update hanya kolom yang pasti ada di users
    $sql = "UPDATE users SET nama='$nama', email='$email' WHERE id=$uid";
    
    if (mysqli_query($koneksi, $sql)) {
        setcookie('nama', $nama, time() + (86400 * 30), "/");
        echo json_encode(['status'=>'success']);
    } else {
        echo json_encode(['status'=>'error', 'msg'=>mysqli_error($koneksi)]);
    }
    exit();
}

// 4. LOGIKA LOAD PAGE (Tetap seperti aslinya agar dashboard tidak error)
if ($action === 'getPage') {
    $page = preg_replace('/[^a-zA-Z0-9_]/','',$_POST['page']??'');
    include __DIR__ . "/../../views/" . $page . ".php";
    exit();
}