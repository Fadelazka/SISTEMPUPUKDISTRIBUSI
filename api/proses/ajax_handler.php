<?php
require_once __DIR__ . '/../../service/koneksi.php';

// Set header agar outputnya selalu JSON
header('Content-Type: application/json');

// Ambil data dari Cookie atau POST
$id_user = $_COOKIE['id'] ?? null;
$action  = $_POST['action'] ?? '';
$type    = $_POST['type'] ?? '';

if (!$id_user && $type !== 'login') {
    echo json_encode(['status' => 'error', 'msg' => 'Sesi berakhir, silakan login ulang']);
    exit;
}

// --- LOGIKA PROFIL (Agar tidak error "Tidak Ditemukan") ---
if ($type == 'profile') {
    $nama  = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);

    // Gunakan ID dari cookie untuk update
    $sql = "UPDATE users SET nama = '$nama', email = '$email' WHERE id = '$id_user'";
    
    if (mysqli_query($koneksi, $sql)) {
        // Update juga cookie nama agar tampilan di header langsung berubah
        setcookie('nama', $nama, time() + (86400 * 30), "/");
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => mysqli_error($koneksi)]);
    }
    exit;
}

// --- LOGIKA PETANI (Sinkron dengan kolom 'alokasi') ---
if ($type == 'petani') {
    $nama    = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $desa    = mysqli_real_escape_string($koneksi, $_POST['desa']);
    $luas    = mysqli_real_escape_string($koneksi, $_POST['luas_lahan']);
    $alokasi = mysqli_real_escape_string($koneksi, $_POST['alokasi']);
    $status  = mysqli_real_escape_string($koneksi, $_POST['status']);

    if ($action == 'add') {
        $sql = "INSERT INTO petani (nama, desa, luas_lahan, alokasi, status) VALUES ('$nama', '$desa', '$luas', '$alokasi', '$status')";
    } elseif ($action == 'edit') {
        $id  = $_POST['id'];
        $sql = "UPDATE petani SET nama='$nama', desa='$desa', luas_lahan='$luas', alokasi='$alokasi', status='$status' WHERE id=$id";
    } elseif ($action == 'delete') {
        $id  = $_POST['id'];
        $sql = "DELETE FROM petani WHERE id=$id";
    }
}

// --- LOGIKA DISTRIBUSI (Sinkron dengan kolom 'kelompok') ---
if ($type == 'distribusi') {
    $tgl      = mysqli_real_escape_string($koneksi, $_POST['tgl']);
    $kelompok = mysqli_real_escape_string($koneksi, $_POST['kelompok']);
    $pupuk    = mysqli_real_escape_string($koneksi, $_POST['pupuk']);
    $jumlah   = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
    $no_do    = mysqli_real_escape_string($koneksi, $_POST['no_do']);

    if ($action == 'add') {
        $sql = "INSERT INTO distribusi (tgl, kelompok, pupuk, jumlah, no_do) VALUES ('$tgl', '$kelompok', '$pupuk', '$jumlah', '$no_do')";
    } elseif ($action == 'edit') {
        $id  = $_POST['id'];
        $sql = "UPDATE distribusi SET tgl='$tgl', kelompok='$kelompok', pupuk='$pupuk', jumlah='$jumlah', no_do='$no_do' WHERE id=$id";
    } elseif ($action == 'delete') {
        $id  = $_POST['id'];
        $sql = "DELETE FROM distribusi WHERE id=$id";
    }
}

// --- LOGIKA KELOLA USER (Sinkron dengan kolom 'nama') ---
if ($type == 'user') {
    $nama  = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $role  = mysqli_real_escape_string($koneksi, $_POST['role']);
    $pass  = $_POST['password'] ?? '';

    if ($action == 'add') {
        $sql = "INSERT INTO users (nama, email, role, password) VALUES ('$nama', '$email', '$role', '$pass')";
    } elseif ($action == 'edit') {
        $id = $_POST['id'];
        if (!empty($pass)) {
            $sql = "UPDATE users SET nama='$nama', email='$email', role='$role', password='$pass' WHERE id=$id";
        } else {
            $sql = "UPDATE users SET nama='$nama', email='$email', role='$role' WHERE id=$id";
        }
    } elseif ($action == 'delete') {
        $id = $_POST['id'];
        $sql = "DELETE FROM users WHERE id=$id";
    }
}

// Eksekusi Query dan Kirim Response
if (isset($sql)) {
    if (mysqli_query($koneksi, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => mysqli_error($koneksi)]);
    }
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Aksi tidak dikenali']);
}
?>