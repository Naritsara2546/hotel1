<?php
$servername = "localhost";
$username = "root";  // ชื่อผู้ใช้ MySQL ของคุณ
$password = "";      // รหัสผ่าน MySQL ของคุณ

// เชื่อมต่อฐานข้อมูล user_db
$userConn = new mysqli($servername, $username, $password, "user_db");
if ($userConn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูล user_db ล้มเหลว: " . $userConn->connect_error);
}

// เชื่อมต่อฐานข้อมูล hotel_db
$hotelConn = new mysqli($servername, $username, $password, "hotel_db");
if ($hotelConn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูล hotel_db ล้มเหลว: " . $hotelConn->connect_error);
}
?>
