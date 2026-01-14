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
              FROM locations l
              INNER JOIN location_owners lo ON l.location_id = lo.location_id
              INNER JOIN userinfo u ON lo.user_id = u.user_id";
$count_result = mysqli_query($conn, $count_sql);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $items_per_page);


$sql = "SELECT l.location_name, lo.user_id, u.user_name
        FROM locations l
        INNER JOIN location_owners lo ON l.location_id = lo.location_id
        INNER JOIN userinfo u ON lo.user_id = u.user_id
        LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $items_per_page, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    echo "เกิดข้อผิดพลาดในการดึงข้อมูล: " . mysqli_error($conn);
    exit();
}

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายชื่อสถานที่พร้อมเจ้าของ</title>
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
            max-width: 1000px;
            margin: 30px auto;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }

        h2 {
            font-size: 1.8rem;
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
            padding: 15px 20px;
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            font-weight: 500;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            min-height: 60px;
        }

        a:hover {
            background: rgba(212, 175, 55, 0.2);
            color: #ffd700;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.2);
            border-color: rgba(212, 175, 55, 0.5);
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

            h2 {
                font-size: 1.4rem;
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
                padding: 12px 15px;
                font-size: 0.9rem;
                min-height: 50px;
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
        <h2>รายชื่อสถานที่พร้อมเจ้าของ</h2>
        <table>
            <thead>
                <tr>
                    <th>ชื่อสถานที่</th>
                    <th>ชื่อเจ้าของ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['location_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['user_name']) . "</td>";
                    echo "</tr>";
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