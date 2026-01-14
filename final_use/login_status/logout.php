<?php
session_start();
session_destroy(); // ลบ session ทั้งหมด

//  ลบ Cookie ที่ใช้เก็บข้อมูลผู้ใช้ 
setcookie("user_id", "", time() - 3600, "/");
setcookie("user_name", "", time() - 3600, "/");
setcookie("role_id", "", time() - 3600, "/");

header("Location: /index.php");
exit();
?>
