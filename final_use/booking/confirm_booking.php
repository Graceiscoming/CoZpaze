<?php
include '../login_status/require_login.php';
include '../config/db_connect.php';

ob_start(); 

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    die(" ไม่มีรายการในตะกร้า กรุณาเลือกสถานที่ก่อนทำการจอง");
}

$user_id = $_SESSION['user_id'] ?? 1;


$check_booking_query = $conn->prepare("SELECT COUNT(*) AS booking_count FROM booking WHERE user_id = ? AND status = 'confirmed'");
$check_booking_query->bind_param("i", $user_id);
$check_booking_query->execute();
$booking_count = $check_booking_query->get_result()->fetch_assoc()['booking_count'];

if ($booking_count >= 3) {
    die(" คุณไม่สามารถทำการจองได้เกิน 3 ครั้ง โปรดตรวจสอบสถานะการจองของคุณ");
}


$check_pending_query = $conn->prepare("SELECT COUNT(*) AS pending_count FROM booking WHERE user_id = ? AND status = 'pending'");
$check_pending_query->bind_param("i", $user_id);
$check_pending_query->execute();
$pending_count = $check_pending_query->get_result()->fetch_assoc()['pending_count'];

if ($pending_count >= 5) {
    die(" คุณมีรายการ Pending เกิน 5 รายการ");
}


$wallet_query = $conn->prepare("SELECT balance FROM wallets WHERE user_id = ?");
$wallet_query->bind_param("i", $user_id);
$wallet_query->execute();
$wallet_balance = $wallet_query->get_result()->fetch_assoc()['balance'] ?? 0;


$total_price = array_sum(array_column($_SESSION['cart'], 'price'));

if ($wallet_balance < $total_price) {
    echo "<script>alert(' ยอดเงินไม่พอ กรุณาเติมเงินก่อนทำการจอง'); window.location.href='cart.php';</script>";
    exit();
}


$location_query = $conn->prepare("SELECT location_name FROM locations WHERE location_id = ?");
$stmt = $conn->prepare("
    INSERT INTO booking (user_id, location_id, booking_time, booking_date, total_price, booking_start_time, booking_end_time, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
");

$confirmation_messages = [];

foreach ($_SESSION['cart'] as $booking) {
    $location_id = $booking['location_id'];
    $booking_time = $booking['time_slot'];
    $booking_date = $booking['booking_date'];
    $price = $booking['price'];

    if (preg_match('/(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})\s*(AM|PM)?/', $booking_time, $matches)) {
        $am_pm = $matches[3] ?? '';
        $booking_start_time = date("H:i:s", strtotime($matches[1] . " " . $am_pm));
        $booking_end_time = date("H:i:s", strtotime($matches[2] . " " . $am_pm));
    } else {
        die(" รูปแบบเวลาไม่ถูกต้อง: $booking_time");
    }

    $stmt->bind_param("isssiss", $user_id, $location_id, $booking_time, $booking_date, $price, $booking_start_time, $booking_end_time);
    $stmt->execute();

    $location_query->bind_param("i", $location_id);
    $location_query->execute();
    $location_name = $location_query->get_result()->fetch_assoc()['location_name'] ?? "ไม่ทราบชื่อสถานที่";

    $confirmation_messages[] = " <strong>$location_name</strong><br>เวลา: <strong>$booking_time</strong> → จองสำเร็จ!";
}


$new_balance = $wallet_balance - $total_price;
$update_wallet = $conn->prepare("UPDATE wallets SET balance = ? WHERE user_id = ?");
$update_wallet->bind_param("di", $new_balance, $user_id);
$update_wallet->execute();

unset($_SESSION['cart']);
setcookie('cart_items', '', time() - 3600, "/");

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>ผลการจอง</title>
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Prompt', sans-serif;
      background: linear-gradient(120deg, #e0f7fa, #fce4ec);
      background-attachment: fixed;
      padding-top: 5rem;
      color: #333;
    }
    .container {
      padding-top: 80px;
      max-width: 960px;
      margin: 60px auto;
      background: rgba(255, 255, 255, 0.95);
      padding: 40px 30px;
      border-radius: 28px;
      box-shadow: 0 12px 36px rgba(0, 0, 0, 0.05);
      text-align: center;
      backdrop-filter: blur(5px);
    }
    h1 {
    margin-bottom: 40px;
    font-size: 2rem;
    font-weight: bold;
    color: #ec407a; 
    background: linear-gradient(to right, #ec407a, #7c4dff);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    }
    .message {
      font-size: 1.1rem;
      line-height: 2;
      margin-bottom: 20px;
    }
    .balance {
      font-size: 1.2rem;
      font-weight: bold;
      color: #6a1b9a;
    }
    .btn-primary {
      padding: 10px 20px;
      border-radius: 12px;
      background: #7e57c2;
      color: white;
      text-decoration: none;
      display: inline-block;
      margin-top: 30px;
      transition: 0.3s ease;
    }
    .btn-primary:hover {
      background: #9575cd;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1> การจองของคุณเสร็จสมบูรณ์!</h1>
    <div class="message">
      <?php foreach ($confirmation_messages as $msg) echo "<p>$msg</p>"; ?>
      <p class="balance"> ยอดเงินคงเหลือ: <?= number_format($new_balance, 2) ?> Token</p>
    </div>
    <a href="/index.php" class="btn-primary"> กลับหน้าแรก</a>
  </div>
</body>
</html>
