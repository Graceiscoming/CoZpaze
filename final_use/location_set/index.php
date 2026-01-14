<?php 

include '../config/db_connect.php';

$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : "";
$selected_times = isset($_GET['time']) ? $_GET['time'] : [];


$keyword = mysqli_real_escape_string($conn, $keyword);

$sql = "SELECT * FROM locations WHERE uni LIKE '%$keyword%'";

if (!empty($selected_times)) {
    $time_conditions = [];
    foreach ($selected_times as $time) {
        $safe_time = mysqli_real_escape_string($conn, $time);
        $time_conditions[] = "time LIKE '%$safe_time%'";
    }
    if (!empty($time_conditions)) {
        $sql .= " AND (" . implode(" OR ", $time_conditions) . ")";
    }
}


$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แนะนำสถานที่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-3">
    <h1 class="text-center">แนะนำสถานที่จาก <?php echo $keyword; ?></h1>

    <div class="text-center mb-3">
        <a href="search.php" class="btn btn-secondary">🔍 ค้นหาใหม่</a>
    </div>

    <div class="row">
        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $row['location_name']; ?></h5>
                        <p class="card-text"><?php echo $row['description']; ?></p>
                        <p>หมวดหมู่: <?php echo $row['category']; ?></p>
                        <p> มหาวิทยาลัย: <?php echo $row['uni']; ?></p>
                        <p> เวลาเปิด: <?php echo !empty($row['time']) ? $row['time'] : "ไม่ระบุ"; ?></p>
                        <a href="location.php?id=<?php echo $row['location_id']; ?>" class="btn btn-primary">ดูแผนที่</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>
