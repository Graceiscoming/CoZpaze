<?php
session_start();
include '../config/db_connect.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../../index.php");
    exit();
}

$user_name = $_SESSION['user_name'];


$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;


$count_sql = "SELECT COUNT(*) as total 
              FROM transactions t
              JOIN wallets w ON t.user_id = w.user_id
              WHERE t.transaction_type = 'pending'";
$count_result = mysqli_query($conn, $count_sql);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $items_per_page);


$sql = "SELECT t.id AS transaction_id, t.user_id, t.amount, t.transaction_date, w.balance, t.slip_image 
        FROM transactions t
        JOIN wallets w ON t.user_id = w.user_id
        WHERE t.transaction_type = 'pending' 
        ORDER BY t.transaction_date ASC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $items_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_transaction'])) {
    $user_id = $_POST['user_id'];
    $amount = $_POST['amount'];
    $transaction_id = $_POST['transaction_id'];


    $conn->begin_transaction();
    try {

        $update_wallet = "UPDATE wallets SET balance = balance + ? WHERE user_id = ?";
        $stmt1 = $conn->prepare($update_wallet);
        $stmt1->bind_param("di", $amount, $user_id);
        $stmt1->execute();
        

        $update_transaction = "UPDATE transactions SET transaction_type = 'success' WHERE id = ?";
        $stmt2 = $conn->prepare($update_transaction);
        $stmt2->bind_param("i", $transaction_id);
        $stmt2->execute();
        

        $conn->commit();
        echo "<script>alert(' ยืนยันการเติมเงินสำเร็จ!'); window.location.href='admin_check_topup.php';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert(' เกิดข้อผิดพลาด ลองใหม่อีกครั้ง');</script>";
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_transaction'])) {
    $transaction_id = $_POST['transaction_id'];
    

    $update_transaction = "UPDATE transactions SET transaction_type = 'cancel' WHERE id = ?";
    $stmt = $conn->prepare($update_transaction);
    $stmt->bind_param("i", $transaction_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('ยกเลิกการเติมเงินสำเร็จ!'); window.location.href='admin_check_topup.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาด ลองใหม่อีกครั้ง');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตรวจสอบรายการเติมเงิน</title>
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
        h1 {
            font-size: 2.2rem;
            color: #d4af37;
            margin-bottom: 30px;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            letter-spacing: 1px;
            font-weight: 600;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        th, td {
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
        tr:hover {
            background: rgba(255, 255, 255, 0.08);
        }
        button {
            background: rgba(212, 175, 55, 0.1);
            color: #d4af37;
            border: 1px solid rgba(212, 175, 55, 0.3);
            padding: 15px 20px;
            border-radius: 12px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Prompt', sans-serif;
            font-weight: 500;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        button:hover {
            background: rgba(212, 175, 55, 0.2);
            color: #ffd700;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.2);
            border-color: rgba(212, 175, 55, 0.5);
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
        .no-transactions {
            text-align: center;
            padding: 20px;
            color: #d4af37;
            background: rgba(212, 175, 55, 0.1);
            border-radius: 12px;
            border: 1px solid rgba(212, 175, 55, 0.2);
            margin: 20px 0;
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
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            overflow: auto;
        }
        .modal-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 90vh;
            margin-top: 5vh;
        }
        .close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
            cursor: pointer;
        }
        .close:hover {
            color: #bbb;
        }
        .slip-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            transition: transform 0.3s;
            border: 2px solid rgba(212, 175, 55, 0.3);
            cursor: pointer;
        }
        .slip-image:hover {
            transform: scale(1.1);
            border-color: #d4af37;
        }
        @media (max-width: 768px) {
            .pagination {
                flex-direction: column;
                align-items: center;
            }
            .pagination a {
                width: 100%;
                max-width: 200px;
            }
            .slip-image {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="welcome">
            ยินดีต้อนรับผู้ดูแลระบบ! คุณ <?php echo htmlspecialchars($user_name); ?>
        </div>
        <h1> รายการเติมเงินที่รอการยืนยัน</h1>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th> วันที่</th>
                    <th> ผู้ใช้</th>
                    <th> จำนวนเงิน</th>
                    <th> ยอดเงินในกระเป๋า</th>
                    <th> สลิป</th>
                    <th> ยืนยัน</th>
                    <th> ยกเลิก</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date('d/m/Y H:i', strtotime($row['transaction_date'])); ?></td>
                    <td><?php echo $row['user_id']; ?></td>
                    <td><?php echo number_format($row['amount'], 2); ?> บาท</td>
                    <td><?php echo number_format($row['balance'], 2); ?> บาท</td>
                    <td>
                        <?php if (!empty($row['slip_image'])): ?>
                            <img src="../tokenshop/user_upload/<?php echo $row['slip_image']; ?>" 
                                 class="slip-image" 
                                 alt="สลิป"
                                 onclick="showSlip('../tokenshop/user_upload/<?php echo $row['slip_image']; ?>')">
                        <?php else: ?>
                            <span style="color: #666;">ไม่มีสลิป</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                            <input type="hidden" name="amount" value="<?php echo $row['amount']; ?>">
                            <input type="hidden" name="transaction_id" value="<?php echo $row['transaction_id']; ?>">
                            <button type="submit" name="confirm_transaction"> ยืนยัน</button>
                        </form>
                    </td>
                    <td>
                        <form method="post" onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะยกเลิกการเติมเงินนี้?');">
                            <input type="hidden" name="transaction_id" value="<?php echo $row['transaction_id']; ?>">
                            <button type="submit" name="cancel_transaction"> ยกเลิก</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>

            <div class="page-info">
                หน้า <?php echo $page; ?> จาก <?php echo $total_pages; ?>
            </div>

            <div class="pagination">
                <a href="?page=<?php echo max(1, $page - 1); ?>" class="<?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <i>←</i> ก่อนหน้า
                </a>
                <a href="?page=<?php echo min($total_pages, $page + 1); ?>" class="<?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    ถัดไป <i>→</i>
                </a>
            </div>
        <?php else: ?>
            <div class="no-transactions">
                ไม่มีรายการเติมเงินที่รอการยืนยัน
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 30px;">
            <a href="admin_dashboard.php"> กลับไปหน้าหลัก</a>
        </div>


        <div id="slipModal" class="modal">
            <span class="close" onclick="closeSlipModal()">&times;</span>
            <img class="modal-content" id="slipPreview">
        </div>

        <script>
            function showSlip(imageSrc) {
                const modal = document.getElementById('slipModal');
                const modalImg = document.getElementById('slipPreview');
                modal.style.display = "block";
                modalImg.src = imageSrc;
            }

            function closeSlipModal() {
                document.getElementById('slipModal').style.display = "none";
            }


            window.onclick = function(event) {
                const modal = document.getElementById('slipModal');
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
        </script>
    </div>
</body>
</html>
