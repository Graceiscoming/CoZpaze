<?php
session_start();
include '../config/db_connect.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$user_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการยอดเงินผู้ใช้</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #e0e0e0;
            min-height: 100vh;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            display: flex;
            gap: 30px;
            padding: 0 20px;
        }

        .left-section {
            flex: 1;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }

        .right-section {
            flex: 2;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }

        h1,
        h2 {
            font-size: 2.2rem;
            color: #d4af37;
            margin-bottom: 30px;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            letter-spacing: 1px;
            font-weight: 600;
        }

        .welcome {
            font-size: 1.2rem;
            margin-bottom: 30px;
            color: #d4af37;
            text-align: center;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            padding: 15px;
            background: rgba(212, 175, 55, 0.1);
            border-radius: 12px;
            border: 1px solid rgba(212, 175, 55, 0.2);
        }

        #fetchBalances,
        button {
            background: rgba(212, 175, 55, 0.1);
            color: #d4af37;
            border: 1px solid rgba(212, 175, 55, 0.3);
            padding: 15px 30px;
            border-radius: 12px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Prompt', sans-serif;
            margin: 10px 0;
            width: 100%;
        }

        #fetchBalances:hover,
        button:hover {
            background: rgba(212, 175, 55, 0.2);
            color: #ffd700;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.2);
            border-color: rgba(212, 175, 55, 0.5);
        }

        #userBalances {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        #userBalances li {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px 20px;
            margin: 10px 0;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        #userBalances li:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(5px);
        }

        #walletForm {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 30px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        label {
            color: #c0a36e;
            font-size: 1.1rem;
        }

        input,
        select {
            padding: 12px 15px;
            border-radius: 12px;
            border: 1px solid rgba(212, 175, 55, 0.3);
            background: rgba(255, 255, 255, 0.05);
            color: #e0e0e0;
            font-family: 'Prompt', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: rgba(212, 175, 55, 0.5);
            background: rgba(255, 255, 255, 0.08);
        }

        #balanceDisplay {
            color: #d4af37;
            font-weight: bold;
            font-size: 1.2rem;
            padding: 5px 10px;
            background: rgba(212, 175, 55, 0.1);
            border-radius: 8px;
        }

        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 25px;
            background: rgba(212, 175, 55, 0.1);
            color: #d4af37;
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 12px;
            text-decoration: none;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-align: center;
        }

        .back-button:hover {
            background: rgba(212, 175, 55, 0.2);
            color: #ffd700;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.2);
            border-color: rgba(212, 175, 55, 0.5);
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                padding: 20px;
            }

            .left-section,
            .right-section {
                width: 100%;
            }

            h1 {
                font-size: 1.8rem;
            }

            input,
            select,
            button {
                padding: 10px 12px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="left-section">
            <h2>เช็คยอดเงินของผู้ใช้ทั้งหมด</h2>
            <button id="fetchBalances">โหลดข้อมูล</button>
            <ul id="userBalances"></ul>
        </div>

        <div class="right-section">
            <div class="welcome">
                ยินดีต้อนรับผู้ดูแลระบบ! คุณ <?php echo htmlspecialchars($user_name); ?>
            </div>

            <h2>จัดการยอดเงินผู้ใช้</h2>
            <form id="walletForm">
                <div class="form-group">
                    <label for="user_id">User ID:</label>
                    <input type="number" id="user_id" name="user_id" required>
                </div>

                <div class="form-group">
                    <label for="amount">จำนวนเงิน:</label>
                    <input type="number" id="amount" name="amount" required>
                </div>

                <div class="form-group">
                    <label for="transaction_type">เลือกประเภท:</label>
                    <select id="transaction_type" name="transaction_type">
                        <option value="credit">เพิ่มเงิน</option>
                        <option value="debit">ถอนเงิน</option>
                    </select>
                </div>

                <button type="submit">อัปเดตยอดเงิน</button>
            </form>

            <div style="text-align: center; margin: 20px 0;">
                <p style="margin-bottom: 10px;">ยอดเงินของ User ID: <span id="balanceDisplay">---</span></p>
                <button id="checkBalanceBtn">เช็คยอดเงิน</button>
            </div>

            <a href="<?php echo BASE_URL; ?>/final_use/admin/admin_dashboard.php" class="back-button">
                กลับไปยังแดชบอร์ด</a>
        </div>
    </div>

    <script>

        let currentPage = 1;
        const usersPerPage = 5;
        let totalUsers = 0;
        let allUsers = [];

        function displayUsers(page) {
            const startIndex = (page - 1) * usersPerPage;
            const endIndex = startIndex + usersPerPage;
            const usersToShow = allUsers.slice(startIndex, endIndex);

            let userList = document.getElementById("userBalances");
            userList.innerHTML = "";

            usersToShow.forEach(user => {
                let listItem = document.createElement("li");
                listItem.textContent = `ชื่อ: ${user.user_name} (User ID: ${user.user_id}) - ยอดเงิน: ${user.balance}`;
                userList.appendChild(listItem);
            });


            updatePaginationControls();
        }

        function updatePaginationControls() {
            const totalPages = Math.ceil(totalUsers / usersPerPage);
            const paginationContainer = document.getElementById("paginationControls");

            if (!paginationContainer) {
                const newPaginationContainer = document.createElement("div");
                newPaginationContainer.id = "paginationControls";
                newPaginationContainer.className = "mt-4 flex flex-col items-center space-y-2";
                document.getElementById("userBalances").parentNode.insertBefore(newPaginationContainer, document.getElementById("userBalances").nextSibling);
            }

            let paginationHTML = `
            <div class="flex items-center space-x-2">
                ${currentPage > 1 ? `
                    <button onclick="changePage(1)" class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition">
                        &lt;&lt;
                    </button>
                    <button onclick="changePage(${currentPage - 1})" class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition">
                        &lt;
                    </button>
                ` : ''}
                
                <span class="px-3 py-1 bg-yellow-600 text-white rounded-lg">${currentPage}</span>
                
                ${currentPage < totalPages ? `
                    <button onclick="changePage(${currentPage + 1})" class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition">
                        &gt;
                    </button>
                    <button onclick="changePage(${totalPages})" class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition">
                        &gt;&gt;
                    </button>
                ` : ''}
            </div>
            <div class="text-sm text-yellow-500">
                แสดง ${(currentPage - 1) * usersPerPage + 1} - ${Math.min(currentPage * usersPerPage, totalUsers)} จาก ${totalUsers} ผู้ใช้
            </div>
        `;

            document.getElementById("paginationControls").innerHTML = paginationHTML;
        }

        function changePage(newPage) {
            currentPage = newPage;
            displayUsers(currentPage);
        }

        document.getElementById("fetchBalances").addEventListener("click", function () {
            fetch("../admin/admin_check_balance.php")
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    allUsers = data.users;
                    totalUsers = allUsers.length;
                    currentPage = 1;
                    displayUsers(currentPage);
                })
                .catch(error => console.error("เกิดข้อผิดพลาด:", error));
        });


        document.getElementById("walletForm").addEventListener("submit", function (event) {
            event.preventDefault();

            let formData = new FormData(this);

            fetch("<?php echo BASE_URL; ?>/final_use/userwallets/update_wallet.php", {
                method: "POST",
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.new_balance !== undefined) {
                        document.getElementById("balanceDisplay").textContent = data.new_balance;
                    }
                })
                .catch(error => console.error("เกิดข้อผิดพลาด:", error));
        });


        document.getElementById("checkBalanceBtn").addEventListener("click", function () {
            let userId = document.getElementById("user_id").value;
            if (!userId) {
                alert("กรุณากรอก User ID");
                return;
            }

            fetch("<?php echo BASE_URL; ?>/final_use/userwallets/check_balance.php?user_id=" + userId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        document.getElementById("balanceDisplay").textContent = data.balance;
                    }
                })
                .catch(error => console.error("เกิดข้อผิดพลาด:", error));
        });
    </script>
</body>

</html>