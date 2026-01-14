<?php
session_start();
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);


if (!isset($_SESSION['user_id'])) {
    echo json_encode(["logged_in" => false, "debug" => $_SESSION]);
    exit();
}


$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "Guest";


echo json_encode([
    "logged_in" => true,
    "username" => $username,
    "debug" => $_SESSION
]);

?>
