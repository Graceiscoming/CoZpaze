<?php
include '../config/db_connect.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    die(" กรุณาเข้าสู่ระบบก่อน");
}

$user_id = $_SESSION['user_id'];


$sql = "SELECT COUNT(*) AS is_owner FROM location_owners WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if ($row['is_owner'] == 0) {
    die(" คุณไม่มีสิทธิ์เข้าถึงหน้านี้");
}


$sql_owner = "SELECT user_name FROM userinfo WHERE user_id = ?";
$stmt_owner = mysqli_prepare($conn, $sql_owner);
mysqli_stmt_bind_param($stmt_owner, "i", $user_id);
mysqli_stmt_execute($stmt_owner);
$result_owner = mysqli_stmt_get_result($stmt_owner);
$owner_name = "ไม่ทราบชื่อ";

if ($row_owner = mysqli_fetch_assoc($result_owner)) {
    $owner_name = $row_owner['user_name'];
}

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แดชบอร์ดเจ้าของธุรกิจ</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            color: #2c3e50;
            min-height: 100vh;
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        h1 {
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 20px;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            letter-spacing: 1px;
        }

        p {
            color: #34495e;
            font-size: 1.2rem;
            text-align: center;
            margin-bottom: 40px;
        }

        ul {
            list-style: none;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 0;
        }

        li {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        li:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        a {
            display: block;
            padding: 25px;
            color: #2c3e50;
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        a::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(52, 152, 219, 0.1), transparent);
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        a:hover::before {
            transform: translateX(0);
        }

        a:hover {
            color: #3498db;
            padding-left: 30px;
        }

        .logout-link {
            background: rgba(231, 76, 60, 0.1);
            border-color: rgba(231, 76, 60, 0.2);
        }

        .logout-link:hover {
            background: rgba(231, 76, 60, 0.15);
        }

        .logout-link a:hover {
            color: #e74c3c;
        }

        .user-link {
            background: rgba(46, 204, 113, 0.1);
            border-color: rgba(46, 204, 113, 0.2);
        }

        .user-link:hover {
            background: rgba(46, 204, 113, 0.15);
        }

        .user-link a:hover {
            color: #2ecc71;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            h1 {
                font-size: 2rem;
            }

            ul {
                grid-template-columns: 1fr;
            }

            a {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1> ยินดีต้อนรับ, <?php echo htmlspecialchars($owner_name); ?></h1>
        <p>คุณเป็นเจ้าของธุรกิจ! CoZpaze ยินดีต้อนรับ</p>

        <ul>
            <li><a href="check_booking.php">จัดการการจอง</a></li>
            <li><a href="manage_location.php">จัดการสถานที่</a></li>
            <li><a href="booking_confirm.php">ยืนยันการจอง</a></li>
            <li><a href="check_wallet.php">เช็คยอดแต่ละที่</a></li>
            <li class="logout-link"><a href="<?php echo BASE_URL; ?>/final_use/login_status/logout.php">ออกจากระบบ</a>
            </li>
            <li class="user-link"><a href="<?php echo BASE_URL; ?>/index.php">ไปหน้าผู้ใช้ปกติ</a></li>
        </ul>
    </div>
</body>

</html>