<?php
include '../config/db_connect.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    die("❌ กรุณาเข้าสู่ระบบก่อน");
}

$owner_id = $_SESSION['user_id'];

$sql_owner = "SELECT user_name FROM userinfo WHERE user_id = ?";
$stmt_owner = mysqli_prepare($conn, $sql_owner);
mysqli_stmt_bind_param($stmt_owner, "i", $owner_id);
mysqli_stmt_execute($stmt_owner);
$result_owner = mysqli_stmt_get_result($stmt_owner);
$owner_name = "ไม่ทราบชื่อ"; 

if ($row_owner = mysqli_fetch_assoc($result_owner)) {
    $owner_name = $row_owner['user_name'];
}


$sql_locations = "SELECT l.location_id, l.location_name 
                  FROM locations l
                  JOIN location_owners lo ON l.location_id = lo.location_id
                  WHERE lo.user_id = ?
                  ORDER BY l.location_name ASC";
$stmt_locations = mysqli_prepare($conn, $sql_locations);
mysqli_stmt_bind_param($stmt_locations, "i", $owner_id);
mysqli_stmt_execute($stmt_locations);
$result_locations = mysqli_stmt_get_result($stmt_locations);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการการจอง - ผู้ประกอบการ</title>
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

        h1 {
            color: #e67e22;
            font-size: 2.2rem;
            margin-bottom: 30px;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px 0;
            border-bottom: 2px solid rgba(230, 126, 34, 0.2);
        }

        h2 {
            color: #d35400;
            font-size: 1.8rem;
            margin: 30px 0 20px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(211, 84, 0, 0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        th {
            background: #e67e22;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background: #fff5e6;
        }

        td {
            color: #4a4a4a;
        }

        a {
            color: #e67e22;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 5px 10px;
            border-radius: 5px;
        }

        a:hover {
            color: #d35400;
            background: rgba(230, 126, 34, 0.1);
        }

        button {
            background: #e67e22;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Prompt', sans-serif;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        button:hover {
            background: #d35400;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(230, 126, 34, 0.2);
        }

        p {
            color: #666;
            font-size: 1.1rem;
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin: 20px 0;
        }

        .back-link {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 20px;
            background: #e67e22;
            color: white;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            background: #d35400;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(230, 126, 34, 0.2);
        }

        @media (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
            }

            h1 {
                font-size: 1.8rem;
            }

            h2 {
                font-size: 1.5rem;
            }

            th, td {
                padding: 10px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <h1>จัดการการจองของสถานที่ของคุณ (<?php echo htmlspecialchars($owner_name); ?>)</h1>

    <?php 
    while ($location = mysqli_fetch_assoc($result_locations)) {
        $location_id = $location['location_id'];
        $location_name = $location['location_name'];

        echo "<h2>" . htmlspecialchars($location_name) . "</h2>";


        $sql = "SELECT b.booking_id, b.booking_date, b.booking_start_time, b.booking_end_time, 
                       u.user_name, b.status
                FROM booking b
                JOIN userinfo u ON b.user_id = u.user_id
                WHERE b.location_id = ?
                ORDER BY b.booking_date DESC, b.booking_start_time ASC";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $location_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            echo "<table>
                    <thead>
                        <tr>
                            <th>ลำดับ</th>
                            <th>ผู้จอง</th>
                            <th>วันที่</th>
                            <th>เวลา</th>
                            <th>สถานะ</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>";

            $count = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>{$count}</td>
                        <td>" . htmlspecialchars($row['user_name']) . "</td>
                        <td>" . date('d/m/Y', strtotime($row['booking_date'])) . "</td>
                        <td>{$row['booking_start_time']} - {$row['booking_end_time']}</td>
                        <td id='status-{$row['booking_id']}'>" . htmlspecialchars($row['status']) . "</td>
                        <td>
                            <a href='../location_owner/edit_booking.php?id={$row['booking_id']}'>แก้ไข</a> | 
                            <a href='../location_owner/delete_booking.php?id={$row['booking_id']}' onclick='return confirm(\"ยืนยันการลบ?\");'>ลบ</a> ";


                if (strcasecmp($row['status'], 'confirmed') === 0) {
                    echo " | <form action='update_status.php' method='POST' style='display:inline;'>
                              <input type='hidden' name='booking_id' value='{$row['booking_id']}'>
                              <button type='submit' name='mark_finished' onclick='return confirm(\"เปลี่ยนสถานะเป็น เสร็จสิ้น ใช่หรือไม่?\");'>
                                  ✅ เสร็จสิ้น
                              </button>
                          </form>";
                }

                echo "</td></tr>";
                $count++;
            }

            echo "</tbody></table>";
        } else {
            echo "<p>ไม่มีข้อมูลการจองสำหรับสถานที่นี้</p>";
        }
    }
    ?>
    <a href="index.php" class="back-link">กลับหน้ารวม</a>
</body>
</html>