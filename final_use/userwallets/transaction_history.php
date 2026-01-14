<?php

include '../config/db_connect.php'; 
include '../login_status/require_login.php'; 
$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn, "SELECT * FROM transactions WHERE user_id = '$user_id' ORDER BY transaction_date DESC");

echo "<h3>ประวัติการทำรายการ</h3>";
echo "<table border='1'>
<tr><th>ประเภท</th><th>จำนวน</th><th>เวลา</th><th>รายละเอียด</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>
        <td>{$row['transaction_type']}</td>
        <td>{$row['amount']}</td>
        <td>{$row['transaction_date']}</td>
        <td>{$row['description']}</td>
    </tr>";
}
echo "</table>";
?>
