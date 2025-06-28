<?php
// File: /config/database.php

// Aktifkan pelaporan error untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$DB_HOST = 'localhost';
$DB_USER = 'user_db';
$DB_PASS = 'Puputchen12$';
$DB_NAME = 'user_db';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}

// Set timezone
date_default_timezone_set('Asia/Jayapura');
?>
