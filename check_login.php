<?php
// check_login.php
session_start();
require_once "conn.php";

// --------- รับค่าจากฟอร์ม ----------
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    header("Location: login_form.php?err=empty");
    exit;
}

// --------- ตรวจสอบผู้ใช้จากตาราง members ----------
// หมายเหตุ: ถ้ารหัสผ่านเก็บแบบ "ตัวอักษรตรงๆ" ใช้บล็อก A
// ถ้าเก็บแบบ hash (เช่น PASSWORD_BCRYPT) ให้ใช้บล็อก B

/* --------------- บล็อก A: รหัสผ่านเก็บตรง ๆ (ตามของเดิม) --------------- */
$sql = "SELECT id, username, password, name, email, address 
        FROM members 
        WHERE username = ? AND password = ?
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$res = $stmt->get_result();
$user = $res && $res->num_rows ? $res->fetch_assoc() : null;
$stmt->close();

/* --------------- บล็อก B (ตัวเลือก): ถ้ารหัสผ่านเป็นแฮช -------------------
   เปิดใช้แทนบล็อก A โดยคอมเมนต์บล็อก A ข้างบน และยกเลิกคอมเมนต์ส่วนนี้

$sql = "SELECT id, username, password, name, email, address 
        FROM members 
        WHERE username = ?
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();
$row = $res && $res->num_rows ? $res->fetch_assoc() : null;
$stmt->close();

$user = null;
if ($row && password_verify($password, $row['password'])) {
    $user = $row;
}
-------------------------------------------------------------------------- */

if (!$user) {
    header("Location: login_form.php?err=invalid");
    exit;
}

// --------- ตั้งค่าเซสชันให้เข้ากับระบบเดิม ----------
$_SESSION['sess_username'] = $user['username'];
$_SESSION['sess_id']       = (int)($user['id'] ?? 0);

// --------- ซิงก์เข้าตาราง customer ให้ checkout / order ใช้งานได้ ----------
$cusId = null;

// หาใน customer ก่อน
$sql = "SELECT ID FROM customer WHERE username = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user['username']);
$stmt->execute();
$r = $stmt->get_result();
if ($r && $r->num_rows > 0) {
    // มีแล้ว → update ข้อมูลเผื่อปรับปรุง (ไม่แตะ password)
    $rowCus = $r->fetch_assoc();
    $cusId  = (int)$rowCus['ID'];
    $stmt->close();

    $sqlU = "UPDATE customer
             SET name = ?, email = ?, address = ?, Mobile_Phone = COALESCE(Mobile_Phone, '')
             WHERE ID = ?";
    $stmtU = $conn->prepare($sqlU);
    $stmtU->bind_param("sssi",
        $user['name'], $user['email'], $user['address'], $cusId
    );
    $stmtU->execute();
    $stmtU->close();

} else {
    // ยังไม่มี → insert ใหม่
    $stmt->close();

    // ถ้ารหัสผ่านคุณเก็บเป็นแฮชใน members และอยากให้ customer เป็นแฮชด้วย
    // ให้เอาค่า $user['password'] ที่เป็นแฮชมาใช้ได้เลย
    $mobile = ''; // ไม่มีใน members เลยให้ค่าว่างไว้ก่อน
    $sqlI = "INSERT INTO customer (username, password, name, email, Mobile_Phone, address)
             VALUES (?, ?, ?, ?, ?, ?)";
    $stmtI = $conn->prepare($sqlI);
    $stmtI->bind_param("ssssss",
        $user['username'], $user['password'], $user['name'], $user['email'], $mobile, $user['address']
    );
    $stmtI->execute();
    $cusId = (int)$stmtI->insert_id;
    $stmtI->close();
}

// เก็บ customer_id เพิ่มไว้เผื่อส่วนอื่นเรียกใช้
$_SESSION['customer_id'] = $cusId;

// --------- ล็อกอินสำเร็จ → ไปหน้าโฮมลูกค้า ----------
header("Location: customer_home.php");
exit;
