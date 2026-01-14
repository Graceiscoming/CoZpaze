<?php
include '../config/db_connect.php'; 
include '../login_status/require_login.php'; 

function update_balance($user_id, $amount, $type, $description = '') {
    global $conn;

    if ($type == 'debit') {
        $amount = -abs($amount);
    } else {
        $amount = abs($amount);
    }

    // อัปเดต balance
    mysqli_query($conn, "UPDATE wallets SET balance = balance + $amount WHERE user_id = '$user_id'");

    // บันทึก transaction
    $t_type = $amount >= 0 ? 'credit' : 'debit';
    mysqli_query($conn, "INSERT INTO transactions (user_id, transaction_type, amount, description)
                         VALUES ('$user_id', '$t_type', ABS($amount), '$description')");
}
?>
