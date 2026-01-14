<?php
include '../login_status/require_login.php';
include __DIR__ . '/../config/db_connect.php';

$user_id = $_SESSION['user_id'] ?? null;

//  ยกเลิก  + คืนเงิน
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_pending_one'])) {
    $booking_id = $_POST['delete_booking_id'];

    $check_booking = $conn->prepare("SELECT total_price FROM booking WHERE booking_id = ? AND user_id = ? AND status = 'Pending'");
    $check_booking->bind_param("ii", $booking_id, $user_id);
    $check_booking->execute();
    $result = $check_booking->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('ไม่พบการจองที่ต้องการยกเลิก'); window.location.href='user_check_booking.php';</script>";
        exit();
    }

    $price_data = $result->fetch_assoc();
    $check_booking->close();

    // คืนเงิน
    if ($price_data) {
        $refund_amount = $price_data['total_price'];
        $update_wallet = $conn->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
        $update_wallet->bind_param("di", $refund_amount, $user_id);
        $update_wallet->execute();
        $update_wallet->close();
    }

    // ลบ
    $delete_sql = "DELETE FROM booking WHERE booking_id = ? AND user_id = ? AND status = 'Pending'";
    $stmt_delete = $conn->prepare($delete_sql);
    $stmt_delete->bind_param("ii", $booking_id, $user_id);

    if ($stmt_delete->execute()) {
        echo "<script>alert('ยกเลิกการจองสำเร็จ เงินจะถูกคืนกลับไปยังบัญชีของคุณ'); window.location.href='user_check_booking.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการยกเลิกการจอง'); window.location.href='user_check_booking.php';</script>";
    }

    $stmt_delete->close();
    exit();
}

$sql_user = "SELECT user_name FROM userinfo WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_name = ($row_user = $result_user->fetch_assoc()) ? $row_user['user_name'] : "ไม่ทราบชื่อ";
$stmt_user->close();


$sql = "SELECT b.booking_id, b.booking_date, b.booking_start_time, b.booking_end_time, 
                l.location_name, b.status, b.total_price, b.created_at
            FROM booking b
            JOIN locations l ON b.location_id = l.location_id
            WHERE b.user_id = ?
            ORDER BY b.booking_date DESC, b.booking_start_time ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>การจองของฉัน</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #fce4ec 0%, #e0f7fa 100%);
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(236, 72, 153, 0.2);
            text-align: center;
        }

        .header h1 {
            color: white;
            font-size: 1.5rem;
            margin: 0;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(236, 72, 153, 0.1);
            overflow-x: auto;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 600px;
        }

        th {
            background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%);
            color: #be185d;
            padding: 15px 10px;
            font-weight: 600;
            border-bottom: 2px solid #fce7f3;
        }

        td {
            padding: 12px 10px;
            border-bottom: 1px solid #fce7f3;
        }

        tr:nth-child(even) {
            background-color: #ffffff;
        }

        tr:nth-child(odd) {
            background-color: #fdf2f8;
        }

        .status-pending {
            color: #f59e0b;
            font-weight: 600;
        }

        .status-completed {
            color: #10b981;
            font-weight: 600;
        }

        .btn-cancel {
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3);
        }

        .back-link {
            display: inline-block;
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .back-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3);
        }

        .no-booking {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(236, 72, 153, 0.1);
            margin: 20px 0;
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .container {
                padding: 10px;
            }

            .header {
                padding: 15px;
                margin-bottom: 15px;
            }

            .header h1 {
                font-size: 1.3rem;
            }

            th,
            td {
                padding: 10px 8px;
                font-size: 0.9rem;
            }

            .btn-cancel {
                padding: 6px 12px;
                font-size: 0.8rem;
            }

            .back-link {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.2rem;
            }

            th,
            td {
                padding: 8px 6px;
                font-size: 0.8rem;
            }

            .btn-cancel {
                padding: 5px 10px;
                font-size: 0.75rem;
            }

            .back-link {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>รายการการจองของคุณ (<?= htmlspecialchars($user_name); ?>)</h1>
        </div>

        <?php
        if ($result->num_rows > 0) {
            echo '<div class="table-container">';
            echo "<table>
                    <thead>
                        <tr>
                            <th>ลำดับ</th>
                            <th>สถานที่</th>
                            <th>วันที่จอง</th>
                            <th>วันที่</th>
                            <th>เวลา</th>
                            <th>ราคา</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>";

            $count = 1;
            while ($row = $result->fetch_assoc()) {
                $status = htmlspecialchars($row['status']);
                $statusClass = $status === 'Pending' ? 'status-pending' : 'status-completed';

                echo "<tr>
                        <td>{$count}</td>
                        <td>" . htmlspecialchars($row['location_name']) . "</td>
                        <td>" . date('d/m/Y', strtotime($row['created_at'])) . "</td>
                        <td>" . date('d/m/Y', strtotime($row['booking_date'])) . "</td>
                        <td>{$row['booking_start_time']} - {$row['booking_end_time']}</td>
                        <td>" . number_format($row['total_price'], 2) . "</td>
                        <td class='{$statusClass}'>{$status}</td>";

                if ($status === 'Pending') {
                    echo "<td>
                        <form method='POST' onsubmit='return confirm(\"คุณแน่ใจหรือไม่ว่าต้องการยกเลิกการจองนี้? เงินจะถูกคืนกลับไปยังบัญชีของคุณ\");'>
                            <input type='hidden' name='delete_booking_id' value='{$row['booking_id']}'>
                            <button type='submit' name='delete_pending_one' class='btn-cancel'>
                                 ยกเลิก
                            </button>
                        </form>
                    </td>";
                } else {
                    echo "<td>-</td>";
                }

                $count++;
            }
            echo "</tbody></table></div>";
        } else {
            echo '<div class="no-booking">
                    <i class="fas fa-calendar-times" style="font-size: 3rem; color: #ec4899; margin-bottom: 15px;"></i>
                    <p>คุณยังไม่มีการจอง</p>
                  </div>';
        }
        ?>

        <div style="text-align:center;">
            <a class="back-link" href="<?php echo BASE_URL; ?>/index.php">
                <i class="fas fa-arrow-left"></i> กลับหน้าหลัก
            </a>
        </div>
    </div>
</body>

</html>

<?php
$conn->close();
?>