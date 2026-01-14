<?php
session_start();
include '../config/db_connect.php';


if (!isset($_SESSION['user_id'])) {
    die("❌ กรุณาเข้าสู่ระบบก่อน");
}

$owner_id = $_SESSION['user_id'];


$query = "
    SELECT b.booking_id, b.user_id, u.user_name, b.location_id, l.location_name, b.booking_time, b.booking_date, b.total_price, b.status
    FROM booking b
    JOIN userinfo u ON b.user_id = u.user_id
    JOIN location_owners lo ON b.location_id = lo.location_id
    JOIN locations l ON b.location_id = l.location_id
    WHERE b.status = 'Pending' AND lo.user_id = ?
    ORDER BY l.location_name, b.booking_date, b.booking_time
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();

$bookings_by_location = [];
while ($row = $result->fetch_assoc()) {
    $bookings_by_location[$row['location_name']][] = $row;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];

    if (isset($_POST['confirm'])) {

        $update_stmt = $conn->prepare("UPDATE booking SET status = 'Confirmed' WHERE booking_id = ?");
        $update_stmt->bind_param("i", $booking_id);

        if ($update_stmt->execute()) {

            $info_stmt = $conn->prepare("SELECT total_price, location_id FROM booking WHERE booking_id = ?");
            $info_stmt->bind_param("i", $booking_id);
            $info_stmt->execute();
            $info_result = $info_stmt->get_result();

            if ($info = $info_result->fetch_assoc()) {
                $amount = $info['total_price'];
                $location_id = $info['location_id'];


                $wallet_stmt = $conn->prepare("UPDATE locations SET wallet = wallet + ? WHERE location_id = ?");
                $wallet_stmt->bind_param("di", $amount, $location_id);
                $wallet_stmt->execute();
                $wallet_stmt->close();


                $transaction_stmt = $conn->prepare("
                    INSERT INTO wallet_transaction (location_id, booking_id, amount, transaction_type)
                    VALUES (?, ?, ?, 'credit')
                ");
                $transaction_stmt->bind_param("iid", $location_id, $booking_id, $amount);
                $transaction_stmt->execute();
                $transaction_stmt->close();


                $today = date('Y-m-d');
                $check_stmt = $conn->prepare("SELECT id FROM wallet_daily_summary WHERE location_id = ? AND summary_date = ?");
                $check_stmt->bind_param("is", $location_id, $today);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {

                    $update_summary = $conn->prepare("UPDATE wallet_daily_summary SET total_credit = total_credit + ? WHERE location_id = ? AND summary_date = ?");
                    $update_summary->bind_param("dis", $amount, $location_id, $today);
                    $update_summary->execute();
                    $update_summary->close();
                } else {

                    $insert_summary = $conn->prepare("INSERT INTO wallet_daily_summary (location_id, summary_date, total_credit) VALUES (?, ?, ?)");
                    $insert_summary->bind_param("isd", $location_id, $today, $amount);
                    $insert_summary->execute();
                    $insert_summary->close();
                }

                $check_stmt->close();
            }

            $info_stmt->close();
        }

        $update_stmt->close();

    } elseif (isset($_POST['cancel'])) {

        $update_stmt = $conn->prepare("UPDATE booking SET status = 'Canceled' WHERE booking_id = ?");
        $update_stmt->bind_param("i", $booking_id);
        $update_stmt->execute();
        $update_stmt->close();
    }


    header("Location: booking_confirm.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันการจอง</title>
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
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #e67e22;
            font-size: 2.2rem;
            margin-bottom: 30px;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(230, 126, 34, 0.2);
        }

        h3 {
            color: #e67e22;
            font-size: 1.5rem;
            margin: 30px 0 15px;
            padding-left: 10px;
            border-left: 4px solid #e67e22;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        th {
            background: #e67e22;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 500;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid rgba(230, 126, 34, 0.1);
        }

        tr:hover {
            background: rgba(230, 126, 34, 0.05);
        }

        form {
            display: flex;
            gap: 10px;
        }

        button {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        button[name="confirm"] {
            background: #2ecc71;
            color: white;
        }

        button[name="confirm"]:hover {
            background: #27ae60;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(46, 204, 113, 0.2);
        }

        button[name="cancel"] {
            background: #e74c3c;
            color: white;
        }

        button[name="cancel"]:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(231, 76, 60, 0.2);
        }

        .back-link {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 25px;
            background: #f8f9fa;
            color: #4a4a4a;
            border-radius: 8px;
            transition: all 0.3s ease;
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

        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 10px;
            }

            h2 {
                font-size: 1.8rem;
            }

            h3 {
                font-size: 1.3rem;
            }

            table {
                display: block;
                overflow-x: auto;
            }

            form {
                flex-direction: column;
                gap: 5px;
            }

            button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>รายการจองที่รอยืนยัน</h2>

        <?php foreach ($bookings_by_location as $location_name => $bookings): ?>
            <h3> สถานที่: <?php echo htmlspecialchars($location_name); ?></h3>
            <table>
                <tr>
                    <th>ชื่อผู้จอง</th>
                    <th>วันจอง</th>
                    <th>ช่วงเวลา</th>
                    <th>ราคา</th>
                    <th>สถานะ</th>
                    <th>ดำเนินการ</th>
                </tr>
                <?php foreach ($bookings as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['booking_date'])); ?></td>
                        <td><?php echo htmlspecialchars($row['booking_time']); ?></td>
                        <td><?php echo number_format($row['total_price'], 2); ?> บาท</td>
                        <td id='status-<?php echo $row['booking_id']; ?>'><?php echo htmlspecialchars($row['status']); ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                <button type="submit" name="confirm">ยืนยัน</button>
                                <button type="submit" name="cancel"> ยกเลิก</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endforeach; ?>

        <a href="index.php" class="back-link">⬅️ กลับหน้ารวม</a>
    </div>
</body>
</html>

<?php $conn->close(); ?>
