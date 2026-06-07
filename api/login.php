<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Login | SubsidiTani</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root{
      --gold:#f5c842; --dark:#0a1f18; --forest:#12362a;
      --green:#1a5c42; --em:#2d8c65; --lime:#7ec88a;
      --cream:#fdf6e3; --glass:rgba(255,255,255,.08);
    }
    *{margin:0;padding:0;box-sizing:border-box;}
    html,body{height:100%;font-family:'DM Sans',sans-serif;}
    body{
      min-height:100vh;
      display:flex; align-items:stretch;
      background:var(--dark);
      overflow:hidden;
    }

    /* ===== LEFT PANEL ===== */
    .left-panel{
      flex:1; position:relative;
      display:none;
      overflow:hidden;
    }
    @media(min-width:1024px){.left-panel{display:block;}}

    #bgCanvas{position:absolute;inset:0;width:100%;height:100%;}

    .left-content{
      position:absolute; inset:0; z-index:2;
      display:flex; flex-direction:column;
      padding:48px;
      background:linear-gradient(135deg,rgba(10,31,24,.7) 0%,rgba(26,92,66,.3) 100%);
    }
    .brand{
      font-family:'Syne',sans-serif; font-size:1.5rem; font-weight:800;
      color:var(--gold); display:flex; align-items:center; gap:10px;
    }
    .brand-icon{
      width:38px;height:38px;background:var(--gold);border-radius:10px;
      display:flex;align-items:center;justify-content:center;
      color:var(--dark);font-size:.9rem;
    }
    .left-main{flex:1;display:flex;flex-direction:column;justify-content:center;}
    .left-tag{
      display:inline-flex;align-items:center;gap:8px;
      background:rgba(245,200,66,.15);border:1px solid rgba(245,200,66,.3);
      color:var(--gold);padding:5px 14px;border-radius:40px;
      font-size:.7rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;
      margin-bottom:24px;
    }
    .left-tag .dot{width:6px;height:6px;background:var(--gold);border-radius:50%;animation:blink 1.5s infinite;}
    @keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
    .left-h{
      font-family:'Syne',sans-serif;font-weight:800;
      font-size:clamp(2rem,3vw,3.2rem);line-height:1.1;
      color:#fff;margin-bottom:20px;
    }
    .left-h em{font-style:normal;color:var(--gold);}
    .left-sub{color:rgba(253,246,227,.6);font-size:.9rem;line-height:1.7;max-width:440px;margin-bottom:40px;}

    /* Floating info cards on left */
    .info-cards{display:flex;flex-direction:column;gap:12px;max-width:360px;}
    .info-card{
      background:rgba(255,255,255,.07);
      border:1px solid rgba(255,255,255,.1);
      backdrop-filter:blur(12px);
      border-radius:16px;padding:16px 20px;
      display:flex;align-items:center;gap:14px;
      animation:slideIn .6s ease both;
    }
    .info-card:nth-child(2){animation-delay:.1s;}
    .info-card:nth-child(3){animation-delay:.2s;}
    @keyframes slideIn{from{opacity:0;transform:translateX(-20px)}to{opacity:1;transform:translateX(0)}}
    .ic-icon{
      width:40px;height:40px;border-radius:12px;
      display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;
    }
    .ic-label{font-size:.7rem;color:rgba(253,246,227,.4);margin-bottom:2px;text-transform:uppercase;letter-spacing:.06em;}
    .ic-val{font-family:'Syne',sans-serif;font-weight:700;font-size:.95rem;color:#fff;}
    .ic-trend{font-size:.7rem;margin-left:6px;}
    .up-t{color:var(--lime);}

    /* Price ticker */
    .ticker-wrap{
      margin-top:32px;
      background:rgba(0,0,0,.2);border:1px solid rgba(255,255,255,.07);
      border-radius:12px;padding:12px 20px;
      overflow:hidden;
    }
    .ticker-label{font-size:.65rem;text-transform:uppercase;letter-spacing:.1em;color:rgba(253,246,227,.35);margin-bottom:8px;}
    .ticker{display:flex;gap:28px;animation:scroll 18s linear infinite;width:max-content;}
    @keyframes scroll{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
    .tick-item{display:flex;align-items:center;gap:8px;font-size:.78rem;white-space:nowrap;}
    .tick-name{color:rgba(253,246,227,.7);}
    .tick-price{font-family:'Syne',sans-serif;font-weight:700;color:#fff;}
    .tick-chg{font-size:.68rem;}

    /* ===== RIGHT PANEL ===== */
    .right-panel{
      width:100%;max-width:480px;
      display:flex;flex-direction:column;justify-content:center;align-items:center;
      padding:40px 32px;
      background:linear-gradient(160deg,#0e2a1f 0%,#0a1f18 100%);
      position:relative;overflow:hidden;
    }
    @media(min-width:1024px){.right-panel{width:460px;flex:0 0 460px;}}

    /* Decorative blobs */
    .blob{position:absolute;border-radius:50%;filter:blur(60px);pointer-events:none;}
    .blob1{width:300px;height:300px;background:rgba(45,140,101,.2);top:-80px;right:-80px;}
    .blob2{width:200px;height:200px;background:rgba(245,200,66,.08);bottom:-60px;left:-60px;}
    .blob3{width:150px;height:150px;background:rgba(126,200,138,.12);bottom:100px;right:20px;animation:moveBlob 8s ease-in-out infinite;}
    @keyframes moveBlob{0%,100%{transform:translateY(0)}50%{transform:translateY(-30px)}}

    /* Subtle grid bg */
    .right-panel::before{
      content:'';position:absolute;inset:0;
      background-image:linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),
                       linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);
      background-size:40px 40px;
      pointer-events:none;
    }

    .form-wrap{position:relative;z-index:2;width:100%;}

    /* Mobile brand */
    .mobile-brand{
      display:flex;align-items:center;gap:10px;justify-content:center;
      margin-bottom:36px;
      font-family:'Syne',sans-serif;font-size:1.3rem;font-weight:800;color:var(--gold);
    }
    @media(min-width:1024px){.mobile-brand{display:none;}}

    /* THEME SWITCHER */
    .theme-tabs{
      display:flex;gap:6px;margin-bottom:28px;
      background:rgba(255,255,255,.05);
      border:1px solid rgba(255,255,255,.08);
      border-radius:40px;padding:5px;
    }
    .theme-tab{
      flex:1;padding:7px;border-radius:30px;border:none;cursor:pointer;
      font-size:.72rem;font-weight:600;letter-spacing:.04em;
      color:rgba(255,255,255,.45);background:transparent;transition:.25s;
    }
    .theme-tab.active{background:var(--gold);color:var(--dark);}

    /* Different themes */
    body.theme-night .right-panel{background:linear-gradient(160deg,#0d1b3e 0%,#07111f 100%);}
    body.theme-night .blob1{background:rgba(99,102,241,.2);}
    body.theme-night .blob2{background:rgba(245,200,66,.1);}
    body.theme-night .form-card{border-color:rgba(99,102,241,.2);}

    body.theme-earth .right-panel{background:linear-gradient(160deg,#2d1a0e 0%,#1a1008 100%);}
    body.theme-earth .blob1{background:rgba(234,179,8,.15);}
    body.theme-earth .blob2{background:rgba(251,146,60,.12);}

    /* FORM CARD */
    .form-card{
      background:rgba(255,255,255,.04);
      border:1px solid rgba(255,255,255,.1);
      border-radius:28px;padding:36px 32px;
      animation:popIn .7s cubic-bezier(.2,.9,.4,1.1) both;
    }
    @keyframes popIn{from{opacity:0;transform:scale(.95) translateY(20px)}to{opacity:1;transform:scale(1) translateY(0)}}

    .form-head{text-align:center;margin-bottom:32px;}
    .form-icon-wrap{
      width:64px;height:64px;border-radius:20px;
      background:linear-gradient(135deg,var(--green),var(--em));
      display:flex;align-items:center;justify-content:center;
      font-size:1.5rem;color:var(--gold);
      margin:0 auto 16px;
      box-shadow:0 8px 24px rgba(26,92,66,.4);
      animation:iconPop .8s .3s cubic-bezier(.2,.9,.4,1.1) both;
    }
    @keyframes iconPop{from{transform:scale(0) rotate(-20deg)}to{transform:scale(1) rotate(0)}}
    .form-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.5rem;color:#fff;margin-bottom:6px;}
    .form-sub{font-size:.82rem;color:rgba(253,246,227,.45);}

    /* INPUT */
    .inp-group{margin-bottom:18px;position:relative;}
    .inp-label{display:block;font-size:.72rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:rgba(253,246,227,.4);margin-bottom:8px;}
    .inp-wrap{position:relative;}
    .inp-wrap .ico{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:rgba(253,246,227,.3);font-size:.9rem;pointer-events:none;transition:.25s;}
    .inp-wrap input{
      width:100%;padding:14px 16px 14px 44px;
      background:rgba(255,255,255,.06);
      border:1.5px solid rgba(255,255,255,.1);
      border-radius:14px;
      font-size:.9rem;color:#fff;font-family:'DM Sans',sans-serif;
      outline:none;transition:.25s;
    }
    .inp-wrap input::placeholder{color:rgba(253,246,227,.3);}
    .inp-wrap input:focus{
      background:rgba(255,255,255,.1);
      border-color:var(--gold);
      box-shadow:0 0 0 3px rgba(245,200,66,.15);
    }
    .inp-wrap input:focus + .ico-right,
    .inp-wrap input:focus ~ .ico{color:var(--gold);}
    .inp-wrap .ico-right{
      position:absolute;right:14px;top:50%;transform:translateY(-50%);
      color:rgba(253,246,227,.3);cursor:pointer;font-size:.9rem;transition:.25s;
    }

    /* Strength indicator */
    .strength-bar{display:flex;gap:4px;margin-top:8px;}
    .sb{flex:1;height:3px;border-radius:10px;background:rgba(255,255,255,.1);transition:.4s;}

    /* SUBMIT BTN */
    .submit-btn{
      width:100%;padding:15px;border:none;border-radius:14px;cursor:pointer;
      font-family:'Syne',sans-serif;font-weight:700;font-size:1rem;
      background:linear-gradient(135deg,var(--gold) 0%,#ffd84a 100%);
      color:var(--dark);transition:.3s;
      display:flex;align-items:center;justify-content:center;gap:10px;
      margin-top:8px;
      box-shadow:0 8px 24px rgba(245,200,66,.3);
    }
    .submit-btn:hover{transform:translateY(-2px);box-shadow:0 14px 32px rgba(245,200,66,.4);}
    .submit-btn:active{transform:translateY(0);}

    /* DIVIDER */
    .or-div{display:flex;align-items:center;gap:12px;margin:20px 0;color:rgba(253,246,227,.25);font-size:.75rem;}
    .or-div::before,.or-div::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.07);}

    /* SOCIAL */
    .social-btns{display:flex;gap:10px;}
    .soc-btn{
      flex:1;padding:11px;border-radius:12px;border:1.5px solid rgba(255,255,255,.1);
      background:transparent;color:rgba(253,246,227,.6);cursor:pointer;font-size:.82rem;
      display:flex;align-items:center;justify-content:center;gap:8px;transition:.25s;
    }
    .soc-btn:hover{background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.2);color:#fff;}

    /* ERROR */
    .error-msg{
      background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25);
      color:#fca5a5;border-radius:12px;padding:11px 16px;
      font-size:.82rem;text-align:center;margin-bottom:16px;
      display:none;
    }
    /* FOOTER LINK */
    .form-footer{text-align:center;margin-top:24px;font-size:.8rem;color:rgba(253,246,227,.4);}
    .form-footer a{color:var(--gold);text-decoration:none;font-weight:600;}
    .form-footer a:hover{text-decoration:underline;}

    /* Ring animation around icon */
    .ring{
      position:absolute;inset:-8px;border-radius:28px;
      border:2px solid rgba(245,200,66,.2);
      animation:ring 3s ease-in-out infinite;
    }
    @keyframes ring{0%,100%{transform:scale(1);opacity:.5}50%{transform:scale(1.05);opacity:1}}
    .form-icon-wrap-outer{position:relative;width:64px;margin:0 auto 20px;}
  </style>
</head>
<body class="theme-forest" id="appBody">

<!-- LEFT -->
<div class="left-panel">
  <canvas id="bgCanvas"></canvas>
  <div class="left-content">
    <div class="brand">
      <div class="brand-icon"><i class="fas fa-seedling"></i></div>
      SubsidiTani
    </div>
    <div class="left-main">
      <div class="left-tag"><div class="dot"></div> Sistem Distribusi Resmi</div>
      <h2 class="left-h">Pupuk Tepat,<br>Panen <em>Maksimal</em></h2>
      <p class="left-sub">Platform distribusi pupuk bersubsidi terintegrasi dengan tracking real-time, marketplace digital, dan analitik wilayah berbasis BPS.</p>
      <div class="info-cards">
        <div class="info-card">
          <div class="ic-icon" style="background:rgba(126,200,138,.15);color:#7ec88a"><i class="fas fa-truck"></i></div>
          <div><div class="ic-label">Distribusi Hari Ini</div><div class="ic-val">247 Kg <span class="ic-trend up-t">▲ 12%</span></div></div>
        </div>
        <div class="info-card">
          <div class="ic-icon" style="background:rgba(245,200,66,.15);color:#f5c842"><i class="fas fa-users"></i></div>
          <div><div class="ic-label">Petani Aktif</div><div class="ic-val">12.480 Petani</div></div>
        </div>
        <div class="info-card">
          <div class="ic-icon" style="background:rgba(96,165,250,.15);color:#60a5fa"><i class="fas fa-check-circle"></i></div>
          <div><div class="ic-label">Realisasi Bulan Ini</div><div class="ic-val">72.4% dari Target</div></div>
        </div>
      </div>
      <div class="ticker-wrap">
        <div class="ticker-label">Harga Pupuk Hari Ini</div>
        <div class="ticker">
          <div class="tick-item"><span class="tick-name">Urea</span><span class="tick-price">Rp2.250</span><span class="tick-chg up-t">▲2.1%</span></div>
          <div class="tick-item"><span class="tick-name">NPK Phonska</span><span class="tick-price">Rp2.300</span><span class="tick-chg up-t">▲0.8%</span></div>
          <div class="tick-item"><span class="tick-name">SP-36</span><span class="tick-price">Rp1.800</span><span class="tick-chg" style="color:#f87171">▼0.3%</span></div>
          <div class="tick-item"><span class="tick-name">ZA</span><span class="tick-price">Rp1.700</span><span class="tick-chg up-t">▲1.2%</span></div>
          <div class="tick-item"><span class="tick-name">Petroganik</span><span class="tick-price">Rp800</span><span class="tick-chg up-t">▲0.5%</span></div>
          <!-- repeat for scroll -->
          <div class="tick-item"><span class="tick-name">Urea</span><span class="tick-price">Rp2.250</span><span class="tick-chg up-t">▲2.1%</span></div>
          <div class="tick-item"><span class="tick-name">NPK Phonska</span><span class="tick-price">Rp2.300</span><span class="tick-chg up-t">▲0.8%</span></div>
          <div class="tick-item"><span class="tick-name">SP-36</span><span class="tick-price">Rp1.800</span><span class="tick-chg" style="color:#f87171">▼0.3%</span></div>
          <div class="tick-item"><span class="tick-name">ZA</span><span class="tick-price">Rp1.700</span><span class="tick-chg up-t">▲1.2%</span></div>
          <div class="tick-item"><span class="tick-name">Petroganik</span><span class="tick-price">Rp800</span><span class="tick-chg up-t">▲0.5%</span></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- RIGHT -->
<div class="right-panel">
  <div class="blob blob1"></div>
  <div class="blob blob2"></div>
  <div class="blob blob3"></div>

  <div class="form-wrap">
    <div class="mobile-brand"><i class="fas fa-seedling"></i> SubsidiTani</div>

    <!-- Theme Tabs -->
    <div class="theme-tabs">
      <button class="theme-tab active" onclick="setTheme('theme-forest',this)">🌿 Hutan</button>
      <button class="theme-tab" onclick="setTheme('theme-night',this)">🌙 Malam</button>
      <button class="theme-tab" onclick="setTheme('theme-earth',this)">🌾 Bumi</button>
    </div>

    <div class="form-card">
      <div class="form-head">
        <div class="form-icon-wrap-outer">
          <div class="ring"></div>
          <div class="form-icon-wrap"><i class="fas fa-seedling"></i></div>
        </div>
        <div class="form-title">Selamat Datang</div>
        <div class="form-sub">Masuk ke dashboard distribusi Anda</div>
      </div>

      <?php if(isset($_SESSION['error'])): ?>
        <div class="error-msg" style="display:block"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
      <?php endif; ?>

      <form action="proses/prosesLogin.php" method="POST">
        <div class="inp-group">
          <label class="inp-label">Email</label>
          <div class="inp-wrap">
            <i class="ico fas fa-envelope"></i>
            <input type="email" name="email" placeholder="nama@email.com" required autocomplete="off">
          </div>
        </div>
        <div class="inp-group">
          <label class="inp-label">Kata Sandi</label>
          <div class="inp-wrap">
            <i class="ico fas fa-lock"></i>
            <input type="password" name="password" id="pwdInput" placeholder="••••••••" required>
            <i class="ico-right fas fa-eye" id="pwdToggle" onclick="togglePwd()"></i>
          </div>
          <div class="strength-bar" id="strengthBar">
            <div class="sb" id="sb1"></div><div class="sb" id="sb2"></div><div class="sb" id="sb3"></div><div class="sb" id="sb4"></div>
          </div>
        </div>
        <button type="submit" class="submit-btn"><i class="fas fa-arrow-right-to-bracket"></i> Masuk Sekarang</button>
      </form>

      <div class="or-div">atau masuk dengan</div>
      <div class="social-btns">
        <button class="soc-btn"><i class="fab fa-google"></i> Google</button>
        <button class="soc-btn"><i class="fab fa-facebook"></i> Facebook</button>
      </div>
    </div>

    <div class="form-footer">
      Belum punya akun? <a href="/api/register.php">Daftar sekarang</a><br>
      <span style="font-size:.72rem;margin-top:6px;display:block">Demo: admin@example.com / password</span>
    </div>
  </div>
</div>

<script>
// Theme switcher
function setTheme(theme, btn){
  document.getElementById('appBody').className = theme;
  document.querySelectorAll('.theme-tab').forEach(t=>t.classList.remove('active'));
  btn.classList.add('active');
}
// Password toggle
function togglePwd(){
  const inp = document.getElementById('pwdInput');
  const ico = document.getElementById('pwdToggle');
  inp.type = inp.type==='password'?'text':'password';
  ico.className = inp.type==='password'?'ico-right fas fa-eye':'ico-right fas fa-eye-slash';
}
// Strength
document.getElementById('pwdInput').addEventListener('input',function(){
  const v=this.value, bars=[sb1,sb2,sb3,sb4];
  bars.forEach(b=>b.style.background='rgba(255,255,255,.1)');
  const colors=['#f87171','#fb923c','#facc15','#7ec88a'];
  let str=0;
  if(v.length>4)str++;if(v.length>8)str++;if(/[A-Z]/.test(v))str++;if(/[^a-zA-Z0-9]/.test(v))str++;
  for(let i=0;i<str;i++) bars[i].style.background=colors[str-1];
});
// Canvas BG
const canvas=document.getElementById('bgCanvas');
if(canvas){
  const ctx=canvas.getContext('2d');
  let W,H; const pts=[];
  function resize(){W=canvas.width=canvas.offsetWidth;H=canvas.height=canvas.offsetHeight;}
  resize();window.addEventListener('resize',resize);
  for(let i=0;i<80;i++) pts.push({x:Math.random()*1200,y:Math.random()*900,vx:(Math.random()-.5)*.4,vy:(Math.random()-.5)*.4,r:Math.random()*2+1});
  function draw(){
    ctx.clearRect(0,0,W,H);
    ctx.fillStyle='rgba(18,54,42,1)';ctx.fillRect(0,0,W,H);
    pts.forEach(p=>{
      ctx.beginPath();ctx.arc(p.x,p.y,p.r,0,Math.PI*2);
      ctx.fillStyle='rgba(126,200,138,.4)';ctx.fill();
      p.x+=p.vx;p.y+=p.vy;
      if(p.x<0||p.x>W)p.vx*=-1;if(p.y<0||p.y>H)p.vy*=-1;
    });
    pts.forEach((a,i)=>pts.slice(i+1).forEach(b=>{
      const d=Math.hypot(a.x-b.x,a.y-b.y);
      if(d<100){ctx.beginPath();ctx.moveTo(a.x,a.y);ctx.lineTo(b.x,b.y);ctx.strokeStyle=`rgba(126,200,138,${.2*(1-d/100)})`;ctx.lineWidth=.5;ctx.stroke();}
    }));
    requestAnimationFrame(draw);
  }
  draw();
}
</script>
</body>
</html>
