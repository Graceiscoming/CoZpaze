<?php
session_start();
include '../config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('กรุณาเข้าสู่ระบบ'); window.location.href='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$price = isset($_GET['price']) ? (int) $_GET['price'] : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["payment_method"]) && isset($_FILES["payment_slip"])) {
    $payment_method = $_POST["payment_method"];

    $target_dir = "user_upload/";
    $file_name = time() . "_" . basename($_FILES["payment_slip"]["name"]);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = ["jpg", "jpeg", "png"];

    if (in_array($imageFileType, $allowed_types) && move_uploaded_file($_FILES["payment_slip"]["tmp_name"], $target_file)) {
        date_default_timezone_set('Asia/Bangkok');
        $transaction_date = date('Y-m-d H:i:s');

        $insert_sql = "INSERT INTO transactions (user_id, transaction_type, amount, transaction_date, slip_image) 
                       VALUES (?, 'pending', ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("idss", $user_id, $price, $transaction_date, $file_name);
        $insert_stmt->execute();

        echo "<script>alert('ส่งหลักฐานการชำระเงินแล้ว! รอการตรวจสอบ'); window.location.href='transactions.php';</script>";
    } else {
        echo "<script>alert('อัปโหลดสลิปไม่สำเร็จ!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ยืนยันการชำระเงิน</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/final_use/style/payment.css">

    <script>
        function showPaymentDetails() {
            const method = document.querySelector('input[name="payment_method"]:checked').value;
            document.getElementById("qr_code").style.display = (method === "QR Code") ? "block" : "none";
            document.getElementById("truemoney_details").style.display = (method === "TrueMoney") ? "block" : "none";

            document.querySelectorAll(".payment-card").forEach(el => {
                const input = el.querySelector("input");
                if (input.checked) {
                    el.classList.add("border-4", "border-pink-400", "shadow-lg", "scale-105");
                } else {
                    el.classList.remove("border-4", "border-pink-400", "shadow-lg", "scale-105");
                }
            });
        }

        window.onload = showPaymentDetails;
    </script>
</head>

<body>
    <div class="container shadow-lg">
        <h1 class="text-2xl font-bold gradient-text mb-4">ยืนยันการชำระเงิน</h1>
        <p class="text-lg">🔹 <strong>ราคาที่ต้องชำระ:</strong> <?php echo number_format($price, 2); ?> บาท</p>

        <form method="post" enctype="multipart/form-data" class="mt-4">
            <label class="block mb-3 text-lg font-semibold text-gray-700">💳 เลือกวิธีชำระเงิน(กดที่รูปได้เลย):</label>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6" id="paymentOptions">
                <label class="payment-card" data-method="TrueMoney">
                    <input type="radio" name="payment_method" value="TrueMoney" onclick="showPaymentDetails()"
                        class="hidden" required>
                    <div class="flex flex-col items-center justify-center gap-3">
                        <img src="<?php echo BASE_URL; ?>/final_use/tokenshop/img/truemoney.png" alt="TrueMoney"
                            class="w-12 h-12">
                        <span class="text-pink-700 font-semibold text-base">TrueMoney Wallet</span>
                    </div>
                </label>

                <label class="payment-card" data-method="QR Code">
                    <input type="radio" name="payment_method" value="QR Code" onclick="showPaymentDetails()"
                        class="hidden" required>
                    <div class="flex flex-col items-center justify-center gap-3">
                        <img src="https://img.icons8.com/ios-filled/50/000000/qr-code.png" alt="QR Code"
                            class="w-12 h-12">
                        <span class="text-indigo-700 font-semibold text-base">โอนผ่าน QR Code</span>
                    </div>
                </label>
            </div>


            <div id="truemoney_details" style="display:none;">
                <p class="text-pink-700"> โอนเงินไปที่เบอร์ TrueMoney: <strong>091-414-xxxx</strong></p>
            </div>

            <div id="qr_code" style="display:none;">
                <p> สแกน QR Code เพื่อโอนเงิน:</p>
                <img src="./img//qr.jpg" alt="QR Code" width="200px" class="mx-auto my-3 rounded-xl shadow">
            </div>

            <div class="mb-3">
                <label class="form-label">📎 อัปโหลดสลิปการโอนเงิน:</label>
                <input type="file" name="payment_slip" accept="image/*" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-full"> ยืนยันการชำระเงิน</button>
        </form>

        <div class="mt-4">
            <a href="tokenshop.php" class="btn mt-2"> กลับไปหน้าร้านค้า</a>
        </div>
    </div>
</body>

</html>