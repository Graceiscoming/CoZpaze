<?php
include '../config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

if (isset($_GET['id'])) {
    $booking_id = $_GET['id'];
    $sql = "SELECT * FROM booking WHERE booking_id = '$booking_id'";
    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_assoc($result);

    if (!$data) {
        echo "ไม่พบข้อมูล!";
        exit();
    }
} else {
    header("Location: admin_bookings.php?error=invalid_id");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_date = $_POST['booking_date'];
    $booking_start_time = $_POST['booking_start_time'];
    $booking_end_time = $_POST['booking_end_time'];
    $status = $_POST['status'];

    $update_sql = "UPDATE booking SET 
                   booking_date = '$booking_date', 
                   booking_start_time = '$booking_start_time', 
                   booking_end_time = '$booking_end_time', 
                   status = '$status' 
                   WHERE booking_id = '$booking_id'";

    if (mysqli_query($conn, $update_sql)) {
        header("Location: ../admin/admin_bookings.php?success=updated");
    } else {
        header("Location: ../admin/admin_bookings.php?error=update_failed");
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขการจอง</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #e0e0e0;
            min-height: 100vh;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }

        h1 {
            font-size: 2.2rem;
            color: #d4af37;
            margin-bottom: 30px;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            letter-spacing: 1px;
            font-weight: 600;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        label {
            color: #c0a36e;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        input,
        select {
            padding: 12px 15px;
            border-radius: 12px;
            border: 1px solid rgba(212, 175, 55, 0.3);
            background: rgba(255, 255, 255, 0.05);
            color: #e0e0e0;
            font-family: 'Prompt', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: rgba(212, 175, 55, 0.5);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.2);
        }

        button {
            background: rgba(212, 175, 55, 0.1);
            color: #d4af37;
            border: 1px solid rgba(212, 175, 55, 0.3);
            padding: 15px 30px;
            border-radius: 12px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Prompt', sans-serif;
            margin-top: 20px;
        }

        button:hover {
            background: rgba(212, 175, 55, 0.2);
            color: #ffd700;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.2);
            border-color: rgba(212, 175, 55, 0.5);
        }

        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 25px;
            background: rgba(212, 175, 55, 0.1);
            color: #d4af37;
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 12px;
            text-decoration: none;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-align: center;
        }

        .back-button:hover {
            background: rgba(212, 175, 55, 0.2);
            color: #ffd700;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.2);
            border-color: rgba(212, 175, 55, 0.5);
        }

        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 30px 20px;
            }

            h1 {
                font-size: 1.8rem;
            }

            input,
            select,
            button {
                padding: 10px 12px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>แก้ไขการจอง</h1>

        <form action="" method="POST">
            <div class="form-group">
                <label>วันที่จอง:</label>
                <input type="date" name="booking_date" value="<?php echo $data['booking_date']; ?>" required>
            </div>

            <div class="form-group">
                <label>เวลาเริ่ม:</label>
                <input type="time" name="booking_start_time" value="<?php echo $data['booking_start_time']; ?>"
                    required>
            </div>

            <div class="form-group">
                <label>เวลาสิ้นสุด:</label>
                <input type="time" name="booking_end_time" value="<?php echo $data['booking_end_time']; ?>" required>
            </div>

            <div class="form-group">
                <label>สถานะ:</label>
                <select name="status">
                    <option value="pending" <?php if ($data['status'] == "pending")
                        echo "selected"; ?>>รอการยืนยัน
                    </option>
                    <option value="confirmed" <?php if ($data['status'] == "confirmed")
                        echo "selected"; ?>>ยืนยันแล้ว
                    </option>
                    <option value="canceled" <?php if ($data['status'] == "canceled")
                        echo "selected"; ?>>ยกเลิก</option>
                </select>
            </div>

            <button type="submit">บันทึกการเปลี่ยนแปลง</button>
        </form>

        <a href="<?php echo BASE_URL; ?>/final_use/admin/admin_bookings.php" class="back-button">⬅️
            กลับไปยังรายการจอง</a>
    </div>
</body>

</html>