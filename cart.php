<?php
// cart.php — ตะกร้าสินค้า (แก้ปัญหา array * float)
session_start();
require_once "conn.php";

// ---------------------- helpers ----------------------
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/** ทำให้ตะกร้าอยู่ในรูปแบบมาตรฐาน ['items' => [product_id => qty]] */
function normalize_cart(){
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    // ถ้าเป็นรูปแบบเดิม [id => qty]
    if (isset($_SESSION['cart']) && !isset($_SESSION['cart']['items'])) {
        // ถ้า cart มีแต่ numeric keys → ถือว่าเป็น [id=>qty]
        $isLegacy = true;
        foreach($_SESSION['cart'] as $k=>$v){
            if (!is_int($k) && !ctype_digit((string)$k)) { $isLegacy=false; break; }
        }
        if ($isLegacy) {
            $_SESSION['cart'] = ['items' => array_map('intval', $_SESSION['cart'])];
        } else {
            $_SESSION['cart'] = ['items' => []];
        }
    }
    if (!isset($_SESSION['cart']['items']) || !is_array($_SESSION['cart']['items'])) {
        $_SESSION['cart']['items'] = [];
    }
    // ลบจำนวนติดลบ/ศูนย์
    foreach($_SESSION['cart']['items'] as $pid => $q){
        $q = (int)$q;
        if ($q <= 0) unset($_SESSION['cart']['items'][$pid]);
        else $_SESSION['cart']['items'][$pid] = $q;
    }
}
normalize_cart();

// ---------------------- cart actions ----------------------
$action = $_GET['action'] ?? '';

if ($action === 'add') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $_SESSION['cart']['items'][$id] = ($_SESSION['cart']['items'][$id] ?? 0) + 1;
    }
    header("Location: cart.php");
    exit;
}

if ($action === 'remove') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0 && isset($_SESSION['cart']['items'][$id])) {
        unset($_SESSION['cart']['items'][$id]);
    }
    header("Location: cart.php");
    exit;
}

if ($action === 'empty') {
    $_SESSION['cart']['items'] = [];
    header("Location: cart.php");
    exit;
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // จะได้ $_POST['qty'][product_id] เป็นตัวเลข
    $posted = $_POST['qty'] ?? [];
    if (is_array($posted)) {
        foreach ($posted as $pid => $q) {
            $pid = (int)$pid;
            $q   = (int)$q;
            if ($pid <= 0) continue;
            if ($q <= 0) unset($_SESSION['cart']['items'][$pid]);
            else $_SESSION['cart']['items'][$pid] = $q;
        }
    }
    header("Location: cart.php");
    exit;
}

// ---------------------- fetch data ----------------------
$items = $_SESSION['cart']['items']; // [id => qty]
$productRows = [];
if (!empty($items)) {
    $ids = array_keys($items);
    $ids = array_map('intval', $ids);
    $in  = implode(',', $ids);
    $sql = "SELECT product_id, product_name, price, image FROM product WHERE product_id IN ($in)";
    $res = $conn->query($sql);
    while($r = $res->fetch_assoc()){
        $productRows[(int)$r['product_id']] = $r;
    }
}

