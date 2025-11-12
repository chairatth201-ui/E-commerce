<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>สมัครสมาชิก • Gunpla Master</title>
<!-- ฟอนต์ -->
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Kanit:wght@500;700&display=swap" rel="stylesheet">
<style>
  :root{
    --navH: 70px;
    --primary:#2abf9e;
    --primary-2:#15a488;
    --border:rgba(255,255,255,.25);
  }
  *{box-sizing:border-box;}
  body{
    margin:0;
    font-family:"Kanit",system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
    background:
      linear-gradient(180deg,rgba(12,26,50,.92),rgba(12,26,50,.82)),
      url('https://i.pinimg.com/originals/0e/b1/56/0eb15636563ecc2920056a5dd6e496c5.gif') no-repeat center/cover fixed;
    min-height:100vh;
    display:flex;
    flex-direction:column;
  }

  /* NAV */
  header{
    height:var(--navH); display:flex; align-items:center;
    padding:0 20px; backdrop-filter:blur(6px);
    background:rgba(12,26,50,.85); border-bottom:1px solid var(--border);
  }
  .brand{
    font-family:'Orbitron', sans-serif;
    font-size:1.8rem; font-weight:900; color:#bff6f1;
    letter-spacing:1.2px; text-shadow:0 4px 12px rgba(0,0,0,.45);
    user-select:none;
  }
  .menu{ margin-left:auto; }
  .menu a{
    color:#fff; text-decoration:none; margin-left:20px; font-weight:600;
  }
  .menu a:hover{ color:#2abf9e; }

  /* Card */
  .page-wrap{
    flex:1; display:flex; align-items:center; justify-content:center;
    padding:20px;
  }
  .card{
    width:100%; max-width:520px;
    background:#fff; border-radius:16px; padding:28px 24px;
    box-shadow:0 18px 36px rgba(0,0,0,.25);
  }
  .card h2{ margin:0 0 16px; color:#2abf9e; font-weight:800; text-align:center; }
  .sub{ margin:-4px 0 18px; text-align:center; color:#607080; }

  label{ display:block; margin:10px 0 6px; font-weight:600; }
  input, textarea, select{
    width:100%; padding:12px 14px; border:1px solid #ccd5e0; border-radius:10px;
    font-size:1rem; outline:none; background:#fff;
  }
  textarea{ min-height:90px; resize:vertical; }

  .row{ display:grid; grid-template-columns:1fr 1fr; gap:12px; }
  @media (max-width:560px){ .row{ grid-template-columns:1fr; } }

  button{
    width:100%; margin-top:16px; padding:12px 16px; border-radius:12px;
    border:0; background:var(--primary); color:#fff; font-weight:700; font-size:1.05rem;
    cursor:pointer; transition:.15s background,.15s transform;
  }
  button:hover{ background:var(--primary-2); transform:translateY(-1px); }

  .links{ margin-top:14px; text-align:center; font-size:.95rem; }
  .links a{ color:#2abf9e; text-decoration:none; }
  .links a:hover{ text-decoration:underline; }
</style>
</head>
<body>

<header>
  <div class="brand">Gunpla Master</div>
  <nav class="menu">
    <a href="login_form.php">เข้าสู่ระบบ</a>
  </nav>
</header>

<div class="page-wrap">
  <form class="card" action="register.php" method="post" autocomplete="on">
    <h2>สมัครสมาชิก</h2>
    <div class="sub">สร้างบัญชีใหม่เพื่อเริ่มช้อปกันเลย</div>

    <label for="username">Username</label>
    <input type="text" id="username" name="username" minlength="3" maxlength="30" required>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" minlength="6" required>

    <label for="name">ชื่อ - นามสกุล</label>
    <input type="text" id="name" name="name" required>

    <div class="row">
      <div>
        <label for="email">อีเมล</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div>
        <label for="phone">เบอร์โทรศัพท์</label>
        <input type="tel" id="phone" name="phone" pattern="[0-9]{9,12}" placeholder="เช่น 0812345678" required>
      </div>
    </div>

    <label for="address">ที่อยู่สำหรับจัดส่ง</label>
    <textarea id="address" name="address" placeholder="บ้านเลขที่ ถนน ตำบล/แขวง อำเภอ/เขต จังหวัด รหัสไปรษณีย์" required></textarea>

    <button type="submit">สมัครสมาชิก</button>

    <div class="links">
      มีบัญชีอยู่แล้ว? <a href="login_form.php">เข้าสู่ระบบ</a>
    </div>
  </form>
</div>

</body>
</html>
