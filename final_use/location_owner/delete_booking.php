<?php
include '../config/db_connect.php';
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Error");
}

$booking_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];


$sql_check = "
    SELECT b.booking_id, b.location_id 
    FROM booking b
    JOIN location_owners lo ON b.location_id = lo.location_id
    WHERE b.booking_id = ? AND lo.user_id = ?
";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $booking_id, $user_id);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows === 0) {
    die(" Error");
}

$stmt_check->close();


$sql_delete = "DELETE FROM booking WHERE booking_id = ?";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("i", $booking_id);

if (!$stmt_delete->execute()) {
    die(" Error" . mysqli_error($conn)); 
}

$stmt_delete->close();
$conn->close();

header("Location: ../location_owner/check_booking.php?success=deleted");
exit();
?>
