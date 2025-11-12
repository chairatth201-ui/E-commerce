<?php
// customer_home.php — หน้าแรกฝั่งลูกค้า
require_once "check_session.php";   // ตรวจ session ตามของเดิมคุณ
require_once "conn.php";            // เชื่อมต่อฐานข้อมูล

$username = $_SESSION['sess_username'] ?? 'guest';

/* ดึงสินค้าแนะนำ: เอาสินค้าที่เพิ่มล่าสุด 6 ชิ้น */
$featured = [];
$sql = "SELECT product_id, product_name, price, details, image
        FROM product
        ORDER BY product_id DESC
        LIMIT 6";
if ($res = $conn->query($sql)) {
  while ($r = $res->fetch_assoc()) { $featured[] = $r; }
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>Gunpla Master — บัญชีของฉัน</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- ฟอนต์แนว Gundam -->
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&display=swap" rel="stylesheet">

<style>
  :root{
    --primary:#2196f3; --primary2:#1976d2; --border:#cfe9ff;
    --card:#ffffff; --text:#123a5a; --muted:#6b8aa6;
    --shadow:0 10px 24px rgba(25,118,210,.18);
  }
  *{box-sizing:border-box}
  body{
    margin:0; color:var(--text);
    font-family:system-ui,-apple-system,Segoe UI,Roboto,"TH Sarabun New",sans-serif;

    /* 👇 พื้นหลังหลักของหน้า (gif/jpg/png ได้หมด) */
    background: url("https://i.pinimg.com/1200x/6e/74/5d/6e745d43489e4824f556583423eae78a.jpg") no-repeat center center fixed;
    background-size: cover;
  }

  /* ชั้น overlay ให้เนื้อหาอ่านง่าย */
  .overlay{
    min-height:100vh;
    background: linear-gradient(180deg, rgba(85, 95, 102, 0.92), rgba(82, 91, 98, 0.85));
  }

  /* ส่วนหัวร้าน (ชื่อร้าน) */
  .brandbar{
    display:flex; align-items:center; justify-content:center;
    gap:12px; padding:16px 20px 8px;
  }
  .brandname{
    font-family:'Orbitron', sans-serif;
    font-size:2rem; font-weight:700;
    color:#fff; letter-spacing:2px;
    text-shadow:0 0 10px rgba(0,180,255,.9), 0 0 20px rgba(0,120,255,.8);
  }

  .wrap{ max-width:1200px; margin:0 auto; padding:12px 16px 28px; }

  /* แถบเมนูจาก customer_nav.php */
  .space{ height:6px; }

  /* การ์ดทักทาย */
  .hello{
    background:rgba(255,255,255,.9);
    border:1.5px solid var(--border);
    border-radius:16px; box-shadow:var(--shadow);
    padding:18px; margin:10px 0 18px;
    display:flex; align-items:center; justify-content:space-between; gap:10px;
  }
  .hello .title{
    margin:0; color:#0f4fa0; font-weight:800; font-size:1.4rem;
  }
  .hello .desc{ color:var(--muted); margin-top:4px; }

  /* กริดเมนูลัด */
  .grid{
    display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:22px;
  }
  @media (max-width:1100px){ .grid{ grid-template-columns:repeat(3,1fr);} }
  @media (max-width:820px){ .grid{ grid-template-columns:repeat(2,1fr);} }
  @media (max-width:560px){ .grid{ grid-template-columns:1fr;} }

  .card{
    background:var(--card);
    border:1.5px solid var(--border);
    border-radius:16px; box-shadow:var(--shadow);
    padding:18px; display:flex; flex-direction:column; gap:8px;
    transition:.2s transform,.2s box-shadow;
  }
  .card:hover{ transform:translateY(-4px); box-shadow:0 16px 30px rgba(0,0,0,.18); }
  .card h3{ margin:0; color:#0f4fa0; }
  .card p{ margin:0; color:var(--muted); }
  .btn{
    margin-top:auto; display:inline-flex; align-items:center; justify-content:center;
    padding:10px 14px; border-radius:10px; background:var(--primary);
    color:#fff; text-decoration:none; font-weight:700; box-shadow:0 6px 14px rgba(33,150,243,.25);
  }
  .btn:hover{ background:var(--primary2); }

  /* ส่วนสินค้าแนะนำ */
  .sec-title{
    font-weight:900; color:#0f4fa0; font-size:1.3rem; margin:6px 0 12px;
  }
  .grid-products{
    display:grid; grid-template-columns:repeat(3,1fr); gap:16px;
  }
  @media (max-width:980px){ .grid-products{ grid-template-columns:repeat(2,1fr);} }
  @media (max-width:600px){ .grid-products{ grid-template-columns:1fr;} }

  .p-card{
    background:rgba(255,255,255,.95);
    border:1.5px solid var(--border);
    border-radius:16px; box-shadow:var(--shadow);
    overflow:hidden; display:flex; flex-direction:column;
  }
  .thumb{ width:100%; aspect-ratio:4/3; display:flex; align-items:center; justify-content:center;
          background:linear-gradient(180deg,#f6fbff,#e7f3ff); border-bottom:1px solid var(--border); }
  .thumb img{ max-width:90%; max-height:90%; object-fit:contain; border-radius:10px; }
  .content{ padding:14px; display:flex; flex-direction:column; gap:6px; }
  .name{ font-weight:700; color:#0f4fa0; min-height:2.6em; }
  .details{ color:var(--muted); font-size:.95rem; line-height:1.45; max-height:3.9em; overflow:hidden; }
  .price{ font-weight:800; color:#0e71c7; }
  .rowbtn{ display:flex; gap:10px; margin-top:6px; }
</style>
</head>
<body>

  <!-- แถบชื่อร้าน (พื้นหลังหลักมองทะลุ + glow) -->
  <div class="brandbar">
    <div class="brandname">Gunpla Master</div>
  </div>

  <!-- แถบนำทางของลูกค้า (ใช้ include) -->
  <?php include "customer_nav.php"; ?>

  <div class="overlay">
    <div class="wrap">
      <!-- การ์ดทักทาย -->
      <div class="hello">
        <div>
          <h2 class="title">ยินดีต้อนรับ, <?php echo h($username); ?> 👋</h2>
          <div class="desc">เริ่มช้อปกันเลย เลือกดูสินค้าหรือเปิดดูตะกร้าสินค้าได้ทันที</div>
        </div>
        <a class="btn" href="product_list.php">ไปดูสินค้า</a>
      </div>

      <!-- เมนูลัด -->
      <div class="grid">
        <div class="card">
          <h3>เริ่มช้อป</h3>
          <p>เลือกดูสินค้ากันดั้มล่าสุดทั้งหมด</p>
          <a class="btn" href="product_list.php">ดูสินค้า</a>
        </div>
        <div class="card">
          <h3>ตะกร้าสินค้า</h3>
          <p>ตรวจสอบและแก้ไขจำนวน ก่อนยืนยันสั่งซื้อ</p>
          <a class="btn" href="cart.php">ไปที่ตะกร้า</a>
        </div>
        <div class="card">
          <h3>คำสั่งซื้อของฉัน</h3>
          <p>ประวัติและรายละเอียดคำสั่งซื้อทั้งหมด</p>
          <a class="btn" href="order_history.php">เปิดดูประวัติ</a>
        </div>
        <div class="card">
          <h3>โปรไฟล์</h3>
          <p>ข้อมูลติดต่อและที่อยู่จัดส่งของคุณ</p>
          <a class="btn" href="show_profile.php">จัดการโปรไฟล์</a>
        </div>
      </div>

      <!-- สินค้าแนะนำ -->
      <h3 class="sec-title">สินค้าแนะนำ</h3>
      <?php if (empty($featured)): ?>
        <div class="card">ยังไม่มีสินค้าในระบบ</div>
      <?php else: ?>
        <div class="grid-products">
          <?php foreach ($featured as $p): 
                $img = trim((string)($p['image'] ?? ''));
                $imgSrc = $img !== '' ? "uploads/".rawurlencode($img) : null;
          ?>
          <article class="p-card">
            <div class="thumb">
              <?php if($imgSrc): ?>
                <img src="<?php echo $imgSrc; ?>" alt="">
              <?php else: ?>
                <svg width="120" height="90" viewBox="0 0 120 90" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <rect x="1" y="1" width="118" height="88" rx="8" stroke="#b9ddff" stroke-width="2" fill="#f3faff"/>
                  <path d="M18 64l20-24 18 20 12-14 34 34H18z" fill="#d7ecff"/>
                  <circle cx="42" cy="30" r="8" fill="#c9e6ff"/>
                </svg>
              <?php endif; ?>
            </div>
            <div class="content">
              <div class="name"><?php echo h($p['product_name']); ?></div>
              <div class="details"><?php echo nl2br(h($p['details'] ?? '')); ?></div>
              <div class="price"><?php echo number_format((float)$p['price'], 2); ?> บาท</div>
              <div class="rowbtn">
                <a class="btn" href="show_product.php?product_id=<?php echo (int)$p['product_id']; ?>">ดูรายละเอียด</a>
                <a class="btn" href="cart.php?action=add&id=<?php echo (int)$p['product_id']; ?>">🛒 เพิ่มลงตะกร้า</a>
              </div>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
