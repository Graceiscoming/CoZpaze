<?php
session_start();

include '../config/db_connect.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"];
    $user = $_POST["user"];
    $location_id = $_POST["location_id"];

    $query = "SELECT user_id FROM userinfo WHERE user_id = ? OR user_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $user, $user);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id);
        $stmt->fetch();

        if ($action == "add") {
            $check_sql = "SELECT * FROM location_owners WHERE location_id = ? AND user_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $location_id, $user_id);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows == 0) {
                $insert_sql = "INSERT INTO location_owners (location_id, user_id) VALUES (?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("ii", $location_id, $user_id);

                if ($insert_stmt->execute()) {
                    $message = "<p class='success-message'>เพิ่ม Owner สำเร็จ!</p>";
                } else {
                    $message = "<p class='error-message'>เกิดข้อผิดพลาดในการเพิ่ม Owner</p>";
                }
                $insert_stmt->close();
            } else {
                $message = "<p class='warning-message'>ผู้ใช้นี้เป็น Owner อยู่แล้ว</p>";
            }
            $check_stmt->close();
        } elseif ($action == "remove") {
            $check_sql = "SELECT * FROM location_owners WHERE location_id = ? AND user_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $location_id, $user_id);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $delete_sql = "DELETE FROM location_owners WHERE location_id = ? AND user_id = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param("ii", $location_id, $user_id);

                if ($delete_stmt->execute()) {
                    $message = "<p class='success-message'>ลบ Owner สำเร็จ!</p>";
                } else {
                    $message = "<p class='error-message'>เกิดข้อผิดพลาดในการลบ Owner</p>";
                }
                $delete_stmt->close();
            } else {
                $message = "<p class='warning-message'>ผู้ใช้นี้ไม่ได้เป็น Owner ของสถานที่นี้</p>";
            }
            $check_stmt->close();
        }
    } else {
        $message = "<p class='error-message'>ไม่พบ User ID หรือ Username นี้</p>";
    }
    $stmt->close();
}

$sql = "SELECT location_id, location_name FROM locations";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการ Owner สถานที่</title>
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

        button {
            flex: 1;
            background: rgba(212, 175, 55, 0.1);
            color: #d4af37;
            border: 1px solid rgba(212, 175, 55, 0.3);
            padding: 15px 20px;
            border-radius: 12px;
            font-size: 1.1rem;
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

        .success-message {
            color: #28a745;
            background: rgba(40, 167, 69, 0.1);
            padding: 15px;
            border-radius: 12px;
            border: 1px solid rgba(40, 167, 69, 0.2);
            margin-bottom: 20px;
        }

        .error-message {
            color: #dc3545;
            background: rgba(220, 53, 69, 0.1);
            padding: 15px;
            border-radius: 12px;
            border: 1px solid rgba(220, 53, 69, 0.2);
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

            button {
                width: 100%;
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
        <h2>จัดการ Owner สถานที่</h2>
        <?php echo $message; ?>

        <form action="" method="POST">
            <div>
                <label for="user">User ID หรือ Username:</label>
                <input type="text" id="user" name="user" required>
            </div>

            <div>
                <label for="location">เลือกสถานที่:</label>
                <select id="location" name="location_id">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <option value="<?= $row['location_id'] ?>"><?= $row['location_name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="button-group">
                <button type="submit" name="action" value="add">เพิ่ม Owner</button>
                <button type="submit" name="action" value="remove">ลบ Owner</button>
            </div>
        </form>
    </div>
</body>

</html>