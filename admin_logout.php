<?php
session_start();

// ล้าง session ทั้งหมด
session_unset();
session_destroy();

// กลับไปหน้า login ของแอดมิน
header("Location: admin_login.php");
exit;
