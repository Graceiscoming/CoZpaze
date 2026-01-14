<?php
session_start();
include '../config/db_connect.php';

header('Content-Type: application/json');


if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../../index.php");
    exit();
}



if (!$conn) {
    echo json_encode(["error" => "เชื่อมต่อฐานข้อมูลไม่ได้"]);
    exit();
}


$sql = "SELECT userinfo.user_id, userinfo.user_name, wallets.balance 
        FROM userinfo
        INNER JOIN wallets ON userinfo.user_id = wallets.user_id";

$result = mysqli_query($conn, $sql);


if (!$result) {
    echo json_encode(["error" => "เกิดข้อผิดพลาดในการดึงข้อมูล: " . mysqli_error($conn)]);
    exit();
}

$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = [
        "user_id" => $row["user_id"],
        "user_name" => $row["user_name"],
        "balance" => $row["balance"]
    ];
}


if (empty($users)) {
    echo json_encode(["error" => "ไม่พบข้อมูลในฐานข้อมูล"]);
    exit();
}


echo json_encode(["users" => $users]);
?>
