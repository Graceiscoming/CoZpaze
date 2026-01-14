<?php
include '../config/db_connect.php';
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}


$items_per_page = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;


$count_sql = "SELECT COUNT(*) as total 
              FROM booking b
              JOIN locations l ON b.location_id = l.location_id
              JOIN userinfo u ON b.user_id = u.user_id";
$count_result = mysqli_query($conn, $count_sql);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $items_per_page);


$sql = "SELECT b.booking_id, b.booking_date, b.booking_start_time, b.booking_end_time, 
               l.location_name, u.user_name, b.status
        FROM booking b
        JOIN locations l ON b.location_id = l.location_id
        JOIN userinfo u ON b.user_id = u.user_id
        ORDER BY b.booking_date DESC, b.booking_start_time ASC
        LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $items_per_page, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการการจอง - Admin</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Prompt&display=swap');

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
            max-width: 1200px;
            margin: 30px auto;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px 30px;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 15px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        th {
            background: rgba(212, 175, 55, 0.1);
            color: #d4af37;
            font-weight: 600;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
        }

        tr {
            transition: all 0.3s ease;
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        a {
            text-decoration: none;
            color: #d4af37;
            padding: 8px 15px;
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            letter-spacing: 0.5px;
            margin: 0 5px;
        }

        a:hover {
            background: rgba(212, 175, 55, 0.2);
            color: #ffd700;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(212, 175, 55, 0.2);
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #d4af37;
            background: rgba(212, 175, 55, 0.1);
            border-radius: 12px;
            border: 1px solid rgba(212, 175, 55, 0.2);
            margin: 20px 0;
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .status-confirmed {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }

        .status-cancelled {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 15px;
            background: rgba(212, 175, 55, 0.1);
            color: #d4af37;
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .back-btn:hover {
            background: rgba(212, 175, 55, 0.2);
            color: #ffd700;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.2);
            border-color: rgba(212, 175, 55, 0.5);
        }

        .back-btn i {
            margin-right: 6px;
            font-size: 0.9rem;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }

        .pagination a {
            min-width: 100px;
            text-align: center;
        }

        .pagination a.disabled {
            background: rgba(255, 255, 255, 0.05);
            color: #666;
            cursor: not-allowed;
            pointer-events: none;
        }

        .page-info {
            text-align: center;
            margin: 20px 0;
            color: #d4af37;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .container {
                margin: 20px 15px;
                padding: 30px 20px;
            }

            h1 {
                font-size: 1.8rem;
            }

            table {
                display: block;
                overflow-x: auto;
            }

            th,
            td {
                padding: 10px;
                font-size: 0.9rem;
            }

            a {
                padding: 6px 10px;
                font-size: 0.9rem;
            }

            .back-btn {
                width: auto;
                text-align: center;
                padding: 8px 15px;
            }

            .pagination {
                flex-direction: column;
                align-items: center;
            }

            .pagination a {
                width: 100%;
                max-width: 200px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="<?php echo BASE_URL; ?>/final_use/admin/admin_dashboard.php" class="back-btn">
            <i>←</i> กลับไปที่แดชบอร์ด
        </a>
        <h1>จัดการการจอง</h1>

        <table>
            <thead>
                <tr>
                    <th>ลำดับ</th>
                    <th>ชื่อสถานที่</th>
                    <th>ผู้จอง</th>
                    <th>วันที่</th>
                    <th>เวลา</th>
                    <th>สถานะ</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    $count = ($page - 1) * $items_per_page + 1;
                    while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <tr>
                            <td><?php echo $count++; ?></td>
                            <td><?php echo htmlspecialchars($row['location_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['booking_date'])); ?></td>
                            <td><?php echo $row['booking_start_time'] . " - " . $row['booking_end_time']; ?></td>
                            <td>
                                <span class="status status-<?php echo strtolower($row['status']); ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a
                                    href="<?php echo BASE_URL; ?>/final_use/booking/edit_booking.php?id=<?php echo $row['booking_id']; ?>">แก้ไข</a>
                                <a href="<?php echo BASE_URL; ?>/final_use/booking/delete_booking.php?id=<?php echo $row['booking_id']; ?>"
                                    onclick="return confirm('ยืนยันการลบ?');">ลบ</a>
                            </td>
                        </tr>
                    <?php
                    }
                } else {
                    echo "<tr><td colspan='7' class='no-data'>ไม่พบข้อมูลการจอง</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="page-info">
            หน้า <?php echo $page; ?> จาก <?php echo $total_pages; ?>
        </div>

        <div class="pagination">
            <a href="?page=<?php echo max(1, $page - 1); ?>" class="<?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <i>←</i> ก่อนหน้า
            </a>
            <a href="?page=<?php echo min($total_pages, $page + 1); ?>"
                class="<?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                ถัดไป <i>→</i>
            </a>
        </div>
    </div>
</body>

</html>