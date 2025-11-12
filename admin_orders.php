<?php
// orders_admin.php — รายการคำสั่งซื้อ (Admin)
session_start();
require_once "conn.php";

// ต้องเป็นแอดมินเท่านั้น
if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit;
}

// ฟังก์ชันตรวจคอลัมน์ (กัน schema แตกต่าง)
function has_column(mysqli $conn, string $table, string $column): bool {
  $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $table, $column);
  $stmt->execute();
  $res = $stmt->get_result();
  return ($res && $res->num_rows > 0);
}
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// ตรวจคอลัมน์ที่มักใช้
$hasCustomerId = has_column($conn,'orders','customer_id') || has_column($conn,'orders','member_id') || has_column($conn,'orders','user_id') || has_column($conn,'orders','username');
$hasOrderDate  = has_column($conn,'orders','order_date')  || has_column($conn,'orders','created_at') || has_column($conn,'orders','date');
$detailHasQty  = has_column($conn,'order_details','qty')  || has_column($conn,'order_details','quantity');
$detailHasPrice= has_column($conn,'order_details','price');

// map ชื่อคอลัมน์จริงที่จะใช้
$colCustomer = has_column($conn,'orders','customer_id') ? 'customer_id' :
               (has_column($conn,'orders','member_id')  ? 'member_id'  :
               (has_column($conn,'orders','user_id')    ? 'user_id'    :
               (has_column($conn,'orders','username')   ? 'username'   : null)));

$colOrderDate = has_column($conn,'orders','order_date') ? 'order_date' :
               (has_column($conn,'orders','created_at') ? 'created_at' :
               (has_column($conn,'orders','date')       ? 'date'       : null));

$colQty   = has_column($conn,'order_details','qty')      ? 'qty'      :
           (has_column($conn,'order_details','quantity') ? 'quantity' : null);
$colPrice = $detailHasPrice ? 'price' : null;

// ค้นหา (โดยเลขที่สั่งซื้อ)
$search_id = (int)($_GET['order_id'] ?? 0);

// ดึงรายการคำสั่งซื้อ
if ($search_id > 0) {
  $sql = "SELECT order_id"
       . ($colCustomer ? ", $colCustomer" : "")
       . ($colOrderDate ? ", $colOrderDate" : "")
       . " FROM orders WHERE order_id = ? ORDER BY order_id DESC";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $search_id);
  $stmt->execute();
  $orders = $stmt->get_result();
} else {
  $sql = "SELECT order_id"
       . ($colCustomer ? ", $colCustomer" : "")
       . ($colOrderDate ? ", $colOrderDate" : "")
       . " FROM orders ORDER BY order_id DESC";
  $orders = $conn->query($sql);
}

