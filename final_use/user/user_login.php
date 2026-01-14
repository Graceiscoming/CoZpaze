<?php
session_start();
include '../config/db_connect.php';
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style/login.css">
</head>

<body>
    <style>
        .back-button {
            position: fixed;
            top: 0;
            right: 0;
            padding: 10px 15px;
            background-color: rgba(236, 72, 153, 0.2);
            color: #ec4899;
            border-bottom-left-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            z-index: 9999;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.2s ease, color 0.2s ease;
        }
    </style>
    <a href="<?php echo BASE_URL; ?>/index.php" class="back-button">กลับหน้าแรก</a>

    <div class="login-container">
        <div class="login-card">
            <h2>เข้าสู่ระบบ</h2>
            <form id="login-form">
                <div class="form-group">
                    <label for="user_name">ชื่อผู้ใช้ / อีเมล</label>
                    <input type="text" id="user_name" name="user_name" required placeholder="กรอกชื่อผู้ใช้หรืออีเมล">
                </div>
                <div class="form-group">
                    <label for="user_pass">รหัสผ่าน</label>
                    <input type="password" id="user_pass" name="user_pass" required placeholder="กรอกรหัสผ่าน">
                </div>
                <button type="submit" class="login-btn">
                    <span>เข้าสู่ระบบ</span>
                </button>
                <div class="extra-links">
                    <a href="<?php echo BASE_URL; ?>/final_use/user/user_forgot.php">ลืมรหัสผ่าน?</a>
                    <span>|</span>
                    <a href="<?php echo BASE_URL; ?>/final_use/user/user_reg.php">สมัครสมาชิก</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("login-form");

            form.addEventListener("submit", function (event) {
                event.preventDefault();

                const formData = new FormData(form);

                fetch('<?php echo BASE_URL; ?>/final_use/user/login.php', {
                    method: "POST",
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = data.redirect;
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error("Login error:", error);
                        alert("เกิดข้อผิดพลาดในการเข้าสู่ระบบ");
                    });
            });
        });
    </script>
</body>

</html>