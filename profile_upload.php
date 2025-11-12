<?php
// profile_upload.php — รับอัปโหลดรูปโปรไฟล์
session_start();
require_once "conn.php";

if (!isset($_SESSION['sess_username'])) {
  header("Location: login_form.php"); exit;
}

$username = $_SESSION['sess_username'];
if (!isset($_POST['username']) || $_POST['username'] !== $username) {
  header("Location: show_profile.php"); exit;
}

// ตรวจ DB column
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
$avatarCol = null;
if (has_column($conn,'members','avatar'))        $avatarCol = 'avatar';
elseif (has_column($conn,'members','profile_image')) $avatarCol = 'profile_image';

// ตรวจไฟล์
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
  header("Location: show_profile.php"); exit;
}

$uploadDir = "uploads/avatars/";
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0777, true);

$info = getimagesize($_FILES['avatar']['tmp_name']);
if ($info === false) { header("Location: show_profile.php"); exit; }

// จำกัดนามสกุล
$extMap = [
  IMAGETYPE_JPEG => 'jpg',
  IMAGETYPE_PNG  => 'png',
  IMAGETYPE_WEBP => 'webp',
];
$ext = $extMap[$info[2]] ?? null;
if (!$ext) { header("Location: show_profile.php"); exit; }

// จำกัดขนาด (~ 2MB)
if ($_FILES['avatar']['size'] > 2*1024*1024) {
  header("Location: show_profile.php"); exit;
}

// ชื่อไฟล์ปลายทาง
$base = "profile_{$username}.".$ext;
$dest = $uploadDir.$base;

// ลบไฟล์เก่าที่ต่างนามสกุล
foreach (['jpg','jpeg','png','webp'] as $e) {
  $old = $uploadDir."profile_{$username}.".$e;
  if (is_file($old) && $old !== $dest) @unlink($old);
}

// ย้ายไฟล์
if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
  header("Location: show_profile.php"); exit;
}

// อัปเดต DB ถ้ามีคอลัมน์
if ($avatarCol) {
  $st = $conn->prepare("UPDATE members SET $avatarCol=? WHERE username=? LIMIT 1");
  $st->bind_param("ss", $base, $username);
  $st->execute();
}

header("Location: show_profile.php");
exit;
    