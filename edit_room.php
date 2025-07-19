<?php
session_start();
require 'config.php';

// ตรวจสอบสิทธิ์ admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$room_code = $_GET['room_code'] ?? '';
if (!$room_code) {
    header("Location: admin.php");
    exit;
}

$error = '';
$success = '';

//  ดึงข้อมูลห้องประชุมก่อน (ยังไม่ใช่ UPDATE)
$stmt = $hotelConn->prepare("SELECT * FROM meeting_rooms WHERE room_code = ?");
$stmt->bind_param("s", $room_code);
$stmt->execute();
$result = $stmt->get_result();
$room = $result->fetch_assoc();
$stmt->close();

if (!$room) {
    die("ไม่พบห้องประชุมนี้");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $description = $_POST['description'];
  $capacity = (int)$_POST['capacity'];
  $room_size = $_POST['room_size'];
  $tools = $_POST['tools'];
  $room_status = $_POST['room_status'];

  $image = $room['image'];  // เก็บรูปเดิมไว้

  // ถ้ามีอัปโหลดรูปใหม่
  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
      $uploadDir = 'uploads/';
      $newImage = basename($_FILES['image']['name']);
      $targetFile = $uploadDir . $newImage;

      if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
          $image = $newImage;

          // ลบไฟล์เก่า
          if (!empty($room['image']) && file_exists($uploadDir . $room['image'])) {
              unlink($uploadDir . $room['image']);
          }
      } else {
          $error = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
      }
  }

  // ✅ ทำการอัปเดตข้อมูล
  if (!$error) {
      $stmt = $hotelConn->prepare("
          UPDATE meeting_rooms 
          SET name = ?, description = ?, capacity = ?, image = ?, room_size = ?, tools = ?, room_status = ?
          WHERE room_code = ?
      ");
      $stmt->bind_param("ssisssss", $name, $description, $capacity, $image, $room_size, $tools, $room_status, $room_code);

      if ($stmt->execute()) {
          header("Location: admin.php");
          exit;
      } else {
          $error = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
      }

      $stmt->close();
  }
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8" />
<title>แก้ไขห้องประชุม</title>
<style>
  body { font-family: 'Sarabun', sans-serif; background: #747474ff; color: white; padding: 20px; max-width: 600px; margin: auto; }
  label { display: block; margin-top: 15px; }
  input, textarea { width: 100%; padding: 8px; margin-top: 5px; border-radius: 5px; border: none; }
  button { margin-top: 20px; padding: 10px 20px; border: none; border-radius: 20px; background: #0072ff; color: white; font-weight: 600; cursor: pointer; }
  button:hover { background: #0056b3; }
  a { color: #00c6ff; display: inline-block; margin-top: 15px; }
  .error { color: #ff4d4d; margin-top: 10px; }
  .success { color: #28a745; margin-top: 10px; }
  img { width: 150px; border-radius: 8px; margin-top: 10px; }
</style>
</head>
<body>

<h1>แก้ไขห้องประชุม (<?= htmlspecialchars($room['room_code']) ?>)</h1>

<?php if ($error): ?>
  <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<?php if ($success): ?>
  <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
  <label>ชื่อห้อง</label>
  <input type="text" name="name" required value="<?= htmlspecialchars($room['name']) ?>">

  <label>รายละเอียด</label>
  <textarea name="description"><?= htmlspecialchars($room['description']) ?></textarea>

  <label>จำนวนที่นั่ง</label>
  <input type="number" name="capacity" required min="1" value="<?= htmlspecialchars($room['capacity']) ?>">


  <label>ขนาดห้อง</label>
<input type="text" name="room_size" required value="<?= htmlspecialchars($room['room_size']) ?>">

<label>อุปกรณ์</label>
<textarea name="tools" required><?= htmlspecialchars($room['tools']) ?></textarea>

<label>สถานะ</label>
<select name="room_status" required>
  <option value="available" <?= $room['room_status'] == 'available' ? 'selected' : '' ?>>ว่าง</option>
  <option value="unavailable" <?= $room['room_status'] == 'unavailable' ? 'selected' : '' ?>>ไม่ว่าง</option>
</select>

  <label>รูปภาพ (ถ้าต้องการเปลี่ยน)</label>
  <input type="file" name="image" accept="image/*">

  <?php if (!empty($room['image']) && file_exists('uploads/' . $room['image'])): ?>
    <img src="<?= 'uploads/' . htmlspecialchars($room['image']) ?>" alt="รูปภาพห้องประชุม">
  <?php else: ?>
    <p>ไม่มีรูปภาพ</p>
  <?php endif; ?>

  <button type="submit">บันทึกการแก้ไข</button>
</form>

<a href="admin.php">กลับไปหน้าแอดมิน</a>

</body>
</html>
