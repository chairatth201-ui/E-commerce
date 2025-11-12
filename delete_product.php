<?php
// delete_product.php
session_start();
require_once "conn.php";

// ต้องเป็นแอดมิน
if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit;
}

// ตรวจ CSRF แบบง่าย
if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
  header("Location: show_allProduct.php?msg=" . urlencode("คำขอไม่ถูกต้อง (CSRF)"));
  exit;
}

$pid = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
if ($pid <= 0) {
  header("Location: show_allProduct.php?msg=" . urlencode("ไม่พบรหัสสินค้า"));
  exit;
}

// ดึงชื่อไฟล์ภาพก่อนเผื่อลบไฟล์
$stmt = $conn->prepare("SELECT image FROM product WHERE product_id=? LIMIT 1");
$stmt->bind_param("i", $pid);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
  header("Location: show_allProduct.php?msg=" . urlencode("ไม่พบสินค้าในระบบ"));
  exit;
}
$row = $res->fetch_assoc();
$image = trim((string)$row['image']);
$stmt->close();

// ลบสินค้า
$stmt = $conn->prepare("DELETE FROM product WHERE product_id=? LIMIT 1");
$stmt->bind_param("i", $pid);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
  // ลบไฟล์ภาพถ้ามี
  if ($image !== '') {
    $path = __DIR__ . "/uploads/" . $image;
    if (is_file($path)) @unlink($path);
  }
  header("Location: show_allProduct.php?msg=" . urlencode("ลบสินค้าสำเร็จ"));
} else {
  header("Location: show_allProduct.php?msg=" . urlencode("เกิดข้อผิดพลาดในการลบสินค้า"));
}
exit;
