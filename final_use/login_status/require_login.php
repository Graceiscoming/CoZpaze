<?php
session_start();
include_once __DIR__ . '/../config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/final_use/user/user_login.php");
    exit();
}
?>