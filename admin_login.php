<?php
session_start();
require_once "conn.php";

// ถ้าล็อกอินแล้ว -> ไปหน้า admin_home
if (isset($_SESSION['admin'])) {
  header("Location: admin_home.php");
  exit;
}

/*
 * ตั้งค่าผู้ดูแลแบบตายตัว (สำหรับทดสอบ / local)
 * หากต้องการเก็บรหัสแบบปลอดภัย ให้เปลี่ยนเป็น hash และใช้ password_verify
 */
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'admin'); // **เฉพาะ dev/local เท่านั้น** — ห้ามใช้ใน production

$error = '';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');

  // 1) ตรวจสอบกับค่าตายตัวก่อน (fixed credential)
  if ($username === ADMIN_USER && $password === ADMIN_PASS) {
    $_SESSION['admin'] = ADMIN_USER;
    header("Location: admin_home.php");
    exit;
  }

  // 2) ถ้าไม่ตรงค่าตายตัว ให้ fallback ตรวจสอบจากฐานข้อมูล (ถ้ามีตาราง admin)
  //    ถ้าไม่ต้องการ fallback ส่วนนี้ ให้เอาออกได้
  $stmt = $conn->prepare("SELECT username, password FROM admin WHERE username = ? LIMIT 1");
  if ($stmt) {
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && ($row = $res->fetch_assoc())) {
      // สมมติใน DB เก็บ hashed password (password_hash)
      if (password_verify($password, $row['password'])) {
        $_SESSION['admin'] = $row['username'];
        header("Location: admin_home.php");
        exit;
      } else {
        $error = "รหัสผ่านไม่ถูกต้อง";
      }
    } else {
      // ไม่พบชื่อผู้ใช้ใน DB (และไม่ใช่ fixed account)
      $error = "ไม่พบชื่อผู้ใช้นี้";
    }
  } else {
    // ถ้าไม่มีตาราง admin, ให้แจ้งว่าไม่พบผู้ใช้ (หรือเอาข้อความอื่นก็ได้)
    $error = "เกิดข้อผิดพลาด (ตาราง admin อาจไม่มีในฐานข้อมูล)";
  }
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>Admin Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
/* (ใส่สไตล์เดียวกับที่คุณใช้ก่อนหน้า — ย่อไว้เพื่อความสั้น) */
:root{ --primary:#2d8cf0; --border:rgba(255,255,255,.2) }
body{ margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,"TH Sarabun New",sans-serif;
  min-height:100vh; display:grid; place-items:center;
  background:linear-gradient(180deg,#101820,#142436); color:#eef6ff; padding:20px; }
.card{ width:min(92vw,540px); padding:28px; border-radius:18px; background:rgba(255,255,255,.05);
  border:1px solid var(--border); box-shadow: 0 18px 38px rgba(0,0,0,.35); }
h1{ margin:0 0 16px; font-size:1.5rem; text-align:center }
label{ display:block; margin:8px 0 6px; color:#cfe3ff; font-weight:700 }
input{ width:100%; padding:12px 14px; border-radius:10px; border:1px solid var(--border);
  background:rgba(255,255,255,.08); color:#fff; outline:none; }
button{ margin-top:12px; width:100%; padding:12px; border-radius:10px; background:var(--primary);
  color:#fff; border:0; font-weight:800; cursor:pointer }
.error{ background: rgba(255,77,77,.12); color:#ffd4d4; padding:10px; border-radius:8px; margin-bottom:10px; text-align:center }
</style>
</head>
<body>
  <div class="card">
    <h1>🔑 Admin Login</h1>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="">
      <label for="username">ชื่อผู้ใช้</label>
      <input id="username" name="username" type="text" required autofocus>

      <label for="password">รหัสผ่าน</label>
      <input id="password" name="password" type="password" required>

      <button type="submit">เข้าสู่ระบบ</button>
    </form>
  </div>
</body>
</html>
