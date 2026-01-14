<?php
include '../login_status/require_login.php';
include __DIR__ . '/../config/db_connect.php';




$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];
$sql = "SELECT user_name, user_mail, university_name, phone_num, Fullname FROM UserInfo WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_name, $user_mail, $university_name, $phone_num, $Fullname);
$stmt->fetch();
$stmt->close();


$sql_check_owner = "SELECT COUNT(*) FROM location_owners WHERE user_id = ?";
$stmt_check_owner = $conn->prepare($sql_check_owner);
$stmt_check_owner->bind_param("i", $user_id);
$stmt_check_owner->execute();
$stmt_check_owner->bind_result($is_owner);
$stmt_check_owner->fetch();
$stmt_check_owner->close();


// ฟังก์ชันบีบอัดรูป
function compressImage($source, $destination, $quality = 70)
{
  $info = getimagesize($source);

  if ($info['mime'] == 'image/jpeg') {
    $image = imagecreatefromjpeg($source);
  } elseif ($info['mime'] == 'image/png') {
    $image = imagecreatefrompng($source);
    $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
    $white = imagecolorallocate($bg, 255, 255, 255);
    imagefilledrectangle($bg, 0, 0, imagesx($image), imagesy($image), $white);
    imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
    $image = $bg;
  } else {
    return false;
  }

  return imagejpeg($image, $destination, $quality);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $fullname = $_POST['fullname'];
  $university = $_POST['university_name'];
  $phone = $_POST['phone_num'];

  $update_sql = "UPDATE userinfo SET Fullname = ?, university_name = ?, phone_num = ? WHERE user_id = ?";
  $stmt = $conn->prepare($update_sql);
  $stmt->bind_param("sssi", $fullname, $university, $phone, $user_id);
  $stmt->execute();


  if (!empty($_FILES['profile_image']['name'])) {
    $targetDir = __DIR__ . '/../userimg/';

    if (!file_exists($targetDir)) {
      mkdir($targetDir, 0777, true);
    }


    $filename = "user_" . $user_id . ".jpg";
    $uploadPath = $targetDir . $filename;

    if (compressImage($_FILES['profile_image']['tmp_name'], $uploadPath)) {

      $img_sql = "UPDATE userinfo SET image = ? WHERE user_id = ?";
      $img_stmt = $conn->prepare($img_sql);
      $img_stmt->bind_param("si", $filename, $user_id);
      $img_stmt->execute();
    } else {
      echo " เกิดข้อผิดพลาดในการบันทึกรูป";
    }
  }
}


$sql = "SELECT user_name, Fullname, user_mail, university_name, phone_num, image FROM userinfo WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();


$timestamp = time();
$profile_img = BASE_URL . "/final_use/userimg/" . (!empty($row['image']) ? $row['image'] : "user.png") . "?t=" . $timestamp;




?>

<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>โปรไฟล์ของฉัน</title>
  <link href="https://fonts.googleapis.com/css2?family=Prompt&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/final_use/style/profile.css">
</head>

