<?php
// File: /admin/ron-creative-action.php

// Muat konfigurasi inti dan otentikasi.
require_once __DIR__ . '/init.php'; 

// Validasi bahwa request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ron-campaigns-report.php');
    exit();
}

// 1. Validasi dan Sanitasi Input
$campaign_id = filter_input(INPUT_POST, 'campaign_id', FILTER_VALIDATE_INT);
$creative_name = trim($_POST['creative_name'] ?? '');
$bid_model = in_array($_POST['bid_model'], ['cpc', 'cpm']) ? $_POST['bid_model'] : 'cpc';
$bid_amount = filter_input(INPUT_POST, 'bid_amount', FILTER_VALIDATE_FLOAT);
$creative_size = trim($_POST['creative_size'] ?? '');
$image_url = filter_input(INPUT_POST, 'image_url', FILTER_VALIDATE_URL);

// Cek apakah ada data wajib yang kosong
if (!$campaign_id || empty($creative_name) || !$bid_amount || empty($creative_size) || !$image_url) {
    // Jika ada yang kosong, kembalikan ke halaman sebelumnya dengan pesan error
    $_SESSION['error_message'] = "All fields are required to add a creative.";
    header('Location: ron-creative.php?campaign_id=' . $campaign_id);
    exit();
}

// 2. Proses ke Database
try {
    $stmt = $conn->prepare(
        "INSERT INTO creatives (campaign_id, name, bid_model, bid_amount, image_url, sizes) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    // `sizes` untuk RON hanya memiliki satu nilai, berbeda dengan RTB
    $stmt->bind_param("issdss", $campaign_id, $creative_name, $bid_model, $bid_amount, $image_url, $creative_size);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "New creative '" . htmlspecialchars($creative_name) . "' has been added successfully!";
    } else {
        throw new Exception($stmt->error);
    }
    
    $stmt->close();

} catch (Exception $e) {
    // Simpan pesan error untuk ditampilkan
    $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
}

// Arahkan kembali ke halaman manajemen creative setelah proses selesai
header('Location: ron-creative.php?campaign_id=' . $campaign_id);
exit();
?>
