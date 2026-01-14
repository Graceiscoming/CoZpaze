<?php
session_start();
include '../config/db_connect.php'; 


if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    echo json_encode(["error" => "❌ คุณไม่มีสิทธิ์เข้าถึง"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST["user_id"]) || !isset($_POST["amount"]) || !isset($_POST["transaction_type"])) {
        echo json_encode(["error" => "❌ ข้อมูลไม่ครบถ้วน"]);
        exit();
    }


    $user_id = intval($_POST["user_id"]);
    $amount = floatval($_POST["amount"]);
    $type = $_POST["transaction_type"];

    if ($amount <= 0) {
        echo json_encode(["error" => "❌ จำนวนเงินต้องมากกว่า 0"]);
        exit();
    }


    $stmt = $conn->prepare("SELECT balance FROM wallets WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($balance);
    $stmt->fetch();
    $stmt->close();

    if ($balance === null) {
        echo json_encode(["error" => "❌ ไม่พบผู้ใช้ที่มี User ID นี้"]);
        exit();
    }

    // คำนวณยอดเงินใหม่
    if ($type === "credit") {
        $new_balance = $balance + $amount;
    } elseif ($type === "debit") {
        if ($balance < $amount) {
            echo json_encode(["error" => "❌ ยอดเงินไม่เพียงพอ"]);
            exit();
        }
        $new_balance = $balance - $amount;
    } else {
        echo json_encode(["error" => "❌ ประเภทธุรกรรมไม่ถูกต้อง"]);
        exit();
    }

    // อัปเดตยอดเงิน
    $update_stmt = $conn->prepare("UPDATE wallets SET balance = ? WHERE user_id = ?");
    $update_stmt->bind_param("di", $new_balance, $user_id);

    if ($update_stmt->execute()) {

        $insert_stmt = $conn->prepare("INSERT INTO transactions (user_id, transaction_type, amount, transaction_date) VALUES (?, ?, ?, NOW())");
        $insert_stmt->bind_param("isd", $user_id, $type, $amount);

        if ($insert_stmt->execute()) {
            echo json_encode([
                "message" => "ธุรกรรมสำเร็จ! ยอดเงินปัจจุบันของ User ID $user_id: " . number_format($new_balance, 2) . " บาท",
                "new_balance" => number_format($new_balance, 2) . " บาท"
            ]);
        } else {
            echo json_encode(["error" => " เกิดข้อผิดพลาดในการบันทึกธุรกรรม"]);
        }

        $insert_stmt->close();
    } else {
        echo json_encode(["error" => " เกิดข้อผิดพลาดในการอัปเดตยอดเงิน"]);
    }

    $update_stmt->close();
}

$conn->close();
?>
