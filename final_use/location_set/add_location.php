<?php
session_start();

include '../config/db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: " . BASE_URL . "/final_use/user/user_login.php?error=access_denied");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มสถานที่</title>
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

        h1 {
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
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 12px;
            color: #e0e0e0;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.2);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        input[type="checkbox"] {
            margin-right: 10px;
            accent-color: #d4af37;
        }

        button[type="submit"] {
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
            margin-top: 20px;
        }

        button[type="submit"]:hover {
            background: rgba(212, 175, 55, 0.2);
            color: #ffd700;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.2);
            border-color: rgba(212, 175, 55, 0.5);
        }

        .time-slots {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .time-slot {
            display: flex;
            align-items: center;
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .time-slot:hover {
            background: rgba(255, 255, 255, 0.08);
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

            h1 {
                font-size: 1.8rem;
            }

            .time-slots {
                grid-template-columns: 1fr;
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
        <h1>เพิ่มสถานที่</h1>
        <form action="process_add.php" method="POST" enctype="multipart/form-data">
            <div>
                <label>ชื่อสถานที่:</label>
                <input type="text" name="location_name" required>
            </div>

            <div>
                <label>รายละเอียด:</label>
                <textarea name="description" required></textarea>
            </div>

            <div>
                <label>Google map link:</label>
                <input type="text" name="link" required>
            </div>

            <div>
                <label>Image</label>
                <input type="file" name="image" accept="image/*">
            </div>

            <div>
                <label>ละติจูด:</label>
                <input type="text" name="latitude" required>
            </div>

            <div>
                <label>ลองจิจูด:</label>
                <input type="text" name="longitude" required>
            </div>

            <div>
                <label>หมวดหมู่:</label>
                <select name="category">
                    <option value="Café">Café</option>
                    <option value="CO-Working">CO-Working</option>
                    <option value="Free space">Free space</option>
                    <option value="Etc.">ETC..</option>
                </select>
            </div>

            <div>
                <label>ราคาต่อชั่วโมง (THB):</label>
                <input type="number" name="price_per_hour" step="0.01" required>
            </div>

            <div>
                <label>มหาวิทยาลัย:</label>
                <input type="text" name="uni" required>
            </div>

            <div>
                <label>เวลาเปิด:</label>
                <div class="time-slots">
                    <?php
                    for ($hour = 10; $hour < 22; $hour += 2) {
                        $start = sprintf("%02d:00", $hour);
                        $end = sprintf("%02d:00", $hour + 2);
                        echo "<div class='time-slot'>";
                        echo "<input type='checkbox' name='time[]' value='$start-$end' id='time-$start'>";
                        echo "<label for='time-$start'>$start - $end</label>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>

            <button type="submit">เพิ่มสถานที่</button>
        </form>
    </div>
</body>

</html>