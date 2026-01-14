<?php
include '../config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("กรุณาเข้าสู่ระบบ");
}

$user_id = $_SESSION['user_id'];
$location_id = $_POST['location_id'];
$rating = $_POST['rating'];
$review_text = $_POST['review_text'];


$check_sql = "SELECT COUNT(*) as review_count FROM reviews WHERE user_id = '$user_id' AND location_id = '$location_id'";
$result = mysqli_query($conn, $check_sql);
$row = mysqli_fetch_assoc($result);

if ($row['review_count'] >= 5) {
    echo "<script>
        alert('คุณสามารถรีวิวสถานที่นี้ได้ไม่เกิน 5 ครั้ง');
        window.history.back();
    </script>";
    exit;
}


$sql = "INSERT INTO reviews (user_id, location_id, rating, review_text) 
        VALUES ('$user_id', '$location_id', '$rating', '$review_text')";

if (mysqli_query($conn, $sql)) {
    header("Location: ../location_set/location.php?id=$location_id");
} else {
    echo "เกิดข้อผิดพลาด: " . mysqli_error($conn);
}
?>