<style>
  .home-button {
    position: fixed;
    top: 10px;
    right: 10px;
    padding: 10px 15px;
    background-color: rgba(255, 192, 203, 0.3);
    color: #b30059;
    border-bottom-left-radius: 10px;
    text-decoration: none;
    font-weight: bold;
    z-index: 9999;
    box-shadow: 0 4px 10px rgba(255, 192, 203, 0.4);
    transition: all 0.3s ease;
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    border: 1px solid rgba(255, 192, 203, 0.4);
  }

  .home-button:hover {
    background-color: rgba(255, 105, 180, 0.85);
    color: white;
    box-shadow: 0 6px 12px rgba(255, 105, 180, 0.6);
  }

  /* Hamburger Menu */
  .hamburger {
    display: none;
    position: fixed;
    top: 10px;
    left: 10px;
    z-index: 1001;
    cursor: pointer;
    padding: 10px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  }

  .hamburger span {
    display: block;
    width: 25px;
    height: 3px;
    background: #333;
    margin: 5px 0;
    transition: all 0.3s ease;
  }

  @media (max-width: 768px) {
    body {
      flex-direction: column;
    }

    .hamburger {
      display: flex;
    }

    .sidebar {
      position: absolute;
      top: 60px;
      left: 10px;
      right: 10px;
      background-color: white;
      border-radius: 15px;
      padding: 1rem;
      transform: scaleY(0);
      transform-origin: top;
      transition: transform 0.3s ease;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
      width: auto;
    }

    .sidebar.active {
      transform: scaleY(1);
    }

    .profile-content {
      padding: 1rem;
    }
  }

  @media (min-width: 769px) {
    .sidebar {
      position: static;
      transform: none;
    }
  }

  @media (max-width: 400px) {
    .sidebar {
      width: 100%;
      max-width: 320px;
      margin: 0 auto;
      padding: 0.5rem;
      box-sizing: border-box;
      overflow-wrap: break-word;
    }

    .menu-item {
      font-size: 14px;
      word-break: break-word;
    }

    .home-button {
      font-size: 13px;
      padding: 8px 10px;
    }
  }
</style>

<body>
  <div class="hamburger">
    <span></span>
    <span></span>
    <span></span>
  </div>
  <a href="<?php echo BASE_URL; ?>/index.php" class="home-button"> กลับหน้าแรก</a>
  <div class="sidebar">
    <h2>Profile</h2>
    <a class="menu-item" href="user_profile.php"> ข้อมูลผู้ใช้</a>
    <a class="menu-item" href="<?php echo BASE_URL; ?>/final_use/userwallets/wallet.php"> กระเป๋าเงิน</a>
    <a class="menu-item" href="<?php echo BASE_URL; ?>/final_use/user/user_check_booking.php"> ประวัติการจองของฉัน</a>
    <a class="menu-item" href="<?php echo BASE_URL; ?>/final_use/tokenshop/transactions.php">
      ประวัติการเติมเงินของฉัน</a>
    <?php if ($is_owner > 0): ?>
      <a class="menu-item" href="../location_owner/index.php"> เมนูเจ้าของธุรกิจ</a>
    <?php endif; ?>
    <?php if ($role_id == 2): ?>
      <a class="menu-item" href="<?php echo BASE_URL; ?>/final_use/admin/admin_dashboard.php"> Admin Menu</a>
    <?php endif; ?>
  </div>

  <div class="profile-content">
    <div class="card">
      <div class="card-header">
        <img src="<?php echo $profile_img; ?>" alt="Avatar" style="width:100px;height:100px;border-radius:50%;">
        <div>
          <h2><?php echo htmlspecialchars($row['user_name']); ?></h2>
        </div>
      </div>

      <form method="POST" enctype="multipart/form-data" class="form-grid">
        <div>
          <label>Username</label>
          <input type="text" value="<?php echo htmlspecialchars($row['user_name']); ?>" disabled>
        </div>
        <div>
          <label>Fullname</label>
          <input type="text" name="fullname" value="<?php echo htmlspecialchars($row['Fullname']); ?>">
        </div>
        <div>
          <label>Usermail</label>
          <input type="email" value="<?php echo htmlspecialchars($row['user_mail']); ?>" disabled>
        </div>
        <div>
          <label>Universityname</label>
          <input type="text" name="university_name" value="<?php echo htmlspecialchars($row['university_name']); ?>">
        </div>
        <div>
          <label>Phonenum</label>
          <input type="tel" name="phone_num" value="<?php echo htmlspecialchars($row['phone_num']); ?>">
        </div>
        <div>
          <label>อัปโหลดรูปโปรไฟล์</label>
          <input type="file" name="profile_image" accept="image/jpeg, image/png">
        </div>
        <button type="submit" class="save-btn"> บันทึกข้อมูล</button>
      </form>
    </div>
  </div>

  <script>
    document.querySelector('.hamburger').addEventListener('click', function () {
      this.classList.toggle('active');
      document.querySelector('.sidebar').classList.toggle('active');
    });
  </script>
</body>

</html>