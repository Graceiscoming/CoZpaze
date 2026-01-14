<?php
include '../config/db_connect.php';
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../../index.php");
    exit();
}


if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ไม่พบข้อมูล!";
    header("Location: admin_managelocation.php");
    exit();
}

$location_id = intval($_GET['id']);


$sql = "SELECT * FROM locations WHERE location_id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $location_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    $_SESSION['error'] = "เกิดข้อผิดพลาด: " . mysqli_error($conn);
    header("Location: admin_managelocation.php");
    exit();
}

$data = mysqli_fetch_assoc($result);

if (!$data) {
    $_SESSION['error'] = "ไม่พบข้อมูลสถานที่";
    header("Location: admin_managelocation.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['location_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price = floatval($_POST['price_per_hour']);
    $latitude = floatval($_POST['latitude']);
    $longitude = floatval($_POST['longitude']);
    $image_path = $data['image_path'];


    if (empty($name) || empty($description) || empty($category)) {
        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } else {

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $targetDir = "../img/location/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $fileName = time() . "_" . basename($_FILES["image"]["name"]);
            $targetFilePath = $targetDir . $fileName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png'];
            $maxFileSize = 5 * 1024 * 1024; // 5MB

            if ($_FILES['image']['size'] > $maxFileSize) {
                $_SESSION['error'] = "ไฟล์ภาพมีขนาดใหญ่เกินไป (สูงสุด 5MB)";
            } elseif (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {

                    if (!empty($data['image_path']) && file_exists($targetDir . $data['image_path'])) {
                        unlink($targetDir . $data['image_path']);
                    }
                    $image_path = $fileName;
                } else {
                    $_SESSION['error'] = "อัปโหลดภาพไม่สำเร็จ";
                }
            } else {
                $_SESSION['error'] = "รองรับเฉพาะไฟล์ .jpg, .jpeg, .png เท่านั้น";
            }
        }

        $time = $_POST['time'] ?? '';


        $update_sql = "UPDATE locations 
        SET location_name=?, 
            description=?, 
            category=?, 
            price_per_hour=?, 
            latitude=?, 
            longitude=?, 
            time=?,
            image_path=?
        WHERE location_id=?";

        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "sssdssssi", $name, $description, $category, $price, $latitude, $longitude, $time, $image_path, $location_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "แก้ไขข้อมูลสถานที่สำเร็จ";
            header("Location: admin_managelocation.php");
            exit();
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาด: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขสถานที่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #1a1a1a;
            color: #ffffff;
        }
        .container {
            background-color: #2d2d2d;
            padding: 2rem;
            border-radius: 10px;
            margin-top: 2rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        .form-control {
            background-color: #3d3d3d;
            border: 1px solid #4d4d4d;
            color: #ffffff;
        }
        .form-control:focus {
            background-color: #3d3d3d;
            border-color: #ffd700;
            color: #ffffff;
            box-shadow: 0 0 0 0.25rem rgba(255, 215, 0, 0.25);
        }
        .btn-primary {
            background-color: #ffd700;
            border-color: #ffd700;
            color: #000000;
        }
        .btn-primary:hover {
            background-color: #ffc800;
            border-color: #ffc800;
            color: #000000;
        }
        .btn-secondary {
            background-color: #3d3d3d;
            border-color: #4d4d4d;
            color: #ffffff;
        }
        .btn-secondary:hover {
            background-color: #4d4d4d;
            border-color: #5d5d5d;
            color: #ffffff;
        }
        .alert {
            background-color: #3d3d3d;
            border: 1px solid #4d4d4d;
            color: #ffffff;
        }
        .alert-danger {
            border-color: #dc3545;
        }
        .alert-success {
            border-color: #198754;
        }
        h2 {
            color: #ffd700;
            margin-bottom: 1.5rem;
        }
        .form-label {
            color: #ffd700;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>แก้ไขสถานที่</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="location_name" class="form-label">ชื่อสถานที่</label>
                <input type="text" class="form-control" id="location_name" name="location_name" value="<?php echo htmlspecialchars($data['location_name']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">รายละเอียด</label>
                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($data['description']); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="category" class="form-label">ประเภท</label>
                <input type="text" class="form-control" id="category" name="category" value="<?php echo htmlspecialchars($data['category']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="price_per_hour" class="form-label">ราคาต่อชั่วโมง</label>
                <input type="number" step="0.01" class="form-control" id="price_per_hour" name="price_per_hour" value="<?php echo htmlspecialchars($data['price_per_hour']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="latitude" class="form-label">ละติจูด</label>
                <input type="number" step="0.000001" class="form-control" id="latitude" name="latitude" value="<?php echo htmlspecialchars($data['latitude']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="longitude" class="form-label">ลองจิจูด</label>
                <input type="number" step="0.000001" class="form-control" id="longitude" name="longitude" value="<?php echo htmlspecialchars($data['longitude']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="time" class="form-label">เวลาเปิด-ปิด</label>
                <input type="text" class="form-control" id="time" name="time" value="<?php echo htmlspecialchars($data['time']); ?>">
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">รูปภาพ</label>
                <?php if (!empty($data['image_path'])): ?>
                    <div class="mb-2">
                        <img src="../img/location/<?php echo htmlspecialchars($data['image_path']); ?>" alt="Current Image" style="max-width: 200px;">
                    </div>
                <?php endif; ?>
                <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/jpg">
            </div>

            <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
            <a href="admin_managelocation.php" class="btn btn-secondary">ยกเลิก</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
