<?php
// product_list.php  (customer view – no delete, no add)
session_start();
require_once "conn.php";

// optional: ดึงชื่อผู้ใช้ที่ล็อกอิน (ถ้ามี)
$me = $_SESSION['sess_username'] ?? '';

// ค้นหาแบบง่าย (ชื่อ/รายละเอียด)
$q = trim($_GET['q'] ?? '');
$params = [];
$sql = "SELECT product_id, product_name, price, details, image FROM product";
if ($q !== '') {
  $sql .= " WHERE product_name LIKE ? OR details LIKE ?";
  $like   = "%{$q}%";
  $params = [$like, $like];
}
$sql .= " ORDER BY product_id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
  $stmt->bind_param("ss", ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>รายการสินค้า</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
  :root{
    --glass: rgba(255,255,255,.08);
    --glass-2: rgba(255,255,255,.18);
    --card:#ffffff;
    --primary:#2196f3;
    --primary-2:#1976d2;
    --muted:#88a7c0;
    --text:#0c2c44;
    --border: rgba(255,255,255,.25);
  }
  *{box-sizing:border-box}

  /* พื้นหลังภาพ + โทนฟ้า/อวกาศ */
  body{
    margin:0;
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, "TH Sarabun New", sans-serif;
    color: var(--text);
    min-height:100vh;
    background:
      linear-gradient(180deg, rgba(14, 30, 56, .65), rgba(12, 26, 50, .65)),
      url('https://i.pinimg.com/1200x/d9/8a/8e/d98a8e5198bbb078ca23f0de98e17267.jpg') no-repeat top / cover fixed;
  }

  .wrap{
    max-width:1200px;
    margin:40px auto;
    padding:0 18px 42px;
  }

  .title-bar{
    display:flex;
    align-items:center;
    gap:14px;
    margin-bottom:16px;
  }
  .title{
    color:#e9f4ff;
    font-weight:800;
    font-size:1.8rem;
    text-shadow: 0 6px 18px rgba(0,0,0,.35);
    letter-spacing:.3px;
  }

  /* แถบค้นหา */
  .search{
    margin-left:auto;
    display:flex;
    gap:8px;
    align-items:center;
    background: var(--glass);
    border:1px solid var(--border);
    border-radius:14px;
    padding:6px 8px 6px 10px;
    backdrop-filter: blur(8px);
  }
  .search input{
    width:240px;
    border:0;
    outline:none;
    background:transparent;
    color:#fff;
    font-size:14.5px;
  }
  .search input::placeholder{ color:#cfe7ff; }
  .search button{
    border:0; padding:8px 12px; border-radius:10px;
    background: var(--primary);
    color:#fff; font-weight:700; cursor:pointer;
    transition:.15s background;
  }
  .search button:hover{ background: var(--primary-2); }

  /* ตะกร้าการ์ดแบบ grid */
  .grid{
    display:grid; gap:18px;
    grid-template-columns: repeat(4, 1fr);
  }
  @media (max-width:1100px){ .grid{ grid-template-columns: repeat(3, 1fr);} }
  @media (max-width:800px){  .grid{ grid-template-columns: repeat(2, 1fr);} }
  @media (max-width:560px){  .grid{ grid-template-columns: 1fr;} }

  .card{
    display:flex; flex-direction:column;
    background: var(--glass);
    border:1px solid var(--border);
    border-radius:16px;
    overflow:hidden;
    backdrop-filter: blur(8px);
    box-shadow: 0 12px 26px rgba(0,0,0,.25);
  }
  .thumb{
    width:100%; aspect-ratio: 4/3;
    background: linear-gradient(180deg, #e9f4ff, #cfe7ff);
    display:flex; align-items:center; justify-content:center;
    border-bottom:1px solid rgba(255,255,255,.22);
  }
  .thumb img{
    max-width:92%; max-height:92%;
    object-fit:contain; border-radius:12px;
    box-shadow: 0 8px 20px rgba(0,0,0,.12);
  }

  .content{
    padding:14px 14px 12px; display:flex; flex-direction:column; gap:8px;
  }
  .name{
    color:#fff; font-weight:800; line-height:1.3;
    text-shadow: 0 8px 24px rgba(0,0,0,.28);
    min-height:2.6em;
  }
  .details{
    color:#cfe7ff; font-size:.95rem; line-height:1.45;
    max-height:4.1em; overflow:hidden;
  }
  .price{
    color:#cfe7ff; font-weight:800; font-size:1.05rem;
    background: var(--glass-2);
    width:fit-content; padding:4px 10px; border-radius:10px;
    border:1px solid var(--border);
  }

  .actions{ margin-top:auto; display:flex; gap:10px; }
  .btn{
    display:inline-flex; align-items:center; justify-content:center;
    gap:8px; flex:1; padding:10px 12px;
    border-radius:10px; text-decoration:none;
    background: var(--primary); color:#fff; font-weight:700; border:0;
    box-shadow: 0 8px 18px rgba(33,150,243,.25);
    transition:.15s background, .15s transform;
  }
  .btn:hover{ background: var(--primary-2); transform: translateY(-1px); }
  .btn.secondary{ background: #7b91a6; }
  .btn.secondary:hover{ background: #6b839a; }

  /* เมื่อไม่มีข้อมูล */
  .empty{
    margin-top:18px;
    padding:20px;
    text-align:center;
    color:#cfe7ff;
    border:1px dashed var(--border);
    border-radius:14px;
    background: var(--glass);
    backdrop-filter: blur(8px);
  }
</style>
</head>
<body>

<?php include 'customer_nav.php'; ?>  <!-- แถบนำทางลูกค้า -->

<div class="page-wrap"><!-- เว้นระยะจากเมนู (กำหนดใน customer_nav.php) -->
  <div class="wrap">
    <div class="title-bar">
      <h1 class="title">รายการสินค้า</h1>

      <form class="search" method="get" action="">
        <input type="text" name="q" value="<?= h($q) ?>" placeholder="ค้นหาชื่อสินค้า…">
        <button type="submit">ค้นหา</button>
      </form>
    </div>

    <?php if (!$result || $result->num_rows === 0): ?>
      <div class="empty">ยังไม่มีข้อมูลสินค้าหรือไม่พบตามคำค้น</div>
    <?php else: ?>
      <div class="grid">
        <?php while($row = $result->fetch_assoc()): ?>
          <?php
            $img = trim((string)$row['image']);
            $imgSrc = $img !== '' ? "uploads/".rawurlencode($img) : null;
          ?>
          <article class="card">
            <div class="thumb">
              <?php if($imgSrc): ?>
                <img src="<?= $imgSrc ?>" alt="">
              <?php else: ?>
                <!-- placeholder -->
                <svg width="120" height="90" viewBox="0 0 120 90" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <rect x="1" y="1" width="118" height="88" rx="8" stroke="#cfe7ff" stroke-width="2" fill="#e9f4ff"/>
                  <path d="M18 64l20-24 18 20 12-14 34 34H18z" fill="#d7ecff"/>
                  <circle cx="42" cy="30" r="8" fill="#c9e6ff"/>
                </svg>
              <?php endif; ?>
            </div>

            <div class="content">
              <div class="name"><?= h($row['product_name']) ?></div>
              <div class="details"><?= nl2br(h($row['details'] ?? '')) ?></div>
              <div class="price"><?= number_format((float)$row['price'], 2) ?> บาท</div>

              <div class="actions">
                <a class="btn secondary" href="show_product.php?product_id=<?= (int)$row['product_id'] ?>">ดูรายละเอียด</a>
                <a class="btn" href="cart.php?action=add&id=<?= (int)$row['product_id'] ?>">🛒 หยิบใส่ตะกร้า</a>
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

