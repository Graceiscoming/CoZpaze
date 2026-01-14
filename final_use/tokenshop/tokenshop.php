<?php
include '../login_status/require_login.php';
include '../config/db_connect.php';


if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];

  $stmt = $conn->prepare("SELECT user_name FROM userinfo WHERE user_id = ?");
  $stmt->bind_param("s", $user_id);
  $stmt->execute();
  $result_user = $stmt->get_result();

  if ($result_user && $result_user->num_rows > 0) {
    $user_data = $result_user->fetch_assoc();
    $user_name = $user_data['user_name'];
  } else {
    $user_name = "ไม่พบข้อมูลผู้ใช้";
  }

  $stmt->close();


  $sql_wallet = "SELECT balance FROM wallets WHERE user_id = '$user_id'";
  $result_wallet = mysqli_query($conn, $sql_wallet);

  if ($result_wallet && mysqli_num_rows($result_wallet) > 0) {
    $wallet_data = mysqli_fetch_assoc($result_wallet);
    $balance = $wallet_data['balance'];
  } else {
    $balance = 0;
  }
} else {
  $user_name = "ไม่ได้ login";
  $balance = "-";
}

?>

<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>เติมโทเคน</title>
  <link href="https://fonts.googleapis.com/css2?family=Prompt&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/final_use/style/token.css">
  <style>
    .check-status-btn {
      background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
      color: white;
      padding: 12px 24px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3);
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
      border: none;
      cursor: pointer;
      margin-left: auto;
    }

    .check-status-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(236, 72, 153, 0.4);
    }

    .token-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    @media (max-width: 768px) {
      .token-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
      }

      .check-status-btn {
        width: 100%;
        margin-left: 0;
      }
    }

    .custom-token {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
    }

    .custom-input-container {
      display: flex;
      flex-direction: column;
      gap: 8px;
      width: 100%;
      max-width: 200px;
    }

    #customAmount {
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
      text-align: center;
    }

    .custom-pay-btn {
      background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      transition: all 0.3s ease;
      font-weight: 600;
    }

    .custom-pay-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3);
    }

    .button-box {
      position: fixed;
      top: 100px;
      right: 30px;
      z-index: 1000;
    }

    @media (max-width: 768px) {
      .button-box {
        top: 80px;
        right: 15px;
      }

      .check-status-btn {
        padding: 10px 20px;
        font-size: 0.9rem;
      }
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav id="navbar"
    class="fixed top-0 left-0 w-full z-50 flex justify-between items-center p-4 shadow bg-white transition-transform duration-300">
    <div class="text-xl font-bold">CoZpaze</div>

    <button class="md:hidden text-xl" onclick="toggleMobileMenu()">☰</button>

    <!-- Desktop menu -->
    <div class="space-x-1.5 hidden md:flex items-center">
      <span>🔹 ผู้ใช้: <?php echo $user_name; ?></span>
      <a href="<?php echo BASE_URL; ?>/index.php" class="block px-2 py-1 hover:underline">หน้าแรก</a>
      <a href="<?php echo BASE_URL; ?>/final_use/uniselect.php"
        class="block px-2 py-1 hover:underline">หน้าเลือกมหาลัย</a>
      <a href="<?php echo BASE_URL; ?>/final_use/tokenshop/tokenshop.php"
        class="block px-2 py-1 hover:underline">เติมโทเคน</a>
      <a href="<?php echo BASE_URL; ?>/final_use/booking/cart.php"
        class="block px-2 py-1 hover:underline">ตระกร้าของฉัน</a>
      <a href="<?php echo BASE_URL; ?>/final_use/user/user_check_booking.php"
        class="block px-2 py-1 hover:underline">การจองของฉัน</a>
      <a href="<?php echo BASE_URL; ?>/final_use/user/user_profile.php"
        class="block px-2 py-1 hover:underline">หน้าของฉัน</a>
      <span
        class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-800 text-sm font-semibold px-3 py-1 rounded-full shadow-sm">
        🪙 <span><?php echo $balance; ?></span> Coin
      </span>
    </div>

    <div class="hidden md:flex space-x-2">
      <?php if (!isset($_SESSION['user_id'])): ?>
        <button onclick="window.location.href='<?php echo BASE_URL; ?>/final_use/user/user_login.php'"
          class="py-2.5 px-5 text-sm font-medium border rounded-full hover:bg-gray-100">เข้าสู่ระบบ</button>
        <button onclick="window.location.href='<?php echo BASE_URL; ?>/final_use/user/user_reg.php'"
          class="text-white bg-blue-700 hover:bg-blue-800 rounded-full text-sm px-5 py-2.5">ลงชื่อเข้าใช้</button>
      <?php else: ?>
        <button onclick="window.location.href='<?php echo BASE_URL; ?>/final_use/login_status/logout.php'"
          class="py-2.5 px-5 text-sm font-medium border rounded-full hover:bg-gray-100">Logout</button>
      <?php endif; ?>
    </div>
  </nav>

  <!-- Mobile menu -->
  <div id="mobileMenu" class="md:hidden hidden fixed top-16 left-0 w-full bg-white z-40 border-t p-4 text-sm space-y-2">
    <div class="mb-2 text-gray-700">🔹 ผู้ใช้: <?php echo $user_name; ?></div>
    <a href="<?php echo BASE_URL; ?>/index.php" class="block px-2 py-1 hover:underline">หน้าแรก</a>
    <a href="<?php echo BASE_URL; ?>/final_use/uniselect.php"
      class="block px-2 py-1 hover:underline">หน้าเลือกมหาลัย</a>
    <a href="<?php echo BASE_URL; ?>/final_use/tokenshop/tokenshop.php"
      class="block px-2 py-1 hover:underline">เติมโทเคน</a>
    <a href="<?php echo BASE_URL; ?>/final_use/booking/cart.php"
      class="block px-2 py-1 hover:underline">ตระกร้าของฉัน</a>
    <a href="<?php echo BASE_URL; ?>/final_use/user/user_check_booking.php"
      class="block px-2 py-1 hover:underline">การจองของฉัน</a>
    <a href="<?php echo BASE_URL; ?>/final_use/user/user_profile.php"
      class="block px-2 py-1 hover:underline">หน้าของฉัน</a>
    <div class="text-sm bg-yellow-100 text-yellow-800 font-semibold px-3 py-1 rounded-full inline-block">
      🪙 <?php echo $balance; ?> Coin
    </div>
    <?php if (!isset($_SESSION['user_id'])): ?>
      <button onclick="window.location.href='<?php echo BASE_URL; ?>/final_use/user/user_login.php'"
        class="w-full py-2 mt-3 border rounded-full text-gray-700 hover:bg-gray-100">เข้าสู่ระบบ</button>
      <button onclick="window.location.href='<?php echo BASE_URL; ?>/final_use/user/user_reg.php'"
        class="w-full py-2 bg-blue-600 text-white rounded-full mt-1">ลงชื่อเข้าใช้</button>
    <?php else: ?>
      <button onclick="window.location.href='<?php echo BASE_URL; ?>/login_status/logout.php'"
        class="w-full py-2 mt-3 border rounded-full text-gray-700 hover:bg-gray-100">Logout</button>
    <?php endif; ?>
  </div>

  </nav>

  <div class="container">
    <div class="token-header">
      <h1>เลือกแพ็กเกจโทเคน</h1>
      <button onclick="window.location.href='<?php echo BASE_URL; ?>/final_use/tokenshop/transactions.php'"
        class="check-status-btn">
        <i class="fas fa-history"></i>
        ตรวจสอบสถานะการเติมเงิน
      </button>
    </div>
    <div class="token-container">
      <div class="token-item" onclick="goToPayment(1, '100')">
        <img src="<?php echo BASE_URL; ?>/final_use/tokenshop/img/coin.png" alt="token">
        <p>ราคา: 100 บาท</p>
      </div>
      <div class="token-item" onclick="goToPayment(2, '250')">
        <img src="<?php echo BASE_URL; ?>/final_use/tokenshop/img/coin2.png" alt="token">
        <p>ราคา: 250 บาท</p>
      </div>
      <div class="token-item" onclick="goToPayment(3, '500')">
        <img src="<?php echo BASE_URL; ?>/final_use/tokenshop/img/coin3.png" alt="token">
        <p>ราคา: 500 บาท</p>
      </div>
      <div class="token-item" onclick="goToPayment(4, '1000')">
        <img src="<?php echo BASE_URL; ?>/final_use/tokenshop/img/coin4.png" alt="token">
        <p>ราคา: 1000 บาท</p>
      </div>
      <div class="token-item custom-token">
        <img src="<?php echo BASE_URL; ?>/final_use/tokenshop/img/coin.png" alt="token">
        <div class="custom-input-container">
          <input type="number" id="customAmount" min="10" max="10000" placeholder="จำนวนเหรียญ (10-10000)"
            oninput="limitInput(this)">
          <button onclick="goToCustomPayment()" class="custom-pay-btn">เติมเหรียญ</button>
        </div>
      </div>
    </div>
  </div>

  </div>

  <script>
    function limitInput(input) {
      if (input.value > 10000) {
        input.value = 10000;
      }
    }

    function goToPayment(tokenId, price) {
      window.location.href = `payment.php?price=${price}`;
    }

    function goToCustomPayment() {
      const customAmount = document.getElementById('customAmount').value;
      if (customAmount >= 10 && customAmount <= 10000) {
        window.location.href = `payment.php?price=${customAmount}`;
      } else {
        alert('กรุณากรอกจำนวนเหรียญระหว่าง 10-10000');
      }
    }

    let lastScrollTop = 0;
    const navbar = document.getElementById('navbar');

    window.addEventListener('scroll', function () {
      const currentScroll = window.pageYOffset || document.documentElement.scrollTop;

      if (currentScroll > lastScrollTop) {

        navbar.style.transform = 'translateY(-100%)';
      } else {

        navbar.style.transform = 'translateY(0)';
      }

      lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
    }, false);

    function toggleMobileMenu() {
      const menu = document.getElementById("mobileMenu");
      if (menu) {
        menu.classList.toggle("hidden");
      }
    }
  </script>
</body>

</html>