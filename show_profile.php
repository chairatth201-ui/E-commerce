<?php
// show_profile.php — หน้าโปรไฟล์ลูกค้า (พร้อมอัปโหลดรูป)
// ---------------------------------------------------------
session_start();
require_once "conn.php";

if (!isset($_SESSION['sess_username'])) {
  header("Location: login_form.php");
  exit;
}
$username = $_SESSION['sess_username'];

// utility
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function has_column(mysqli $conn, string $table, string $column): bool {
  $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
          LIMIT 1";
  $st = $conn->prepare($sql);
  $st->bind_param("ss",$table,$column);
  $st->execute();
  $r = $st->get_result();
  return ($r && $r->num_rows>0);
}

// รู้ว่าตารางมีคอลัมน์รูปชื่ออะไรได้บ้าง
$avatarCol = null;
if (has_column($conn,'members','avatar'))        $avatarCol = 'avatar';
elseif (has_column($conn,'members','profile_image')) $avatarCol = 'profile_image';

// ดึงสมาชิก
$st = $conn->prepare("SELECT id, username, name, email, address, 
  ".($avatarCol ? "$avatarCol" : "'' AS avatar")."
  FROM members WHERE username=? LIMIT 1");
$st->bind_param("s",$username);
$st->execute();
$mem = $st->get_result()->fetch_assoc();

// เตรียม path รูปโปรไฟล์
$uploadDir = "uploads/avatars/";
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0777, true);

// ถ้ามีชื่อไฟล์จาก DB ใช้นั้นก่อน
$avatarFile = trim((string)($avatarCol ? $mem[$avatarCol] : ''));

// ถ้า DB ไม่มี ให้ลองเดาไฟล์จาก username (fallback)
if ($avatarFile === '') {
  foreach (['jpg','jpeg','png','webp'] as $ext) {
    $probe = $uploadDir."profile_{$mem['username']}.".$ext;
    if (is_file($probe)) { $avatarFile = basename($probe); break; }
  }
}
$avatarUrl = $avatarFile !== '' ? $uploadDir . rawurlencode($avatarFile) : null;
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>ข้อมูลส่วนตัว</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  :root{
    --navH: 74px;
    --glass: rgba(255,255,255,.08);
    --border: rgba(255,255,255,.25);
    --primary:#2196f3; --primary-2:#1976d2;
  }
  *{box-sizing:border-box}
  body{
    margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,"TH Sarabun New",sans-serif;
    background:
      linear-gradient(180deg, rgba(14,30,56,.65), rgba(12,26,50,.65)),
      url('assets/bg_space.jpg') center/cover fixed no-repeat;
    color:#0b2c4a;
  }
  .page-wrap{ padding-top: calc(var(--navH) + 18px); }
  .wrap{ max-width:880px; margin:36px auto 60px; padding:0 16px; }

  .card{
    background:#fff; border:1px solid #e9f2ff; border-radius:20px;
    box-shadow:0 16px 34px rgba(0,0,0,.18); overflow:hidden;
  }
  .head{
    padding:22px; background:linear-gradient(180deg,#4db2ff,#3ca0f1);
    color:#fff; position:relative;
  }
  .title{ margin:0; font-size:1.6rem; font-weight:900; }
  .grid{
    display:grid; grid-template-columns: 220px 1fr; gap:18px; padding:18px; align-items:flex-start;
  }
  @media (max-width:820px){ .grid{ grid-template-columns:1fr; } }

  .avatar-wrap{
    display:flex; flex-direction:column; align-items:center; gap:12px;
  }
  .avatar{
    width:180px; height:180px; border-radius:50%;
    background:#eef6ff; border:3px solid #e6f0ff; overflow:hidden;
    display:flex; align-items:center; justify-content:center;
    box-shadow:0 10px 22px rgba(0,0,0,.15);
  }
  .avatar img{ width:100%; height:100%; object-fit:cover; }
  .avatar .ph{
    width:60%; opacity:.55;
  }
  .upload-box{
    background:#f6fbff; border:1px dashed #cfe7ff; border-radius:12px;
    padding:12px; text-align:center; width:100%;
  }
  .upload-box input[type=file]{ width:100%; }
  .btn{
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    padding:10px 14px; border-radius:10px; border:0; cursor:pointer;
    background:var(--primary); color:#fff; font-weight:800;
    box-shadow:0 10px 22px rgba(33,150,243,.25); transition:.15s transform,.15s background;
  }
  .btn:hover{ background:var(--primary-2); transform:translateY(-1px); }

  .info{
    background:#fff; border:1px solid #eef5ff; border-radius:12px; overflow:hidden;
  }
  .row{ padding:14px 16px; border-bottom:1px solid #eef4ff; }
  .row:last-child{ border-bottom:0; }
  .label{ color:#56708a; font-weight:700; margin-bottom:6px; }
  .val{ font-weight:700; color:#113a66; }
  .hint{ color:#6e8397; font-size:.9rem; margin-top:6px; }
</style>
</head>
<body>

<?php if (file_exists('customer_nav.php')) include 'customer_nav.php'; ?>

<div class="page-wrap">
  <div class="wrap">
    <div class="card">
      <div class="head">
        <h1 class="title">ข้อมูลส่วนตัว</h1>
      </div>

      <div class="grid">
        <!-- รูปโปรไฟล์ + อัปโหลด -->
        <div class="avatar-wrap">
          <div class="avatar">
            <?php if ($avatarUrl): ?>
              <img src="<?= $avatarUrl ?>" alt="avatar">
            <?php else: ?>
              <!-- placeholder -->
              <svg class="ph" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="8" r="4" stroke="#a9c9ff" stroke-width="1.6"/>
                <path d="M4 20c1.7-4 13.3-4 15 0" stroke="#a9c9ff" stroke-width="1.6" stroke-linecap="round"/>
              </svg>
            <?php endif; ?>
          </div>

          <form class="upload-box" method="post" action="profile_upload.php" enctype="multipart/form-data">
            <input type="hidden" name="username" value="<?= h($mem['username']) ?>">
            <input type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp" required>
            <div style="margin-top:10px">
              <button class="btn" type="submit">อัปโหลดรูปโปรไฟล์</button>
            </div>
            <div class="hint">รองรับ .jpg .png .webp (ไม่เกิน ~2MB)</div>
          </form>
        </div>

        <!-- ข้อมูล -->
        <div class="info">
          <div class="row">
            <div class="label">ชื่อผู้ใช้ (USERNAME):</div>
            <div class="val"><?= h($mem['username']) ?></div>
          </div>
          <div class="row">
            <div class="label">ชื่อ-นามสกุล:</div>
            <div class="val"><?= h($mem['name'] ?? '-') ?></div>
          </div>
          <div class="row">
            <div class="label">อีเมล:</div>
            <div class="val"><?= h($mem['email'] ?? '-') ?></div>
          </div>
          <div class="row">
            <div class="label">ที่อยู่จัดส่ง:</div>
            <div class="val"><?= nl2br(h($mem['address'] ?? '-')) ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
