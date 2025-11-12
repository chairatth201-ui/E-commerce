<?php
// login.php
session_start();
require_once "conn.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: login_form.php");
  exit;
}

$username = trim($_POST['username'] ?? '');
$password = (string)($_POST['password'] ?? '');

if ($username === '' || $password === '') {
  header("Location: login_form.php?err=1");
  exit;
}

try {
  // หา user จากตาราง members
  $sql = "SELECT id, username, password, name FROM members WHERE username = ? LIMIT 1";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $res = $stmt->get_result();

  if (!$res || $res->num_rows === 0) {
    // ไม่พบบัญชี
    header("Location: login_form.php?err=2");
    exit;
  }

  $user = $res->fetch_assoc();

  // ตรวจรหัสผ่าน (password เป็น hash)
  if (!password_verify($password, $user['password'])) {
    header("Location: login_form.php?err=2");
    exit;
  }

  // ผ่านแล้ว -> ตั้ง session
  $_SESSION['user_id']  = (int)$user['id'];
  $_SESSION['username'] = $user['username'];
  $_SESSION['name']     = $user['name'];

  // ไปหน้าแรกผู้ใช้
  $redirect = "product_list.php";   // ถ้าต้องการไปหน้าอื่นให้ปรับตรงนี้
  header("Location: $redirect");
  exit;

} catch (Throwable $e) {
  // เกิดข้อผิดพลาด
  header("Location: login_form.php?err=3");
  exit;
}