// เตรียม statement ดึงรายละเอียดแต่ละคำสั่งซื้อ
$detailStmt = $conn->prepare("SELECT * FROM order_details WHERE order_id = ?");
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>คำสั่งซื้อรวม • Admin</title>
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
  }
  *{box-sizing:border-box}
  body{
    margin:0; color:#eaf4ff;
    font-family:system-ui,-apple-system,Segoe UI,Roboto,"TH Sarabun New",sans-serif;
    min-height:100vh;
    background:
      linear-gradient(180deg,rgba(12,26,50,.88),rgba(12,26,50,.78) 65%,rgba(12,26,50,.62)),
      url('https://i.pinimg.com/originals/0e/b1/56/0eb15636563ecc2920056a5dd6e496c5.gif') center/cover no-repeat fixed;
  }

  /* NAV (เหมือนหน้าแอดมินอื่น ๆ) */
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
    margin:0 0 16px; font-size:1.8rem; font-weight:900; color:#fff;
    text-shadow:0 10px 28px rgba(0,0,0,.35);
  }

  /* กล่องค้นหา */
  .search{
    display:flex; gap:10px; align-items:center; flex-wrap:wrap;
    background: var(--glass); border:1px solid var(--border); border-radius:14px;
    padding:12px; margin-bottom:14px;
  }
  .search input{
    min-width:220px; flex:1; padding:10px 12px; border-radius:10px; border:1px solid var(--border);
    background:rgba(255,255,255,.08); color:#fff; outline:none;
  }
  .btn{ display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:10px; border:0; cursor:pointer;
        background: var(--primary); color:#fff; font-weight:800; text-decoration:none; }
  .btn:hover{ filter:brightness(1.05); transform: translateY(-1px); }

  /* ตาราง */
  .card{
    background: var(--glass);
    border:1px solid var(--border);
    border-radius:16px;
    padding:12px;
    backdrop-filter: blur(8px);
  }
  table{
    width:100%; border-collapse:collapse; overflow:hidden; border-radius:12px; background:#eef6ff; color:#0b2c4a;
  }
  thead th{ background:#e0efff; text-align:left; padding:12px; }
  tbody td{ padding:12px; border-top:1px solid #d7eaff; }
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
    <a class="c-btn" href="show_allProduct.php">สินค้า</a>
    <a class="c-btn active" href="orders_admin.php">คำสั่งซื้อรวม</a>
    <a class="c-btn" href="showmember.php">ข้อมูลสมาชิก</a>
    <span class="c-user">👑 <?= h($_SESSION['admin']) ?></span>
    <a class="c-btn primary" href="logout.php">ออกจากระบบ</a>
  </nav>
</header>

<div class="page-wrap">
  <div class="wrap">
    <h1 class="title">คำสั่งซื้อรวม</h1>

    <form class="search" method="get" action="">
      <input type="number" name="order_id" min="1" value="<?= $search_id ?: '' ?>" placeholder="ค้นหาตามเลขที่สั่งซื้อ (เช่น 9)">
      <button class="btn" type="submit">ค้นหา</button>
      <?php if ($search_id): ?>
        <a class="btn" href="orders_admin.php" style="background:#7b91a6">ล้างการค้นหา</a>
      <?php endif; ?>
    </form>

    <?php if (!$orders || $orders->num_rows === 0): ?>
      <div class="empty">ยังไม่มีคำสั่งซื้อ</div>
    <?php else: ?>
      <div class="card">
        <table>
          <thead>
            <tr>
              <th style="width:100px">เลขที่</th>
              <th style="width:220px">ลูกค้า</th>
              <th style="width:220px">วันที่/เวลา</th>
              <th style="width:140px">จำนวนรายการ</th>
              <th style="width:160px">ราคารวมโดยประมาณ</th>
              <th>การจัดการ</th>
            </tr>
          </thead>
          <tbody>
            <?php while($o = $orders->fetch_assoc()): ?>
              <?php
                $oid = (int)$o['order_id'];
                // ดึงรายละเอียดคำสั่งซื้อนี้
                $detailStmt->bind_param("i", $oid);
                $detailStmt->execute();
                $dr = $detailStmt->get_result();

                $itemCount = 0;
                $total = null;
                $sum = 0.0;

                while($d = $dr->fetch_assoc()){
                  $itemCount++;
                  if ($colPrice && $colQty && isset($d[$colPrice]) && isset($d[$colQty])) {
                    $sum += (float)$d[$colPrice] * (float)$d[$colQty];
                  } elseif ($colPrice && isset($d[$colPrice])) {
                    $sum += (float)$d[$colPrice];
                  }
                }
                if ($sum > 0) $total = $sum;

                // ลูกค้า/วันที่
                $customer = '-';
                if ($colCustomer && isset($o[$colCustomer]) && $o[$colCustomer] !== '') {
                  $customer = (string)$o[$colCustomer];
                }
                $odate = '-';
                if ($colOrderDate && isset($o[$colOrderDate]) && $o[$colOrderDate] !== '') {
                  $odate = (string)$o[$colOrderDate];
                }
              ?>
              <tr>
                <td>#<?= $oid ?></td>
                <td><?= h($customer) ?></td>
                <td><?= h($odate) ?></td>
                <td><?= number_format($itemCount) ?></td>
                <td><?= $total !== null ? number_format($total, 2) . " บาท" : '—' ?></td>
                <td>
                  <a class="btn" href="viewOrder.php?order_id=<?= $oid ?>">ดูรายละเอียด</a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

  </div>
</div>

</body>
</html>
