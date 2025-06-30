<?php
// File: /publisher/init.php (NEW)

// Mulai session jika belum ada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hubungkan ke database
require_once __DIR__ . '/../config/database.php';

// Cek apakah publisher sudah login
if (!isset($_SESSION['publisher_id'])) {
    // Jika tidak, dan mereka tidak sedang di halaman login, arahkan ke login
    if (basename($_SERVER['PHP_SELF']) != 'login.php' && basename($_SERVER['PHP_SELF']) != 'auth.php') {
        header('Location: login.php');
        exit();
    }
}
?>