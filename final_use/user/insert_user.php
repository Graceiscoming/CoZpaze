<?php
include '../config/db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = $_POST["user_name"];
    $user_pass = password_hash($_POST["user_pass"], PASSWORD_BCRYPT); 
    $user_mail = $_POST["user_mail"];
    $university_name = $_POST["university_name"];
    $phone_num = $_POST["phone_num"] ?? null; 

    $stmt = $conn->prepare("INSERT INTO UserInfo (user_name, user_pass, user_mail, university_name, phone_num) VALUES (?, ?, ?, ?, ?)"); #ใช้ prepare() เพื่อป้องกัน SQL Injection และ ใช้เครื่องหมาย ? เป็นตัวแทนค่าที่จะถูกเพิ่มเข้าไปในฐานข้อมูล
    $stmt->bind_param("sssss", $user_name, $user_pass, $user_mail, $university_name, $phone_num); #bind_param("sssss", ...) กำหนดค่าที่จะถูกแทนที่ใน ?

    if ($stmt->execute()) { #รันคำสั่ง SQL ที่เตรียมไว้
        echo "Successfully Register!";
    } else {
        echo "Error: " . $stmt->error; #แสดงข้อความ Error ถ้าไม่สามารถเพิ่มข้อมูลได้
    }

    $stmt->close();
}

$conn->close();
?>
