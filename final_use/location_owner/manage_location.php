<?php
include '../config/db_connect.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    die(" กรุณาเข้าสู่ระบบก่อน");
}

$user_id = $_SESSION['user_id'];


$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;


$count_sql = "SELECT COUNT(*) as total 
              FROM locations l
              JOIN location_owners lo ON l.location_id = lo.location_id
              WHERE lo.user_id = ?";
$count_stmt = mysqli_prepare($conn, $count_sql);
mysqli_stmt_bind_param($count_stmt, "i", $user_id);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $items_per_page);


$sql = "SELECT l.location_id, l.location_name 
        FROM locations l
        JOIN location_owners lo ON l.location_id = lo.location_id
        WHERE lo.user_id = ?
        ORDER BY l.location_name ASC
        LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iii", $user_id, $items_per_page, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสถานที่</title>
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

        ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        li {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(230, 126, 34, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        li:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(230, 126, 34, 0.1);
            border-color: rgba(230, 126, 34, 0.4);
        }

        a {
            color: #e67e22;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 8px 15px;
            border-radius: 5px;
            margin-left: 10px;
        }

        a:hover {
            color: #d35400;
            background: rgba(230, 126, 34, 0.1);
        }

        .edit-link {
            background: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }

        .edit-link:hover {
            background: rgba(52, 152, 219, 0.2);
            color: #2980b9;
        }

        .delete-link {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .delete-link:hover {
            background: rgba(231, 76, 60, 0.2);
            color: #c0392b;
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
            padding: 12px 25px;
            background: #e67e22;
            color: white;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-align: center;
            width: 100%;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            background: #d35400;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(230, 126, 34, 0.2);
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
            background: rgba(230, 126, 34, 0.1);
            color: #e67e22;
            border: 1px solid rgba(230, 126, 34, 0.3);
        }

        .pagination a.disabled {
            background: rgba(255, 255, 255, 0.5);
            color: #999;
            cursor: not-allowed;
            pointer-events: none;
        }

        .page-info {
            text-align: center;
            margin: 20px 0;
            color: #e67e22;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
                margin: 20px;
            }

            h1 {
                font-size: 1.8rem;
            }

            li {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }

            a {
                margin: 5px 0;
                display: inline-block;
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
        <h1>สถานที่ของคุณ</h1>

        <?php 
        if (mysqli_num_rows($result) > 0) {
            echo "<ul>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<li>" . htmlspecialchars($row['location_name']) . " 
                      <div>
                          <a href='edit_location.php?id=" . $row['location_id'] . "' class='edit-link'>✏️ แก้ไข</a>
                          <a href='delete_location.php?id=" . $row['location_id'] . "' class='delete-link' onclick='return confirm(\"ลบสถานที่นี้?\");'>🗑 ลบ</a>
                      </div>
                      </li>";
            }
            echo "</ul>";

            if ($total_pages > 1) {
                echo "<div class='page-info'>หน้า " . $page . " จาก " . $total_pages . "</div>";
                echo "<div class='pagination'>";
                echo "<a href='?page=" . max(1, $page - 1) . "' class='" . ($page <= 1 ? 'disabled' : '') . "'>← ก่อนหน้า</a>";
                echo "<a href='?page=" . min($total_pages, $page + 1) . "' class='" . ($page >= $total_pages ? 'disabled' : '') . "'>ถัดไป →</a>";
                echo "</div>";
            }
        } else {
            echo "<p> คุณยังไม่มีสถานที่</p>";
        }
        ?>
        <a href="index.php" class="back-link">กลับหน้ารวม</a>
    </div>
</body>
</html>
