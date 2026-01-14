<?php
include '../config/db_connect.php';
session_start();



if (!isset($_GET['id'])) {
    echo "ไม่พบข้อมูล!";
    exit();
}

$location_id = intval($_GET['id']);
$owner_id = $_SESSION['user_id'];


$sql_check_owner = "SELECT lo.location_id 
                    FROM location_owners lo
                    WHERE lo.location_id = '$location_id' AND lo.user_id = '$owner_id'";

$result_check_owner = mysqli_query($conn, $sql_check_owner);

if (mysqli_num_rows($result_check_owner) == 0) {
    echo "คุณไม่มีสิทธิ์แก้ไขสถานที่นี้";
    exit();
}


$sql = "SELECT * FROM locations WHERE location_id='$location_id'";
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo "เกิดข้อผิดพลาด: " . mysqli_error($conn);
    exit();
}

$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "ไม่พบข้อมูลสถานที่";
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
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../img/location/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
    
        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $fileName;
    
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png'];
    
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                $image_path = $fileName;
            } else {
                echo "อัปโหลดภาพไม่สำเร็จ";
            }
        } else {
            echo "รองรับเฉพาะไฟล์ .jpg, .jpeg, .png เท่านั้น";
        }
    }
    

    $time = isset($_POST['time']) ? implode(',', $_POST['time']) : '';

    $update_sql = "UPDATE locations 
    SET location_name='$name', 
        description='$description', 
        category='$category', 
        price_per_hour='$price', 
        latitude='$latitude', 
        longitude='$longitude', 
        time='$time',
        image_path='$image_path'
    WHERE location_id='$location_id'";

    if (mysqli_query($conn, $update_sql)) {
        header("Location: ../location_set/location.php?id=" . $location_id);
        exit();
    } else {
        echo "เกิดข้อผิดพลาด: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขสถานที่</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #fff5e6 0%, #ffe8cc 100%);
            color: #4a4a4a;
            min-height: 100vh;
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #e67e22;
            font-size: 2.2rem;
            margin-bottom: 30px;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(230, 126, 34, 0.2);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        label {
            color: #4a4a4a;
            font-weight: 500;
            font-size: 1.1rem;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        textarea {
            padding: 12px 15px;
            border: 2px solid rgba(230, 126, 34, 0.2);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus {
            outline: none;
            border-color: #e67e22;
            box-shadow: 0 0 0 3px rgba(230, 126, 34, 0.1);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .image-preview {
            margin: 10px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .time-slots {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .time-slot {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #e67e22;
        }

        button[type="submit"] {
            background: #e67e22;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        button[type="submit"]:hover {
            background: #d35400;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(230, 126, 34, 0.2);
        }

        .back-link {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 25px;
            background: #f8f9fa;
            color: #4a4a4a;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-align: center;
            width: 100%;
            text-decoration: none;
            font-weight: 500;
            border: 2px solid rgba(230, 126, 34, 0.2);
        }

        .back-link:hover {
            background: #e67e22;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(230, 126, 34, 0.2);
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .success {
            background: rgba(46, 204, 113, 0.1);
            color: #27ae60;
            border: 1px solid rgba(46, 204, 113, 0.2);
        }

        .error {
            background: rgba(231, 76, 60, 0.1);
            color: #c0392b;
            border: 1px solid rgba(231, 76, 60, 0.2);
        }

        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
                margin: 20px;
            }

            h1 {
                font-size: 1.8rem;
            }

            .time-slots {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✏️ แก้ไขสถานที่</h1>

        <?php if (isset($_POST['location_name'])): ?>
            <div class="message success">
                อัปเดตข้อมูลสำเร็จ
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>ชื่อสถานที่:</label>
                <input type="text" name="location_name" value="<?= htmlspecialchars($data['location_name']); ?>" required>
            </div>

            <div class="form-group">
                <label>คำอธิบาย:</label>
                <textarea name="description" required><?= htmlspecialchars($data['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label>รูปภาพ:</label>
                <?php if (!empty($data['image_path'])): ?>
                    <img src="../img/location/<?= htmlspecialchars($data['image_path']); ?>" alt="รูปสถานที่" class="image-preview">
                <?php endif; ?>
                <input type="file" name="image">
            </div>

            <div class="form-group">
                <label>หมวดหมู่:</label>
                <input type="text" name="category" value="<?= htmlspecialchars($data['category']); ?>" required>
            </div>

            <div class="form-group">
                <label>ราคาต่อชั่วโมง:</label>
                <input type="number" step="0.01" name="price_per_hour" value="<?= $data['price_per_hour']; ?>" required>
            </div>

            <div class="form-group">
                <label>ละติจูด:</label>
                <input type="text" name="latitude" value="<?= $data['latitude']; ?>" required>
            </div>

            <div class="form-group">
                <label>ลองจิจูด:</label>
                <input type="text" name="longitude" value="<?= $data['longitude']; ?>" required>
            </div>

            <div class="form-group">
                <label>เวลาที่เปิดให้จอง:</label>
                <div class="time-slots">
                    <?php
                    $available_times = explode(',', $data['time']);
                    for ($hour = 10; $hour <= 20; $hour += 2) {
                        $start = sprintf("%02d:00", $hour);
                        $end = sprintf("%02d:00", $hour + 2);
                        $time_slot = "$start-$end";
                        $checked = in_array($time_slot, $available_times) ? 'checked' : '';
                        echo "<div class='time-slot'>
                                <input type='checkbox' name='time[]' value='$time_slot' $checked>
                                <span>$time_slot</span>
                              </div>";
                    }
                    ?>
                </div>
            </div>

            <button type="submit">บันทึกการเปลี่ยนแปลง</button>
        </form>

    </div>
</body>
</html>