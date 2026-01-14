<?php
session_start();
include '../config/db_connect.php';


if (!isset($_SESSION['user_id'])) {
    die("❌ กรุณาเข้าสู่ระบบก่อน");
}

$owner_id = $_SESSION['user_id'];


$query = "
    SELECT l.location_id, l.location_name, l.wallet
    FROM locations l
    JOIN location_owners lo ON l.location_id = lo.location_id
    WHERE lo.user_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เช็คยอดเงิน</title>
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

        h2 {
            color: #e67e22;
            font-size: 2.2rem;
            margin-bottom: 30px;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(230, 126, 34, 0.2);
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

        .wallet-amount {
            font-weight: 600;
            color: #27ae60;
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

            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2> ยอดเงินของแต่ละสถานที่</h2>
        
        <table>
            <tr>
                <th> ชื่อสถานที่</th>
                <th> ยอดเงินในระบบ</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['location_name']); ?></td>
                    <td class="wallet-amount"><?php echo number_format($row['wallet'], 2); ?> บาท</td>
                </tr>
            <?php endwhile; ?>
        </table>

        <a href="index.php" class="back-link"> กลับหน้าหลัก</a>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
