<?php
include '../config/db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_finished'])) {
    $booking_id = $_POST['booking_id'];


    $update_sql = "UPDATE booking SET status = 'finished' WHERE booking_id = ?";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "i", $booking_id);

    if (mysqli_stmt_execute($stmt)) {

        header("Location: ../location_owner/check_booking.php?success=finished");
        exit();
    } else {

        header("Location: ../location_owner/check_booking.php?error=update_failed");
        exit();
    }
} else {
    header("Location: ../location_owner/check_booking.php");
    exit();
}
?>