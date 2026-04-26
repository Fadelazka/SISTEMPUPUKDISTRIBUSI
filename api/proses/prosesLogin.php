<?php
session_start();
require __DIR__ .  '/../service/koneksi.php';

$email = $_POST['email'];
$password = $_POST['password'];

$query = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email'");

if (mysqli_num_rows($query) == 1) {
    $user = mysqli_fetch_assoc($query);
    
    if (password_verify($password, $user['password'])) {
        // GANTI $_SESSION MENJADI SETCOOKIE (Berlaku 1 Hari)
        setcookie('id', $user['id'], time() + 86400, "/");
        setcookie('nama', $user['nama'], time() + 86400, "/");
        setcookie('role', $user['role'], time() + 86400, "/");
        
        // Kembalikan ke path awal yang benar
        header("Location: /api/dashboard.php");
        exit();
    } else { 
        $_SESSION['error'] = "Password salah"; 
        header("Location: /api/login.php"); 
        exit();
    }
} else { 
    $_SESSION['error'] = "Email tidak terdaftar"; 
    header("Location: /api/login.php"); 
    exit();
}
?>