<?php
// ---------- login_form.php ----------
session_start();

// รับข้อความ error ที่ส่งมาจาก check_login.php (ถ้ามี)
$errorMsg = '';
if (isset($_GET['error']) && $_GET['error'] == 1) {
    $errorMsg = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
}
// ถ้าล็อกอินแล้วไม่ต้องกลับมาหน้านี้อีก
if (!empty($_SESSION['user_id'])) {
    header("Location: customer_home.php");
    exit;
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>เข้าสู่ระบบ • Gunpla Master</title>

<!-- ฟอนต์สำหรับชื่อร้าน/ตัวหนังสือ -->
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Kanit:wght@500;700&display=swap" rel="stylesheet">

<style>
  :root{
    --navH: 70px;
    --primary:#2abf9e;     /* ปุ่มหลัก */
    --primary-2:#15a488;   /* ปุ่ม hover */
    --border:rgba(255,255,255,.25);
  }
  *{ box-sizing: border-box; }
  body{
    margin:0;
    font-family:"Kanit",system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
    background:
      linear-gradient(180deg,rgba(12,26,50,.92),rgba(12,26,50,.82)),
      url('https://i.pinimg.com/originals/0e/b1/56/0eb15636563ecc2920056a5dd6e496c5.gif') no-repeat center/cover fixed;
    min-height:100vh;
    display:flex; flex-direction:column;
    color:#0b2c4a;
  }

  /* Header */
  header{
    height:var(--navH);
    display:flex; align-items:center; gap:16px;
    padding:0 20px;
    background:rgba(12,26,50,.85);
    border-bottom:1px solid var(--border);
    backdrop-filter: blur(6px);
  }
  .brand{
    font-family:'Orbitron',sans-serif;
    font-size:1.8rem; font-weight:900; letter-spacing:1.2px;
    color:#bff6f1; text-shadow:0 4px 12px rgba(0,0,0,.45);
    user-select:none;
  }
  .menu{ margin-left:auto; }
  .menu a{
    color:#fff; text-decoration:none; font-weight:700;
  }
  .menu a:hover{ color:#2abf9e; }

  /* กล่องฟอร์ม */
  .page-wrap{
    flex:1; display:flex; align-items:center; justify-content:center;
    padding:22px;
  }
  .card{
    width:100%; max-width:520px; background:#ffffff; border-radius:18px;
    padding:28px 26px 30px;
    box-shadow:0 18px 36px rgba(0,0,0,.25);
    text-align:left;
  }
  .card h2{
    margin:0 0 6px; text-align:center; color:#2abf9e; font-weight:800;
  }
  .card .sub{
    text-align:center; color:#6b7a8a; margin-bottom:18px;
  }
  label{ display:block; margin:10px 0 6px; font-weight:700; color:#223; }
  input{
    width:100%; padding:12px 14px; border:1px solid #cfd7e6; border-radius:10px;
    font-size:1rem; outline:none; background:#fff;
  }
  .row2{ display:flex; gap:12px; }
  .btn{
    display:block; width:100%; padding:12px 16px; border-radius:12px; border:0;
    background:var(--primary); color:#fff; font-weight:800; font-size:1.05rem;
    margin-top:16px; cursor:pointer; transition:.15s background,.15s transform;
  }
  .btn:hover{ background:var(--primary-2); transform: translateY(-1px); }
  .alert{
    margin:10px 0 6px; padding:10px 12px; border-radius:10px;
    background:#ffe9e9; color:#b62525; border:1px solid #ffc7c7;
    text-align:center; font-weight:700;
  }

  /* ปรับระยะบนมือถือ */
  @media (max-width: 560px){
    .card{ max-width: 94vw; padding:22px 18px; }
  }
</style>
</head>
<body>

<header>
  <div class="brand">Gunpla Master</div>
  <nav class="menu">
    <!-- หน้านี้คือ Login จึงให้ลิงก์ไป "สมัครสมาชิก" -->
    <a href="register_form.php">สมัครสมาชิก</a>
  </nav>
</header>

<div class="page-wrap">
  <form class="card" action="check_login.php" method="post" novalidate>
    <h2>เข้าสู่ระบบ</h2>
    <div class="sub">กรอกบัญชีของคุณเพื่อเริ่มช้อปกันเลย</div>

    <?php if (!empty($errorMsg)): ?>
      <div class="alert"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <label for="u">Username</label>
    <input type="text" id="u" name="username" required autofocus>

    <label for="p">Password</label>
    <input type="password" id="p" name="password" required>

    <button type="submit" class="btn">เข้าสู่ระบบ</button>
  </form>
</div>

</body>
</html>
