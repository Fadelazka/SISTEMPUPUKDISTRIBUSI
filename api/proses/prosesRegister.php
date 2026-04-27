<?php
session_start();
require __DIR__ .  '/../service/koneksi.php';

$nama = $_POST['nama'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// --- LOGIKA PENENTUAN ROLE OTOMATIS ---
if (preg_match('/@distribusi\.com$/i', $email)) {
    $role = 'admin';
} else {
    $role = 'petugas'; 
}

$cek = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email'");

if(mysqli_num_rows($cek) > 0){
    $_SESSION['error'] = "Email sudah digunakan";
    header("Location: /api/register.php");
} else {
    // DISINI PERBAIKANNYA: Nama kolom disesuaikan menjadi 'nama' (bukan nama_lengkap)
    $sql = "INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$password', '$role')";
    
    if(mysqli_query($koneksi, $sql)){
        $_SESSION['success'] = "Registrasi berhasil! Anda terdaftar sebagai " . ucfirst($role) . ", silakan login.";
        header("Location: /api/login.php");
    } else {
        die("Error Database: " . mysqli_error($koneksi));
    }
}
?>