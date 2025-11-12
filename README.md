# E-commerce
เว็บไซต์ E-Commerce

<?php
session_start();
if (empty($_SESSION['admin'])) {  // กันเข้าตรงถ้ายังไม่ล็อกอินแอดมิน
  header("Location: admin_login.php");
  exit;
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>เพิ่มสินค้า - Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  :root{
    --glass: rgba(255,255,255,.08);
    --border: rgba(255,255,255,.25);
    --primary:#2196f3;
    --primary-2:#1976d2;
  }
  *{box-sizing:border-box}
  body{
    margin:0; color:#0b2c4a;
    font-family:system-ui,-apple-system,"Segoe UI",Roboto,"TH Sarabun New",sans-serif;
    background:
      linear-gradient(180deg, rgba(14,30,56,.65), rgba(12,26,50,.65)),
      url('https://i.pinimg.com/originals/c6/f6/21/c6f621e557d40dceaf794b60e960a67d.gif') no-repeat center / cover fixed;
    min-height:100vh;
  }

  /* ========== NAVBAR ========== */
  .c-nav{
    position:fixed; top:0; left:0; right:0; height:70px; z-index:1000;
    display:flex; align-items:center; padding:0 20px;
    background:rgba(10,25,50,.85); backdrop-filter:blur(6px);
    border-bottom:1px solid var(--border);
  }
  .c-brand{
    font-size:22px; font-weight:800; color:#fff; text-decoration:none;
  }
  .c-menu{ margin-left:auto; display:flex; gap:12px; }
  .c-btn{
    padding:10px 16px; border-radius:10px; text-decoration:none;
    font-weight:700; color:#fff; border:1px solid var(--border);
    background:rgba(255,255,255,.12);
    transition:.2s background,.2s transform;
  }
  .c-btn:hover{ background:rgba(255,255,255,.25); transform:translateY(-2px); }
  .c-btn.primary{ background:var(--primary); border:0; }
  .c-btn.primary:hover{ background:var(--primary-2); }

  .page-wrap{ padding-top:90px; }
  .wrap{ max-width:620px; margin:0 auto; padding:0 16px 60px; }
  .card{
    background:#fff; border:1px solid #e9f2ff; border-radius:18px;
    box-shadow:0 16px 34px rgba(0,0,0,.18); overflow:hidden;
  }
  .head{
    padding:20px; background:linear-gradient(180deg,#f4f9ff,#eef6ff);
    border-bottom:1px solid #eaf3ff; color:#0f4fa0;
    font-weight:900; font-size:1.5rem; text-align:center;
  }
  .body{ padding:22px; }
  label{ font-weight:700; display:block; margin:10px 0 6px; }
  input[type="text"], input[type="number"], select, textarea, input[type="file"]{
    width:100%; padding:11px 12px; border:1px solid #cfd7e6; border-radius:10px;
    font-size:1rem; background:#fff;
  }
  textarea{ min-height:100px; resize:vertical; }
  .actions{ margin-top:18px; }
  .btn{
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    width:100%; padding:12px 16px; border-radius:12px; border:0; cursor:pointer;
    background:var(--primary); color:#fff; font-weight:800;
    box-shadow:0 12px 24px rgba(33,150,243,.25);
    transition:.15s background,.15s transform;
  }
  .btn:hover{ background:var(--primary-2); transform:translateY(-1px); }
</style>
</head>
<body>

<!-- ✅ Navbar Admin -->
<header class="c-nav">
  <a href="admin_home.php" class="c-brand">🔧 Gunpla Admin</a>
  <nav class="c-menu">
    <a class="c-btn" href="admin_home.php">หน้าหลัก</a>
    <a class="c-btn active" href="addProduct_form.php">เพิ่มสินค้า</a>
    <a class="c-btn" href="show_allProduct.php">สินค้า</a>
    <a class="c-btn" href="orders_admin.php">ดูข้อมูลการสั่งซื้อ</a>
    <a class="c-btn" href="showmember.php">ดูข้อมูลสมาชิก</a>
    <a class="c-btn primary" href="logout.php">ออกจากระบบ</a>
  </nav>
</header>

<div class="page-wrap">
  <div class="wrap">
    <article class="card">
      <div class="head">เพิ่มข้อมูลสินค้า</div>
      <div class="body">
        <form action="addProduct_save.php" method="post" enctype="multipart/form-data">
          <label for="pname">ชื่อสินค้า</label>
          <input id="pname" type="text" name="product_name" required>

          <label for="cate">หมวดหมู่</label>
          <select id="cate" name="category" required>
            <option value="">-- กรุณาเลือก --</option>
            <option value="HG 1/144">HG 1/144</option>
            <option value="RG 1/144">RG 1/144</option>
            <option value="MG 1/100">MG 1/100</option>
            <option value="PG 1/60">PG 1/60</option>
          </select>

          <label for="price">ราคา (บาท)</label>
          <input id="price" type="number" name="price" min="0" step="0.01" required>

          <label for="details">รายละเอียดเพิ่มเติม</label>
          <textarea id="details" name="details"></textarea>

          <label for="img">อัปโหลดรูปภาพสินค้า</label>
          <input id="img" type="file" name="image" accept="image/*">

          <div class="actions">
            <button type="submit" class="btn">บันทึกข้อมูล</button>
          </div>
        </form>
      </div>
    </article>
  </div>
</div>
</body>
</html>


