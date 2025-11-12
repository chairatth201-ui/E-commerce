<?php
// show_product.php
session_start();
require_once "conn.php";

function has_column(mysqli $conn, string $table, string $column): bool {
  $sql = "SELECT 1
          FROM INFORMATION_SCHEMA.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
          LIMIT 1";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $table, $column);
  $stmt->execute();
  $res = $stmt->get_result();
  return ($res && $res->num_rows > 0);
}
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$product_id = intval($_GET['product_id'] ?? 0);
if ($product_id <= 0) { header("Location: product_list.php"); exit; }

// ตรวจว่ามีคอลัมน์ category ไหม
$hasCategory = has_column($conn, 'product', 'category');

// ดึงข้อมูลสินค้า
if ($hasCategory) {
  $sql = "SELECT product_id, product_name, price, details, image, category
          FROM product WHERE product_id = ? LIMIT 1";
} else {
  $sql = "SELECT product_id, product_name, price, details, image
          FROM product WHERE product_id = ? LIMIT 1";
}
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) { header("Location: product_list.php"); exit; }
$product = $res->fetch_assoc();

// สินค้าแนะนำ (ตาม category ถ้ามี ไม่งั้นล่าสุด)
$recom = [];
if ($hasCategory && !empty($product['category'])) {
  $rs = $conn->prepare(
    "SELECT product_id, product_name, price, image
     FROM product
     WHERE product_id <> ? AND category = ?
     ORDER BY product_id DESC
     LIMIT 6"
  );
  $rs->bind_param("is", $product_id, $product['category']);
  $rs->execute();
  $re = $rs->get_result();
  while ($r = $re->fetch_assoc()) $recom[] = $r;
}
if (count($recom) === 0) {
  $rs = $conn->prepare(
    "SELECT product_id, product_name, price, image
     FROM product
     WHERE product_id <> ?
     ORDER BY product_id DESC
     LIMIT 6"
  );
  $rs->bind_param("i", $product_id);
  $rs->execute();
  $re = $rs->get_result();
  while ($r = $re->fetch_assoc()) $recom[] = $r;
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title><?= h($product['product_name']) ?> | รายละเอียดสินค้า</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
:root{
  --glass: rgba(255,255,255,.08);
  --glass-2: rgba(255,255,255,.18);
  --border: rgba(255,255,255,.25);
  --primary:#2196f3;
  --primary-2:#1976d2;
}
/* fallback เผื่อกรณีไม่ได้ include customer_nav.php (ซึ่งนิยาม --navH ไว้) */
.page-wrap{ padding-top: calc(var(--navH, 74px) + 16px); }

*{box-sizing:border-box}
body{
  margin:0;
  font-family:system-ui,-apple-system,"Segoe UI",Roboto,"TH Sarabun New",sans-serif;
  color:#fff;
  min-height:100vh;
  background:
    linear-gradient(180deg, rgba(10,24,48,.7), rgba(10,24,48,.7)),
    url('assets/bg_space.jpg') center/cover fixed no-repeat;
}
a{ color:inherit; text-decoration:none }

.wrap{ max-width:1200px; margin:32px auto 60px; padding:0 18px; }

.card{
  background: var(--glass);
  border:1px solid var(--border);
  border-radius:18px;
  box-shadow: 0 18px 36px rgba(0,0,0,.25);
  overflow:hidden;
  backdrop-filter: blur(10px);
}
.header{
  padding:18px 22px;
  border-bottom:1px solid var(--border);
  background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,0));
}
.title{ margin:0; font-size:2rem; font-weight:900; color:#fff; }

.body{
  display:grid; grid-template-columns: 520px 1fr; gap:24px; padding:22px;
}
@media (max-width:1280px){ .body{ grid-template-columns: 460px 1fr; } }
@media (max-width:1020px){ .body{ grid-template-columns: 1fr; } }

.thumb{
  width:100%; background:#eef6ff; border-radius:14px;
  display:flex; align-items:center; justify-content:center; padding:10px;
}
.thumb img{ max-width:100%; border-radius:12px; }

.info{ display:flex; flex-direction:column; gap:10px; }
.price{
  display:inline-block; background:var(--glass-2); border:1px solid var(--border);
  padding:8px 14px; border-radius:12px; font-weight:800;
}
.cat{
  display:inline-block; margin-left:8px; font-size:.95rem; color:#cfe7ff;
  background: rgba(123,198,255,.2); border:1px solid var(--border);
  padding:6px 10px; border-radius:10px;
}
.details{
  color:#eaf4ff; line-height:1.6; background:rgba(255,255,255,.05);
  border:1px solid var(--border); border-radius:12px; padding:12px 14px;
  min-height:120px;
}
.actions{ display:flex; gap:12px; margin-top:10px; flex-wrap:wrap; }
.btn{
  display:inline-flex; align-items:center; gap:10px; padding:12px 18px;
  border-radius:12px; border:0; cursor:pointer; font-weight:800;
  background:var(--primary); color:#fff; box-shadow:0 12px 24px rgba(33,150,243,.25);
  transition:.15s transform,.15s background;
}
.btn:hover{ background:var(--primary-2); transform:translateY(-1px); }
.btn.ghost{ background:#7b91a6; box-shadow:0 12px 24px rgba(123,145,166,.25); }
.btn.ghost:hover{ background:#6b839a; }

.section{ margin-top:28px; }
.sec-title{ font-weight:900; font-size:1.3rem; margin:0 0 12px 2px; }

.recom-grid{ display:grid; grid-template-columns:repeat(6,1fr); gap:16px; }
@media (max-width:1200px){ .recom-grid{ grid-template-columns:repeat(4,1fr);} }
@media (max-width:900px){  .recom-grid{ grid-template-columns:repeat(3,1fr);} }
@media (max-width:640px){  .recom-grid{ grid-template-columns:repeat(2,1fr);} }

.recom{
  background:var(--glass); border:1px solid var(--border); border-radius:14px;
  overflow:hidden; text-align:center; backdrop-filter: blur(8px);
  transition: transform .12s ease;
}
.recom:hover{ transform: translateY(-2px); }
.re-thumb{
  width:100%; aspect-ratio:4/3; display:flex; align-items:center; justify-content:center;
  background: linear-gradient(180deg,#f1f7ff,#e4f0ff); border-bottom:1px solid rgba(255,255,255,.22);
}
.re-thumb img{ max-width:92%; max-height:92%; object-fit:contain; border-radius:10px; }
.re-name{ padding:10px 10px 4px; font-weight:700; min-height:2.4em; }
.re-price{ padding:0 0 12px; color:#cfe7ff; font-weight:800; font-size:.95rem; }
</style>
</head>
<body>

<?php if (file_exists('customer_nav.php')) include 'customer_nav.php'; ?>

<div class="page-wrap">
  <div class="wrap">
    <article class="card">
      <header class="header">
        <h1 class="title"><?= h($product['product_name']) ?></h1>
      </header>

      <section class="body">
        <div class="thumb">
          <?php if (trim((string)$product['image']) !== ''): ?>
            <img src="uploads/<?= rawurlencode($product['image']) ?>" alt="">
          <?php else: ?>
            <svg width="300" height="220" viewBox="0 0 120 90" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <rect x="1" y="1" width="118" height="88" rx="8" stroke="#b9ddff" stroke-width="2" fill="#f3faff"/>
              <path d="M18 64l20-24 18 20 12-14 34 34H18z" fill="#d7ecff"/>
              <circle cx="42" cy="30" r="8" fill="#c9e6ff"/>
            </svg>
          <?php endif; ?>
        </div>

        <div class="info">
          <div>
            <span class="price">ราคา: <?= number_format((float)$product['price'], 2) ?> บาท</span>
            <?php if ($hasCategory && !empty($product['category'])): ?>
              <span class="cat">หมวดหมู่: <?= h($product['category']) ?></span>
            <?php endif; ?>
          </div>

          <div class="details">
            <strong>รายละเอียด:</strong><br>
            <?= nl2br(h($product['details'] ?? '—')) ?>
          </div>

          <div class="actions">
            <a class="btn" href="cart.php?action=add&id=<?= (int)$product['product_id'] ?>">🛒 เพิ่มลงตะกร้า</a>
            <a class="btn ghost" href="product_list.php">📜 รายการสินค้า</a>
          </div>
        </div>
      </section>
    </article>

    <?php if (count($recom) > 0): ?>
    <section class="section">
      <h2 class="sec-title">สินค้าแนะนำ</h2>
      <div class="recom-grid">
        <?php foreach ($recom as $r): ?>
          <a class="recom" href="show_product.php?product_id=<?= (int)$r['product_id'] ?>">
            <div class="re-thumb">
              <?php if (trim((string)$r['image']) !== ''): ?>
                <img src="uploads/<?= rawurlencode($r['image']) ?>" alt="">
              <?php else: ?>
                <svg width="120" height="90" viewBox="0 0 120 90" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <rect x="1" y="1" width="118" height="88" rx="8" stroke="#cfe7ff" stroke-width="2" fill="#e9f4ff"/>
                  <path d="M18 64l20-24 18 20 12-14 34 34H18z" fill="#d7ecff"/>
                  <circle cx="42" cy="30" r="8" fill="#c9e6ff"/>
                </svg>
              <?php endif; ?>
            </div>
            <div class="re-name"><?= h($r['product_name']) ?></div>
            <div class="re-price"><?= number_format((float)$r['price'], 2) ?> บาท</div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
