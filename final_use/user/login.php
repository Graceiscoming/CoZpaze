<?php
include '../config/db_connect.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_name = trim($_POST["user_name"]);
    $user_pass = trim($_POST["user_pass"]);

    if (empty($user_name) || empty($user_pass)) {
        echo json_encode(["success" => false, "message" => "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน"]);
        exit();
    }

    $stmt = $conn->prepare("SELECT user_id, user_name, user_pass, role_id 
    FROM UserInfo WHERE user_name = ?");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $db_user_name, $hashed_pass, $role_id);
        $stmt->fetch();

        if (password_verify($user_pass, $hashed_pass)) {
            $_SESSION["user_id"] = $user_id;
            $_SESSION["user_name"] = $db_user_name;
            $_SESSION["role_id"] = $role_id;

            $check_wallet = $conn->prepare("SELECT 1 FROM wallets WHERE user_id = ?");
            $check_wallet->bind_param("i", $user_id);
            $check_wallet->execute();
            $check_wallet->store_result();

            if ($check_wallet->num_rows === 0) {
                $insert_wallet = $conn->prepare("INSERT INTO wallets (user_id, balance) 
                VALUES (?, 0.00)");
                $insert_wallet->bind_param("i", $user_id);
                $insert_wallet->execute();
                $insert_wallet->close();
            }

            $check_wallet->close();
            $stmt->close();
            $conn->close();

            $redirect_url = ($role_id == 2) ? BASE_URL . "/final_use/admin/admin_dashboard.php" : BASE_URL . "/index.php";

            echo json_encode(["success" => true, "redirect" => $redirect_url]);
            exit();
        } else {
            echo json_encode(["success" => false, "message" => "รหัสผ่านไม่ถูกต้อง!"]);
            exit();
        }
    } else {
        echo json_encode(["success" => false, "message" => "ไม่พบบัญชีผู้ใช้นี้!"]);
        exit();
    }

    $stmt->close();
}

$conn->close();
?>