<?php
session_start();
session_destroy();

// Hapus cookie dengan mengatur waktunya mundur
setcookie('id', '', time() - 3600, "/");
setcookie('nama', '', time() - 3600, "/");
setcookie('role', '', time() - 3600, "/");

header("Location: /api/login.php");
exit();
?>