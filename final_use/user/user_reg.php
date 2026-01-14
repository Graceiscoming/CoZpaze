<?php
include '../config/db_connect.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style/login.css"> <!-- ใช้ CSS เดียวกับ login -->
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
            <h2>สมัครสมาชิก</h2>
            <form action="<?php echo BASE_URL; ?>/final_use/user/register.php" method="post">
                <div class="form-group">
                    <label for="user_name">Username</label>
                    <input type="text" id="user_name" name="user_name" required placeholder="กรอกชื่อผู้ใช้">
                </div>
                <div class="form-group">
                    <label for="user_pass">Password</label>
                    <input type="password" id="user_pass" name="user_pass" required placeholder="กรอกรหัสผ่าน">
                </div>
                <div class="form-group">
                    <label for="user_mail">Email</label>
                    <input type="email" id="user_mail" name="user_mail" required placeholder="กรอกอีเมล">
                </div>
                <div class="form-group">
                    <label for="university_name">University</label>
                    <input type="text" id="university_name" name="university_name" required
                        placeholder="ชื่อมหาวิทยาลัย">
                </div>
                <div class="form-group">
                    <label for="phone_num">Phone Number</label>
                    <input type="text" id="phone_num" name="phone_num" placeholder="เบอร์โทร (ไม่บังคับ)">
                </div>
                <div class="form-group">
                    <label for="reset_question">คำถามสำหรับรีเซ็ตรหัสผ่าน</label>
                    <input type="text" id="reset_question" name="reset_question" required
                        placeholder="เช่น ชื่อสัตว์เลี้ยงตัวแรก">
                </div>
                <div class="form-group">
                    <label for="reset_token">คำตอบ</label>
                    <input type="text" id="reset_token" name="reset_token" required placeholder="กรอกคำตอบของคุณ">
                </div>
                <button type="submit" class="login-btn">
                    <span>สมัครสมาชิก</span>
                </button>

                <div class="extra-links">
                    <a href="user_login.php">เข้าสู่ระบบ</a>
                </div>
                <div class="form-group">
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("register-form").addEventListener("submit", function (event) {
                event.preventDefault();

                const formData = new FormData(this);

                fetch('<?php echo BASE_URL; ?>/final_use/user/register.php', {
                    method: "POST",
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data);
                        if (data.success) {
                            alert("สมัครสมาชิกสำเร็จ!");
                            window.location.href = "login.html";
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => console.error("Register error:", error));
            });
        });
    </script>
</body>

</html>