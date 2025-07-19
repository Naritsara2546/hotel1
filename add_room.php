<?php
// เปิดแสดง error เพื่อดีบัก
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'config.php';  // ตรวจสอบว่า $hotelConn ถูกเชื่อมแล้ว

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_code = trim($_POST['room_code']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $capacity = (int) $_POST['capacity'];
    $room_size = trim($_POST['room_size']);
    $tools = trim($_POST['tools']);
    $room_status = trim($_POST['room_status']);

    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $image = basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $image;

        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $error = "รองรับเฉพาะไฟล์ภาพนามสกุล jpg, jpeg, png, gif เท่านั้น";
        } else {
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $error = "เกิดข้อผิดพลาดในการอัพโหลดไฟล์";
            }
        }
    }

    if (!$error) {
        // ตรวจสอบว่า room_code ซ้ำหรือไม่
        $checkStmt = $hotelConn->prepare("SELECT room_code FROM meeting_rooms WHERE room_code = ?");
        $checkStmt->bind_param("s", $room_code);
        $checkStmt->execute();
        $checkStmt->store_result(); // ต้องมีตัวนี้เพื่อใช้ num_rows

        if ($checkStmt->num_rows > 0) {
            $error = "รหัสห้องนี้มีอยู่แล้ว กรุณาใช้รหัสอื่น";
        } else {
            $stmt = $hotelConn->prepare("INSERT INTO meeting_rooms (room_code, name, description, capacity, room_size, tools, room_status, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssissss", $room_code, $name, $description, $capacity, $room_size, $tools, $room_status, $image);

            if ($stmt->execute()) {
                header("Location: admin.php");
                exit();
            } else {
                $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $stmt->error;
            }

            $stmt->close();
        }

        $checkStmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <title>เพิ่มห้องประชุม</title>
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background: #c9c8c8ff;
            color: white;
            padding: 20px;
            max-width: 800px;
            margin: auto;
        }

        label {
            display: block;
            color: #000000ff;
            margin-top: 15px;
        }

        input,
        textarea {
            width: 100%;
            padding: 8px;
            color: #5e5e5eff;
            margin-top: 5px;
            border-radius: 5px;
            border: none;
        }

        button {
            margin-top: 20px;
            padding: 10px 20px;
            border: none;
            border-radius: 20px;
            background: #28a745;
            color: white;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            background: #1d5a2aff;
        }

        a {
            color: #050505ff;
            display: inline-block;
            margin-top: 15px;
        }

        .error {
            color: #ff4d4d;
            margin-top: 10px;
        }
        .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      height: 100%;
      width: 250px;
      background-color: #111;
      color: white;
      padding-top: 20px;
      box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
    }

    .sidebar a {
      padding: 4px 16px;
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
      margin-left: 260px; /* ทำให้เนื้อหาหลักเลื่อนจากแถบด้านซ้าย */
      padding: 40px 20px;
    }

  .content {
    margin-left: 260px; /* เนื้อหาหลักเลื่อนจากแถบด้านซ้าย */
    padding: 40px 20px;
  }


        h1 {
            font-size: 2.5rem;
            color: #030303ff;
            text-align: center;
            margin-bottom: 20px;

            
        }
    </style>
</head>

<body>

   <!-- แถบด้านซ้าย -->
  <div class="sidebar">
    <h2 class="text-center" style="color:white; padding-left:20px;">เมนู</h2>
    <a href="หน้าแดชบอร์ด">หน้าหลัก</a>
    <a href="submit.php">อนุมัติการจอง</a>
    <a href="logout.php">ออกจากระบบ</a>
  </div>

    <!-- Content Section -->
    <div class="content">
        <h1>เพิ่มห้องประชุม</h1>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label>รหัสห้อง</label>
            <input type="text" name="room_code" required>

            <label>ชื่อห้อง</label>
            <input type="text" name="name" required>

            <label>รายละเอียด</label>
            <textarea name="description"></textarea>

            <label>จำนวนที่นั่ง</label>
            <input type="number" name="capacity" required min="1">

            <label>ขนาดห้อง:</label>
            <input type="text" name="room_size" required>

            <label>อุปกรณ์:</label>
            <textarea name="tools" required></textarea>

            

            <label>รูปภาพ</label>
            <input type="file" name="image" accept="image/*">

            <button type="submit">บันทึก</button>
        </form>

        <a href="admin.php">กลับไปหน้าแอดมิน</a>
    </div>

</body>

</html>