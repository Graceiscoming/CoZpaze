<?php
include '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_input = $_POST["user_input"];

    // ค้นหาผู้ใช้จาก username หรือ email
    $stmt = $conn->prepare("SELECT user_name, reset_question FROM UserInfo WHERE user_name = ? OR user_mail = ?");
    $stmt->bind_param("ss", $user_input, $user_input);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_name, $reset_question);
        $stmt->fetch();
        echo "<div class='login-container'>
        <div class='login-card'>
            <h2>รีเซ็ตรหัสผ่าน</h2>
            <form action='reset_password.php' method='post'>
                <div class='form-group'>
                    <label for='reset_question'>คำถามรีเซ็ตรหัสผ่าน: $reset_question</label>
                </div>
                <div class='form-group'>
                    <input type='hidden' name='user_name' value='$user_name'>
                    <label for='reset_answer'>คำตอบ:</label>
                    <input type='text' name='reset_answer' id='reset_answer' required placeholder='กรอกคำตอบของคุณ'>
                </div>
                <div class='form-group'>
                    <button type='submit' class='login-btn'>
                        <span>ตรวจสอบ</span>
                    </button>
                </div>
            </form>
        </div>
    </div>";
    } else {
        echo "<script>alert('คำตอบผิดพลาด'); window.history.back();</script>";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันรีรหัส</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/final_use/style/login.css">
</head>

</body>

</html>