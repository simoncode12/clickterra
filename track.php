<?php
// File: /track.php (NEW - Placeholder)
// Skrip ini akan menerima ping dari event VAST

// Anda bisa menambahkan logika di sini untuk mencatat event ke database di masa depan
// misalnya ke tabel `vast_events`.

// Untuk saat ini, kita hanya kirim response gambar transparan 1x1 piksel.
header('Content-Type: image/gif');
echo base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICRAEAOw==');
exit();
?>