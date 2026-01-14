<?php



include '../config/db_connect.php';



if (!isset($_GET['id'])) {
    echo "ไม่พบสถานที่";
    exit();
}

$id = $_GET['id'];
$sql = "SELECT * FROM locations WHERE location_id='$id'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "ไม่พบข้อมูล!";
    exit();
}
?>



<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['location_name']; ?> - แผนที่</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        #map { height: 250px; width: 100%; }
    </style>
</head>
<body>
    <h1><?php echo $data['location_name']; ?></h1>
    <p><?php echo $data['description']; ?></p>
    <p>หมวดหมู่: <?php echo $data['category']; ?></p>
    <p>⭐ <?php echo $data['star']; ?></p>

    <div id="map"></div>

    <script>
        var map = L.map('map').setView([<?php echo $data['latitude']; ?>, <?php echo $data['longitude']; ?>], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);
        
        L.marker([<?php echo $data['latitude']; ?>, <?php echo $data['longitude']; ?>])
            .addTo(map)
            .bindPopup('<?php echo $data['location_name']; ?>')
            .openPopup();
    </script>
</body>
</html>
