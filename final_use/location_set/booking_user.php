<?php
include '../config/db_connect.php';


$sql = "SELECT b.booking_id, u.user_name, b.location_id, l.location_name, b.booking_date, b.booking_start_time, b.booking_end_time, b.status 
        FROM booking b 
        JOIN userinfo u ON b.user_id = u.user_id
        JOIN locations l ON b.location_id = l.location_id
        ORDER BY b.booking_date, b.booking_start_time";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h2>รายการจองสถานที่</h2>";
    echo "<table border='1'>
            <tr>
                <th>Booking ID</th>
                <th>User Name</th>
                <th>Location</th>
                <th>Booking Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Status</th>
            </tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row["booking_id"] . "</td>
                <td>" . $row["user_name"] . "</td>
                <td>" . $row["location_name"] . "</td>
                <td>" . $row["booking_date"] . "</td>
                <td>" . $row["booking_start_time"] . "</td>
                <td>" . $row["booking_end_time"] . "</td>
                <td>" . $row["status"] . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>ไม่มีรายการจอง</p>";
}

$conn->close();
?>
