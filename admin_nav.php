<?php
// admin_nav.php — แถบเมนูสำหรับผู้ดูแลระบบ
if (session_status() === PHP_SESSION_NONE) session_start();
$isAdmin = !empty($_SESSION['admin']);
?>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&display=swap" rel="stylesheet">
<style>
  :root{
    --navH: 74px;
    --border: rgba(255,255,255,.25);
    --brand: #c9ecff;
    --primary:#2196f3;
    --primary-2:#1976d2;
  }
  .a-nav{
    position:fixed; inset:0 0 auto 0; height:var(--navH); z-index:999;
    display:flex; align-items:center; gap:18px; padding:10px 18px;
    color:#fff;
    background:
      linear-gradient(180deg,rgba(12,26,50,.88),rgba(12,26,50,.78) 65%,rgba(12,26,50,.62)),
      url('https://i.pinimg.com/originals/c6/f6/21/c6f621e557d40dceaf794b60e960a67d.gif') center/cover no-repeat;
    box-shadow: 0 12px 26px rgba(0,0,0,.35);
    border-bottom: 1px solid var(--border);
    backdrop-filter: blur(6px);
  }
  .a-brand{
    font-family:'Orbitron',system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
    font-weight:800; letter-spacing:.8px; font-size:26px;
    color: var(--brand); text-shadow:0 9px 28px rgba(0,0,0,.45);
    text-decoration:none; display:flex; align-items:center; gap:10px;
  }
  .a-menu{ margin-left:auto; display:flex; gap:10px; align-items:center; }
  .a-btn{
    display:inline-flex; align-items:center; justify-content:center;
    padding:10px 14px; border-radius:12px; font-weight:800;
    color:#fff; text-decoration:none; border:1px solid var(--border);
    background: rgba(255,255,255,.12);
    box-shadow: 0 10px 22px rgba(0,0,0,.25);
    transition:.15s background,.15s transform;
  }
  .a-btn:hover{ background: rgba(255,255,255,.20); transform:translateY(-1px); }
  .a-btn.primary{ background: var(--primary); border-color: transparent; }
  .a-btn.primary:hover{ background: var(--primary-2); }
  .page-wrap{ padding-top: calc(var(--navH) + 18px); }
</style>

<header class="a-nav">
  <a href="admin_home.php" class="a-brand">🔧 Gunpla Admin</a>
  <nav class="a-menu">
    <a class="a-btn" href="admin_home.php">หน้าหลัก</a>
    <a class="a-btn" href="addProduct_form.php">เพิ่มสินค้า</a>
    <a class="a-btn" href="admin_orders.php">ดูข้อมูลการสั่งซื้อ</a>
    <a class="a-btn" href="showmember.php">ดูข้อมูลสมาชิก</a>
    <?php if ($isAdmin): ?>
      <a class="a-btn primary" href="admin_logout.php">ออกจากระบบ</a>
    <?php else: ?>
      <a class="a-btn primary" href="admin_login.php">เข้าสู่ระบบ</a>
    <?php endif; ?>
  </nav>
</header>
