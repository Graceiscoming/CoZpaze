<?php 
session_start();
include '../config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('กรุณาเข้าสู่ระบบ'); window.location.href='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

// แสดงต่อหน้าเท่าไหร่ดี
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;


$count_sql = "SELECT COUNT(*) as total FROM transactions WHERE user_id = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $items_per_page);


$sql = "SELECT * FROM transactions WHERE user_id = ? ORDER BY transaction_date DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $items_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายการธุรกรรม</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #fff5f7;
        }
        .container {
            max-width: 1200px;
            margin: 1rem auto;
            padding: 1.5rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(236, 72, 153, 0.1);
        }
        @media (max-width: 768px) {
            .container {
                margin: 0.5rem;
                padding: 1rem;
                border-radius: 15px;
            }
        }
        .gradient-text {
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: bold;
        }
        .table {
            width: 100%;
            margin-top: 1.5rem;
            border-collapse: separate;
            border-spacing: 0;
        }
        .table th {
            background-color: #fdf2f8;
            padding: 1rem;
            font-weight: 600;
            color: #be185d;
            border-bottom: 2px solid #fce7f3;
        }
        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #fce7f3;
        }
        @media (max-width: 768px) {
            .table th, .table td {
                padding: 0.75rem;
                font-size: 0.9rem;
            }
        }
        .slip-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            transition: transform 0.3s;
            border: 2px solid #fce7f3;
        }
        @media (max-width: 768px) {
            .slip-image {
                width: 60px;
                height: 60px;
            }
        }
        .slip-image:hover {
            transform: scale(1.1);
            border-color: #ec4899;
        }
        .btn {
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            transition: all 0.3s;
            font-weight: 500;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3);
            color: white;
        }
        .amount-positive {
            color: #10b981;
            font-weight: bold;
        }
        .amount-negative {
            color: #ef4444;
            font-weight: bold;
        }
        .table-responsive {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(236, 72, 153, 0.1);
        }
        .nav-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        @media (max-width: 768px) {
            .nav-buttons {
                flex-direction: column;
            }
            .nav-buttons .btn {
                width: 100%;
                text-align: center;
            }
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin: 1rem 0;
        }
        .pagination .btn {
            padding: 0.5rem 1rem;
            min-width: 100px;
        }
        .pagination .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .page-info {
            text-align: center;
            margin: 1rem 0;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-2xl font-bold gradient-text mb-4">
            รายการธุรกรรมของคุณ
        </h1>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>วันที่</th>
                        <th>ประเภท</th>
                        <th>จำนวน</th>
                        <th>สลิป</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['transaction_date'])); ?></td>
                        <td><?php echo $row['transaction_type']; ?></td>
                        <td class="<?php echo $row['amount'] >= 0 ? 'amount-positive' : 'amount-negative'; ?>">
                            <?php echo number_format($row['amount'], 2); ?> บาท
                        </td>
                        <td>
                            <?php if (!empty($row['slip_image'])) { ?>
                                <a href="user_upload/<?php echo $row['slip_image']; ?>" target="_blank">
                                    <img src="user_upload/<?php echo $row['slip_image']; ?>" class="slip-image" alt="สลิป">
                                </a>
                            <?php } else { ?>
                                <span class="text-gray-400">ไม่มีสลิป</span>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="page-info">
            หน้า <?php echo $page; ?> จาก <?php echo $total_pages; ?>
        </div>

        <div class="pagination">
            <a href="?page=<?php echo max(1, $page - 1); ?>" class="btn <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                ก่อนหน้า
            </a>
            <a href="?page=<?php echo min($total_pages, $page + 1); ?>" class="btn <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                ถัดไป
            </a>
        </div>

        <div class="nav-buttons mt-4">
            <a href="tokenshop.php" class="btn">
            กลับไปหน้าร้านค้า
            </a>
            <a href="/index.php" class="btn">
            หน้าแรก
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

