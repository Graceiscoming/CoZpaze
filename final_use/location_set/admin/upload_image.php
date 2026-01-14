<?php
include '../../config/db_connect.php';
session_start();

// แก้การตรวจสอบสิทธิ์ admin จาก 1 เป็น 2
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../location.php?id=" . $_POST['location_id'] . "&upload_error=" . urlencode('คุณไม่มีสิทธิ์ในการอัพโหลดรูปภาพ'));
    exit();
}

$location_id = isset($_POST['location_id']) ? intval($_POST['location_id']) : null;

if (!$location_id) {
    header("Location: ../location.php?upload_error=" . urlencode('ไม่พบข้อมูลสถานที่'));
    exit();
}

// ตรวจสอบว่ามีการอัพโหลดไฟล์หรือไม่
if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
    header("Location: ../location.php?id=$location_id&upload_error=" . urlencode('กรุณาเลือกรูปภาพ'));
    exit();
}

// สร้างโฟลเดอร์ถ้ายังไม่มี
$upload_path = '../../uploads/locations/';
if (!file_exists($upload_path)) {
    mkdir($upload_path, 0777, true);
}

$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
$max_size = 5 * 1024 * 1024; // 5MB
$success_count = 0;
$error_messages = [];

foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
    $file_name = $_FILES['images']['name'][$key];
    $file_size = $_FILES['images']['size'][$key];
    $file_type = $_FILES['images']['type'][$key];
    
    // ตรวจสอบประเภทไฟล์
    if (!in_array($file_type, $allowed_types)) {
        $error_messages[] = "ไฟล์ $file_name ไม่ใช่รูปภาพที่รองรับ";
        continue;
    }
    
    // ตรวจสอบขนาดไฟล์
    if ($file_size > $max_size) {
        $error_messages[] = "ไฟล์ $file_name มีขนาดใหญ่เกินไป";
        continue;
    }
    
    // สร้างชื่อไฟล์ใหม่
    $new_file_name = uniqid() . '_' . $file_name;
    $upload_file = $upload_path . $new_file_name;
    
    if (move_uploaded_file($tmp_name, $upload_file)) {
        // บันทึกข้อมูลลงฐานข้อมูล
        $relative_path = 'uploads/locations/' . $new_file_name;
        $stmt = $conn->prepare("INSERT INTO location_images (location_id, image_path, uploaded_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $location_id, $relative_path);
        
        if ($stmt->execute()) {
            $success_count++;
        } else {
            $error_messages[] = "ไม่สามารถบันทึกข้อมูลรูป $file_name ลงฐานข้อมูล";
            unlink($upload_file);
        }
        $stmt->close();
    } else {
        $error_messages[] = "ไม่สามารถอัพโหลดไฟล์ $file_name";
    }
}

// ส่งผลลัพธ์กลับ
if ($success_count > 0) {
    header("Location: ../location.php?id=$location_id&upload_success=true");
} else {
    header("Location: ../location.php?id=$location_id&upload_error=" . urlencode(implode(", ", $error_messages)));
}
?>