<?php
include '../config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $booking_id = $_GET['id'];

    $sql = "DELETE FROM booking WHERE booking_id = '$booking_id'";
    if (mysqli_query($conn, $sql)) {
        header("Location: ../admin/admin_bookings.php?success=deleted");
    } else {
        header("Location: ../admin/admin_bookings.php?error=delete_failed");
    }
} else {
    header("Location: admin_bookings.php?error=invalid_id");
}
?>
