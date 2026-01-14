<?php
session_start();
include '../config/db_connect.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: " . BASE_URL . "/final_use/user/user_login.php");
    exit();
}


$locations = $conn->query("SELECT location_id, location_name FROM locations");
if (!$locations) {
    die("Error fetching locations: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - จัดการรีวิว</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #e0e0e0;
            min-height: 100vh;
            line-height: 1.6;
            padding: 20px;
        }


        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }


        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
        }

        .back-btn {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            padding: 8px 16px;
            border-radius: 8px;
            background: rgba(212, 175, 55, 0.1);
            color: #d4af37;
            border: 1px solid rgba(212, 175, 55, 0.3);
            text-decoration: none;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: rgba(212, 175, 55, 0.2);
            color: #ffd700;
            transform: translateY(-50%) translateX(-2px);
        }

        .back-btn i {
            font-size: 1.2rem;
        }

        h2 {
            color: #d4af37;
            font-size: 2rem;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .subtitle {
            color: #888;
            font-size: 1.1rem;
        }


        .location-selector {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-label {
            display: block;
            color: #c0a36e;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .form-select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(212, 175, 55, 0.3);
            color: #d4af37;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-select option {
            background: #1a1a1a;
            color: #d4af37;
        }

        .form-select:focus {
            outline: none;
            border-color: rgba(212, 175, 55, 0.5);
            box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.2);
        }


        .table-container {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background: rgba(212, 175, 55, 0.1);
            color: #d4af37;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .table td {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .table tr:last-child td {
            border-bottom: none;
        }


        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: rgba(212, 175, 55, 0.1);
            color: #d4af37;
            border: 1px solid rgba(212, 175, 55, 0.3);
        }

        .btn-primary:hover {
            background: rgba(212, 175, 55, 0.2);
            color: #ffd700;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .btn-danger:hover {
            background: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            transform: translateY(-2px);
        }


        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
        }

        .modal-content {
            background: #1a1a1a;
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.3);
        }

        .modal-title {
            color: #d4af37;
            font-size: 1.5rem;
        }

        .close {
            background: none;
            border: none;
            color: #888;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .modal-body {
            margin-bottom: 20px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 20px;
            border-top: 1px solid rgba(212, 175, 55, 0.3);
        }


        .form-control {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(212, 175, 55, 0.3);
            color: #e0e0e0;
            margin-bottom: 15px;
        }

        .form-control:focus {
            outline: none;
            border-color: rgba(212, 175, 55, 0.5);
            box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.2);
        }


        .no-reviews {
            text-align: center;
            padding: 40px;
            color: #888;
        }


        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .table {
                display: block;
                overflow-x: auto;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .modal-content {
                width: 95%;
                margin: 20px auto;
            }

            .back-btn {
                position: static;
                transform: none;
                margin-bottom: 15px;
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <a href="<?php echo BASE_URL; ?>/final_use/admin/admin_dashboard.php" class="back-btn">
                <i>←</i> กลับไปที่แดชบอร์ด
            </a>
            <h2>จัดการรีวิว</h2>
            <p class="subtitle">เลือกสถานที่เพื่อดูและจัดการรีวิว</p>
        </div>

        <div class="location-selector">
            <label class="form-label">เลือกสถานที่:</label>
            <select id="locationSelect" class="form-select">
                <option value="">-- เลือกสถานที่ --</option>
                <?php while ($loc = $locations->fetch_assoc()) { ?>
                    <option value="<?= $loc['location_id'] ?>"><?= htmlspecialchars($loc['location_name']) ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="table-container">
            <div id="reviewTable">
                <div class="no-reviews">
                    <p>กรุณาเลือกสถานที่เพื่อดูรีวิว</p>
                </div>
            </div>
        </div>
    </div>


    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">แก้ไขรีวิว</h3>
                <button class="close">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editReviewId">
                <div class="form-group">
                    <label class="form-label">ข้อความรีวิว</label>
                    <textarea id="editReviewText" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">คะแนน</label>
                    <input type="number" id="editReviewRating" class="form-control" min="1" max="5">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary close">ปิด</button>
                <button id="saveEdit" class="btn btn-primary">บันทึก</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const locationSelect = document.getElementById('locationSelect');
            const reviewTable = document.getElementById('reviewTable');
            const editModal = document.getElementById('editModal');
            const closeButtons = document.querySelectorAll('.close');
            const saveEditButton = document.getElementById('saveEdit');


            locationSelect.addEventListener('change', function () {
                const locationId = this.value;
                if (locationId) {
                    fetchReviews(locationId);
                } else {
                    reviewTable.innerHTML = '<div class="no-reviews"><p>กรุณาเลือกสถานที่เพื่อดูรีวิว</p></div>';
                }
            });


            closeButtons.forEach(button => {
                button.addEventListener('click', function () {
                    editModal.style.display = 'none';
                });
            });


            saveEditButton.addEventListener('click', function () {
                const reviewId = document.getElementById('editReviewId').value;
                const reviewText = document.getElementById('editReviewText').value;
                const reviewRating = document.getElementById('editReviewRating').value;

                if (!reviewText || !reviewRating) {
                    alert('กรุณากรอกข้อมูลให้ครบถ้วน');
                    return;
                }

                if (reviewRating < 1 || reviewRating > 5) {
                    alert('คะแนนต้องอยู่ระหว่าง 1-5');
                    return;
                }

                saveReview(reviewId, reviewText, reviewRating);
            });

            function fetchReviews(locationId) {
                reviewTable.innerHTML = '<div class="loading"><p>กำลังโหลดข้อมูล...</p></div>';

                fetch('fetch_reviews.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `location_id=${locationId}`
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text();
                    })
                    .then(html => {
                        if (html.trim() === '') {
                            reviewTable.innerHTML = '<div class="no-reviews"><p>ไม่มีรีวิวสำหรับสถานที่นี้</p></div>';
                        } else {
                            reviewTable.innerHTML = html;
                            attachEventListeners();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        reviewTable.innerHTML = '<div class="error"><p>เกิดข้อผิดพลาดในการโหลดรีวิว</p></div>';
                    });
            }

            function attachEventListeners() {

                document.querySelectorAll('.edit-btn').forEach(button => {
                    button.addEventListener('click', function () {
                        const reviewId = this.dataset.id;
                        const reviewText = this.dataset.text;
                        const reviewRating = this.dataset.rating;

                        document.getElementById('editReviewId').value = reviewId;
                        document.getElementById('editReviewText').value = reviewText;
                        document.getElementById('editReviewRating').value = reviewRating;

                        editModal.style.display = 'block';
                    });
                });


                document.querySelectorAll('.delete-btn').forEach(button => {
                    button.addEventListener('click', function () {
                        if (confirm('คุณแน่ใจหรือไม่ที่จะลบรีวิวนี้?')) {
                            const reviewId = this.dataset.id;
                            deleteReview(reviewId);
                        }
                    });
                });
            }

            function saveReview(reviewId, text, rating) {
                const formData = new FormData();
                formData.append('edit_id', reviewId);
                formData.append('review_text', text);
                formData.append('rating', rating);

                fetch('fetch_reviews.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.text())
                    .then(data => {
                        if (data === 'updated') {
                            editModal.style.display = 'none';
                            locationSelect.dispatchEvent(new Event('change'));
                            alert('บันทึกการแก้ไขเรียบร้อยแล้ว');
                        } else {
                            alert('เกิดข้อผิดพลาดในการบันทึกรีวิว');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('เกิดข้อผิดพลาดในการบันทึกรีวิว');
                    });
            }

            function deleteReview(reviewId) {
                const formData = new FormData();
                formData.append('delete_id', reviewId);

                fetch('fetch_reviews.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.text())
                    .then(data => {
                        if (data === 'deleted') {
                            locationSelect.dispatchEvent(new Event('change'));
                            alert('ลบรีวิวเรียบร้อยแล้ว');
                        } else {
                            alert('เกิดข้อผิดพลาดในการลบรีวิว');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('เกิดข้อผิดพลาดในการลบรีวิว');
                    });
            }
        });
    </script>
</body>

</html>