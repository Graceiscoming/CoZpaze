<?php
include '../login_status/require_login.php';
include __DIR__ . '/../config/db_connect.php';


$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];

$sql = "SELECT user_name, user_mail, university_name, phone_num, Fullname FROM UserInfo WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_name, $user_mail, $university_name, $phone_num, $Fullname);
$stmt->fetch();
$stmt->close();

// เจ้าของสถานที่?
$sql_check_owner = "SELECT COUNT(*) FROM location_owners WHERE user_id = ?";
$stmt_check_owner = $conn->prepare($sql_check_owner);
$stmt_check_owner->bind_param("i", $user_id);
$stmt_check_owner->execute();
$stmt_check_owner->bind_result($is_owner);
$stmt_check_owner->fetch();
$stmt_check_owner->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <title>เช็คยอดเงิน</title>
  <link href="https://fonts.googleapis.com/css2?family=Prompt&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../style/wallet.css">

  <style>
    html,
    body {
      height: 100%;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Sarabun', sans-serif;
      margin: 0;
      background: linear-gradient(135deg, #FFE5EC, #E0F7FA);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }


    .sidebar {
      width: 220px;
      background-color: #fff;
      padding: 2rem 1.5rem;
      border-top-right-radius: 30px;
      border-bottom-right-radius: 30px;
      box-shadow: 5px 0 20px rgba(0, 0, 0, 0.05);
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      overflow-y: auto;
    }

    .sidebar h2 {
      color: #ec4899;
      margin-bottom: 2rem;
      font-size: 1.3rem;
    }

    .menu-item {
      margin: 1rem 0;
      font-size: 1rem;
      color: #666;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }


    .balance-card {
      background: white;
      padding: 2rem;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
      text-align: center;
      max-width: 400px;
      width: 90%;
      margin-left: 240px;
      position: relative;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }

    #checkBalanceBtn {
      padding: 0.8rem 2rem;
      background: rgba(236, 72, 153, 0.2);
      color: #ec4899;
      border: none;
      border-radius: 30px;
      font-size: 1rem;
      cursor: pointer;
      transition: background 0.3s, color 0.3s;
      margin-bottom: 1rem;
    }

    #checkBalanceBtn:hover {
      background: #ec4899;
      color: white;
    }

    #balanceDisplay {
      font-size: 1.5rem;
      font-weight: bold;
      color: #8b5cf6;
    }


    .back-button {
      position: fixed;
      top: 0;
      right: 0;
      padding: 10px 15px;
      background-color: rgba(236, 72, 153, 0.2);
      color: #ec4899;
      border-bottom-left-radius: 10px;
      text-decoration: none;
      font-weight: bold;
      z-index: 9999;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      transition: background-color 0.2s ease, color 0.2s ease;
    }

    .back-button:hover {
      background-color: #ec4899;
      color: white;
    }

    /* Mobile */
    @media (max-width: 768px) {
      .sidebar {
        width: 100%;
        padding: 1rem;
        box-shadow: none;
        position: sticky;
        top: 0;
        z-index: 1000;
        background-color: white;
        border-bottom: 1px solid #eee;
      }

      .sidebar h2 {
        display: none;
      }

      .menu-item {
        font-size: 0.9rem;
        margin: 0;
        padding: 0.5rem;
      }

      .balance-card {
        margin-left: 0;
        padding: 1.5rem;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      }

      #checkBalanceBtn {
        width: 100%;
      }
    }
  </style>
</head>

<body>


  <a href="<?php echo BASE_URL; ?>/index.php" class="back-button">กลับหน้าแรก</a>
  <div class="sidebar">
    <h2>Profile</h2>
    <a class="menu-item" href="<?php echo BASE_URL; ?>/final_use/user/user_profile.php"> ข้อมูลผู้ใช้</a>
    <a class="menu-item" href="<?php echo BASE_URL; ?>/final_use/userwallets/wallet.php"> กระเป๋าเงิน</a>
    <a class="menu-item" href="<?php echo BASE_URL; ?>/final_use/user/user_check_booking.php"> ประวัติการจองของฉัน</a>
    <a class="menu-item" href="<?php echo BASE_URL; ?>/final_use/tokenshop/transactions.php">
      ประวัติการเติมเงินของฉัน</a>
    <?php if ($is_owner > 0): ?>
      <a class="menu-item" href="../location_owner/index.php"> เมนูเจ้าของธุรกิจ</a>
    <?php endif; ?>
    <?php if ($role_id == 2): ?>
      <a class="menu-item" href="<?php echo BASE_URL; ?>/final_use/admin/admin_dashboard.php"> Admin Menu</a>
    <?php endif; ?>
  </div>

  <div class="balance-card">
    <h2> เช็คยอดเงินของคุณ</h2>
    <button id="checkBalanceBtn">เช็คยอดเงิน</button>
    <p>ยอดเงินของคุณ: <span id="balanceDisplay">---</span></p>
  </div>

  <script>
    document.getElementById("checkBalanceBtn").addEventListener("click", function () {
      fetch("check_balance.php")
        .then(response => response.json())
        .then(data => {
          if (data.error) {
            alert(data.error);
          } else {
            document.getElementById("balanceDisplay").textContent = data.balance + "";
          }
        })
        .catch(error => console.error("เกิดข้อผิดพลาด:", error));
    });
  </script>

</body>

</html>