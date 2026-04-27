<?php
session_start();
require __DIR__ .  '/../service/koneksi.php';

$nama = $_POST['nama'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Logika Role
$role = (preg_match('/@distribusi\.com$/i', $email)) ? 'admin' : 'petugas';

$cek = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email'");
if(mysqli_num_rows($cek) > 0){
    $_SESSION['error'] = "Email sudah digunakan";
    header("Location: /api/register.php");
} else {
    // Pastikan di sini pakai 'nama', bukan 'nama_lengkap'
    $sql = "INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$password', '$role')";
    if(mysqli_query($koneksi, $sql)){
        $_SESSION['success'] = "Registrasi berhasil sebagai " . ucfirst($role);
        header("Location: /api/login.php");
    } else {
        die("Gagal: " . mysqli_error($koneksi));
    }
}
?>