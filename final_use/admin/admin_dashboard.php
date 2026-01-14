<?php
session_start();
include '../config/db_connect.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
  header("Location: " . BASE_URL . "/index.php");
  exit();
}

$user_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Menu</title>
  <link href="https://fonts.googleapis.com/css2?family=Prompt&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Prompt', sans-serif;
      background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
      color: #e0e0e0;
      min-height: 100vh;
      line-height: 1.6;
    }

    .container {
      max-width: 1000px;
      margin: 30px auto;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 20px;
      padding: 40px 30px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
      backdrop-filter: blur(10px);
    }

    h1 {
      font-size: 2.2rem;
      color: #d4af37;
      margin-bottom: 30px;
      text-align: center;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
      letter-spacing: 1px;
      font-weight: 600;
    }

    h2 {
      font-size: 1.4rem;
      color: #c0a36e;
      margin-top: 40px;
      margin-bottom: 20px;
      border-bottom: 1px solid rgba(212, 175, 55, 0.3);
      padding-bottom: 8px;
      letter-spacing: 0.5px;
      font-weight: 500;
    }

    .welcome {
      font-size: 1.2rem;
      margin-bottom: 30px;
      color: #d4af37;
      text-align: center;
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
      padding: 15px;
      background: rgba(212, 175, 55, 0.1);
      border-radius: 12px;
      border: 1px solid rgba(212, 175, 55, 0.2);
    }

    ul {
      list-style: none;
      padding-left: 0;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 15px;
    }

    li {
      margin: 0;
    }

    a {
      text-decoration: none;
      color: #d4af37;
      padding: 15px 20px;
      background: rgba(212, 175, 55, 0.1);
      border: 1px solid rgba(212, 175, 55, 0.3);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
      font-weight: 500;
      letter-spacing: 0.5px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      text-align: center;
      min-height: 60px;
    }

    a:hover {
      background: rgba(212, 175, 55, 0.2);
      color: #ffd700;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(212, 175, 55, 0.2);
      border-color: rgba(212, 175, 55, 0.5);
    }

    @media (max-width: 768px) {
      .container {
        margin: 20px 15px;
        padding: 30px 20px;
      }

      h1 {
        font-size: 1.8rem;
      }

      h2 {
        font-size: 1.2rem;
      }

      ul {
        grid-template-columns: 1fr;
      }

      a {
        padding: 12px 15px;
        min-height: 50px;
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="welcome">
      ยินดีต้อนรับผู้ดูแลระบบ! คุณ <?php echo htmlspecialchars($user_name); ?>
    </div>
    <h1>เมนูผู้ดูแลระบบ</h1>

    <h2>ระบบผู้ใช้</h2>
    <ul>
      <li><a href="<?php echo BASE_URL; ?>/final_use/admin/adminmoney.php">เพิ่มเงินให้ผู้ใช้</a></li>
      <li><a href="<?php echo BASE_URL; ?>/final_use/admin/admin_check_topup.php">เช็คการเติมเงิน</a></li>
    </ul>

    <h2>ระบบสถานที่</h2>
    <ul>
      <li><a href="<?php echo BASE_URL; ?>/final_use/admin/admin_check_location.php">Check ทุก locations</a></li>
      <li><a href="<?php echo BASE_URL; ?>/final_use/admin/admin_managelocation.php">แก้ location</a></li>
      <li><a href="<?php echo BASE_URL; ?>/final_use/location_set/add_location.php">เพิ่ม locations</a></li>
      <li><a href="<?php echo BASE_URL; ?>/final_use/admin/admin_bookings.php">เช็คการจองทุกคน</a></li>
      <li><a href="<?php echo BASE_URL; ?>/final_use/admin/admin_manage_owner.php">จัดการ Owner</a></li>
      <li><a href="<?php echo BASE_URL; ?>/final_use/admin/admin_keyword.php">เพิ่ม keyword</a></li>
      <li><a href="<?php echo BASE_URL; ?>/final_use/admin/admin_reviews.php">แก้รีวิว</a></li>
      <li><a href="<?php echo BASE_URL; ?>/final_use/admin/admin_check_location_wallets.php">เช็คยอดเงินของสถานที่ทั้งหมด</a></li>
    </ul>

    <h2>เมนูทั่วไป</h2>
    <ul>
      <li><a href="<?php echo BASE_URL; ?>/final_use/login_status/logout.php">ออกจากระบบ</a></li>
      <li><a href="<?php echo BASE_URL; ?>/index.php">ไปหน้าผู้ใช้ปกติ</a></li>
    </ul>
  </div>
</body>

</html>