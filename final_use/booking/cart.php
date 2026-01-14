<?php
include '../login_status/require_login.php';


if (!isset($_SESSION['cart']) && isset($_COOKIE['cart_items'])) {
  $_SESSION['cart'] = json_decode($_COOKIE['cart_items'], true);
}

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}


if (!empty($_SESSION['cart'])) {
  setcookie('cart_items', json_encode($_SESSION['cart']), time() + (86400 * 30), "/"); // 30 วันนะ
} else {

  setcookie('cart_items', '', time() - 3600, "/");
}

$total_price = 0;
foreach ($_SESSION['cart'] as $booking) {
  $total_price += $booking['price'];
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <title>ตะกร้าการจอง</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Prompt&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    body {
      font-family: 'Sarabun', sans-serif;
      background: linear-gradient(to bottom right, #ffe6f0, #e6f7ff);
      color: #333;
      min-height: 100vh;
    }

    .back-button {
      position: fixed;
      top: 10px;
      right: 10px;
      padding: 10px 15px;
      background-color: rgba(255, 199, 199, 0.6);
      color: #b30059;
      border-bottom-left-radius: 10px;
      font-weight: bold;
      z-index: 9999;
      box-shadow: 0 4px 10px rgba(255, 199, 199, 0.4);
      transition: all 0.3s ease;
      text-decoration: none;
      backdrop-filter: blur(5px);
    }

    .back-button:hover {
      background-color: rgba(255, 105, 180, 0.85);
      color: white;
      box-shadow: 0 6px 12px rgba(255, 105, 180, 0.6);
    }
  </style>
</head>

<body class="min-h-screen flex flex-col">
  <a href="<?php echo BASE_URL; ?>/index.php" class="back-button">กลับหน้าแรก</a>


  <!-- Cart -->
  <div class="container mx-auto px-4 py-8 flex-grow">
    <h1 class="text-3xl font-semibold text-[#] text-center mb-8">ตะกร้าการจอง</h1>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <?php if (!empty($_SESSION['cart'])): ?>
        <?php foreach ($_SESSION['cart'] as $index => $booking): ?>
          <div
            class="bg-white bg-opacity-70 backdrop-blur-md p-6 rounded-2xl border border-pink-100 shadow-[0_10px_30px_rgba(255,183,197,0.25)] hover:shadow-xl hover:scale-[1.02] transition-all duration-300">
            <img src="<?php echo BASE_URL; ?>/final_use/img/location/<?php echo basename($booking['image_path']); ?>"
              alt="รูปสถานที่" class="w-full h-48 object-cover rounded-xl">
            <div class="space-y-2 mt-4">
              <h3 class="text-lg font-semibold text-pink-700"><?= htmlspecialchars($booking['location_name']); ?></h3>
              <div class="flex flex-col gap-1 mt-3 text-sm">
                <div class="flex items-center gap-2">

                  <span class="text-gray-700 font-medium">ช่วงเวลา:</span>
                  <span class="text-gray-900 font-semibold"><?= htmlspecialchars($booking['time_slot']); ?></span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-gray-700 font-medium">ราคา:</span>
                  <span class="text-pink-500 font-bold"><?= number_format($booking['price'], 2); ?> บาท</span>
                </div>
              </div>
              <a href="remove_from_cart.php?index=<?= $index ?>"
                class="inline-block px-4 py-2 bg-red-100 text-red-600 font-semibold text-sm rounded-md shadow-sm hover:bg-red-200 hover:text-red-700 transition duration-200">
                ลบออกจากตะกร้า
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-center text-gray-500 text-lg col-span-full">ยังไม่มีรายการในตะกร้า</p>
      <?php endif; ?>
    </div>

    <?php if (!empty($_SESSION['cart'])): ?>
      <div class="text-center mt-10">
        <h2 class="text-xl font-semibold text-gray-700">ราคารวมทั้งหมด:
          <span class="text-[#FFC7C7] font-bold"><?= number_format($total_price, 2); ?> บาท</span>
        </h2>
        <div class="mt-6">
          <a href="confirm_booking.php"
            class="bg-[#FFC7C7] text-white px-6 py-3 rounded-lg shadow-md hover:bg-[#ffabab] transition">
            ยืนยันการจอง
          </a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</body>

</html>