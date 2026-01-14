<?php
include '../config/db_connect.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    header('Location: /final_use/user/user_login.php');
    exit();
}


if (!isset($_POST['location_id'])) {
    die('Location ID is required');
}

$location_id = $_POST['location_id'];
$upload_dir = '../reviews/img/';


if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
    $files = $_FILES['images'];
    $uploaded_files = [];
    $errors = [];


    if (count($files['name']) > 5) {
        $response['message'] = 'สามารถอัพโหลดได้สูงสุด 5 ไฟล์';
        echo json_encode($response);
        exit();
    }


    for ($i = 0; $i < count($files['name']); $i++) {
        $file_name = $files['name'][$i];
        $file_tmp = $files['tmp_name'][$i];
        $file_size = $files['size'][$i];
        $file_error = $files['error'][$i];


        if ($file_error === UPLOAD_ERR_OK) {

            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($file_ext, $allowed_ext)) {
                $errors[] = "ไฟล์ $file_name ไม่ใช่รูปภาพที่อนุญาต";
                continue;
            }


            if ($file_size > 5242880) {
                $errors[] = "ไฟล์ $file_name มีขนาดใหญ่เกินไป";
                continue;
            }


            $new_file_name = uniqid() . '_' . time() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $upload_path)) {
                $uploaded_files[] = $new_file_name;
                

                $image_path = 'reviews/img/' . $new_file_name;
                $stmt = $conn->prepare("INSERT INTO location_images (location_id, image_path) VALUES (?, ?)");
                $stmt->bind_param("is", $location_id, $image_path);
                
                if (!$stmt->execute()) {
                    $errors[] = "ไม่สามารถบันทึกข้อมูล $file_name ลงฐานข้อมูลได้";
                    unlink($upload_path); 
                }
                
                $stmt->close();
            } else {
                $errors[] = "ไม่สามารถอัพโหลดไฟล์ $file_name ได้";
            }
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการอัพโหลดไฟล์ $file_name";
        }
    }

    if (count($uploaded_files) > 0) {
        $response['success'] = true;
        $response['message'] = 'อัพโหลดไฟล์สำเร็จ ' . count($uploaded_files) . ' ไฟล์';
        if (count($errors) > 0) {
            $response['message'] .= ' (มีข้อผิดพลาด: ' . implode(', ', $errors) . ')';
        }
    } else {
        $response['message'] = 'ไม่สามารถอัพโหลดไฟล์ได้: ' . implode(', ', $errors);
    }
} else {
    $response['message'] = 'ไม่พบไฟล์ที่อัพโหลด';
}

echo json_encode($response);
?>
