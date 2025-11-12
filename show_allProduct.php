<?php
// show_allProduct.php — admin view (with delete)
session_start();
require_once "conn.php";

// เฉพาะแอดมินเท่านั้น
if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit;
}

// CSRF token (ง่ายๆ)
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

// ค้นหาสินค้าทั้งหมด
$sql = "SELECT product_id, product_name, price, details, image FROM product ORDER BY product_id DESC";
$res = $conn->query($sql);

// ฟังก์ชัน escape
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// รับข้อความแจ้งเตือน
$msg = isset($_GET['msg']) ? trim($_GET['msg']) : '';
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>รายการสินค้า • Admin</title>
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
    --danger:#e74c3c;
  }
  *{box-sizing:border-box}
  body{
    margin:0; color:#eaf4ff;
    font-family:system-ui,-apple-system,Segoe UI,Roboto,"TH Sarabun New",sans-serif;
    min-height:100vh;
    background:
      linear-gradient(180deg,rgba(12,26,50,.88),rgba(12,26,50,.78) 65%,rgba(12,26,50,.62)),
      url('https://images.unsplash.com/photo-1544456656-470f0b9f3f71?q=80&w=1920&auto=format&fit=crop') center/cover fixed no-repeat;
  }

  /* NAV แบบเดียวกับ admin */
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
  }
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
  .c-btn.active{ background: var(--primary); border-color: transparent; }
  .c-user{
    margin-left:6px; padding:8px 12px; border-radius:12px;
    background: rgba(255,255,255,.12); border:1px solid var(--border);
    font-weight:700; color:#d9f1ff;
  }

  .page-wrap{ padding-top: calc(var(--navH) + 18px); }
  .wrap{ max-width:1200px; margin:28px auto 70px; padding:0 18px; }
  h1.title{
    text-align:center; margin:0 0 16px; font-size:2rem; font-weight:900; color:#fff;
    text-shadow:0 10px 28px rgba(0,0,0,.35);
  }
  .msg{
    margin:0 auto 14px; max-width:900px; padding:10px 14px; border-radius:12px;
    background: rgba(33,150,243,.12); border:1px solid var(--border); color:#dff1ff; text-align:center;
  }

  /* การ์ดสินค้า */
  .grid{
    display:grid; gap:18px;
    grid-template-columns: repeat(4,1fr);
  }
  @media (max-width:1100px){ .grid{ grid-template-columns: repeat(3,1fr);} }
  @media (max-width:800px){  .grid{ grid-template-columns: repeat(2,1fr);} }
  @media (max-width:560px){  .grid{ grid-template-columns: 1fr;} }

  .card{
    background:#e8f6ff;
    border:1px solid #cfe7ff;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 18px 38px rgba(0,0,0,.22);
    color:#0a2a44;
    display:flex; flex-direction:column;
  }
  .thumb{
    background:#eaf2ff; display:flex; align-items:center; justify-content:center;
    padding:10px; border-bottom:1px solid #d8eaff;
  }
  .thumb img{ max-width:100%; border-radius:12px; }
  .ct{ padding:14px; display:flex; flex-direction:column; gap:10px; }
  .name{ font-weight:900; color:#0b2c4a; min-height:2.5em; }
  .price{ font-weight:800; color:#1a74d1; }
  .actions{ display:flex; gap:8px; margin-top:auto; flex-wrap:wrap; }
  .btn{
    display:inline-flex; align-items:center; justify-content:center;
    padding:10px 12px; border-radius:10px; border:0; cursor:pointer; text-decoration:none;
    font-weight:800; color:#fff; background: var(--primary);
    box-shadow: 0 8px 18px rgba(33,150,243,.25);
  }
  .btn:hover{ filter:brightness(1.05); transform: translateY(-1px); }
  .btn.ghost{ background:#7b91a6; }
  .btn.danger{ background:var(--danger); }
  .empty{
    margin-top:10px; text-align:center; color:#cfe7ff; background:rgba(255,255,255,.08);
    border:1px dashed var(--border); border-radius:14px; padding:16px; backdrop-filter: blur(8px);
  }
</style>
</head>
<body>

<header class="c-nav">
  <a class="c-brand" href="admin_home.php">Gunpla&nbsp;Master • Admin</a>
  <nav class="c-menu">
    <a class="c-btn" href="admin_home.php">แดชบอร์ด</a>
    <a class="c-btn" href="addProduct_form.php">เพิ่มสินค้า</a>
    <a class="c-btn active" href="show_allProduct.php">สินค้า</a>
    <a class="c-btn" href="orders_admin.php">คำสั่งซื้อรวม</a>
    <a class="c-btn" href="showmember.php">ข้อมูลสมาชิก</a>
    <span class="c-user">👑 <?= h($_SESSION['admin']) ?></span>
    <a class="c-btn primary" href="logout.php">ออกจากระบบ</a>
  </nav>
</header>

<div class="page-wrap">
  <div class="wrap">
    <h1 class="title">รายการสินค้า</h1>

    <?php if ($msg !== ''): ?>
      <div class="msg"><?= h($msg) ?></div>
    <?php endif; ?>

    <?php if (!$res || $res->num_rows === 0): ?>
      <div class="empty">ยังไม่มีสินค้า</div>
    <?php else: ?>
      <div class="grid">
        <?php while($row = $res->fetch_assoc()): ?>
          <?php
            $pid = (int)$row['product_id'];
            $img = trim((string)$row['image']);
            $imgSrc = $img !== '' ? "uploads/".rawurlencode($img) : null;
          ?>
          <article class="card">
            <div class="thumb">
              <?php if($imgSrc): ?>
                <img src="<?= $imgSrc ?>" alt="">
              <?php else: ?>
                <svg width="260" height="180" viewBox="0 0 120 90" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <rect x="1" y="1" width="118" height="88" rx="8" stroke="#9dc9ff" stroke-width="2" fill="#eef6ff"/>
                  <path d="M18 64l20-24 18 20 12-14 34 34H18z" fill="#d7ecff"/>
                  <circle cx="42" cy="30" r="8" fill="#c9e6ff"/>
                </svg>
              <?php endif; ?>
            </div>
            <div class="ct">
              <div class="name"><?= h($row['product_name']) ?></div>
              <div class="price"><?= number_format((float)$row['price'],2) ?> บาท</div>

              <div class="actions">
                <a class="btn ghost" href="show_product.php?product_id=<?= $pid ?>">ดูรายละเอียด</a>

                <!-- ลบสินค้า: ส่ง POST ไป delete_product.php -->
                <form method="post" action="delete_product.php" onsubmit="return confirm('ยืนยันการลบสินค้าชิ้นนี้?');" style="margin:0">
                  <input type="hidden" name="product_id" value="<?= $pid ?>">
                  <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                  <button class="btn danger" type="submit">ลบสินค้า</button>
                </form>
              </div>
            </div>
          </article>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
