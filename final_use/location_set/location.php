<?php
include '../config/db_connect.php';
session_start();

$id = $_GET['id'];
$sql = "SELECT * FROM locations WHERE location_id='$id'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);


if (!isset($_SESSION["user_id"]) && isset($_COOKIE["user_id"])) {
  $_SESSION["user_id"] = $_COOKIE["user_id"];
  $_SESSION["user_name"] = $_COOKIE["user_name"];
  $_SESSION["role_id"] = $_COOKIE["role_id"];
}



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


if (!$data) {
  echo "ไม่พบข้อมูล!";
  exit();
}


date_default_timezone_set('Asia/Bangkok');
$today = date('Y-m-d');


$review_query = "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews FROM reviews WHERE location_id = '$id'";
$review_result = mysqli_query($conn, $review_query);
$review_data = mysqli_fetch_assoc($review_result);

$avg_rating = $review_data['avg_rating'] ? round($review_data['avg_rating'], 1) : "ยังไม่มีรีวิว";
$total_reviews = $review_data['total_reviews'];


$images_query = "SELECT image_path FROM location_images WHERE location_id = '$id' ORDER BY uploaded_at DESC";
$images_result = mysqli_query($conn, $images_query);
$images = [];
while ($row = mysqli_fetch_assoc($images_result)) {
  $images[] = '../' . $row['image_path'];
}


$available_times = array_map('trim', explode(',', $data['time']));


$booked_times = [];
$booking_query = "SELECT booking_date, booking_start_time, booking_end_time FROM booking WHERE location_id = '$id' AND status = 'pending'";
$booking_result = mysqli_query($conn, $booking_query);
while ($row = mysqli_fetch_assoc($booking_result)) {
  $time_slot = trim($row['booking_start_time'] . "-" . $row['booking_end_time']);
  $booked_times[$row['booking_date']][] = $time_slot;
}

$filter_rating = isset($_GET['rating']) ? intval($_GET['rating']) : null;
$review_query = "
    SELECT u.user_name, r.rating, r.review_text, r.created_at 
    FROM reviews r 
    JOIN userinfo u ON r.user_id = u.user_id 
    WHERE r.location_id = '$id'";

if ($filter_rating) {
  $review_query .= " AND r.rating = '$filter_rating'";
}
$review_query .= " ORDER BY r.created_at DESC LIMIT 5";
$review_result = mysqli_query($conn, $review_query);
?>

<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($data['location_name']); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/final_use/style/location.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Prompt&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.9/dayjs.min.js"></script>
  <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
  <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

  <style>

  </style>
</head>

