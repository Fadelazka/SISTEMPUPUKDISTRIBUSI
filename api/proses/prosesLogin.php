<?php
session_start();
require __DIR__ .  '../service/koneksi.php';
$email = $_POST['email'];
$password = $_POST['password'];
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email'");
if(mysqli_num_rows($query)==1){
    $user = mysqli_fetch_assoc($query);
    if(password_verify($password, $user['password'])){
        $_SESSION['id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        header("Location: /api/dashboard.php");
    } else { $_SESSION['error']="Password salah"; header("Location: /api/login.php"); }
} else { $_SESSION['error']="Email tidak terdaftar"; header("Location: /api/login.php"); }
?>