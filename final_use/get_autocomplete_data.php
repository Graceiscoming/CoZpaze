<?php
include __DIR__ . '/config/db_connect.php';

header('Content-Type: application/json');


$sql = "
    SELECT DISTINCT 
        l.uni as name,
        l.uni as full_name,
        GROUP_CONCAT(DISTINCT k.keyword) as keywords
    FROM locations l
    LEFT JOIN location_keywords k ON l.location_id = k.location_id
    GROUP BY l.uni
";

$result = mysqli_query($conn, $sql);
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $keywords = $row['keywords'] ? explode(',', $row['keywords']) : [];
    $data[] = [
        'name' => $row['name'],
        'full_name' => $row['full_name'],
        'keywords' => $keywords
    ];
}

echo json_encode($data);
?> 