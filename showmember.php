<?php
// showmember.php — รายชื่อสมาชิก (เวอร์ชันแอดมิน ธีมเดียวกัน + รองรับชื่อคอลัมน์เบอร์โทรหลายแบบ)
session_start();
require_once "conn.php";

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/** หา column เบอร์โทรในตาราง members แบบยืดหยุ่น */
function find_phone_column(mysqli $conn): ?string {
  $cands = ['Mobile_Phone','mobile_phone','mobilephone','phone','tel','telephone','mobile'];
  $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'members'";
  $rs = $conn->query($sql);
  if ($rs) {
    $cols = [];
    while ($r = $rs->fetch_assoc()) $cols[strtolower($r['COLUMN_NAME'])] = $r['COLUMN_NAME'];
    foreach ($cands as $c) {
      if (isset($cols[strtolower($c)])) return $cols[strtolower($c)];
    }
  }
  return null; // ไม่พบ
}

$phoneCol = find_phone_column($conn);

// ค้นหา
$q = trim($_GET['q'] ?? '');

$select =
  "SELECT id, username, name, email, "
  . ($phoneCol ? "$phoneCol AS phone" : "'' AS phone")
  . ", address FROM members";

$params = [];
$where = "";

if ($q !== '') {
  if ($phoneCol) {
    $where = " WHERE username LIKE ? OR name LIKE ? OR email LIKE ? OR $phoneCol LIKE ? OR address LIKE ?";
  } else {
    $where = " WHERE username LIKE ? OR name LIKE ? OR email LIKE ? OR address LIKE ?";
  }
}

$sql = $select . $where . " ORDER BY id ASC";
$stmt = $conn->prepare($sql);

if ($q !== '') {
  $like = "%{$q}%";
  if ($phoneCol) {
    $stmt->bind_param("sssss", $like,$like,$like,$like,$like);
  } else {
    $stmt->bind_param("ssss", $like,$like,$like,$like);
  }
}

$stmt->execute();
$result = $stmt->get_result();

$hasNav = file_exists('admin_nav.php');
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>รายชื่อสมาชิก</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  :root{
    --navH: 74px;
    --glass: rgba(255,255,255,.08);
    --border: rgba(255,255,255,.25);
    --primary:#2196f3;
    --primary-2:#1976d2;
  }
  *{box-sizing:border-box}
  body{
    margin:0;
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, "TH Sarabun New", sans-serif;
    color:#eaf4ff;
    background:
      linear-gradient(180deg, rgba(10,24,48,.7), rgba(10,24,48,.7)),
      url('https://i.pinimg.com/originals/c6/f6/21/c6f621e557d40dceaf794b60e960a67d.gif') center/cover fixed no-repeat;
    min-height:100vh;
  }
  .page-wrap{ padding-top: calc(var(--navH) + 18px); }
  .wrap{ max-width:1100px; margin:28px auto 60px; padding:0 18px; }
  .card{ background:var(--glass); border:1px solid var(--border); border-radius:18px; backdrop-filter:blur(8px); box-shadow:0 18px 36px rgba(0,0,0,.25); overflow:hidden; }
  .head{ padding:18px 20px; border-bottom:1px solid var(--border); background:linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,0)); display:flex; gap:14px; align-items:center; flex-wrap:wrap; }
  .title{ margin:0; font-size:1.6rem; font-weight:900; color:#fff; }
  .search{ margin-left:auto; display:flex; gap:8px; align-items:center; background:rgba(255,255,255,.12); border:1px solid var(--border); padding:6px 8px; border-radius:12px; }
  .search input{ border:0; outline:none; background:transparent; color:#fff; min-width:240px; }
  .search button{ border:0; padding:8px 12px; border-radius:10px; cursor:pointer; color:#fff; font-weight:800; background:var(--primary); }
  .search button:hover{ background:var(--primary-2); }
  .table-wrap{ padding:0 12px 14px; }
  table{ width:100%; border-collapse:collapse; background:rgba(255,255,255,.06); border:1px solid var(--border); border-radius:14px; overflow:hidden; }
  thead th{ background:rgba(33,150,243,.2); color:#e9f4ff; text-align:left; padding:14px 12px; font-weight:900; }
  tbody td{ padding:12px; border-top:1px solid rgba(255,255,255,.18); color:#eaf4ff; }
  tbody tr:hover{ background:rgba(255,255,255,.06); }
  .muted{ color:#cfe7ff; }
  @media (max-width:760px){
    thead{ display:none; }
    table, tbody, tr, td{ display:block; width:100%; }
    tr{ margin-bottom:10px; border:1px solid rgba(255,255,255,.15); border-radius:12px; overflow:hidden; }
    tbody td{ border-top:0; display:flex; gap:10px; }
    tbody td::before{ content: attr(data-label); min-width:110px; color:#cfe7ff; font-weight:700; }
    .search input{ min-width:140px; }
  }
</style>
</head>
<body>

<?php if ($hasNav) include 'admin_nav.php'; ?>

<div class="page-wrap">
  <div class="wrap">
    <article class="card">
      <header class="head">
        <h1 class="title">รายชื่อสมาชิก</h1>
        <form class="search" method="get" action="">
          <input type="text" name="q" value="<?= h($q) ?>" placeholder="ค้นหา: ชื่อ, อีเมล, เบอร์, ที่อยู่">
          <button type="submit">ค้นหา</button>
        </form>
      </header>

      <div class="table-wrap">
        <?php if (!$result || $result->num_rows === 0): ?>
          <div style="padding:16px 18px; color:#cfe7ff;">ไม่พบข้อมูลสมาชิก</div>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th style="width:70px;">ID</th>
                <th style="width:160px;">Username</th>
                <th style="width:200px;">ชื่อ</th>
                <th style="width:240px;">Email</th>
                <th style="width:160px;">เบอร์โทร</th>
                <th>ที่อยู่</th>
              </tr>
            </thead>
            <tbody>
              <?php while($m = $result->fetch_assoc()): ?>
                <tr>
                  <td data-label="ID"><?= (int)$m['id'] ?></td>
                  <td data-label="Username"><span class="muted">@</span><?= h($m['username']) ?></td>
                  <td data-label="ชื่อ"><?= h($m['name']) ?></td>
                  <td data-label="Email"><?= h($m['email']) ?></td>
                  <td data-label="เบอร์โทร"><?= h($m['phone']) ?></td>
                  <td data-label="ที่อยู่"><?= h($m['address']) ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </article>
  </div>
</div>
</body>
</html>

