<?php
include '../config/db_connect.php';
session_start();


$owner_id = $_SESSION['user_id'];


$location_query = "SELECT l.location_id, l.location_name, l.category 
                   FROM location_owners lo
                   JOIN locations l ON lo.location_id = l.location_id
                   WHERE lo.user_id = '$owner_id'";
$result = mysqli_query($conn, $location_query);
?>

<h2>รายการสถานที่ของฉัน</h2>

<?php if (mysqli_num_rows($result) > 0): ?>
    <ul>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <li>
                <strong><?= htmlspecialchars($row['location_name']); ?></strong> (<?= htmlspecialchars($row['category']); ?>)
                <a href="edit_location.php?id=<?= $row['location_id']; ?>">แก้ไข</a>
            </li>
        <?php endwhile; ?>
    </ul>
<?php else: ?>
    <p>คุณยังไม่มีสถานที่ที่ลงทะเบียน</p>
<?php endif; ?>
