<?php
// register.php
session_start();
require_once "conn.php";

// ป้องกันการเข้าตรง
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: register_form.php");
  exit;
}

// รับค่าจากฟอร์ม
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$address  = trim($_POST['address'] ?? '');

// ตรวจสอบค่าว่าง
if ($username === '' || $password === '' || $name === '' || $email === '' || $phone === '') {
  header("Location: register_form.php?err=" . urlencode('กรุณากรอกข้อมูลให้ครบถ้วน'));
  exit;
}

// ตรวจสอบ username
if (!preg_match('/^[A-Za-z0-9_]{4,20}$/', $username)) {
  header("Location: register_form.php?err=" . urlencode('ชื่อผู้ใช้ต้องเป็นภาษาอังกฤษ/ตัวเลข 4-20 ตัว'));
  exit;
}

// ตรวจสอบความยาวรหัสผ่าน
if (strlen($password) < 6) {
  header("Location: register_form.php?err=" . urlencode('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร'));
  exit;
}

// ตรวจสอบอีเมล
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  header("Location: register_form.php?err=" . urlencode('อีเมลไม่ถูกต้อง'));
  exit;
}

// ตรวจสอบ username ซ้ำ
$check = $conn->prepare("SELECT id FROM members WHERE username=? LIMIT 1");
$check->bind_param("s", $username);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
  $check->close();
  header("Location: register_form.php?err=" . urlencode("ชื่อผู้ใช้ '$username' ถูกใช้แล้ว"));
  exit;
}
$check->close();

// ตรวจสอบ email ซ้ำ
$checkEmail = $conn->prepare("SELECT id FROM members WHERE email=? LIMIT 1");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$checkEmail->store_result();
if ($checkEmail->num_rows > 0) {
  $checkEmail->close();
  header("Location: register_form.php?err=" . urlencode("อีเมล '$email' ถูกใช้แล้ว"));
  exit;
}
$checkEmail->close();

// บันทึกลงฐานข้อมูล (ไม่ hash)
$stmt = $conn->prepare(
  "INSERT INTO members (username, password, name, email, phone, address)
   VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("ssssss", $username, $password, $name, $email, $phone, $address);

if ($stmt->execute()) {
  $stmt->close();
  $conn->close();
  header("Location: login_form.php?ok=1"); // เสร็จแล้วไปหน้า login
  exit;
} else {
  $err = $stmt->error;
  $stmt->close();
  $conn->close();
  header("Location: register_form.php?err=" . urlencode("เกิดข้อผิดพลาด: $err"));
  exit;
}

