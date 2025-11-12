<?php
    $hostname = "localhost";
    $database = "gunplamaster";
    $username = "root"; // เปลี่ยนตามชื่อผู้ใช้ MySQL ของคุณ
    $password = "";     // ใส่รหัสผ่านของ MySQL หากมี

// สร้างการเชื่อมต่อ
    $conn = new mysqli($hostname, $username, $password, $database);

// ตรวจสอบการเชื่อมต่อ
    if ($conn->connect_error) {
        die("เชื่อมต่อฐานข้อมูลไม่สำเร็จ: " . $conn->connect_error);
}

//echo "เชื่อมต่อฐานข้อมูลสำเร็จ!";
?>
