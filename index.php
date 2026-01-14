<?php
session_start();
include __DIR__ . '/final_use/config/db_connect.php';

//cookies
if (isset($_GET['keyword'])) {
  setcookie('last_search_keyword', $_GET['keyword'], [
    'expires' => time() + (86400 * 30),
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
  ]);
}

if (isset($_GET['time'])) {
  setcookie('last_search_times', json_encode($_GET['time']), [
    'expires' => time() + (86400 * 30),
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
  ]);
}

if (!isset($_GET['keyword']) && isset($_COOKIE['last_search_keyword'])) {
  $keyword = $_COOKIE['last_search_keyword'];
} else {
  $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : "";
}

if (!isset($_GET['time']) && isset($_COOKIE['last_search_times'])) {
  $selected_times = json_decode($_COOKIE['last_search_times'], true);
} else {
  $selected_times = isset($_GET['time']) ? $_GET['time'] : [];
}

$keyword = mysqli_real_escape_string($conn, $keyword);

if (!isset($_SESSION["user_id"]) && isset($_COOKIE["user_id"])) {
  $_SESSION["user_id"] = $_COOKIE["user_id"];
  $_SESSION["user_name"] = $_COOKIE["user_name"];
  $_SESSION["role_id"] = $_COOKIE["role_id"];
}


// login check
if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];

  $check_sql = "SELECT * FROM searchkeywords WHERE keyword = ? AND user_id = ?";
  $check_stmt = $conn->prepare($check_sql);
  $check_stmt->bind_param("ss", $keyword, $user_id);
  $check_stmt->execute();
  $check_result = $check_stmt->get_result();

  if ($check_result->num_rows == 0) {
    $insert_sql = "INSERT INTO searchkeywords (keyword, user_id) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ss", $keyword, $user_id);
    $insert_stmt->execute();
    $insert_stmt->close();
  }
  $check_stmt->close();


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


  $sql_wallet = "SELECT balance FROM wallets WHERE user_id = ?";
  $wallet_stmt = $conn->prepare($sql_wallet);
  $wallet_stmt->bind_param("s", $user_id);
  $wallet_stmt->execute();
  $result_wallet = $wallet_stmt->get_result();

  if ($result_wallet && $result_wallet->num_rows > 0) {
    $wallet_data = $result_wallet->fetch_assoc();
    $balance = $wallet_data['balance'];
  } else {
    $balance = 0;
  }
  $wallet_stmt->close();
} else {
  $user_name = "ไม่ได้ login";
  $balance = "-";
}

$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : "";
$selected_times = isset($_GET['time']) ? $_GET['time'] : [];


$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$results_per_page = 20;
$offset = ($page - 1) * $results_per_page;


$keyword = mysqli_real_escape_string($conn, $keyword);


$count_sql = "
    SELECT COUNT(DISTINCT l.location_id) as total
    FROM locations l
    LEFT JOIN location_keywords k ON l.location_id = k.location_id
";

if (!empty($keyword)) {
  $count_sql .= " WHERE (l.uni LIKE '%$keyword%' OR k.keyword LIKE '%$keyword%')";
}

if (!empty($selected_times)) {
  $time_conditions = [];
  foreach ($selected_times as $time) {
    $safe_time = mysqli_real_escape_string($conn, $time);
    $time_conditions[] = "time LIKE '%$safe_time%'";
  }
  if (!empty($time_conditions)) {
    $count_sql .= (!empty($keyword) ? " AND " : " WHERE ") . "(" . implode(" OR ", $time_conditions) . ")";
  }
}

$count_result = mysqli_query($conn, $count_sql);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $results_per_page);


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

$sql .= " LIMIT $offset, $results_per_page";

$result = mysqli_query($conn, $sql);
?>




