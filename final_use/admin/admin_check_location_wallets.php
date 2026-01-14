<?php
session_start();
include '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../../index.php");
    exit();
}

$user_name = $_SESSION['user_name'];


$items_per_page = 7;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;


$count_sql = "SELECT COUNT(*) as total FROM locations";
$count_result = mysqli_query($conn, $count_sql);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $items_per_page);


$sql = "SELECT l.location_id, l.location_name, l.wallet, 
               GROUP_CONCAT(DISTINCT u.user_name) as owner_names
        FROM locations l
        LEFT JOIN location_owners lo ON l.location_id = lo.location_id
        LEFT JOIN userinfo u ON lo.user_id = u.user_id
        GROUP BY l.location_id
        ORDER BY l.location_name ASC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $items_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เช็คยอดเงินของสถานที่ทั้งหมด</title>
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
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }

        .welcome {
            font-size: 1.2rem;
            margin-bottom: 30px;
            color: #d4af37;
            text-align: center;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            padding: 15px;
            background: rgba(212, 175, 55, 0.1);
            border-radius: 12px;
            border: 1px solid rgba(212, 175, 55, 0.2);
        }

        h1 {
            color: #d4af37;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.2rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        th {
            background: rgba(212, 175, 55, 0.2);
            color: #d4af37;
            font-weight: 500;
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .wallet-amount {
            color: #27ae60;
            font-weight: 600;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
        }

        .pagination a {
            padding: 8px 12px;
            background: rgba(212, 175, 55, 0.1);
            color: #d4af37;
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
            min-width: 36px;
            text-align: center;
        }

        .pagination .pagination-btn {
            font-size: 1.2rem;
            padding: 8px 16px;
        }

        .pagination .pagination-dots {
            color: #d4af37;
            padding: 8px 4px;
        }

        .pagination a:hover {
            background: rgba(212, 175, 55, 0.2);
            color: #ffd700;
        }

        .pagination .active {
            background: rgba(212, 175, 55, 0.3);
            color: #ffd700;
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
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background: rgba(212, 175, 55, 0.2);
            color: #ffd700;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            table {
                display: block;
                overflow-x: auto;
            }

            th, td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="welcome">
            ยินดีต้อนรับผู้ดูแลระบบ! คุณ <?php echo htmlspecialchars($user_name); ?>
        </div>
        
        <h1>ยอดเงินของสถานที่ทั้งหมด</h1>

        <table>
            <thead>
                <tr>
                    <th>ชื่อสถานที่</th>
                    <th>เจ้าของสถานที่</th>
                    <th>ยอดเงินในระบบ</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['location_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['owner_names'] ?? 'ไม่มีเจ้าของ'); ?></td>
                        <td class="wallet-amount"><?php echo number_format($row['wallet'], 2); ?> บาท</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn">&lt;</a>
                <?php endif; ?>
                
                <?php 
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                if ($start_page > 1) {
                    echo '<a href="?page=1">1</a>';
                    if ($start_page > 2) {
                        echo '<span class="pagination-dots">...</span>';
                    }
                }
                
                for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" <?php echo $i == $page ? 'class="active"' : ''; ?>>
                        <?php echo $i; ?>
                    </a>
                <?php endfor;
                
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<span class="pagination-dots">...</span>';
                    }
                    echo '<a href="?page=' . $total_pages . '">' . $total_pages . '</a>';
                }
                ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn">&gt;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <a href="admin_dashboard.php" class="back-button">กลับไปยังแดชบอร์ด</a>
    </div>
</body>
</html> 