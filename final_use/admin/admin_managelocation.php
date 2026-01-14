<?php
include '../config/db_connect.php';
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../../index.php");
    exit();
}


$items_per_page = 5; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;


$count_sql = "SELECT COUNT(*) as total FROM locations";
$count_result = mysqli_query($conn, $count_sql);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $items_per_page);


$sql = "SELECT l.location_id, l.location_name, u.user_name 
        FROM locations l
        LEFT JOIN location_owners lo ON l.location_id = lo.location_id
        LEFT JOIN userinfo u ON lo.user_id = u.user_id
        ORDER BY l.location_name ASC
        LIMIT $items_per_page OFFSET $offset";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสถานที่ทั้งหมด (แอดมิน)</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #1a1a1a;
            color: #ffffff;
            font-family: 'Prompt', sans-serif;
            padding: 2rem 0;
        }
        .container {
            background-color: #2d2d2d;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        h1 {
            color: #ffd700;
            margin-bottom: 2rem;
            font-size: 2rem;
            font-weight: 600;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            background-color: #3d3d3d;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s;
        }
        li:hover {
            transform: translateX(5px);
        }
        .edit-link, .delete-link {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            margin-left: 1rem;
            transition: all 0.2s;
        }
        .edit-link {
            background-color: #ffd700;
            color: #000000;
        }
        .edit-link:hover {
            background-color: #ffc800;
            color: #000000;
        }
        .delete-link {
            background-color: #dc3545;
            color: #ffffff;
        }
        .delete-link:hover {
            background-color: #bb2d3b;
            color: #ffffff;
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
            padding: 15px 20px;
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 12px;
            color: #d4af37;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .pagination a:hover:not(.disabled) {
            background: rgba(212, 175, 55, 0.2);
            color: #ffd700;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.2);
            border-color: rgba(212, 175, 55, 0.5);
        }
        .pagination .disabled {
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
        .back-link {
            display: inline-block;
            margin-top: 2rem;
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
        .back-link:hover {
            background: rgba(212, 175, 55, 0.2);
            color: #ffd700;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.2);
            border-color: rgba(212, 175, 55, 0.5);
        }
        strong {
            color: #ffd700;
        }
        small {
            color: #a0a0a0;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>สถานที่ทั้งหมดในระบบ</h1>

    <?php 
    if (mysqli_num_rows($result) > 0) {
        echo "<ul>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<li>
                    <div>
                        <strong>" . htmlspecialchars($row['location_name']) . "</strong><br>
                        <small>เจ้าของ: " . ($row['user_name'] ? htmlspecialchars($row['user_name']) : "ไม่มีข้อมูล") . "</small>
                    </div>
                    <div>
                        <a href='/final_use/admin/admin_editlocation.php?id=" . $row['location_id'] . "' class='edit-link'> แก้ไข</a>
                        <a href='delete_location.php?id=" . $row['location_id'] . "' class='delete-link' onclick='return confirm(\"ลบสถานที่นี้?\");'>🗑 ลบ</a>
                    </div>
                </li>";
        }
        echo "</ul>";

        if ($total_pages > 1) {
            echo "<div class='page-info'>หน้า " . $page . " จาก " . $total_pages . "</div>";
            echo "<div class='pagination'>";
            echo "<a href='?page=" . max(1, $page - 1) . "' class='" . ($page <= 1 ? 'disabled' : '') . "'>< หน้าที่แล้ว</a>";
            echo "<a href='?page=" . min($total_pages, $page + 1) . "' class='" . ($page >= $total_pages ? 'disabled' : '') . "'>หน้าถัดไป ></a>";
            echo "</div>";
        }
    } else {
        echo "<p style='color: #ffd700;'> ยังไม่มีสถานที่ในระบบ</p>";
    }
    ?>
    <a href="admin_dashboard.php" class="back-link"> กลับหน้าหลักแอดมิน</a>
</div>
</body>
</html>
