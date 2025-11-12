<?php
// order_history.php — ประวัติคำสั่งซื้อ (ลูกค้า)
session_start();
require_once "conn.php";

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// ต้องมีการล็อกอิน และเราจะหา customer_id จากตาราง customer ด้วย username ใน session
$username = $_SESSION['sess_username'] ?? '';
if ($username === '') {
  header("Location: login_form.php");
  exit;
}

// หา customer_id จากตาราง customer
$stmC = $conn->prepare("SELECT ID FROM customer WHERE username = ? LIMIT 1");
$stmC->bind_param("s", $username);
$stmC->execute();
$resC = $stmC->get_result();
if (!$resC || $resC->num_rows === 0) {
  // ไม่มีข้อมูลลูกค้า -> กลับไปหน้าแรก
  header("Location: product_list.php");
  exit;
}
$customer = $resC->fetch_assoc();
$customer_id = (int)$customer['ID'];

// ดึงรายการคำสั่งซื้อของลูกค้าคนนี้ พร้อมยอดรวมและจำนวนรายการ
$sql = "
  SELECT 
    o.order_id,
    o.order_date,
    COALESCE(SUM(d.quantity), 0) AS items,
    COALESCE(SUM(d.price * d.quantity), 0) AS grand
  FROM orders o
  LEFT JOIN order_details d ON d.order_id = o.order_id
  WHERE o.customer_id = ?
  GROUP BY o.order_id, o.order_date
  ORDER BY o.order_id DESC
";
$stm = $conn->prepare($sql);
$stm->bind_param("i", $customer_id);
$stm->execute();
$orders = $stm->get_result();

$hasNav = file_exists('customer_nav.php');
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>ประวัติการสั่งซื้อ</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  :root{
    --navH:74px;
    --glass: rgba(255,255,255,.08);
    --border: rgba(255,255,255,.25);
    --primary:#2196f3;
    --primary-2:#1976d2;
    --muted:#88a7c0;
    --ink:#0b2c4a;
  }
  *{ box-sizing:border-box }
  body{
    margin:0;
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, "TH Sarabun New", sans-serif;
    color:#fff;
    min-height:100vh;
    background:
      linear-gradient(180deg, rgba(10,24,48,.7), rgba(10,24,48,.7)),
      url('assets/bg_space.jpg') center/cover fixed no-repeat;
  }
  a{ color:inherit; text-decoration:none }

  .page-wrap{ padding-top: calc(var(--navH) + 18px); }
  .wrap{ max-width:1100px; margin:34px auto 70px; padding:0 16px; }

  .title-bar{ display:flex; align-items:center; gap:12px; margin-bottom:14px; }
  .title{ margin:0; font-weight:900; font-size:1.8rem; color:#e9f4ff; text-shadow:0 8px 24px rgba(0,0,0,.28); }

  .card{
    background: var(--glass);
    border:1px solid var(--border);
    border-radius:18px;
    box-shadow: 0 18px 36px rgba(0,0,0,.22);
    overflow:hidden;
    backdrop-filter: blur(10px);
  }
  .head{
    padding:16px 18px;
    border-bottom:1px solid var(--border);
    background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,0));
    font-weight:800;
    color:#cfe7ff;
  }

  table{ width:100%; border-collapse:collapse; }
  thead th{
    text-align:left; color:#0f4fa0; background:#f4f9ff;
    padding:12px 14px; border-bottom:1px solid #e6eefb;
  }
  tbody td{
    padding:12px 14px; border-bottom:1px solid rgba(255,255,255,.18);
  }
  tbody tr:hover{ background: rgba(255,255,255,.05); }
  .right{ text-align:right; }
  .muted{ color:#cfe7ff; }

  .btn{
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    padding:10px 14px; border-radius:12px; text-decoration:none; border:0;
    background:var(--primary); color:#fff; font-weight:800;
    box-shadow:0 10px 22px rgba(33,150,243,.22);
    transition:.15s background,.15s transform;
  }
  .btn:hover{ background:var(--primary-2); transform:translateY(-1px); }
  .btn.ghost{ background:#7b91a6; }
  .btn.ghost:hover{ background:#6b839a; }

  .empty{
    margin-top:14px; padding:20px; text-align:center;
    color:#cfe7ff; border:1px dashed var(--border);
    border-radius:14px; background: var(--glass); backdrop-filter: blur(8px);
  }
  .footer-actions{ display:flex; gap:10px; justify-content:flex-end; padding:14px; }
</style>
</head>
<body>

<?php if ($hasNav) include 'customer_nav.php'; ?>

<div class="page-wrap">
  <div class="wrap">
    <div class="title-bar">
      <h1 class="title">ประวัติการสั่งซื้อ</h1>
    </div>

    <article class="card">
      <div class="head">รายการคำสั่งซื้อของคุณ (ผู้ใช้: <?= h($username) ?>)</div>

      <?php if (!$orders || $orders->num_rows === 0): ?>
        <div class="empty">ยังไม่มีคำสั่งซื้อ</div>
        <div class="footer-actions">
          <a class="btn" href="product_list.php">ไปเลือกซื้อสินค้า</a>
        </div>
      <?php else: ?>
        <div style="padding:0 6px 10px;">
          <table>
            <thead>
              <tr>
                <th style="width:120px;">เลขที่</th>
                <th style="width:220px;">วันที่สั่งซื้อ</th>
                <th class="right" style="width:140px;">จำนวนชิ้น</th>
                <th class="right" style="width:180px;">ยอดรวม (บาท)</th>
                <th style="width:160px;">การจัดการ</th>
              </tr>
            </thead>
            <tbody>
              <?php while($row = $orders->fetch_assoc()): ?>
                <tr>
                  <td>#<?= (int)$row['order_id'] ?></td>
                  <td><?= h($row['order_date']) ?></td>
                  <td class="right"><?= (int)$row['items'] ?></td>
                  <td class="right"><?= number_format((float)$row['grand'], 2) ?></td>
                  <td>
                    <a class="btn" href="viewOrder.php?order_id=<?= (int)$row['order_id'] ?>">ดูรายละเอียด</a>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>

        <div class="footer-actions">
          <a class="btn ghost" href="product_list.php">← กลับไปเลือกซื้อสินค้า</a>
        </div>
      <?php endif; ?>
    </article>
  </div>
</div>
</body>
</html>
