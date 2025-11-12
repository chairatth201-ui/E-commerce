<?php
// admin_products.php — จัดการรายการสินค้า (ดู/ค้นหา/ลบ) สำหรับแอดมิน
session_start();
require_once "conn.php";

// ต้องเป็นแอดมินเท่านั้น
if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit;
}

// สร้าง CSRF แบบง่าย ๆ (กันกดลบข้ามโดเมน)
if (empty($_SESSION['adm_tok'])) {
  $_SESSION['adm_tok'] = bin2hex(random_bytes(16));
}

// ตรวจว่ามีคอลัมน์ category ในตาราง product หรือไม่ (ใช้กรอง/แสดงได้ถ้ามี)
function has_column(mysqli $conn, string $table, string $col): bool {
  $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=? LIMIT 1";
  $st  = $conn->prepare($sql);
  $st->bind_param("ss", $table, $col);
  $st->execute();
  $rs  = $st->get_result();
  return ($rs && $rs->num_rows > 0);
}
$hasCategory = has_column($conn, 'product', 'category');

$q   = trim($_GET['q'] ?? '');
$cat = $hasCategory ? trim($_GET['cat'] ?? '') : '';

$sql = "SELECT product_id, product_name, price, details, image" . ($hasCategory ? ", category" : "") . " FROM product";
$cond = [];
$types = "";
$vals  = [];

if ($q !== "") {
  $cond[] = "(product_name LIKE ? OR details LIKE ?)";
  $types .= "ss";
  $like   = "%{$q}%";
  $vals[] = $like; $vals[] = $like;
}
if ($hasCategory && $cat !== "") {
  $cond[] = "category = ?";
  $types .= "s";
  $vals[] = $cat;
}
if ($cond) $sql .= " WHERE " . implode(" AND ", $cond);
$sql .= " ORDER BY product_id DESC";

$st = $conn->prepare($sql);
if ($types !== "") $st->bind_param($types, ...$vals);
$st->execute();
$products = $st->get_result();

