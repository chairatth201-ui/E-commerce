<?php
// checkout.php — บันทึกคำสั่งซื้อและสรุปผล
session_start();
require_once "conn.php";

// ---------- ช่วยจัดรูปแบบตะกร้า ----------
function normalize_cart(){
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    if (!isset($_SESSION['cart']['items'])) {
        // รองรับรูปแบบเดิม [id => qty]
        $legacy = true;
        foreach($_SESSION['cart'] as $k=>$v){
            if (!is_int($k) && !ctype_digit((string)$k)) { $legacy = false; break; }
        }
        $_SESSION['cart'] = $legacy ? ['items'=>array_map('intval', $_SESSION['cart'])] : ['items'=>[]];
    }
    foreach($_SESSION['cart']['items'] as $pid=>$q){
        $q = (int)$q;
        if ($q<=0) unset($_SESSION['cart']['items'][(int)$pid]);
        else $_SESSION['cart']['items'][(int)$pid] = $q;
    }
}
normalize_cart();

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// ---------- ต้องล็อกอิน ----------
$username = $_SESSION['sess_username'] ?? '';
if ($username === '') {
    header("Location: login_form.php?err=login_required");
    exit;
}

// ---------- ดึง customer.id ----------
$cid = 0;
$st = $conn->prepare("SELECT ID FROM customer WHERE username=? LIMIT 1");
$st->bind_param("s", $username);
$st->execute();
$rs = $st->get_result();
if ($rs && $rs->num_rows) {
    $cid = (int)$rs->fetch_assoc()['ID'];
}
if ($cid <= 0) {
    $error = "ไม่พบข้อมูลลูกค้า (username: ".h($username).")";
}

// ---------- เตรียมรายการจากตะกร้า ----------
$items = $_SESSION['cart']['items'];          // [product_id => qty]
$productRows = [];
if (empty($error) && !empty($items)) {
    $ids = array_keys($items);
    $ids = array_map('intval', $ids);
    $in  = implode(',', $ids);
    $sql = "SELECT product_id, product_name, price FROM product WHERE product_id IN ($in)";
    $res = $conn->query($sql);
    while($r = $res->fetch_assoc()){
        $productRows[(int)$r['product_id']] = $r;
    }
}
if (empty($error) && empty($items)) {
    $error = "ยังไม่มีสินค้าในตะกร้า";
}

// ---------- ถ้าพร้อมแล้ว: บันทึกคำสั่งซื้อ ----------
$order_id = 0;
if (empty($error)) {
    $conn->begin_transaction();
    try {
        // 1) orders
        $ins = $conn->prepare("INSERT INTO orders (customer_id, order_date) VALUES (?, NOW())");
        $ins->bind_param("i", $cid);
        $ins->execute();
        $order_id = (int)$conn->insert_id;

        // 2) order_details
        $d = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?,?,?,?)");
        foreach ($items as $pid => $qty) {
            $pid = (int)$pid;
            $qty = (int)$qty;
            if ($qty <= 0) continue;
            if (!isset($productRows[$pid])) continue;

            $price = (float)$productRows[$pid]['price'];
            $d->bind_param("iiid", $order_id, $pid, $qty, $price);
            $d->execute();
        }

        $conn->commit();
        // 3) ล้างตะกร้า
        $_SESSION['cart']['items'] = [];
    } catch (Throwable $e) {
        $conn->rollback();
        $error = "เกิดข้อผิดพลาดในการบันทึกคำสั่งซื้อ: ".h($e->getMessage());
        $order_id = 0;
    }
}

// แถบเมนู (มีอยู่แล้วในโปรเจกต์)
$hasNav = file_exists('customer_nav.php');
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>สรุปการสั่งซื้อ</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  :root{ --navH:74px; --primary:#2196f3; --primary-2:#1976d2; }
  *{box-sizing:border-box}
  body{
    margin:0; font-family:system-ui,-apple-system,"Segoe UI",Roboto,"TH Sarabun New",sans-serif;
    color:#0b2c4a;
    background:
      linear-gradient(180deg, rgba(14,30,56,.65), rgba(12,26,50,.65)),
      url('https://i.pinimg.com/originals/c6/f6/21/c6f621e557d40dceaf794b60e960a67d.gif') center/cover fixed no-repeat;
  }
  .page-wrap{ padding-top: calc(var(--navH) + 18px); }
  .wrap{ max-width:720px; margin:36px auto 60px; padding:0 16px; }
  .card{ background:#fff; border:1px solid #e9f2ff; border-radius:18px; box-shadow:0 16px 34px rgba(0,0,0,.18); overflow:hidden; text-align:center; }
  .head{ padding:20px; background:linear-gradient(180deg,#f4f9ff,#eef6ff); border-bottom:1px solid #eaf3ff; color:#0f4fa0; font-weight:900; font-size:1.6rem; }
  .body{ padding:20px 20px 10px; }
  .ok{ color:#2ecc71; font-weight:800; margin:8px 0 4px; }
  .error{ color:#e74c3c; font-weight:800; }
  .note{ color:#6b7d90; font-size:.95rem; margin:8px 0 0; }
  .actions{ display:flex; flex-direction:column; gap:12px; padding:0 20px 22px; }
  .btn{ display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:12px 16px; border-radius:12px; text-decoration:none; border:0; cursor:pointer; background:var(--primary); color:#fff; font-weight:800; box-shadow:0 12px 24px rgba(33,150,243,.25); transition:.15s background,.15s transform; }
  .btn:hover{ background:var(--primary-2); transform:translateY(-1px); }
  .btn.ghost{ background:#7b91a6; }
  .btn.ghost:hover{ background:#6b839a; }
</style>
</head>
<body>

<?php if ($hasNav) include 'customer_nav.php'; ?>

<div class="page-wrap">
  <div class="wrap">
    <div class="card">
      <div class="head">สรุปการสั่งซื้อ</div>
      <div class="body">
        <?php if (!empty($error)): ?>
          <div class="error"><?= $error ?></div>
          <p class="note">หากมีสินค้าในตะกร้าอยู่ ลองกลับไปตรวจสอบอีกครั้ง</p>
        <?php elseif ($order_id > 0): ?>
          <div class="ok">✅ สั่งซื้อเรียบร้อยแล้ว</div>
          <p class="note">หมายเลขคำสั่งซื้อของคุณคือ <strong>#<?= (int)$order_id ?></strong></p>
        <?php else: ?>
          <div class="error">ไม่พบหมายเลขคำสั่งซื้อ</div>
          <p class="note">อาจเกิดจากการรีเฟรชหน้า หรือยังไม่ได้ยืนยันการสั่งซื้อ</p>
        <?php endif; ?>
      </div>
      <div class="actions">
        <?php if ($order_id > 0): ?>
          <a class="btn" href="viewOrder.php?order_id=<?= (int)$order_id ?>">ดูคำสั่งซื้อ</a>
        <?php endif; ?>
        <a class="btn ghost" href="product_list.php">กลับไปเลือกซื้อสินค้า</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>

