<?php
// File: /admin/init.php
// INI ADALAH FILE INISIALISASI UTAMA UNTUK SEMUA HALAMAN ADMIN

// Tahap 1: Muat Konfigurasi Inti
// Ini akan menjalankan error reporting, session_start(), dan koneksi database.
// 'require_once' memastikan file ini hanya dimuat satu kali.
require_once __DIR__ . '/../config/database.php';

// Tahap 2: Jalankan Skrip Otentikasi
// Karena database.php (dengan session_start) sudah dimuat,
// skrip auth.php sekarang dapat memeriksa sesi dengan andal.
require_once __DIR__ . '/../includes/auth.php';

?>
