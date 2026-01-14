<?php
session_start();
include '../config/db_connect.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../user/user_login.php");
    exit();
}


if (isset($_POST['delete_id'])) {
    $delete_id = $conn->real_escape_string($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM reviews WHERE review_id = ?");
    if (!$stmt) {
        die("Error preparing delete statement: " . $conn->error);
    }
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        echo "deleted";
    } else {
        echo "error";
    }
    exit();
}


if (isset($_POST['edit_id'])) {
    $edit_id = $conn->real_escape_string($_POST['edit_id']);
    $new_text = $conn->real_escape_string($_POST['review_text']);
    $new_rating = intval($_POST['rating']);

    $stmt = $conn->prepare("UPDATE reviews SET review_text=?, rating=? WHERE review_id=?");
    if (!$stmt) {
        die("Error preparing update statement: " . $conn->error);
    }
    $stmt->bind_param("sdi", $new_text, $new_rating, $edit_id);
    if ($stmt->execute()) {
        echo "updated";
    } else {
        echo "error";
    }
    exit();
}


if (isset($_POST['location_id'])) {
    $location_id = $conn->real_escape_string($_POST['location_id']);
    $stmt = $conn->prepare("SELECT r.*, u.user_name 
                           FROM reviews r 
                           JOIN userinfo u ON r.user_id = u.user_id 
                           WHERE r.location_id = ? 
                           ORDER BY r.created_at DESC");
    if (!$stmt) {
        die("Error preparing select statement: " . $conn->error);
    }
    $stmt->bind_param("i", $location_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ผู้ใช้</th>
                        <th>รีวิว</th>
                        <th>Rating</th>
                        <th>วันที่</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr>
                    <td>'.htmlspecialchars($row['review_id']).'</td>
                    <td>'.htmlspecialchars($row['user_name']).'</td>
                    <td>'.htmlspecialchars($row['review_text']).'</td>
                    <td>'.htmlspecialchars($row['rating']).'</td>
                    <td>'.htmlspecialchars($row['created_at']).'</td>
                    <td>
                        <button class="btn btn-primary edit-btn" 
                                data-id="'.$row['review_id'].'" 
                                data-text="'.htmlspecialchars($row['review_text']).'" 
                                data-rating="'.$row['rating'].'">
                            <i class="fas fa-edit"></i> แก้ไข
                        </button>
                        <button class="btn btn-danger delete-btn" 
                                data-id="'.$row['review_id'].'">
                            <i class="fas fa-trash"></i> ลบ
                        </button>
                    </td>
                  </tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<div class="no-reviews"><p>ไม่มีรีวิวสำหรับสถานที่นี้</p></div>';
    }
}
?>