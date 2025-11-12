<?php
require_once "conn.php";

// ต้องมาจาก POST เท่านั้น
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: addProduct_form.php");
  exit;
}

// รับค่าจากฟอร์ม
$product_name = trim($_POST['product_name'] ?? '');
$category     = trim($_POST['category'] ?? '');
$price        = $_POST['price'] ?? '';
$details      = trim($_POST['details'] ?? '');
$image_name   = "";

// validate เบื้องต้น
if ($product_name === '' || $price === '' || !is_numeric($price)) {
  header("Location: addProduct_form.php?msg=ข้อมูลไม่ถูกต้อง");
  exit;
}

// อัปโหลดรูป (ถ้ามี)
if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
  $upload_dir = __DIR__ . "/uploads/";
  if (!is_dir($upload_dir)) { @mkdir($upload_dir, 0777, true); }

  $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
  $safe = preg_replace('/[^a-z0-9\-]/i', '_', pathinfo($_FILES['image']['name'], PATHINFO_FILENAME));
  $image_name = date('Ymd_His') . '_' . substr(sha1($safe.mt_rand()),0,8) . ($ext ? ".$ext" : '');

  if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name)) {
    header("Location: addProduct_form.php?msg=อัปโหลดรูปไม่สำเร็จ");
    exit;
  }
}

// ตรวจว่าตารางมีคอลัมน์ category ไหม
$hasCategory = false;
$chk = $conn->query("SHOW COLUMNS FROM product LIKE 'category'");
if ($chk && $chk->num_rows > 0) { $hasCategory = true; }

// สร้าง/รันคำสั่ง INSERT
if ($hasCategory) {
  $sql = "INSERT INTO product (product_name, category, price, details, image)
          VALUES (?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssdss", $product_name, $category, $price, $details, $image_name);
} else {
  $sql = "INSERT INTO product (product_name, price, details, image)
          VALUES (?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sdss", $product_name, $price, $details, $image_name);
}

if (!$stmt->execute()) {
  // แสดง error ชัดๆ (ช่วงดีบัก) — ผลงานจริงควร log แล้ว redirect พร้อม msg
  die("บันทึกข้อมูลไม่สำเร็จ: " . $stmt->error);
}

$newId = $conn->insert_id;
$stmt->close();
$conn->close();

// กลับไปหน้ารายการ หรือจะเปลี่ยนไปหน้าแสดงรายละเอียดก็ได้
header("Location: product_list.php?msg=เพิ่มสินค้าเรียบร้อย&id=".$newId);
exit;



