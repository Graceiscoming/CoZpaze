<?php
session_start();
include '../config/db_connect.php'; 
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "กรุณาเข้าสู่ระบบ"]);
    exit();
}


$username = isset($_GET['username']) ? $_GET['username'] : null;

if ($username) {

    $stmt = $conn->prepare("SELECT user_id FROM userinfo WHERE user_name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($user_id);
    $stmt->fetch();
    $stmt->close();

    if (!$user_id) {
        echo json_encode(["error" => "ไม่พบผู้ใช้ที่มี Username นี้"]);
        exit();
    }
} else {
    $user_id = $_SESSION['user_id'];
}


$result = $conn->prepare("SELECT balance FROM wallets WHERE user_id = ?");
$result->bind_param("i", $user_id);
$result->execute();
$result->bind_result($balance);
$result->fetch();
$result->close();

if ($balance === null) {
    echo json_encode(["error" => "ไม่พบข้อมูลของคุณ"]);
    exit();
}

echo json_encode(["balance" => number_format($balance, 2) . " บาท"]);
$conn->close();
?>
