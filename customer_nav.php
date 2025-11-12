<?php
// customer_nav.php — หัวเมนูลูกค้าแบบใช้ซ้ำ
if (session_status() === PHP_SESSION_NONE) session_start();

$me = $_SESSION['sess_username'] ?? '';
$now = basename($_SERVER['PHP_SELF']); // ไฟล์ปัจจุบันไว้ไฮไลท์เมนู

function active($files){
  $now = basename($_SERVER['PHP_SELF']);
  foreach((array)$files as $f){
    if ($now === $f) return ' active';
  }
  return '';
}
?>
<!doctype html>
<!-- include ไฟล์นี้ไว้บนสุดของทุกหน้าลูกค้า -->
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&display=swap" rel="stylesheet">
<style>
  :root{
    --navH: 74px;
    --glass: rgba(255,255,255,.08);
    --border: rgba(255,255,255,.25);
    --brand: #c9ecff;
    --primary:#2196f3;
    --primary-2:#1976d2;
    --chip:#7b91a6;
  }
  /* แถบหัวเว็บ */
  .c-nav{
    position:fixed; inset:0 0 auto 0; height:var(--navH); z-index:999;
    display:flex; align-items:center; gap:18px; padding:10px 18px;
    color:#fff;
    background:
      linear-gradient(180deg,rgba(12,26,50,.88),rgba(12,26,50,.78) 65%,rgba(12,26,50,.62)),
      url('https://i.pinimg.com/originals/0e/b1/56/0eb15636563ecc2920056a5dd6e496c5.gif') center/cover no-repeat; /* ใช้ .gif ได้ */
    box-shadow: 0 12px 26px rgba(0,0,0,.35);
    border-bottom: 1px solid var(--border);
    backdrop-filter: blur(6px);
  }
  .c-brand{
    font-family:'Orbitron',system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
    font-weight:800; letter-spacing:.8px; font-size:28px;
    color: var(--brand); text-shadow:0 9px 28px rgba(0,0,0,.45);
    text-decoration:none; display:flex; align-items:center; gap:10px;
    transition: .15s transform, .15s opacity;
  }
  .c-brand:hover{ transform: translateY(-1px); opacity:.95; }

  .c-menu{ margin-left:auto; display:flex; gap:10px; align-items:center; }

  .c-btn{
    display:inline-flex; align-items:center; justify-content:center;
    min-width:108px; padding:10px 14px; border-radius:12px; font-weight:800;
    color:#fff; text-decoration:none; border:1px solid var(--border);
    background: rgba(255,255,255,.12);
    box-shadow: 0 10px 22px rgba(0,0,0,.25);
    transition:.15s background,.15s transform;
  }
  .c-btn:hover{ background: rgba(255,255,255,.20); transform:translateY(-1px); }
  .c-btn.active{ background: var(--primary); border-color: transparent; }
  .c-btn.primary{ background: var(--primary); border-color: transparent; }
  .c-btn.primary:hover{ background: var(--primary-2); }

  .c-user{
    margin-left:6px; padding:8px 12px; border-radius:12px;
    background: rgba(255,255,255,.12); border:1px solid var(--border);
    font-weight:700; color:#d9f1ff;
  }

  /* เว้นพื้นที่ด้านบนให้เนื้อหาหน้าเว็บไม่ชนเมนู */
  .page-wrap{ padding-top: calc(var(--navH) + 16px); }
  @media (max-width:720px){
    .c-btn{ min-width:auto; padding:9px 12px; }
    .c-brand{ font-size:22px; }
  }
</style>

<header class="c-nav">
  <!-- คลิกชื่อร้านเพื่อกลับหน้าแรกลูกค้า -->
  <a href="customer_home.php" class="c-brand">Gunpla&nbsp;Master</a>

  <nav class="c-menu">
    <a class="c-btn<?= active(['show_profile.php','customer_profile.php']) ?>" href="show_profile.php">ข้อมูลส่วนตัว</a>
    <a class="c-btn<?= active(['product_list.php','show_allProduct.php','show_product.php']) ?>" href="product_list.php">สินค้า</a>
    <a class="c-btn<?= active(['cart.php']) ?>" href="cart.php">ตะกร้าสินค้า</a>
    <a class="c-btn<?= active(['viewOrder.php','orders_history.php']) ?>" href="viewOrder.php?order_id=">คำสั่งซื้อ</a>
    <?php if($me): ?><span class="c-user">👤 <?= htmlspecialchars($me) ?></span><?php endif; ?>
    <a class="c-btn primary" href="logout.php">ออกจากระบบ</a>
  </nav>
</header>
