<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "co_working_pj";

// กำหนด BASE_URL สำหรับจัดการ path
// ถ้าโปรเจคอยู่ในโฟลเดอร์ย่อย (เช่น localhost/webproject) ให้ใส่ชื่อโฟลเดอร์ข้างล่างนี้ เช่น '/webproject'
// ถ้าอยู่ root (localhost/) ให้ปล่อยว่างไว้ ''
if (!defined('BASE_URL')) {
    define('BASE_URL', '');
}


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$conn->set_charset("utf8");

?>