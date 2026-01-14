<?php
session_start();
include __DIR__ . '/config/db_connect.php';


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
  <title>เลือกมหาวิทยาลัย</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
</head>


<body class="bg-white text-gray-800">
  <style>
    body {
      background: linear-gradient(120deg, #e0f7fa, #fce4ec);
      font-family: 'Prompt', sans-serif;

    }
  </style>

  <!-- Navbar -->
  <nav id="navbar"
    class="fixed top-0 left-0 w-full z-50 bg-white shadow p-4 flex justify-between items-center transition-transform duration-300">
    <div class="text-xl font-bold">CoZpaze</div>
    <button class="md:hidden text-xl ml-auto" onclick="toggleMobileMenu()">☰</button>
    <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">

      <!-- Menu section -->
      <div id="menuContent"
        class="hidden md:flex flex-col md:flex-row md:items-center md:space-x-3 absolute md:static top-full left-0 w-full md:w-auto bg-white border-t md:border-0 p-4 md:p-0 z-40">
        <div class="text-sm text-gray-700 mb-2 md:mb-0">🔹 ผู้ใช้: <?php echo $user_name; ?></div>

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

        <?php if (isset($_SESSION['user_id'])): ?>
          <span
            class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-800 text-sm font-semibold px-3 py-1 rounded-full shadow-sm my-2 md:my-0">
            🪙 <span><?php echo $balance; ?></span> Coin
          </span>
        <?php endif; ?>

        <div class="flex flex-col md:flex-row gap-2 mt-3 md:mt-0">
          <?php if (!isset($_SESSION['user_id'])): ?>
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/final_use/user/user_login.php'"
              class="py-2 px-4 border rounded-full text-sm text-gray-700 hover:bg-gray-100">เข้าสู่ระบบ</button>
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/final_use/user/user_reg.php'"
              class="py-2 px-4 text-sm bg-blue-700 text-white rounded-full hover:bg-blue-800">ลงชื่อเข้าใช้</button>
          <?php else: ?>
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/final_use/login_status/logout.php'"
              class="py-2 px-4 border rounded-full text-sm text-gray-700 hover:bg-gray-100">Logout</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

  <div class="min-h-screen py-10 px-6 pt-16">
    <h2 class="text-3xl font-bold text-center text-gray-800 mb-10 mt-16">เลือกจากมหาวิทยาลัยใกล้คุณ</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

      <!-- Card -->
      <a href="<?php echo BASE_URL; ?>/index.php?keyword=swu"
        class="bg-[#FFE5EC] rounded-2xl p-6 flex flex-col items-center shadow-md hover:shadow-xl transition-all duration-300 uni-card">
        <img src="<?php echo BASE_URL; ?>/final_use/uni/swu.png" alt="SWU" class="w-20 h-20 object-contain mb-4">
        <div class="text-base font-semibold text-center text-gray-800">มหาวิทยาลัยศรีนครินทรวิโรฒ</div>
        <div class="text-sm text-gray-500 mt-1">SWU</div>
      </a>


      <a href="<?php echo BASE_URL; ?>/index.php?keyword=cu"
        class="bg-[#FFE5EC] rounded-2xl p-6 flex flex-col items-center shadow-md hover:shadow-xl transition-all duration-300 uni-card">
        <img src="<?php echo BASE_URL; ?>/final_use/uni/cu.png" alt="CU" class="w-20 h-20 object-contain mb-4">
        <div class="text-base font-semibold text-center text-gray-800">จุฬาลงกรณ์มหาวิทยาลัย</div>
        <div class="text-sm text-gray-500 mt-1">CU</div>
      </a>

      <a href="<?php echo BASE_URL; ?>/index.php?keyword=tu"
        class="bg-[#FFE5EC] rounded-2xl p-6 flex flex-col items-center shadow-md hover:shadow-xl transition-all duration-300 uni-card">
        <img src="<?php echo BASE_URL; ?>/final_use/uni/tu.png" alt="TU" class="w-20 h-20 object-contain mb-4">
        <div class="text-base font-semibold text-center text-gray-800">มหาวิทยาลัยธรรมศาสตร์</div>
        <div class="text-sm text-gray-500 mt-1">TU</div>
      </a>

      <a href="<?php echo BASE_URL; ?>/index.php?keyword=ku"
        class="bg-[#FFE5EC] rounded-2xl p-6 flex flex-col items-center shadow-md hover:shadow-xl transition-all duration-300 uni-card">
        <img src="<?php echo BASE_URL; ?>/final_use/uni/ku.png" alt="KU" class="w-20 h-20 object-contain mb-4">
        <div class="text-base font-semibold text-center text-gray-800">มหาวิทยาลัยเกษตรศาสตร์ บางเขน</div>
        <div class="text-sm text-gray-500 mt-1">KU</div>
      </a>


      <a href="<?php echo BASE_URL; ?>/index.php?keyword=mu"
        class="bg-[#FFE5EC] rounded-2xl p-6 flex flex-col items-center shadow-md hover:shadow-xl transition-all duration-300 uni-card">
        <img src="<?php echo BASE_URL; ?>/final_use/uni/mu.png" alt="MU" class="w-20 h-20 object-contain mb-4">
        <div class="text-base font-semibold text-center text-gray-800">มหาวิทยาลัยมหิดล</div>
        <div class="text-sm text-gray-500 mt-1">MU</div>
      </a>

      <a href="<?php echo BASE_URL; ?>/index.php?keyword=kmutt"
        class="bg-[#FFE5EC] rounded-2xl p-6 flex flex-col items-center shadow-md hover:shadow-xl transition-all duration-300 uni-card">
        <img src="<?php echo BASE_URL; ?>/final_use/uni/kmutt.png" alt="kmutt" class="w-20 h-20 object-contain mb-4">
        <div class="text-base font-semibold text-center text-gray-800">มหาวิทยาลัยเทคโนโลยีพระจอมเกล้าธนบุรี</div>
        <div class="text-sm text-gray-500 mt-1">KMUTT</div>
      </a>

      <a href="<?php echo BASE_URL; ?>/index.php?keyword=kmilt"
        class="bg-[#FFE5EC] rounded-2xl p-6 flex flex-col items-center shadow-md hover:shadow-xl transition-all duration-300 uni-card">
        <img src="<?php echo BASE_URL; ?>/final_use/uni/kmilt.png" alt="kmilt" class="w-20 h-20 object-contain mb-4">
        <div class="text-base font-semibold text-center text-gray-800">สถาบันเทคโนโลยีพระจอมเกล้าเจ้าคุณทหารลาดกระบัง
        </div>
        <div class="text-sm text-gray-500 mt-1">KMILT</div>
      </a>

      <a href="<?php echo BASE_URL; ?>/index.php?keyword=su"
        class="bg-[#FFE5EC] rounded-2xl p-6 flex flex-col items-center shadow-md hover:shadow-xl transition-all duration-300 uni-card">
        <img src="<?php echo BASE_URL; ?>/final_use/uni/su.png" alt="su" class="w-20 h-20 object-contain mb-4">
        <div class="text-base font-semibold text-center text-gray-800">มหาวิทยาลัยศิลปากร</div>
        <div class="text-sm text-gray-500 mt-1">SU</div>
      </a>

    </div>
    </main>

</body>

<script>
  function toggleMobileMenu() {
    const menu = document.getElementById("menuContent");
    if (menu) {
      menu.classList.toggle("hidden");
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
</script>

</html>