<body class="text-gray-800">
  <nav id="navbar"
    class="fixed top-0 left-0 w-full z-50 bg-white shadow p-4 flex justify-between items-center transition-transform duration-300">
    <div class="text-xl font-bold">Cozpaze</div>
    <!-- button mobile -->
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
  <div class="max-w-6xl mx-auto px-4 pt-28 pb-10 space-y-8">

    <!-- รายละเอียด -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 agoda-shadow bg-white rounded-2xl p-6 ">
      <div class="md:col-span-2 space-y-2 ">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold"><?= htmlspecialchars($data['location_name']); ?></h1>
        </div>
        <p class="text-gray-500 text-sm">หมวดหมู่: <?= htmlspecialchars($data['category']); ?></p>
        <p class="text-gray-600 text-base leading-relaxed"><?= nl2br(htmlspecialchars($data['description'])); ?></p>
        <div class="text-sm text-gray-500 mt-2">⭐ <?= $avg_rating ?> (<?= $total_reviews ?> รีวิว)</div>
        <div class="text-lg font-medium text-green-600">฿<?= number_format($data['price_per_hour'], 2); ?>/ชั่วโมง</div>
        <button onclick="window.open('<?= addslashes($data['link']); ?>', '_blank')"
          class="mt-3 inline-block bg-pink-200 text-red-950 px-4 py-2 rounded-lg hover:bg-pink-300">ดูบน Google
          Map</button>
      </div>
      <div class="space-y-3 border-l pl-6">
        <h2 class="text-xl font-semibold">จองเวลา</h2>
        <form action="<?php echo BASE_URL; ?>/final_use/booking/add_to_cart.php" method="POST" class="space-y-3">
          <input type="hidden" name="location_id" value="<?= $id; ?>">
          <input type="hidden" name="location_name" value="<?= htmlspecialchars($data['location_name']); ?>">
          <input type="hidden" name="price" value="<?= $data['price_per_hour']; ?>">
          <label class="block text-sm">เลือกวันที่</label>
          <select name="booking_date" id="booking_date" required class="w-full border rounded p-2">
            <option value="">-- เลือกวันที่ --</option>
          </select>
          <label class="block text-sm">เลือกช่วงเวลา</label>
          <select name="time_slot" id="time_slot" required class="w-full border rounded p-2">
            <option value="">-- กรุณาเลือกวันที่ก่อน --</option>
          </select>
          <button type="submit"
            class="w-full bg-pink-200 text-red-950 py-2 rounded hover:bg-pink-300">ยืนยันการจอง</button>
        </form>
      </div>
    </div>

    <!-- แผนที่และแกลเลอรี่ -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white rounded-2xl p-4 agoda-shadow relative">
        <h2 class="text-xl font-semibold mb-4">ตำแหน่งที่ตั้ง</h2>
        <div id="map" style="height: 300px;" class="rounded-lg"></div>
      </div>
      <div class="bg-white rounded-2xl p-4 agoda-shadow">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold">รูปภาพ</h2>
          <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 2): ?>
            <button onclick="toggleUploadArea()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
              + เพิ่มรูปภาพ
            </button>
          <?php endif; ?>
        </div>

        <!-- Upload Area -->
        <div id="uploadOverlay"
          class="hidden absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 bg-white rounded-xl shadow-2xl z-20">
          <div class="p-4">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-xl font-semibold">อัพโหลดรูปภาพ</h3>
              <button onclick="toggleUploadArea()" class="text-gray-500 hover:text-gray-700">✕</button>
            </div>
            <div class="upload-area" id="uploadArea">
              <div class="upload-icon">📁</div>
              <div class="upload-text">ลากและวางรูปภาพที่นี่ หรือคลิกเพื่อเลือกไฟล์</div>
              <div class="upload-hint">รองรับไฟล์ JPG, JPEG, PNG, GIF (สูงสุด 5MB ต่อไฟล์)</div>
              <input type="file" id="fileInput" multiple accept="image/*" class="hidden">
            </div>
            <div class="progress-bar hidden" id="progressBar">
              <div class="progress-bar-fill" id="progressFill"></div>
            </div>
            <div class="upload-status" id="uploadStatus"></div>
            <div class="preview-container" id="previewContainer"></div>
          </div>
        </div>

        <!-- Image Gallery -->
        <?php
        $images_query = "SELECT * FROM location_images WHERE location_id = ?";
        $stmt = $conn->prepare($images_query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $images_result = $stmt->get_result();
        $images = [];
        while ($row = $images_result->fetch_assoc()) {
          $images[] = $row;
        }
        if (count($images) > 0):
          ?>
          <div class="swiper mySwiper">
            <div class="swiper-wrapper">
              <?php foreach ($images as $image): ?>
                <div class="swiper-slide">
                  <img src="../<?= htmlspecialchars($image['image_path']) ?>" alt="Location image">
                  <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 2): ?>
                    <form method="POST" action="admin/delete_image.php" class="absolute top-2 right-2"
                      onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบรูปภาพนี้?');">
                      <input type="hidden" name="image_id" value="<?= $image['image_id'] ?>">
                      <input type="hidden" name="location_id" value="<?= $id ?>">
                      <button type="submit" class="delete-button">
                        ลบรูป
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
          </div>
        <?php else: ?>
          <div class="text-center py-10 text-gray-500">ยังไม่มีรูปภาพ</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- รีวิว -->
    <div class="bg-white rounded-2xl p-4 agoda-shadow space-y-4">
      <h2 class="text-lg font-semibold">รีวิวจากผู้ใช้</h2>
      <?php if (isset($_SESSION['user_id'])): ?>
        <form action="<?php echo BASE_URL; ?>/final_use/reviews/submit_review.php" method="POST" class="space-y-2">
          <input type="hidden" name="location_id" value="<?= $id; ?>">
          <select name="rating" class="w-full border rounded p-2">
            <option value="5">⭐⭐⭐⭐⭐</option>
            <option value="4">⭐⭐⭐⭐</option>
            <option value="3">⭐⭐⭐</option>
            <option value="2">⭐⭐</option>
            <option value="1">⭐</option>
          </select>
          <textarea name="review_text" placeholder="แสดงความคิดเห็นของคุณ..." class="w-full border rounded p-2"
            required></textarea>
          <button type="submit" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-800">ส่งรีวิว</button>
        </form>
      <?php else: ?>
        <p class="text-red-500">กรุณาเข้าสู่ระบบเพื่อแสดงความคิดเห็น</p>
      <?php endif; ?>

      <form method="GET">
        <input type="hidden" name="id" value="<?= $id; ?>">
        <select name="rating" onchange="this.form.submit()" class="border rounded p-2">
          <option value="">-- ดูทั้งหมด --</option>
          <option value="5">⭐⭐⭐⭐⭐</option>
          <option value="4">⭐⭐⭐⭐</option>
          <option value="3">⭐⭐⭐</option>
          <option value="2">⭐⭐</option>
          <option value="1">⭐</option>
        </select>
      </form>

      <div class="space-y-3">
        <?php
        if (mysqli_num_rows($review_result) === 0) {
          echo "<p class='text-gray-500'>ยังไม่มีรีวิว</p>";
        } else {
          while ($review = mysqli_fetch_assoc($review_result)) {
            echo "<div class='bg-gray-50 p-3 rounded border border-gray-100'>
                    <p class='text-sm font-semibold'>{$review['user_name']} ⭐ {$review['rating']}</p>
                    <p class='text-sm text-gray-700'>{$review['review_text']}</p>
                    <p class='text-xs text-gray-400'>{$review['created_at']}</p>
                  </div>";
          }
        }
        ?>
      </div>
    </div>
  </div>

  <?php if (isset($_GET['upload_success'])): ?>
    <div class="fixed bottom-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
      อัพโหลดรูปภาพสำเร็จ
      <button onclick="this.parentElement.style.display='none'" class="ml-2">✕</button>
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['upload_error'])): ?>
    <div class="fixed bottom-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
      <?= htmlspecialchars(urldecode($_GET['upload_error'])) ?>
      <button onclick="this.parentElement.style.display='none'" class="ml-2">✕</button>
    </div>
  <?php endif; ?>

  <script>
    const availableTimes = <?= json_encode($available_times); ?>;
    const bookedTimes = <?= json_encode($booked_times); ?>;
    document.addEventListener("DOMContentLoaded", function () {
      const dateSelect = document.getElementById("booking_date");
      const timeSlotSelect = document.getElementById("time_slot");
      const today = dayjs();

      if (dateSelect && timeSlotSelect) {
        for (let i = 0; i < 3; i++) {
          const date = today.add(i, 'day').format('YYYY-MM-DD');
          const option = document.createElement("option");
          option.value = date;
          option.textContent = dayjs(date).format('DD MMM YYYY');
          dateSelect.appendChild(option);
        }

        dateSelect.addEventListener("change", function () {
          const selectedDate = this.value;
          timeSlotSelect.innerHTML = '<option value="">-- กรุณาเลือกช่วงเวลา --</option>';
          availableTimes.forEach(function (time) {
            const isBooked = bookedTimes[selectedDate] && bookedTimes[selectedDate].includes(time);
            const option = document.createElement('option');
            option.value = time;
            option.textContent = time + (isBooked ? " (จองแล้ว)" : "");
            if (isBooked) option.disabled = true;
            timeSlotSelect.appendChild(option);
          });
        });
      }

      const map = L.map('map').setView([<?= $data['latitude']; ?>, <?= $data['longitude']; ?>], 15);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
      L.marker([<?= $data['latitude']; ?>, <?= $data['longitude']; ?>]).addTo(map)
        .bindPopup('<?= $data['location_name']; ?>').openPopup();
    });

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


    document.addEventListener("DOMContentLoaded", function () {
      const track = document.querySelector('.carousel-track');
      const slides = document.querySelectorAll('.carousel-slide');
      const prevButton = document.querySelector('.carousel-prev');
      const nextButton = document.querySelector('.carousel-next');
      const dots = document.querySelectorAll('.carousel-dot');

      let currentIndex = 0;
      const slideCount = slides.length;

      function updateCarousel() {
        track.style.transform = `translateX(-${currentIndex * 100}%)`;
        dots.forEach((dot, index) => {
          dot.classList.toggle('bg-gray-600', index === currentIndex);
          dot.classList.toggle('bg-gray-300', index !== currentIndex);
        });
      }

      prevButton.addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + slideCount) % slideCount;
        updateCarousel();
      });

      nextButton.addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % slideCount;
        updateCarousel();
      });

      dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
          currentIndex = index;
          updateCarousel();
        });
      });


      setInterval(() => {
        currentIndex = (currentIndex + 1) % slideCount;
        updateCarousel();
      }, 5000);
    });


    document.addEventListener('DOMContentLoaded', function () {
      const uploadArea = document.getElementById('uploadArea');
      const fileInput = document.getElementById('fileInput');
      const previewContainer = document.getElementById('previewContainer');
      const progressBar = document.getElementById('progressBar');
      const progressFill = document.getElementById('progressFill');
      const uploadStatus = document.getElementById('uploadStatus');
      const locationId = '<?php echo $id; ?>';


      ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
      });

      function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
      }

      ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, highlight, false);
      });

      ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, unhighlight, false);
      });

      function highlight(e) {
        uploadArea.classList.add('dragover');
      }

      function unhighlight(e) {
        uploadArea.classList.remove('dragover');
      }


      uploadArea.addEventListener('drop', handleDrop, false);
      uploadArea.addEventListener('click', () => fileInput.click());
      fileInput.addEventListener('change', handleFiles);

      function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles({ target: { files } });
      }

      function handleFiles(e) {
        const files = [...e.target.files];
        if (files.length > 5) {
          showStatus('สามารถอัพโหลดได้สูงสุด 5 ไฟล์', 'error');
          return;
        }


        previewContainer.innerHTML = '';


        files.forEach(file => {
          if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
              const previewItem = document.createElement('div');
              previewItem.className = 'preview-item';
              previewItem.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <button class="remove-btn" onclick="this.parentElement.remove()">×</button>
                    `;
              previewContainer.appendChild(previewItem);
            };
            reader.readAsDataURL(file);
          }
        });


        uploadFiles(files);
      }

      function uploadFiles(files) {
        const formData = new FormData();
        formData.append('location_id', locationId);
        files.forEach(file => {
          formData.append('images[]', file);
        });

        progressBar.classList.remove('hidden');
        progressFill.style.width = '0%';
        showStatus('กำลังอัพโหลด...', 'uploading');

        fetch('upload.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showStatus(data.message, 'success');
              progressFill.style.width = '100%';
              setTimeout(() => {
                progressBar.classList.add('hidden');
                window.location.reload();
              }, 1000);
            } else {
              showStatus(data.message, 'error');
              progressBar.classList.add('hidden');
            }
          })
          .catch(error => {
            showStatus('เกิดข้อผิดพลาดในการอัพโหลด', 'error');
            progressBar.classList.add('hidden');
          });
      }

      function showStatus(message, type) {
        uploadStatus.textContent = message;
        uploadStatus.className = 'upload-status';
        if (type === 'success') {
          uploadStatus.classList.add('upload-success');
        } else if (type === 'error') {
          uploadStatus.classList.add('upload-error');
        }
      }
    });

    document.addEventListener('DOMContentLoaded', function () {
      const swiper = new Swiper(".mySwiper", {
        loop: true,
        autoplay: {
          delay: 3000,
          disableOnInteraction: false,
        },
        pagination: {
          el: ".swiper-pagination",
          clickable: true,
        },
        navigation: {
          nextEl: ".swiper-button-next",
          prevEl: ".swiper-button-prev",
        },
      });
    });

    function toggleUploadArea() {
      const uploadOverlay = document.getElementById('uploadOverlay');
      if (uploadOverlay.classList.contains('hidden')) {
        uploadOverlay.classList.remove('hidden');

        const overlay = document.createElement('div');
        overlay.id = 'backgroundOverlay';
        overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-10';
        overlay.onclick = toggleUploadArea;
        document.body.appendChild(overlay);
      } else {
        uploadOverlay.classList.add('hidden');

        const overlay = document.getElementById('backgroundOverlay');
        if (overlay) {
          overlay.remove();
        }
      }
    }

    function toggleMobileMenu() {
      const menu = document.getElementById("menuContent");
      if (menu) {
        menu.classList.toggle("hidden");
      }
    }
  </script>
</body>

</html>