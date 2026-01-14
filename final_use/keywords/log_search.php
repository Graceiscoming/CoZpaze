<?php
include __DIR__ . '/../config/db_connect.php';

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!empty($keyword) && $user_id !== null) {
    $keyword = mysqli_real_escape_string($conn, $keyword);

    $stmt = $conn->prepare("INSERT INTO searchkeywords (keyword, user_id) VALUES (?, ?)");
    $stmt->bind_param("si", $keyword, $user_id);
    $stmt->execute();
    $stmt->close();
}
?>

