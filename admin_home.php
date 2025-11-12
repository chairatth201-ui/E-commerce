<?php
// admin_home.php
session_start();
require_once "conn.php";

// บังคับต้องเป็นแอดมิน
if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit;
}

// ดึงสถิติง่าย ๆ (ถ้าตารางไม่มี/ชื่อไม่ตรง จะไม่ทำให้ล่ม)
$stat = ['product'=>0, 'orders'=>0, 'members'=>0];
if ($conn) {
  if ($res = $conn->query("SELECT COUNT(*) c FROM product"))    { $stat['product']  = (int)$res->fetch_assoc()['c']; }
  if ($res = $conn->query("SELECT COUNT(*) c FROM orders"))     { $stat['orders']   = (int)$res->fetch_assoc()['c']; }
  if ($res = $conn->query("SELECT COUNT(*) c FROM members"))    { $stat['members']  = (int)$res->fetch_assoc()['c']; }
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>Admin • Gunpla Master</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&display=swap" rel="stylesheet">
<style>
  :root{
    --navH: 74px;
    --glass: rgba(255,255,255,.08);
    --glass-2: rgba(255,255,255,.18);
    --border: rgba(255,255,255,.25);
    --brand: #c9ecff;
    --primary:#2196f3;
    --primary-2:#1976d2;
    --chip:#7b91a6;
  }
  *{box-sizing:border-box}

  body{
    margin:0; color:#eaf4ff;
    font-family:system-ui,-apple-system,Segoe UI,Roboto,"TH Sarabun New",sans-serif;
    min-height:100vh;
    background:
      linear-gradient(180deg,rgba(12,26,50,.88),rgba(12,26,50,.78) 65%,rgba(12,26,50,.62)),
      url('https://i.pinimg.com/originals/0e/b1/56/0eb15636563ecc2920056a5dd6e496c5.gif') center/cover no-repeat fixed;
  }

  /* NAV */
  .c-nav{
    position:fixed; inset:0 0 auto 0; height:var(--navH); z-index:999;
    display:flex; align-items:center; gap:18px; padding:10px 18px;
    color:#fff; border-bottom:1px solid var(--border); backdrop-filter: blur(6px);
    background: linear-gradient(180deg,rgba(12,26,50,.88),rgba(12,26,50,.78));
    box-shadow: 0 12px 26px rgba(0,0,0,.35);
  }
  .c-brand{
    font-family:'Orbitron',system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
    font-weight:800; letter-spacing:.8px; font-size:28px;
    color: var(--brand); text-shadow:0 9px 28px rgba(0,0,0,.45);
    text-decoration:none; display:flex; align-items:center; gap:10px;
    transition: .15s transform, .15s opacity;
  }
  .c-brand:hover{ transform: translateY(-1px); opacity:.95; }

  .c-menu{ margin-left:auto; display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
  .c-btn{
    display:inline-flex; align-items:center; justify-content:center;
    min-width:108px; padding:10px 14px; border-radius:12px; font-weight:800;
    color:#fff; text-decoration:none; border:1px solid var(--border);
    background: rgba(255,255,255,.12);
    box-shadow: 0 10px 22px rgba(0,0,0,.25);
    transition:.15s background,.15s transform;
  }
  .c-btn:hover{ background: rgba(255,255,255,.20); transform:translateY(-1px); }
  .c-btn.primary{ background: var(--primary); border-color: transparent; }
  .c-btn.primary:hover{ background: var(--primary-2); }
  .c-user{ margin-left:6px; padding:8px 12px; border-radius:12px;
           background: rgba(255,255,255,.12); border:1px solid var(--border);
           font-weight:700; color:#d9f1ff; }

  .page-wrap{ padding-top: calc(var(--navH) + 18px); }
  .wrap{ max-width:1200px; margin:24px auto 60px; padding:0 18px; }
  .hello{ font-size:1.2rem; color:#cfe7ff; margin-bottom:10px; }
  .stat{ display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:18px; }
  @media (max-width:900px){ .stat{ grid-template-columns:1fr; } }
  .stat .card{
    background:var(--glass); border:1px solid var(--border); border-radius:16px;
    padding:14px; text-align:center; backdrop-filter: blur(8px);
  }
  .stat .num{ font-size:1.8rem; font-weight:900; color:#fff; margin-top:2px; }

  .grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }
  @media (max-width:1000px){ .grid{ grid-template-columns:1fr 1fr; } }
  @media (max-width:680px){ .grid{ grid-template-columns:1fr; } }

  .card-action{
    background:var(--glass); border:1px solid var(--border); border-radius:16px;
    padding:20px; backdrop-filter: blur(8px); box-shadow: 0 12px 26px rgba(0,0,0,.25);
    display:flex; flex-direction:column; gap:12px;
  }
  .card-action h3{ margin:0; color:#fff }
  .card-action p{ margin:0; color:#cfe3ff }
  .btn{
    align-self:flex-start; display:inline-flex; align-items:center; gap:10px;
    padding:12px 16px; border-radius:12px; background:var(--primary); color:#fff;
    font-weight:800; text-decoration:none; border:0; box-shadow: 0 12px 24px rgba(33,150,243,.25);
    transition:.15s background,.15s transform;
  }
  .btn:hover{ background:var(--primary-2); transform:translateY(-1px); }
</style>
</head>
<body>

<header class="c-nav">
  <a href="admin_home.php" class="c-brand">Gunpla&nbsp;Master • Admin</a>

  <nav class="c-menu">
    <a class="c-btn" href="admin_home.php">แดชบอร์ด</a>
    <a class="c-btn" href="addProduct_form.php">เพิ่มสินค้า</a>
    <a class="c-btn" href="orders_admin.php">คำสั่งซื้อรวม</a>
    <a class="c-btn" href="showmember.php">ข้อมูลสมาชิก</a>
    <span class="c-user">👑 <?= htmlspecialchars($_SESSION['admin']) ?></span>
    <!-- ปุ่มออกจากระบบ -->
    <a class="c-btn primary" href="admin_logout.php">ออกจากระบบ</a>
  </nav>
</header>

<div class="page-wrap">
  <div class="wrap">
    <div class="hello">ยินดีต้อนรับ, <strong><?= htmlspecialchars($_SESSION['admin']) ?></strong></div>

    <!-- สถิติ -->
    <div class="stat">
      <div class="card"><div>จำนวนสินค้า</div><div class="num"><?= number_format($stat['product']) ?></div></div>
      <div class="card"><div>จำนวนคำสั่งซื้อ</div><div class="num"><?= number_format($stat['orders']) ?></div></div>
      <div class="card"><div>จำนวนสมาชิก</div><div class="num"><?= number_format($stat['members']) ?></div></div>
    </div>

    <!-- เมนู -->
    <div class="grid">
      <section class="card-action">
        <h3>➕ เพิ่มสินค้า</h3>
        <p>อัปเดตสินค้าใหม่ เพิ่มรูป ราคา และรายละเอียด</p>
        <a class="btn" href="addProduct_form.php">ไปหน้าเพิ่มสินค้า</a>
        <a class="btn" href="show_allProduct.php" style="background:#7b91a6">ดูสินค้าทั้งหมด</a>
      </section>

      <section class="card-action">
        <h3>📦 ดูข้อมูลการสั่งซื้อรวม</h3>
        <p>ตรวจสอบคำสั่งซื้อทั้งหมด หรือค้นหาจากเลขที่สั่งซื้อได้ทันที</p>

        <form class="order-search" method="get" action="viewOrder.php">
          <input type="number" name="order_id" min="1" placeholder="ใส่เลขที่สั่งซื้อ (เช่น 9)" required>
          <button class="btn" type="submit">ดูคำสั่งซื้อ</button>
          <a class="btn" href="orders_admin.php" style="background:#7b91a6">ดูทั้งหมด</a>
        </form>
      </section>

      <section class="card-action">
        <h3>👥 ดูข้อมูลสมาชิก</h3>
        <p>ดูรายชื่อสมาชิก ค้นหาผู้ใช้ และรายละเอียดการติดต่อ</p>
        <a class="btn" href="showmember.php">ไปหน้าข้อมูลสมาชิก</a>
      </section>
    </div>
  </div>
</div>

</body>
</html>
