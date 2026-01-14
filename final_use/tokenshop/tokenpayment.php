<?php 
session_start();
include '../config/db_connect.php';

$user_id = $_SESSION['user_id'];
$price = isset($_GET['price']) ? (int)$_GET['price'] : 0;
$method = isset($_POST['method']) ? $_POST['method'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['slip'])) {
    $target_dir = "user_upload/";
    $max_file_size = 2 * 1024 * 1024; // จำกัดขนาดไฟล์ 2MB
    $allowed_types = ['image/jpeg', 'image/png'];

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_type = $_FILES["slip"]["type"];
    $file_size = $_FILES["slip"]["size"];
    $file_name = time() . "_" . basename($_FILES["slip"]["name"]);
    $target_file = $target_dir . $file_name;

    if (!in_array($file_type, $allowed_types)) {
        echo "<script>alert('อนุญาตเฉพาะไฟล์ JPG และ PNG เท่านั้น!'); window.history.back();</script>";
        exit();
    }

    if ($file_size > $max_file_size) {
        echo "<script>alert('ไฟล์ใหญ่เกินไป! กรุณาอัปโหลดไฟล์ที่มีขนาดไม่เกิน 2MB'); window.history.back();</script>";
        exit();
    }

    if (move_uploaded_file($_FILES["slip"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO transactions (user_id, transaction_type, amount, transaction_date, slip_image) 
                VALUES (?, 'credit', ?, NOW(), ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ids", $user_id, $price, $file_name);
        $stmt->execute();

        echo "<script>alert('อัปโหลดสลิปเรียบร้อย! กรุณารอการตรวจสอบ'); window.location.href='tokenshop.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการอัปโหลดสลิป');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อัปโหลดสลิป</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>การชำระเงิน</h1>
    <p>ราคา: <?php echo number_format($price, 2); ?> บาท</p>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="price" value="<?php echo $price; ?>">

        <h2>เลือกวิธีชำระเงิน</h2>
        <label>
            <input type="radio" name="method" value="PromptPay" onclick="showUploadSection('promptpay.png')" required> PromptPay
        </label>
        <label>
            <input type="radio" name="method" value="TrueMoney" onclick="showUploadSection('truemoney.png')" required> TrueMoney
        </label>

        <div id="upload-section" style="display: none;">
            <h3>สแกน QR Code</h3>
            <img id="qr-image" src="" alt="QR Code" width="200px">
            <h3>อัปโหลดสลิป</h3>
            <input type="file" name="slip" accept="image/*" required>
            <br>
            <button type="submit">อัปโหลดสลิป</button>
        </div>
    </form>

    <script>
        function showUploadSection(image) {
            document.getElementById('upload-section').style.display = 'block';
            document.getElementById('qr-image').src = image;
        }
    </script>
</body>
</html>
