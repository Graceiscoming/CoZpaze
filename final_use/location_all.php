<?php
session_start();
include __DIR__ . '/config/db_connect.php';



if (!isset($_SESSION["user_id"]) && isset($_COOKIE["user_id"])) {
  $_SESSION["user_id"] = $_COOKIE["user_id"];
  $_SESSION["user_name"] = $_COOKIE["user_name"];
  $_SESSION["role_id"] = $_COOKIE["role_id"];
}


if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];


  $sql_user = "SELECT user_name FROM userinfo WHERE user_id = '$user_id'";
  $result_user = mysqli_query($conn, $sql_user);

  if ($result_user && mysqli_num_rows($result_user) > 0) {
    $user_data = mysqli_fetch_assoc($result_user);
    $user_name = $user_data['user_name'];
  } else {
    $user_name = "ไม่พบข้อมูลผู้ใช้";
  }


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

$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : "";
$selected_times = isset($_GET['time']) ? $_GET['time'] : [];

$keyword = mysqli_real_escape_string($conn, $keyword);

$sql = "
    SELECT DISTINCT l.*
    FROM locations l
    LEFT JOIN location_keywords k ON l.location_id = k.location_id
";

if (!empty($keyword)) {
  $sql .= " WHERE (l.uni LIKE '%$keyword%' OR k.keyword LIKE '%$keyword%')";
}

if (!empty($selected_times)) {
  $time_conditions = [];
  foreach ($selected_times as $time) {
    $safe_time = mysqli_real_escape_string($conn, $time);
    $time_conditions[] = "time LIKE '%$safe_time%'";
  }
  if (!empty($time_conditions)) {
    $sql .= (!empty($keyword) ? " AND " : " WHERE ") . "(" . implode(" OR ", $time_conditions) . ")";
  }
}

$result = mysqli_query($conn, $sql);
?>




<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>All Location Show</title>
  <link href="<?php echo BASE_URL; ?>/final_use/style/main.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Prompt&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      font-family: 'Prompt', sans-serif;
    }
  </style>
</head>

<body class="bg-[linear-gradient(120deg,#e0f7fa,#fce4ec)] text-gray-800">

  <!-- Navbar -->
  <nav id="navbar"
    class="fixed top-0 left-0 w-full z-50 bg-white shadow p-4 flex justify-between items-center transition-transform duration-300">
    <div class="text-xl font-bold">CoZpaze</div>

    <button class="md:hidden text-xl ml-auto" onclick="toggleMobileMenu()">☰</button>
    <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">

      <!-- Menu section-->
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
            <span><?php echo $balance; ?></span> Coin
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


  <!-- พื้นที่แนะนำ -->
  <section class="py-12 px-6 text-center">
    <div class="max-w-7xl mx-auto p-4">
      <br><br>
      <h1
        class="text-center text-2xl font-bold mb-8 bg-white/80 text-gray-800 py-3 px-6 rounded-lg inline-block shadow-sm">
        แนะนำสถานที่ทั้งหมด</h1>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php
        while ($row = mysqli_fetch_assoc($result)):
          ?>
          <div
            class="mx-auto bg-white rounded-2xl shadow-md hover:shadow-xl transition duration-300 overflow-hidden flex flex-col w-full max-w-sm">
            <?php if (!empty($row['image_path'])): ?>
              <img src="<?php echo BASE_URL; ?>/final_use/img/location/<?php echo basename($row['image_path']); ?>"
                class="w-full h-48 object-cover" alt="รูปสถานที่">
            <?php endif; ?>

            <div class="p-5 text-left flex-1 flex flex-col justify-between">
              <div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo $row['location_name']; ?></h3>
                <p class="text-sm text-gray-600 mb-2"><?php echo $row['description']; ?></p>
                <ul class="text-sm text-gray-500 space-y-1">
                  <li><strong class="text-gray-700">หมวดหมู่:</strong> <?php echo $row['category']; ?></li>
                  <li><strong class="text-gray-700">ค่าบริการ:</strong> <?php echo $row['price_per_hour']; ?> Coin</li>
                  <li><strong class="text-gray-700">มหาวิทยาลัย:</strong> <?php echo $row['uni']; ?></li>
                  <li><strong class="text-gray-700">เวลาเปิด:</strong>
                    <?php echo !empty($row['time']) ? $row['time'] : "ไม่ระบุ"; ?></li>
                </ul>
              </div>

              <div class="mt-4">
                <a href="<?php echo BASE_URL; ?>/final_use/location_set/location.php?id=<?php echo $row['location_id']; ?>"
                  class="inline-block w-full text-center px-4 py-2 bg-[linear-gradient(120deg,#2196f3,#f06292)] text-white rounded-lg transition text-sm hover:opacity-90">
                  ดูรายละเอียดเพิ่มเติม
                </a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-gradient-to-r from-blue-500 to-pink-500 py-6 text-center text-sm text-black mt-12">
    © 2025 - CoZpaze All rights reserved.
  </footer>

  <script>

    function toggleMobileMenu() {
      const menu = document.getElementById("menuContent");
      if (menu) {
        menu.classList.toggle("hidden");
      }
    }

    // ซ่อน Navbar เมื่อเลื่อนลง
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


</body>

</html>