<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

$room_code = $_POST['room_code'] ?? '';
$date = $_POST['date'] ?? '';
$duration = $_POST['duration'] ?? '';
$price = $_POST['price'] ?? '';

if (!$room_code || !$date || !$duration || !$price) {
  die("ข้อมูลไม่ครบถ้วน");
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>ชำระเงิน</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Sarabun', sans-serif;
      background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
      color: #fff;
      padding: 30px;
      max-width: 600px;
      margin: auto;
    }
    .payment-container {
      background: rgba(255, 255, 255, 0.1);
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.5);
    }
    h1 {
      text-align: center;
      margin-bottom: 25px;
      font-size: 2rem;
    }
    label {
      display: block;
      margin-top: 15px;
      font-weight: 600;
    }
    input, select {
      width: 100%;
      padding: 12px;
      margin-top: 5px;
      border-radius: 10px;
      border: none;
      font-size: 1rem;
      background: rgba(255,255,255,0.95);
    }
    input[type="file"] {
      background: rgba(255,255,255,0.95);
      padding: 10px;
    }
    button {
      margin-top: 30px;
      width: 100%;
      background-color: #00cc99;
      border: none;
      padding: 15px;
      font-size: 1.2rem;
      font-weight: bold;
      color: #0f2027;
      border-radius: 12px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #009970;
      color: #fff;
    }
    .bank-info {
      background-color: rgba(0,0,0,0.3);
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 25px;
      text-align: center;
    }
    .bank-info p {
      margin: 5px 0;
    }
    .bank-account {
      font-size: 1.5rem;
      letter-spacing: 1px;
      color: #00ffcc;
      font-weight: bold;
      user-select: all;
    }
    .promptpay-qr {
      width: 150px;
      height: 150px;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
      margin-top: 15px;
      display: inline-block;
    }
    .copy-btn {
      margin: 10px 0 15px;
      padding: 8px 15px;
      border-radius: 8px;
      border: none;
      background-color: #00cc99;
      color: #0f2027;
      font-weight: 600;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div class="payment-container">
    <h1>ชำระเงิน</h1>

    <div class="bank-info">
      <p><strong>กรุณาชำระเงินผ่านบัญชีธนาคาร</strong></p>
      <p>ธนาคารกสิกรไทย</p>
      <p id="bankAccount" class="bank-account">044-8-91274-4</p>
      <button type="button" class="copy-btn" onclick="copyAccount()">คัดลอกเลขบัญชี</button>
      <p>ชื่อบัญชี: <strong>นางสาวนริศรา พระสว่าง</strong></p>

      <p style="font-weight: 600; margin-top: 20px;">พร้อมเพย์ (QR Code):</p>

    </div>

    <form action="thankyou.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="room_code" value="<?= htmlspecialchars($room_code) ?>">
      <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
      <input type="hidden" name="duration" value="<?= htmlspecialchars($duration) ?>">
      <input type="hidden" name="price" value="<?= htmlspecialchars($price) ?>">

      <label for="fullname">ชื่อ-นามสกุล</label>
      <input type="text" id="fullname" name="fullname" required>

      <label for="email">อีเมล</label>
      <input type="email" id="email" name="email" required>

      <label for="phone">เบอร์โทรศัพท์</label>
      <input type="text" id="phone" name="phone" required>

      <label for="payment_method">วิธีการชำระเงิน</label>
      

      <label for="slip">แนบสลิปการโอนเงิน (เฉพาะเมื่อโอนผ่านธนาคาร/พร้อมเพย์)</label>
      <input type="file" name="slip" id="slip" accept="image/*" required>

      <button type="submit">ยืนยันการชำระเงิน</button>
    </form>
  </div>

  <script>
    function copyAccount() {
      const account = document.getElementById('bankAccount').innerText;
      navigator.clipboard.writeText(account).then(() => {
        alert('คัดลอกเลขบัญชีเรียบร้อยแล้ว: ' + account);
      }).catch(() => {
        alert('ไม่สามารถคัดลอกเลขบัญชีได้ กรุณาคัดลอกด้วยตัวเอง');
      });
    }
  </script>
</body>
</html>