// ---------------------- UI ----------------------
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>ตะกร้าสินค้า</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  :root{ --navH:74px; --primary:#2196f3; --primary-2:#1976d2; }
  *{box-sizing:border-box}
  body{
    margin:0; font-family:system-ui,-apple-system,"Segoe UI",Roboto,"TH Sarabun New",sans-serif;
    background:
      linear-gradient(180deg, rgba(14,30,56,.65), rgba(12,26,50,.65)),
      url('https://i.pinimg.com/originals/3e/7a/8d/3e7a8decf4b6a3f4d1ac2703d8a1f5ad.jpg') center/cover fixed no-repeat;
    color:#0b2c4a;
  }
  .page-wrap{ padding-top: calc(var(--navH) + 18px); }
  .wrap{ max-width:1100px; margin:24px auto 60px; padding:0 16px; }
  .card{ background:#fff; border-radius:16px; box-shadow:0 18px 36px rgba(0,0,0,.2); overflow:hidden; }
  .head{ padding:16px 20px; font-weight:900; font-size:1.4rem; color:#0f4fa0; background:linear-gradient(180deg,#f4f9ff,#eef6ff); border-bottom:1px solid #eaf3ff; }
  table{ width:100%; border-collapse:collapse; }
  th,td{ padding:14px 12px; border-bottom:1px solid #eef2f7; vertical-align:middle; }
  th{ background:#f9fbff; color:#556; text-align:left; }
  .thumb{ width:66px; height:66px; border-radius:10px; object-fit:cover; background:#eef6ff; display:block; }
  .row-actions a{ display:inline-block; background:#e74c3c; color:#fff; padding:8px 12px; border-radius:10px; text-decoration:none; }
  .row-actions a:hover{ opacity:.9; }
  .qty{ width:90px; padding:8px; border:1px solid #cfe1ff; border-radius:10px; }
  .totals{ display:flex; gap:10px; justify-content:flex-end; padding:14px; }
  .btn{ display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:10px 14px; border-radius:12px; text-decoration:none; border:0; cursor:pointer; font-weight:800; }
  .btn.primary{ background:var(--primary); color:#fff; box-shadow:0 12px 24px rgba(33,150,243,.25); }
  .btn.primary:hover{ background:var(--primary-2); }
  .btn.ghost{ background:#7b91a6; color:#fff; }
  .btn.danger{ background:#e74c3c; color:#fff; }
  .empty{ background:#fff; border-radius:16px; padding:24px; text-align:center; box-shadow:0 18px 36px rgba(0,0,0,.2); }
</style>
</head>
<body>

<?php if (file_exists('customer_nav.php')) include 'customer_nav.php'; ?>

<div class="page-wrap">
  <div class="wrap">
    <?php if (empty($items)): ?>
      <div class="empty">
        <h2 style="margin:0 0 10px">ตะกร้าสินค้า</h2>
        <p>ยังไม่มีสินค้าในตะกร้า</p>
        <a class="btn primary" href="product_list.php">← กลับไปเลือกสินค้า</a>
      </div>
    <?php else: ?>
      <div class="card">
        <div class="head">ตะกร้าสินค้า</div>
        <form method="post" action="cart.php?action=update">
          <table>
            <thead>
              <tr>
                <th style="width:90px">รูป</th>
                <th>สินค้า</th>
                <th style="width:140px">ราคา/ชิ้น</th>
                <th style="width:120px">จำนวน</th>
                <th style="width:140px">รวม</th>
                <th style="width:110px">จัดการ</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $grand = 0.0;
                foreach($items as $pid => $qty):
                  $pid = (int)$pid;
                  $qty = (int)$qty;                       // ★ ทำให้แน่ใจว่าเป็นจำนวน (int)
                  $p   = $productRows[$pid] ?? null;
                  if (!$p) continue;
                  $price = (float)$p['price'];            // ★ ราคาเป็น float
                  $line  = $price * $qty;                 // ★ คูณเลขกับเลขเท่านั้น
                  $grand += $line;
              ?>
              <tr>
                <td>
                  <?php if (!empty($p['image'])): ?>
                    <img class="thumb" src="uploads/<?= h($p['image']) ?>" alt="">
                  <?php else: ?>
                    <span class="thumb" style="display:flex;align-items:center;justify-content:center;">—</span>
                  <?php endif; ?>
                </td>
                <td><?= h($p['product_name']) ?></td>
                <td><?= number_format($price,2) ?></td>
                <td>
                  <input class="qty" type="number" min="1" name="qty[<?= $pid ?>]" value="<?= $qty ?>">
                </td>
                <td><?= number_format($line,2) ?></td>
                <td class="row-actions">
                  <a href="cart.php?action=remove&id=<?= $pid ?>" class="danger">ลบ</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <div class="totals">
            <div style="margin-right:auto; font-weight:800;">
              ราคาสุทธิ: <?= number_format($grand,2) ?> บาท
            </div>
            <a class="btn ghost" href="product_list.php">← กลับไปเลือกสินค้า</a>
            <a class="btn danger" href="cart.php?action=empty" onclick="return confirm('ล้างตะกร้าทั้งหมด?')">ล้างตะกร้า</a>
            <button class="btn primary" type="submit">อัปเดตจำนวน</button>
            <a class="btn primary" href="checkout.php">ดำเนินการสั่งซื้อ</a>
          </div>
        </form>
      </div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
