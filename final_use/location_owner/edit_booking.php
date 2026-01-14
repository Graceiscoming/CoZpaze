<?php
include '../config/db_connect.php';
session_start();



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
    header("Location:../location_owner/check_booking.php?error=invalid_id");
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
        header("Location: ../location_owner/check_booking.php?success=updated");
    } else {
        header("Location: ../location_owner/check_booking.php?error=update_failed");
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขการจอง</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
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
            color: #d35400;
            font-size: 1.1rem;
            font-weight: 500;
        }

        input[type="date"],
        input[type="time"],
        select {
            padding: 12px 15px;
            border: 2px solid rgba(230, 126, 34, 0.2);
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Prompt', sans-serif;
            color: #4a4a4a;
            background: white;
            transition: all 0.3s ease;
        }

        input[type="date"]:focus,
        input[type="time"]:focus,
        select:focus {
            outline: none;
            border-color: #e67e22;
            box-shadow: 0 0 0 3px rgba(230, 126, 34, 0.1);
        }

        button {
            background: #e67e22;
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Prompt', sans-serif;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        button:hover {
            background: #d35400;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(230, 126, 34, 0.2);
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #e67e22;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            text-align: center;
            width: 100%;
        }

        .back-link:hover {
            color: #d35400;
        }

        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
                margin: 20px;
            }

            h1 {
                font-size: 1.8rem;
            }

            input[type="date"],
            input[type="time"],
            select {
                padding: 10px 12px;
            }

            button {
                padding: 12px 20px;
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
                <input type="time" name="booking_start_time" value="<?php echo $data['booking_start_time']; ?>" required>
            </div>

            <div class="form-group">
                <label>เวลาสิ้นสุด:</label>
                <input type="time" name="booking_end_time" value="<?php echo $data['booking_end_time']; ?>" required>
            </div>

            <div class="form-group">
                <label>สถานะ:</label>
                <select name="status">
                    <option value="pending" <?php if ($data['status'] == "pending") echo "selected"; ?>>รอการยืนยัน</option>
                    <option value="confirmed" <?php if ($data['status'] == "confirmed") echo "selected"; ?>>ยืนยันแล้ว</option>
                    <option value="canceled" <?php if ($data['status'] == "canceled") echo "selected"; ?>>ยกเลิก</option>
                </select>
            </div>

            <button type="submit">บันทึกการเปลี่ยนแปลง</button>
        </form>

        <a href="check_booking.php" class="back-link">← กลับไปหน้าการจอง</a>
    </div>
</body>
</html>
