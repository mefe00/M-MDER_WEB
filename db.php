<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "mumder_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Veritabanı bağlantı hatası: " . $conn->connect_error);
}
?>