// ดึงรายการหมวด (ถ้ามีคอลัมน์ category)
$cats = [];
if ($hasCategory) {
  $rs = $conn->query("SELECT DISTINCT category FROM product WHERE category IS NOT NULL AND category<>'' ORDER BY category ASC");
  while ($r = $rs->fetch_assoc()) $cats[] = $r['category'];
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>จัดการสินค้า • Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&display=swap" rel="stylesheet">
<style>
  :root{ --navH:74px; --glass:rgba(255,255,255,.08); --border:rgba(255,255,255,.25);
         --primary:#2196f3; --primary-2:#1976d2; }
  *{box-sizing:border-box}
  body{
    margin:0; color:#eaf4ff;
    font-family:system-ui,-apple-system,Segoe UI,Roboto,"TH Sarabun New",sans-serif;
    background:
      linear-gradient(180deg,rgba(12,26,50,.88),rgba(12,26,50,.78) 65%,rgba(12,26,50,.62)),
      url('https://i.pinimg.com/originals/0e/b1/56/0eb15636563ecc2920056a5dd6e496c5.gif') center/cover no-repeat fixed;
    min-height:100vh;
  }
  /* NAV (ยกชุดจาก admin_home.php) */
  .c-nav{position:fixed;inset:0 0 auto 0;height:var(--navH);z-index:999;display:flex;align-items:center;gap:18px;padding:10px 18px;color:#fff;border-bottom:1px solid var(--border);backdrop-filter:blur(6px);background:linear-gradient(180deg,rgba(12,26,50,.88),rgba(12,26,50,.78));box-shadow:0 12px 26px rgba(0,0,0,.35);}
  .c-brand{font-family:'Orbitron',system-ui,-apple-system,Segoe UI,Roboto,sans-serif;font-weight:800;letter-spacing:.8px;font-size:28px;color:#c9ecff;text-decoration:none;}
  .c-menu{margin-left:auto;display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
  .c-btn{display:inline-flex;align-items:center;justify-content:center;min-width:108px;padding:10px 14px;border-radius:12px;font-weight:800;color:#fff;text-decoration:none;border:1px solid var(--border);background:rgba(255,255,255,.12);}
  .c-btn.primary{background:var(--primary);border-color:transparent;}
  .c-user{margin-left:6px;padding:8px 12px;border-radius:12px;background:rgba(255,255,255,.12);border:1px solid var(--border);font-weight:700;color:#d9f1ff;}
  .page-wrap{padding-top:calc(var(--navH) + 18px);}
  .wrap{max-width:1200px;margin:24px auto 60px;padding:0 18px;}
  .toolbar{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:14px;}
  .toolbar form{display:flex;gap:8px;align-items:center;background:var(--glass);border:1px solid var(--border);padding:8px 10px;border-radius:12px;}
  .toolbar input,.toolbar select{border:0;outline:none;background:transparent;color:#fff;padding:6px 8px;}
  .toolbar button{border:0;padding:8px 12px;border-radius:10px;background:var(--primary);color:#fff;font-weight:800;cursor:pointer;}
  .toolbar .ghost{background:#7b91a6;}
  .card{background:var(--glass);border:1px solid var(--border);border-radius:16px;backdrop-filter:blur(8px);overflow:hidden;box-shadow:0 12px 26px rgba(0,0,0,.25);}
  table{width:100%;border-collapse:collapse;}
  thead th{background:rgba(33,150,243,.2);padding:12px;text-align:left;}
  tbody td{padding:12px;border-top:1px solid rgba(255,255,255,.18);}
  .thumb{width:64px;height:64px;object-fit:cover;border-radius:8px;border:1px solid rgba(255,255,255,.25);background:#eef6ff;}
  .act{display:flex;gap:8px;flex-wrap:wrap;}
  .btn{display:inline-flex;align-items:center;gap:8px;padding:8px 10px;border-radius:10px;border:0;cursor:pointer;color:#fff;background:var(--primary);}
  .btn.gray{background:#7b91a6;}
  .btn.red{background:#e74c3c;}
  @media (max-width:860px){
    thead{display:none;}
    table,tbody,tr,td{display:block;width:100%;}
    tr{border-top:1px solid rgba(255,255,255,.2);padding:10px;}
    td{border:0;padding:6px 0;}
    td::before{content:attr(data-label);display:block;color:#cfe7ff;font-weight:700;margin-bottom:4px;}
  }
</style>
</head>
<body>

<header class="c-nav">
  <a class="c-brand" href="admin_home.php">Gunpla&nbsp;Master • Admin</a>
  <nav class="c-menu">
    <a class="c-btn" href="admin_home.php">แดชบอร์ด</a>
    <a class="c-btn" href="addProduct_form.php">เพิ่มสินค้า</a>
    <a class="c-btn" href="admin_products.php">จัดการสินค้า</a>
    <a class="c-btn" href="orders_admin.php">คำสั่งซื้อรวม</a>
    <a class="c-btn" href="showmember.php">ข้อมูลสมาชิก</a>
    <span class="c-user">👑 <?= h($_SESSION['admin']) ?></span>
    <a class="c-btn primary" href="logout.php">ออกจากระบบ</a>
  </nav>
</header>

<div class="page-wrap">
  <div class="wrap">

    <div class="toolbar">
      <form method="get" action="">
        <input type="text" name="q" value="<?= h($q) ?>" placeholder="ค้นหาชื่อ/รายละเอียด">
        <?php if ($hasCategory): ?>
          <select name="cat">
            <option value="">ทุกหมวดหมู่</option>
            <?php foreach($cats as $c): ?>
              <option value="<?= h($c) ?>" <?= $c===$cat?'selected':'' ?>><?= h($c) ?></option>
            <?php endforeach; ?>
          </select>
        <?php endif; ?>
        <button type="submit">ค้นหา</button>
        <a class="btn ghost" href="admin_products.php">ล้างตัวกรอง</a>
      </form>

      <a class="btn" href="addProduct_form.php">+ เพิ่มสินค้า</a>
      <a class="btn gray" href="show_allProduct.php" target="_blank">👁‍🗨 ดูหน้า show_allProduct (ลูกค้า)</a>
    </div>

    <div class="card">
      <?php if (!$products || $products->num_rows===0): ?>
        <div style="padding:16px;">ยังไม่มีสินค้า หรือไม่พบตามคำค้น</div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th style="width:90px;">รูป</th>
              <th>ชื่อสินค้า</th>
              <?php if ($hasCategory): ?><th style="width:160px;">หมวด</th><?php endif; ?>
              <th style="width:140px;">ราคา (บาท)</th>
              <th style="width:240px;">การจัดการ</th>
            </tr>
          </thead>
          <tbody>
            <?php while($p=$products->fetch_assoc()): ?>
              <?php
                $img = trim((string)$p['image']);
                $src = $img!=='' ? "uploads/".rawurlencode($img) : "";
              ?>
              <tr>
                <td data-label="รูป">
                  <?php if($src): ?>
                    <img class="thumb" src="<?= h($src) ?>" alt="">
                  <?php else: ?>
                    <div class="thumb"></div>
                  <?php endif; ?>
                </td>
                <td data-label="ชื่อสินค้า"><?= h($p['product_name']) ?></td>
                <?php if ($hasCategory): ?>
                  <td data-label="หมวด"><?= h($p['category'] ?? '') ?></td>
                <?php endif; ?>
                <td data-label="ราคา"><?= number_format((float)$p['price'],2) ?></td>
                <td data-label="การจัดการ">
                  <div class="act">
                    <a class="btn gray" href="show_product.php?product_id=<?= (int)$p['product_id'] ?>" target="_blank">ดูหน้าแสดง</a>
                    <form method="post" action="product_delete.php" onsubmit="return confirm('ลบสินค้า \"<?= h($p['product_name']) ?>\" ?');">
                      <input type="hidden" name="id" value="<?= (int)$p['product_id'] ?>">
                      <input type="hidden" name="tok" value="<?= h($_SESSION['adm_tok']) ?>">
                      <button class="btn red" type="submit">ลบ</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div style="margin-top:12px;padding:10px 12px;border-radius:10px;background:rgba(46,204,113,.15);border:1px solid rgba(46,204,113,.35);">
        <?= h($_GET['msg']) ?>
      </div>
    <?php endif; ?>

  </div>
</div>
</body>
</html>
