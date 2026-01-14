<?php
include '../login_status/require_login.php'; 
include '../config/db_connect.php';
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ค้นหาสถานที่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-3">
    <h1 class="text-center">ค้นหาสถานที่ตามมหาวิทยาลัย</h1>
    <form action="index.php" method="GET" class="d-flex justify-content-center">
        <input type="text" name="keyword" placeholder="กรอกชื่อมหาวิทยาลัย" required class="form-control w-50 me-2">
        <button type="submit" class="btn btn-primary">ค้นหา</button>
    </form>
</div>

</body>
</html>
