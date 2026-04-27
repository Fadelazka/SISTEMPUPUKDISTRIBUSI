<?php
session_start();
require __DIR__ .  '/../service/koneksi.php';

$nama = $_POST['nama'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// --- LOGIKA PENENTUAN ROLE OTOMATIS ---
// Jika email diakhiri dengan @distribusi.com (huruf besar/kecil tidak masalah), jadikan 'admin'
// Jika menggunakan email lain (gmail, yahoo, dll), jadikan 'petugas'
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
    // Masukkan data beserta role yang sudah ditentukan secara otomatis
    mysqli_query($koneksi, "INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$password', '$role')");
    
    $_SESSION['success'] = "Registrasi berhasil! Anda terdaftar sebagai " . ucfirst($role) . ", silakan login.";
    header("Location: /api/login.php");
}
?>