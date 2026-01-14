<?php
include 'config/db_connect.php';
header('Content-Type: application/json');

$locationDir = __DIR__ . '/img/location/';
$images = [];

if (is_dir($locationDir)) {
    $files = scandir($locationDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $images[] = BASE_URL . '/final_use/img/location/' . $file;
            }
        }
    }
}

echo json_encode($images);
?>