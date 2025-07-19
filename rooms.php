<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// ดึงรายชื่อห้องทั้งหมด
$rooms = [];
$resRooms = $hotelConn->query("SELECT * FROM meeting_rooms ORDER BY name ASC");
if ($resRooms && $resRooms->num_rows > 0) {
    while ($r = $resRooms->fetch_assoc()) {
        $rooms[$r['room_code']] = $r;
    }
}

// ห้องที่เลือกจาก dropdown (default เอาห้องแรก)
$room_code = $_GET['room_code'] ?? '';
if (!$room_code || !isset($rooms[$room_code])) {
    // ถ้าไม่มีหรือไม่ถูกต้อง เลือกห้องแรกในรายการแทน
    $room_code = array_key_first($rooms);
}

$room = $rooms[$room_code];

// ดึงวันที่ถูกจองของห้องนี้ (3 เดือนข้างหน้า)
$today = date('Y-m-01');
$endDate = date('Y-m-t', strtotime("+3 months"));

$stmt2 = $hotelConn->prepare("SELECT booking_date FROM bookings WHERE room_code = ? AND booking_date BETWEEN ? AND ?");
if ($stmt2 === false) {
    die("Prepare failed: " . htmlspecialchars($hotelConn->error));
}
$stmt2->bind_param("sss", $room_code, $today, $endDate);
$stmt2->execute();

$reservedDates = [];
$result2 = $stmt2->get_result();
while ($row = $result2->fetch_assoc()) {
    $reservedDates[] = $row['booking_date'];
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>รายละเอียดห้องประชุม</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Poppins', sans-serif;
    }

    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      height: 100%;
      width: 250px;
      background-color: #050505ff;
      color: white;
      padding-top: 20px;
      box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
    }

    .sidebar a {
      padding: 10px 15px;
      text-decoration: none;
      font-size: 18px;
      color: white;
      display: block;
      transition: background-color 0.3s;
    }

    .sidebar a:hover {
      background-color: #575757;
    }

    .content {
      margin-left: 260px;
      padding: 40px 20px;
    }

    h1 {
      font-size: 2.5rem;
      text-align: center;
      margin-bottom: 20px;
    }

    .room-card {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      padding: 20px;
      margin-bottom: 30px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .room-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 10px 20px rgba(0, 123, 255, 0.2);
    }

    .room-image {
      width: 100%;
      max-width: 300px;
      height: 180px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 15px;
    }

    .room-name {
      font-size: 1.5rem;
      font-weight: bold;
    }

    .room-info {
      font-size: 1rem;
      color: #555;
      margin-bottom: 10px;
    }

    .select-button {
      background-color: #2b7c39ff;
      color: white;
      border: none;
      padding: 12px 30px;
      border-radius: 30px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .select-button:hover {
      background-color: #808b89ff;
    }
    .confirm-button-container {
      display: flex;
      justify-content: center; /* จัดตำแหน่งปุ่มให้อยู่กลางแนวนอน */
      align-items: center; /* จัดตำแหน่งปุ่มให้อยู่กลางแนวตั้ง */
      margin-top: 30px; /* เพิ่มช่องว่างด้านบนให้กับปุ่ม */
    }
  </style>
</head>
<body>
  <!-- Sidebar Section -->
  <div class="sidebar">
    <h2 class="text-center">เมนู</h2>
    <a href="index.php">หน้าหลัก</a>
    <a href="hisroom.php">ประวัติการจองของฉัน</a>
    <a href="rooms.php">จองห้องประชุม</a>
    <a href="logout.php">ออกจากระบบ</a>
  </div>

  <!-- Content Section -->
  <div class="content">
    <h1>รายละเอียดห้องประชุม</h1>
    <form method="GET" id="roomForm" class="mb-4">
      <label for="room_code">เลือกห้องประชุม:</label>
      <select name="room_code" id="room_code" class="form-select" onchange="document.getElementById('roomForm').submit()">
        <?php foreach ($rooms as $code => $r): ?>
          <option value="<?= htmlspecialchars($code) ?>" <?= $code === $room_code ? 'selected' : '' ?>>
            <?= htmlspecialchars($r['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>

    <div class="row">
      <div class="col-md-12">
        <div class="room-card">
          <h2><?= htmlspecialchars($room['name']) ?></h2>
          
          <?php if (!empty($room['image']) && file_exists('uploads/' . $room['image'])): ?>
            <img class="room-image" src="uploads/<?= htmlspecialchars($room['image']) ?>" alt="ภาพห้องประชุม" />
          <?php endif; ?>

          <p class="room-info"><?= htmlspecialchars($room['description']) ?></p>
          <p class="room-info"><strong>จำนวนที่นั่ง:</strong> <?= $room['capacity'] ?></p>
          <p class="room-info"><strong>ขนาด:</strong> <?= $room['room_size'] ?></p>
          <p class="room-info"><strong>อุปกรณ์:</strong> <?= $room['tools'] ?></p>
        </div>
      </div>
    </div>

    <!-- Add Date and Time Picker -->
    <label for="selectDate">เลือกวันที่:</label>
    <input type="date" id="selectDate" class="form-control" onchange="showTimeSelector()" />

    <div id="timeSelection" style="display:none;">
      <label for="selectTime">เลือกเวลา:</label>
      <select id="selectTime" class="form-select" onchange="calculatePrice()">
        <option value="9:00-12:00">9:00 - 12:00</option>
        <option value="13:00-17:00">13:00 - 17:00</option>
        <option value="all-day">ทั้งวัน</option>
      </select>
      <p id="priceInfo" class="mt-2"></p>
    </div>

    <!-- Add Confirm Button -->
    <form method="POST" action="payment.php" id="confirmationForm">
      <input type="hidden" name="room_code" value="<?= htmlspecialchars($room_code) ?>" />
      <input type="hidden" name="selected_date" id="selected_date" />
      <input type="hidden" name="selected_time" id="selected_time" />
      <button type="submit" class="select-button" id="confirmButton">ยืนยันการจอง</button>
    </form>
  </div>

  <script>
    // Function to show time selection after date selection
    function showTimeSelector() {
      document.getElementById("timeSelection").style.display = "block";
    }

    // Function to calculate price based on selected time
    function calculatePrice() {
      const selectedTime = document.getElementById("selectTime").value;
      let price = 0;

      // Simple price calculation
      if (selectedTime === "9:00-12:00" || selectedTime === "13:00-17:00") {
        price = 8000; // Assume 1000 baht for half-day
      } else if (selectedTime === "all-day") {
        price = 12,000; // Assume 2000 baht for full-day
      }

      document.getElementById("priceInfo").innerText = `ราคาห้อง: ฿${price}`;

      // Set the selected date and time values
      const selectedDate = document.getElementById("selectDate").value;
      const selectedTime = document.getElementById("selectTime").value;

      document.getElementById("selected_date").value = selectedDate;
      document.getElementById("selected_time").value = selectedTime;
    }
  </script>

  <!-- เพิ่ม Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
