<?php
include '../login_status/require_login.php';
include '../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location_id = $_POST['location_id'];
    $location_name = $_POST['location_name'];
    $time_slot = $_POST['time_slot'];
    $price = $_POST['price'];


    $stmt = $conn->prepare("SELECT image_path FROM locations WHERE location_id = ?");
    $stmt->bind_param("i", $location_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $image_path = $row['image_path'];

  
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }


        $_SESSION['cart'][] = [
            'location_id' => $location_id,
            'location_name' => $location_name,
            'time_slot' => $time_slot,
            'price' => $price,
            'image_path' => $image_path,
            'booking_date' => $_POST['booking_date']
        ];
    }


    header("Location: cart.php");
    exit();
}
?>
