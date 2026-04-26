<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Login | Sistem Distribusi Pupuk Subsidi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* style persis seperti login.html yang diberikan */
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { min-height:100vh; background:linear-gradient(135deg,#0b3b2f 0%,#1a5d4a 100%); display:flex; align-items:center; justify-content:center; position:relative; overflow-x:hidden; }
        body::before { content:""; position:absolute; width:200%; height:200%; top:-50%; left:-50%; background:radial-gradient(circle,rgba(255,255,255,0.05) 2%,transparent 2.5%); background-size:40px 40px; animation:moveDots 25s linear infinite; pointer-events:none; }
        @keyframes moveDots { 0% { transform:translate(0,0) rotate(0deg); } 100% { transform:translate(80px,80px) rotate(5deg); } }
        .particle { position:absolute; background:rgba(255,255,200,0.2); border-radius:50%; pointer-events:none; animation:floatParticle 8s infinite ease-in-out; }
        @keyframes floatParticle { 0%,100% { transform:translateY(0) translateX(0); opacity:0.3; } 50% { transform:translateY(-40px) translateX(20px); opacity:0.8; } }
        .login-container { width:100%; max-width:450px; margin:20px; z-index:2; }
        .login-card { background:rgba(255,255,255,0.1); backdrop-filter:blur(12px); border-radius:48px; padding:40px 35px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.4); border:1px solid rgba(255,255,255,0.2); transition:all 0.4s ease; animation:fadeSlideUp 0.8s cubic-bezier(0.2,0.9,0.4,1.1); }
        @keyframes fadeSlideUp { from { opacity:0; transform:translateY(40px); } to { opacity:1; transform:translateY(0); } }
        .login-card:hover { transform:translateY(-5px); box-shadow:0 30px 55px -12px rgba(0,0,0,0.5); border-color:rgba(255,255,255,0.4); }
        .logo-area { text-align:center; margin-bottom:30px; }
        .logo-area i { font-size:60px; color:#f5e7a4; filter:drop-shadow(0 4px 8px rgba(0,0,0,0.2)); animation:pulseSoft 2s infinite; }
        @keyframes pulseSoft { 0% { transform:scale(1); } 50% { transform:scale(1.05); } 100% { transform:scale(1); } }
        h2 { color:white; font-weight:700; font-size:28px; margin-top:10px; letter-spacing:-0.3px; }
        .sub { color:rgba(255,255,245,0.8); font-size:14px; margin-top:5px; }
        .input-group { margin-bottom:25px; position:relative; }
        .input-group i { position:absolute; left:18px; top:50%; transform:translateY(-50%); color:#cbd5e1; font-size:18px; }
        .input-group input { width:100%; padding:15px 20px 15px 48px; background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.3); border-radius:44px; font-size:16px; color:white; font-weight:500; outline:none; transition:all 0.25s; }
        .input-group input::placeholder { color:rgba(255,255,240,0.7); font-weight:400; }
        .input-group input:focus { background:rgba(255,255,255,0.25); border-color:#f5e7a4; box-shadow:0 0 0 3px rgba(245,231,164,0.3); }
        .login-btn { width:100%; background:#f5e7a4; border:none; padding:14px; border-radius:44px; font-size:18px; font-weight:700; color:#1a4d3e; cursor:pointer; transition:0.3s; margin-top:10px; display:flex; align-items:center; justify-content:center; gap:10px; }
        .login-btn:hover { background:#ffe9a3; transform:scale(1.02); box-shadow:0 10px 20px -5px rgba(0,0,0,0.3); }
        .error-msg { color:#ffb3a6; background:rgba(220,38,38,0.2); border-radius:40px; padding:10px 15px; font-size:13px; margin-top:15px; text-align:center; backdrop-filter:blur(4px); display:none; }
        footer { text-align:center; color:rgba(255,255,240,0.6); font-size:12px; margin-top:30px; }
        a { color:#f5e7a4; text-decoration:none; }
    </style>
</head>
<body>
<?php for($i=0;$i<35;$i++){ $size=rand(2,10); echo "<div class='particle' style='width:{$size}px;height:{$size}px;left:".rand(0,100)."%;top:".rand(0,100)."%;animation-delay:".rand(0,8)."s;animation-duration:".rand(6,14)."s;'></div>"; } ?>
<div class="login-container">
    <div class="login-card">
        <div class="logo-area"><i class="fas fa-seedling"></i><h2>Subsidi Tani</h2><div class="sub">Sistem Distribusi Pupuk Bersubsidi</div></div>
        <?php if(isset($_SESSION['error'])): ?>
            <div class="error-msg" style="display:block; margin-bottom:15px;"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <form action="/api/proses/prosesLogin.php" method="POST">
            <div class="input-group"><i class="fas fa-envelope"></i><input type="email" name="email" placeholder="Alamat Email" required autocomplete="off"></div>
            <div class="input-group"><i class="fas fa-lock"></i><input type="password" name="password" placeholder="Kata Sandi" required></div>
            <button type="submit" class="login-btn"><i class="fas fa-arrow-right-to-bracket"></i> Masuk ke Dashboard</button>
        </form>
        <footer>Belum punya akun? <a href="/api/register.php">Daftar di sini</a><br>Demo: admin@example.com / password</footer>
    </div>
</div>
</body>
</html>