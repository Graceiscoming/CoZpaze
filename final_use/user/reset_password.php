<?php
include '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = $_POST["user_name"];
    $reset_answer = $_POST["reset_answer"];


    $stmt = $conn->prepare("SELECT reset_token FROM UserInfo WHERE user_name = ?");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($correct_answer);
    $stmt->fetch();

    if ($reset_answer === $correct_answer) {

        echo "<div class='login-container'>
                <div class='login-card'>
                    <h2>ตั้งรหัสผ่านใหม่</h2>
                    <form action='update_password.php' method='post'>
                        <div class='form-group'>
                            <input type='hidden' name='user_name' value='$user_name'>
                            <label for='new_password'>รหัสผ่านใหม่:</label>
                            <input type='password' name='new_password' id='new_password' required placeholder='กรอกรหัสผ่านใหม่'>
                        </div>
                        <div class='form-group'>
                            <button type='submit' class='login-btn'>
                                <span>เปลี่ยนรหัสผ่าน</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>";
    } else {
        echo "<script>alert('คำตอบไม่ถูกต้อง!'); window.history.back();</script>";
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
    <title>ตั้งรหัสผ่านใหม่</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/final_use/style/login.css">
</head>

<body>

</body>

</html>