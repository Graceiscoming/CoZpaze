<?php
include '../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $location_name = mysqli_real_escape_string($conn, $_POST['location_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $latitude = floatval($_POST['latitude']);
    $longitude = floatval($_POST['longitude']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price_per_hour = floatval($_POST['price_per_hour']);
    $uni = mysqli_real_escape_string($conn, $_POST['uni']);
    $link = mysqli_real_escape_string($conn, $_POST['link']);

    $time = isset($_POST['time']) ? implode(',', $_POST['time']) : '';


    $image_path = ""; 
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../img/location/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true); 
        }

        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $image_path = $targetDir . $fileName; 

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            $image_path = "../img/location/" . $fileName;
        }else {
            echo "Error uploading file.";
    }
}


    $sql = "INSERT INTO locations (location_name, description, latitude, longitude, category, price_per_hour, uni, time, link, image_path) 
            VALUES ('$location_name', '$description', '$latitude', '$longitude', '$category', '$price_per_hour', '$uni', '$time', '$link', '$image_path')";

    if (mysqli_query($conn, $sql)) {
        header("Location: ../admin/admin_dashboard.php?success=location_added");
        exit();
    } else {
        echo "เกิดข้อผิดพลาด: " . mysqli_error($conn);
    }
}
?>
