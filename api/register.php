<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Register | Sistem Distribusi Pupuk Subsidi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { min-height:100vh; background:linear-gradient(135deg,#0b3b2f 0%,#1a5d4a 100%); display:flex; align-items:center; justify-content:center; position:relative; }
        body::before { content:""; position:absolute; width:200%; height:200%; top:-50%; left:-50%; background:radial-gradient(circle,rgba(255,255,255,0.05) 2%,transparent 2.5%); background-size:40px 40px; animation:moveDots 25s linear infinite; pointer-events:none; }
        @keyframes moveDots { 0% { transform:translate(0,0) rotate(0deg); } 100% { transform:translate(80px,80px) rotate(5deg); } }
        .login-container { width:100%; max-width:450px; margin:20px; z-index:2; }
        .login-card { background:rgba(255,255,255,0.1); backdrop-filter:blur(12px); border-radius:48px; padding:40px 35px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.4); border:1px solid rgba(255,255,255,0.2); transition:all 0.4s ease; animation:fadeSlideUp 0.8s cubic-bezier(0.2,0.9,0.4,1.1); }
        @keyframes fadeSlideUp { from { opacity:0; transform:translateY(40px); } to { opacity:1; transform:translateY(0); } }
        h2 { color:white; font-weight:700; font-size:28px; text-align:center; margin-bottom:20px; }
        .input-group { margin-bottom:20px; position:relative; }
        .input-group i { position:absolute; left:18px; top:50%; transform:translateY(-50%); color:#cbd5e1; }
        .input-group input { width:100%; padding:14px 20px 14px 48px; background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.3); border-radius:44px; font-size:16px; color:white; outline:none; }
        .input-group input::placeholder { color:rgba(255,255,240,0.7); }
        .login-btn { width:100%; background:#f5e7a4; border:none; padding:14px; border-radius:44px; font-size:18px; font-weight:700; color:#1a4d3e; cursor:pointer; transition:0.3s; margin-top:10px; }
        .login-btn:hover { background:#ffe9a3; transform:scale(1.02); }
        .error-msg, .success-msg { border-radius:40px; padding:10px 15px; font-size:13px; text-align:center; margin-bottom:20px; }
        .error-msg { background:rgba(220,38,38,0.2); color:#ffb3a6; }
        .success-msg { background:rgba(40,167,69,0.2); color:#b3ffcf; }
        footer { text-align:center; color:rgba(255,255,240,0.6); margin-top:25px; }
        a { color:#f5e7a4; text-decoration:none; }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-card">
        <h2><i class="fas fa-user-plus"></i> Daftar Akun</h2>
        <?php if(isset($_SESSION['error'])): ?>
            <div class="error-msg"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['success'])): ?>
            <div class="success-msg"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <form action="/api/proses/prosesRegister.php" method="POST">
            <div class="input-group"><i class="fas fa-user"></i><input type="text" name="nama" placeholder="Nama Lengkap" required></div>
            <div class="input-group"><i class="fas fa-envelope"></i><input type="email" name="email" placeholder="Alamat Email" required></div>
            <div class="input-group"><i class="fas fa-lock"></i><input type="password" name="password" placeholder="Password" required></div>
            <button type="submit" class="login-btn"><i class="fas fa-check-circle"></i> Daftar</button>
        </form>
        <footer>Sudah punya akun? <a href="/api/login.php">Login di sini</a></footer>
    </div>
</div>
</body>
</html>