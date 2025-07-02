<?php
// File: /config/database.php (FIXED)
$servername = "localhost";
$username = "user_db"; 
$password = "Puputchen12$"; 
$dbname = "user_db";

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
