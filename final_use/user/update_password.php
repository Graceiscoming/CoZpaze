<?php
include '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = $_POST["user_name"];
    $new_password = password_hash($_POST["new_password"], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("UPDATE UserInfo SET user_pass = ? WHERE user_name = ?");
    $stmt->bind_param("ss", $new_password, $user_name);

    if ($stmt->execute()) {
        echo "<script>alert('เปลี่ยนรหัสผ่านสำเร็จ!'); window.location.href='" . BASE_URL . "/index.php';</script>";
    } else {
        echo "เกิดข้อผิดพลาด: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>