<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CoZpaze</title>
  <link href="https://fonts.googleapis.com/css2?family=Prompt&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/final_use/style/main.css">
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <script src="https://cdn.tailwindcss.com"></script>

  <script>
    // UI cookies
    document.addEventListener('DOMContentLoaded', function () {

      const menuState = getCookie('menu_state');
      if (menuState === 'collapsed') {
        document.getElementById('menuContent').classList.add('hidden');
      }
      const language = getCookie('language') || 'th';
      if (language !== 'th') {

      }
    });
    function toggleMobileMenu() {
      const menu = document.getElementById('menuContent');
      menu.classList.toggle('hidden');
      setCookie('menu_state', menu.classList.contains('hidden') ? 'collapsed' : 'expanded', 30);
    }
    function setCookie(name, value, days) {
      const date = new Date();
      date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
      document.cookie = name + "=" + value + ";expires=" + date.toUTCString() + ";path=/";
    }
    function getCookie(name) {
      const value = `; ${document.cookie}`;
      const parts = value.split(`; ${name}=`);
      if (parts.length === 2) return parts.pop().split(';').shift();
    }
  </script>
</head>

<body class="bg-white text-gray-800">
  <!-- Cookie Banner -->
  <div id="cookieConsent" class="fixed bottom-0 left-0 right-0 bg-gray-800 text-white p-4 hidden z-50">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between">
      <div class="mb-4 md:mb-0">
        <p class="text-sm">เราใช้คุกกี้เพื่อปรับปรุงประสบการณ์การใช้งานของคุณ <a href="/privacy-policy.php"
            class="underline">นโยบายความเป็นส่วนตัว</a></p>
      </div>
      <div class="flex gap-4">
        <button onclick="acceptCookies()"
          class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">ยอมรับ</button>
        <button onclick="declineCookies()"
          class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">ปฏิเสธ</button>
      </div>
    </div>
  </div>

  <script>
    if (!getCookie('cookie_consent')) {
      document.getElementById('cookieConsent').classList.remove('hidden');
    }

    function acceptCookies() {
      setCookie('cookie_consent', 'accepted', 365);
      document.getElementById('cookieConsent').classList.add('hidden');
    }

    function declineCookies() {
      setCookie('cookie_consent', 'declined', 365);
      document.getElementById('cookieConsent').classList.add('hidden');
      document.cookie.split(";").forEach(function (c) {
        document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
      });
    }
  </script>

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
        <a href="<?php echo BASE_URL; ?>/final_use/uniselect.php" class="block px-2 py-1 hover:underline">หน้าเลือกมหาลัย</a>
        <a href="<?php echo BASE_URL; ?>/final_use/tokenshop/tokenshop.php" class="block px-2 py-1 hover:underline">เติมโทเคน</a>
        <a href="<?php echo BASE_URL; ?>/final_use/booking/cart.php" class="block px-2 py-1 hover:underline">ตระกร้าของฉัน</a>
        <a href="<?php echo BASE_URL; ?>/final_use/user/user_check_booking.php" class="block px-2 py-1 hover:underline">การจองของฉัน</a>
        <a href="<?php echo BASE_URL; ?>/final_use/user/user_profile.php" class="block px-2 py-1 hover:underline">หน้าของฉัน</a>

        <?php if (isset($_SESSION['user_id'])): ?>
          <span
            class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-800 text-sm font-semibold px-3 py-1 rounded-full shadow-sm my-2 md:my-0">
            <span>🪙<?php echo $balance; ?></span> Coin
          </span>
        <?php endif; ?>

        <div class="flex flex-col md:flex-row gap-2 mt-3 md:mt-0">
          <?php if (!isset($_SESSION['user_id'])): ?>
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/final_use/user/user_login.php'"
              class="py-2 px-4 border rounded-full text-sm text-gray-700 hover:bg-[linear-gradient(120deg,#2196f3,#f06292)]">เข้าสู่ระบบ</button>
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/final_use/user/user_reg.php'"
              class="py-2 px-4 text-sm bg-[linear-gradient(120deg,#2196f3,#f06292)] text-white rounded-full hover:opacity-90">ลงชื่อเข้าใช้</button>
          <?php else: ?>
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/final_use/login_status/logout.php'"
              class="py-2 px-4 border rounded-full text-sm text-gray-700 hover:bg-[linear-gradient(120deg,#2196f3,#f06292)]">Logout</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>


  <!-- Hero Section -->
  <section class="relative h-[600px] overflow-hidden" id="location-slideshow">
    <div class="absolute inset-0 flex transition-transform duration-1500 ease-in-out" id="slideshow-container">
    </div>
    <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
      <div class="bg-white/90 backdrop-blur-md p-10 rounded-2xl shadow-2xl w-full max-w-lg text-center">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">จะไปที่ไหนหรอ? ให้เราลองช่วยแนะนำมั้ย</h1>
        <p class="text-sm text-gray-600 mb-6">ลองบอกสถานีที่ที่คุณสนใจหน่อยสิ ✨</p>

        <form method="GET" class="space-y-4" id="searchForm">
          <div class="relative">
            <input id="keyword" type="text" name="keyword"
              class="block w-full p-3 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="ลองใส่ชื่อสถาที่หรือชื่อมหาลัยใกล้เคียงดูสิ"
              value="<?php echo htmlspecialchars($keyword); ?>">
            <div id="autocomplete-results"
              class="absolute z-20 w-full bg-white border border-gray-300 rounded-lg mt-2 shadow-md max-h-60 overflow-y-auto hidden">
            </div>
            <div id="searchLoading" class="absolute right-3 top-3 hidden">
              <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
            </div>
          </div>

          <div id="searchError" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
            role="alert">
            <span class="block sm:inline" id="errorMessage"></span>
          </div>

          <div class="text-left">
            <button type="button" onclick="toggleTimeOptions()"
              class="w-full bg-[linear-gradient(120deg,#2196f3,#f06292)] text-white py-2 rounded-lg transition duration-300 hover:opacity-90">
              เลือกเวลา
            </button>
            <div id="time-options" class="mt-3 hidden">
              <label class="block mb-2 text-sm font-medium text-gray-700">เลือกช่วงเวลา:</label>
              <div class="flex flex-wrap gap-2">
                <?php
                for ($hour = 10; $hour < 22; $hour += 2) {
                  $start = sprintf("%02d:00", $hour);
                  $end = sprintf("%02d:00", $hour + 2);
                  $checked = in_array("$start-$end", $selected_times) ? "checked" : "";
                  echo "<label class='inline-flex items-center gap-1 bg-gray-100 px-3 py-1 rounded-md shadow-sm text-sm text-gray-700'>
                            <input type='checkbox' name='time[]' value='$start-$end' $checked>
                            $start - $end
                          </label>";
                }
                ?>
              </div>
            </div>
          </div>

          <!-- Submit button -->
          <button type="submit"
            class="w-full bg-[linear-gradient(120deg,#2196f3,#f06292)] text-white py-2 rounded-lg font-medium transition duration-300 hover:opacity-90">
            ค้นหา
          </button>
        </form>
      </div>
    </div>
  </section>

  <script>

    async function fetchLocationImages() {
      try {
        const response = await fetch('<?php echo BASE_URL; ?>/final_use/get_location_images.php');
        const images = await response.json();
        return images;
      } catch (error) {
        console.error('Error fetching location images:', error);
        return [];
      }
    }


    async function initializeSlideshow() {
      const locationImages = await fetchLocationImages();
      const container = document.getElementById('slideshow-container');
      let currentIndex = 0;

      locationImages.forEach((image, index) => {
        const imgDiv = document.createElement('div');
        imgDiv.className = 'min-w-full h-full bg-cover bg-center';
        imgDiv.style.backgroundImage = `url('${image}')`;
        imgDiv.style.filter = 'blur(0)';
        container.appendChild(imgDiv);
      });

      function slideImages() {
        const images = container.children;
        if (images.length === 0) return;


        Array.from(images).forEach(img => {
          img.style.filter = 'blur(5px)';
        });

        setTimeout(() => {

          const firstImage = images[0];
          container.appendChild(firstImage);

          container.style.transform = 'translateX(0)';
          Array.from(images).forEach(img => {
            img.style.filter = 'blur(0)';
          });
        }, 500);
      }

      setInterval(slideImages, 2000);
    }
    initializeSlideshow();
  </script>

  <!--พื้นที่แนะนำ -->
  <section class="py-16 px-6 bg-gray-50">
    <div class="max-w-7xl mx-auto text-center">
      <h1 class="text-4xl font-extrabold text-gray-900 mb-10 tracking-tight">
        แนะนำสถานที่
      </h1>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 justify-center">
        <?php
        $location_count = 0;
        while ($row = mysqli_fetch_assoc($result)):
          if ($location_count >= 6) {
            break;
          }
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
          <?php
          $location_count++;
        endwhile;
        ?>
      </div>

      <?php if ($total_pages > 1): ?>
        <div class="mt-8 flex flex-col items-center space-y-4">
        </div>
      <?php endif; ?>

      <div class="mt-10">
        <a href="<?php echo BASE_URL; ?>/final_use/location_all.php" class="text-gray-700 hover:opacity-90 transition">
          ดูพื้นที่ทั้งหมด
        </a>
      </div>
    </div>
  </section>


  <!-- Footer -->
  <footer class="bg-gray-100 py-6 text-center text-sm text-gray-600">
    © 2025 CoZpaze. All rights reserved.
  </footer>

  <script>
    document.addEventListener("DOMContentLoaded", function () {

      const toggleBtn = document.querySelector("[onclick='toggleTimeOptions()']");
      if (toggleBtn) {
        toggleBtn.onclick = toggleTimeOptions;
      }
    });

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
      const menu = document.getElementById("menuContent");
      if (menu) {
        menu.classList.toggle("hidden");
      }
    }

    function toggleTimeOptions() {
      const timeOptions = document.getElementById("time-options");
      timeOptions.classList.toggle("hidden");
    }

    document.getElementById('searchForm').addEventListener('submit', function (e) {
      const keyword = document.getElementById('keyword').value.trim();
      if (keyword.length < 2) {
        e.preventDefault();
        document.getElementById('searchError').classList.remove('hidden');
        document.getElementById('errorMessage').textContent = 'กรุณาใส่คำค้นหาอย่างน้อย 2 ตัวอักษร';
        return;
      }

      document.getElementById('searchLoading').classList.remove('hidden');
    });

    <?php if (isset($_GET['error'])): ?>
      document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('searchError').classList.remove('hidden');
        document.getElementById('errorMessage').textContent = '<?php echo htmlspecialchars($_GET['error']); ?>';
      });
    <?php endif; ?>
  </script>

  <script>
    let searchData = [];

    async function fetchAutocompleteData() {
      try {
        const response = await fetch('<?php echo BASE_URL; ?>/final_use/get_autocomplete_data.php');
        searchData = await response.json();
      } catch (error) {
        console.error('Error fetching autocomplete data:', error);
      }
    }

    fetchAutocompleteData();

    const searchInput = document.getElementById('keyword');
    const autocompleteResults = document.getElementById('autocomplete-results');

    searchInput.addEventListener('input', function (e) {
      const searchTerm = e.target.value.toLowerCase().trim();
      autocompleteResults.innerHTML = '';

      const matches = searchData.filter(item => {
        const nameMatch = item.name.toLowerCase().includes(searchTerm);
        const keywordMatch = item.keywords.some(keyword =>
          keyword.toLowerCase().includes(searchTerm)
        );
        return nameMatch || keywordMatch;
      });

      if (matches.length > 0) {
        matches.forEach(item => {
          const div = document.createElement('div');
          div.className = 'p-2 hover:bg-gray-100 cursor-pointer';
          div.innerHTML = `
            <div class="font-semibold">${item.name}</div>
            ${item.keywords.length > 0 ?
              `<div class="text-sm text-gray-500">${item.keywords.join(', ')}</div>` :
              ''}
          `;
          div.addEventListener('click', () => {
            searchInput.value = item.name;
            autocompleteResults.classList.add('hidden');
            document.getElementById('searchForm').submit();
          });
          autocompleteResults.appendChild(div);
        });
        autocompleteResults.classList.remove('hidden');
      } else {
        autocompleteResults.classList.add('hidden');
      }
    });

    document.addEventListener('click', function (e) {
      if (!searchInput.contains(e.target) && !autocompleteResults.contains(e.target)) {
        autocompleteResults.classList.add('hidden');
      }
    });
  </script>
</body>

</html>