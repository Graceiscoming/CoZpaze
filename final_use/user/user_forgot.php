<?php include '../config/db_connect.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/final_use/style/login.css">
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <h2>ลืมรหัสผ่าน</h2>
            <form action="user_forgot2.php" method="post">
                <div class="form-group">
                    <label for="user_input">ชื่อผู้ใช้หรืออีเมล</label>
                    <input type="text" id="user_input" name="user_input" required placeholder="กรอกชื่อผู้ใช้หรืออีเมล">
                </div>
                <button type="submit" class="login-btn">
                    <span>ถัดไป</span>
                </button>
                <div class="extra-links">
                    <a href="<?php echo BASE_URL; ?>/final_use/user/user_login.php">กลับสู่หน้าเข้าสู่ระบบ</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>