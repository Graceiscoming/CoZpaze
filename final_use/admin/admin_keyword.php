<?php
include '../config/db_connect.php';
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$locations_result = mysqli_query($conn, "SELECT location_id, location_name FROM locations");


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_keyword'])) {
    $location_id = $_POST['location_id'];
    $keyword = trim($_POST['keyword']);
    $location_id = mysqli_real_escape_string($conn, $location_id);
    $keyword = mysqli_real_escape_string($conn, strtolower($keyword));

    $check = mysqli_query($conn, "SELECT * FROM location_keywords WHERE keyword = '$keyword'");
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($conn, "INSERT INTO location_keywords (location_id, keyword, user_id) VALUES ('$location_id', '$keyword', '$user_id')");
        $msg = "<p class='success-message'>เพิ่ม keyword สำเร็จ!</p>";
    } else {
        $msg = "<p class='warning-message'>Keyword นี้ถูกใช้ไปแล้ว</p>";
    }
}


if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM location_keywords WHERE id = '$delete_id'");
    $msg = "<p class='success-message'>ลบ keyword สำเร็จ!</p>";
}


$selected_location_id = $_GET['location_id'] ?? '';
$keywords_result = null;

if (!empty($selected_location_id)) {
    $selected_location_id = mysqli_real_escape_string($conn, $selected_location_id);
    $keywords_result = mysqli_query($conn, "
        SELECT id, keyword FROM location_keywords
        WHERE location_id = '$selected_location_id'
    ");
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin: เพิ่ม Keyword</title>
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
            max-width: 800px;
            margin: 30px auto;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }

        h2 {
            font-size: 2.2rem;
            color: #d4af37;
            margin-bottom: 30px;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            letter-spacing: 1px;
            font-weight: 600;
        }

        h4 {
            color: #d4af37;
            margin: 25px 0 15px;
            font-size: 1.4rem;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        label {
            color: #d4af37;
            font-size: 1.1rem;
            margin-bottom: 5px;
            display: block;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 12px;
            color: #d4af37;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        select option {
            background: #1a1a1a;
            color: #d4af37;
        }

        input[type="text"]:focus,
        select:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.2);
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        button,
        .btn {
            background: rgba(212, 175, 55, 0.1);
            color: #d4af37;
            border: 1px solid rgba(212, 175, 55, 0.3);
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Prompt', sans-serif;
            font-weight: 500;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            display: inline-block;
        }

        button:hover,
        .btn:hover {
            background: rgba(212, 175, 55, 0.2);
            color: #ffd700;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.2);
            border-color: rgba(212, 175, 55, 0.5);
        }

        .btn-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border-color: rgba(220, 53, 69, 0.3);
        }

        .btn-danger:hover {
            background: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            border-color: rgba(220, 53, 69, 0.5);
        }

        .success-message {
            color: #28a745;
            background: rgba(40, 167, 69, 0.1);
            padding: 15px;
            border-radius: 12px;
            border: 1px solid rgba(40, 167, 69, 0.2);
            margin-bottom: 20px;
        }

        .warning-message {
            color: #ffc107;
            background: rgba(255, 193, 7, 0.1);
            padding: 15px;
            border-radius: 12px;
            border: 1px solid rgba(255, 193, 7, 0.2);
            margin-bottom: 20px;
        }

        .keyword-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .keyword-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .keyword-item:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(5px);
        }

        .keyword-text {
            color: #e0e0e0;
            font-size: 1.1rem;
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

        @media (max-width: 768px) {
            .container {
                margin: 20px 15px;
                padding: 30px 20px;
            }

            h2 {
                font-size: 1.8rem;
            }

            .button-group {
                flex-direction: column;
            }

            button,
            .btn {
                width: 100%;
                text-align: center;
            }

            .back-btn {
                width: auto;
                text-align: center;
                padding: 8px 15px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="<?php echo BASE_URL; ?>/final_use/admin/admin_dashboard.php" class="back-btn">
            <i>←</i> กลับไปที่แดชบอร์ด
        </a>
        <h2>เพิ่ม Keyword ให้สถานที่</h2>

        <?php if (isset($msg))
            echo $msg; ?>

        <form method="GET">
            <label>เลือกสถานที่:</label>
            <select name="location_id" onchange="this.form.submit()" required>
                <option value="">-- เลือกสถานที่ --</option>
                <?php while ($row = mysqli_fetch_assoc($locations_result)): ?>
                    <option value="<?= $row['location_id'] ?>" <?= ($selected_location_id == $row['location_id']) ? 'selected' : '' ?>>
                        <?= $row['location_name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <?php if ($selected_location_id): ?>
            <form method="POST">
                <input type="hidden" name="location_id" value="<?= $selected_location_id ?>">
                <input type="text" name="keyword" placeholder="ใส่ keyword เพิ่มเลย" required>
                <div class="button-group">
                    <button type="submit" name="add_keyword">➕ เพิ่ม Keyword</button>
                    <a href="admin_dashboard.php" class="btn">-กลับหา Admin-</a>
                </div>
            </form>

            <h4> Keyword ที่มีแล้ว:</h4>
            <?php if ($keywords_result && mysqli_num_rows($keywords_result) > 0): ?>
                <ul class="keyword-list">
                    <?php while ($k = mysqli_fetch_assoc($keywords_result)): ?>
                        <li class="keyword-item">
                            <span class="keyword-text"><?= htmlspecialchars($k['keyword']) ?></span>
                            <a href="?location_id=<?= $selected_location_id ?>&delete_id=<?= $k['id'] ?>" class="btn btn-danger"
                                onclick="return confirm('ยืนยันลบ keyword นี้?')">ลบ</a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>ยังไม่มี keyword ในสถานที่นี้</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>

</html>