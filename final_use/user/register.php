<?php
include __DIR__ . '/../../final_use/config/db_connect.php';
session_start(); 

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = $_POST["user_name"];
    $user_pass = password_hash($_POST["user_pass"], PASSWORD_BCRYPT);
    $user_mail = $_POST["user_mail"];
    $university_name = $_POST["university_name"];
    $phone_num = $_POST["phone_num"] ?? null;
    $reset_question = $_POST["reset_question"];
    $reset_token = $_POST["reset_token"];


    $check_user = $conn->prepare("SELECT user_id, user_name, user_mail FROM userinfo
     WHERE user_name = ? OR user_mail = ?");
    $check_user->bind_param("ss", $user_name, $user_mail);
    $check_user->execute();
    $check_user->store_result();

    if ($check_user->num_rows > 0) {
        $check_user->bind_result($db_user_id, $db_user_name, $db_user_mail);
        $check_user->fetch();

        if ($db_user_name === $user_name) {
            echo "<script>alert('ชื่อผู้ใช้นี้ถูกใช้ไปแล้ว!'); window.history.back();</script>";
        } elseif ($db_user_mail === $user_mail) {
            echo "<script>alert('อีเมลนี้ถูกใช้ไปแล้ว!'); window.history.back();</script>";
        }
    } else {

        $stmt = $conn->prepare("INSERT INTO userinfo (user_name, user_pass, user_mail, university_name, 
        phone_num, reset_question, reset_token, role_id) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("sssssss", $user_name, $user_pass, $user_mail, $university_name, $phone_num, 
        $reset_question, $reset_token);

        if ($stmt->execute()) {

            $user_id = $conn->insert_id;


            $stmt_wallet = $conn->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, 0)");
            $stmt_wallet->bind_param("i", $user_id);

            if ($stmt_wallet->execute()) {
                echo "<script>alert('สมัครสมาชิกสำเร็จ!'); window.location.href='./user_login.php';</script>";
            } else {
                echo "เกิดข้อผิดพลาดในการสร้าง Wallet: " . $stmt_wallet->error;
            }

            $stmt_wallet->close();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $check_user->close();
}

$conn->close();
?>
