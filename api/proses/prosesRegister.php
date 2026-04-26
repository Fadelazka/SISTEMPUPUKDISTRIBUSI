<?php
session_start();
require __DIR__ .  '/../service/koneksi.php';
$nama = $_POST['nama'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$cek = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email'");
if(mysqli_num_rows($cek)>0){
    $_SESSION['error']="Email sudah digunakan";
    header("Location: /api/register.php");
} else {
    mysqli_query($koneksi, "INSERT INTO users (nama,email,password,role) VALUES ('$nama','$email','$password','user')");
    $_SESSION['success']="Registrasi berhasil, silakan login";
    header("Location: /api/login.php");
}
?>