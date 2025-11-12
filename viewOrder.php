<?php
// viewOrder.php — รายละเอียดคำสั่งซื้อ (ลูกค้า) [แก้ให้ใช้ order_id ตาม schema จริง]
session_start();
require_once "conn.php";

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($order_id <= 0) {
  header("Location: product_list.php");
  exit;
}

/* --------- ดึงหัวคำสั่งซื้อ (ใช้ order_id) --------- */
$sqlHead = "SELECT order_id, customer_id, order_date
            FROM orders
            WHERE order_id = ? LIMIT 1";
$stmH = $conn->prepare($sqlHead);
$stmH->bind_param("i", $order_id);
$stmH->execute();
$head = $stmH->get_result()->fetch_assoc();

if (!$head) {
  header("Location: product_list.php");
  exit;
}

/* --------- ดึงรายการสินค้าในคำสั่งซื้อ --------- */
$sqlItems = "SELECT d.product_id, p.product_name, d.price, d.quantity
             FROM order_details d
             LEFT JOIN product p ON p.product_id = d.product_id
             WHERE d.order_id = ?
             ORDER BY d.product_id ASC";
$stmI = $conn->prepare($sqlItems);
$stmI->bind_param("i", $order_id);
$stmI->execute();
$resI = $stmI->get_result();

$items = [];
$grand = 0.0;
while($row = $resI->fetch_assoc()){
  $qty   = (int)$row['quantity'];
  $price = (float)$row['price'];
  $sum   = $price * $qty;
  $grand += $sum;
  $items[] = [
    'product_id'   => $row['product_id'],
    'product_name' => $row['product_name'] ?? ('รหัสสินค้า #' . $row['product_id']),
    'price'        => $price,
    'quantity'     => $qty,
    'sum'          => $sum
  ];
}

$hasNav = file_exists('customer_nav.php');
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>รายละเอียดคำสั่งซื้อ #<?= (int)$order_id ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  :root{ --navH:74px; --border:#e6eefb; --primary:#2196f3; --primary-2:#1976d2; --muted:#6c7a89; }
  *{ box-sizing:border-box }
  body{
    margin:0;
    font-family:system-ui,-apple-system,"Segoe UI",Roboto,"TH Sarabun New",sans-serif;
    background:linear-gradient(180deg,rgba(230,243,255,.65),rgba(220,238,255,.65)),#eaf6ff;
    color:#0b2c4a;
  }
  .page-wrap{ padding-top:calc(var(--navH) + 18px); }
  .wrap{ max-width:1100px; margin:24px auto 60px; padding:0 16px; }
  .card{ background:#fff; border:1px solid var(--border); border-radius:18px; box-shadow:0 14px 28px rgba(0,0,0,.08); overflow:hidden; }
  .head{ padding:18px 22px; background:linear-gradient(180deg,#f4f9ff,#eef6ff); border-bottom:1px solid var(--border); font-weight:900; color:#0f4fa0; }
  .meta{ padding:14px 22px; border-bottom:1px solid var(--border); color:#123; }
  .meta-grid{ display:grid; grid-template-columns:160px 1fr 160px 1fr; gap:6px 14px; }
  @media (max-width:720px){ .meta-grid{ grid-template-columns:1fr 1fr; } }
  table{ width:100%; border-collapse:collapse; }
  thead th{ text-align:left; background:#f4f9ff; border-bottom:1px solid var(--border); padding:12px 14px; font-weight:800; color:#0f4fa0; }
  tbody td{ border-bottom:1px solid var(--border); padding:12px 14px; }
  .right{ text-align:right; }
  .muted{ color:var(--muted); }
  .footer{ display:flex; justify-content:space-between; align-items:center; gap:10px; padding:16px; background:#fafcff; border-top:1px solid var(--border); }
  .btn{ display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:12px 16px; border-radius:12px; text-decoration:none; border:0;
        background:var(--primary); color:#fff; font-weight:800; box-shadow:0 12px 24px rgba(33,150,243,.18); transition:.15s background,.15s transform; }
  .btn:hover{ background:var(--primary-2); transform:translateY(-1px); }
  .total{ font-weight:900; color:#0f4fa0; background:#eef6ff; border:1px solid var(--border); padding:10px 14px; border-radius:12px; }
</style>
</head>
<body>

<?php if ($hasNav) include 'customer_nav.php'; ?>

<div class="page-wrap">
  <div class="wrap">
    <div class="card">
      <div class="head">รายละเอียดคำสั่งซื้อ #<?= (int)$order_id ?></div>

      <div class="meta">
        <div class="meta-grid">
          <div class="muted">ลูกค้า</div>
          <div><?= h($head['customer_id']) ?></div>

          <div class="muted">วัน/เวลาที่สั่งซื้อ</div>
          <div><?= h($head['order_date']) ?></div>
        </div>
      </div>

      <div style="padding:0 6px 10px;">
        <table>
          <thead>
            <tr>
              <th style="width:110px;">รหัสสินค้า</th>
              <th>ชื่อสินค้า</th>
              <th class="right" style="width:140px;">ราคา/ชิ้น</th>
              <th class="right" style="width:120px;">จำนวน</th>
              <th class="right" style="width:160px;">รวม</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($items)): ?>
              <tr><td colspan="5" class="muted" style="text-align:center; padding:22px;">ไม่มีรายการสินค้าในคำสั่งซื้อนี้</td></tr>
            <?php else: ?>
              <?php foreach($items as $it): ?>
                <tr>
                  <td><?= (int)$it['product_id'] ?></td>
                  <td><?= h($it['product_name']) ?></td>
                  <td class="right"><?= number_format($it['price'], 2) ?></td>
                  <td class="right"><?= (int)$it['quantity'] ?></td>
                  <td class="right"><?= number_format($it['sum'], 2) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="footer">
        <a class="btn" href="product_list.php">← กลับไปเลือกซื้อสินค้า</a>
        <div class="total">ราคารวมทั้งสิ้น: <?= number_format($grand, 2) ?> บาท</div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
