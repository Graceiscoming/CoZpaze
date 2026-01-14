<?php
include '../../config/db_connect.php';
session_start();

// แก้การตรวจสอบสิทธิ์ admin จาก 1 เป็น 2
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../location.php?id=" . $_POST['location_id'] . "&error=unauthorized");
    exit();
}

$image_id = isset($_POST['image_id']) ? intval($_POST['image_id']) : null;
$location_id = isset($_POST['location_id']) ? intval($_POST['location_id']) : null;

if (!$image_id || !$location_id) {
    header("Location: ../location.php?id=$location_id&error=missing_data");
    exit();
}

// ดึงข้อมูลรูปภาพก่อนลบ
$stmt = $conn->prepare("SELECT image_path FROM location_images WHERE image_id = ? AND location_id = ?");
$stmt->bind_param("ii", $image_id, $location_id);
$stmt->execute();
$result = $stmt->get_result();
$image = $result->fetch_assoc();

if (!$image) {
    header("Location: ../location.php?id=$location_id&error=image_not_found");
    exit();
}

// ลบไฟล์รูปภาพ
$image_path = '../../' . $image['image_path'];
if (file_exists($image_path)) {
    unlink($image_path);
}

// ลบข้อมูลจากฐานข้อมูล
$stmt = $conn->prepare("DELETE FROM location_images WHERE image_id = ? AND location_id = ?");
$stmt->bind_param("ii", $image_id, $location_id);

if ($stmt->execute()) {
    header("Location: ../location.php?id=$location_id&delete_success=true");
} else {
    header("Location: ../location.php?id=$location_id&error=delete_failed");
}

$stmt->close();
$conn->close();
?> 