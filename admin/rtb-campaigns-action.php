<?php
// File: /admin/rtb-campaigns-action.php (Updated)

// Muat konfigurasi inti dan otentikasi.
require_once __DIR__ . '/init.php'; 

// Validasi bahwa request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: rtb-campaigns.php');
    exit();
}

// 1. Validasi dan Sanitasi Input
$campaign_name = trim($_POST['campaign_name'] ?? '');
$advertiser_id = filter_input(INPUT_POST, 'advertiser_id', FILTER_VALIDATE_INT);
$category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT); // <-- BARIS BARU

// Targeting (mengubah array menjadi string dipisahkan koma)
$countries = isset($_POST['countries']) ? implode(',', $_POST['countries']) : '';
$browsers = isset($_POST['browsers']) ? implode(',', $_POST['browsers']) : '';
$devices = isset($_POST['devices']) ? implode(',', $_POST['devices']) : '';
$os = isset($_POST['os']) ? implode(',', $_POST['os']) : '';
$connections = isset($_POST['connections']) ? implode(',', $_POST['connections']) : '';

// Creative
$creative_name = trim($_POST['creative_name'] ?? '');
$bid_model = in_array($_POST['bid_model'], ['cpc', 'cpm']) ? $_POST['bid_model'] : 'cpc';
$bid_amount = filter_input(INPUT_POST, 'bid_amount', FILTER_VALIDATE_FLOAT);
$creative_sizes = isset($_POST['creative_sizes']) ? implode(',', $_POST['creative_sizes']) : ''; // Untuk RTB
$image_url = filter_input(INPUT_POST, 'image_url', FILTER_VALIDATE_URL);

// Cek apakah ada data wajib yang kosong (termasuk category_id)
if (empty($campaign_name) || !$advertiser_id || !$category_id || empty($creative_name) || !$bid_amount || empty($creative_sizes) || !$image_url) {
    $_SESSION['error_message'] = "All fields are required. Please fill out the form completely.";
    header('Location: rtb-campaigns.php');
    exit();
}


// 2. Proses ke Database menggunakan Transaksi
$conn->begin_transaction();

try {
    // Langkah A: Insert ke tabel `campaigns` (dengan category_id)
    $stmt1 = $conn->prepare("INSERT INTO campaigns (advertiser_id, category_id, name, campaign_type) VALUES (?, ?, ?, 'rtb')");
    $stmt1->bind_param("iis", $advertiser_id, $category_id, $campaign_name); // <-- 'i' ditambahkan
    $stmt1->execute();
    
    $campaign_id = $conn->insert_id;
    
    if ($campaign_id == 0) {
        throw new Exception("Failed to create campaign.");
    }

    // Langkah B: Insert ke tabel `campaign_targeting`
    $stmt2 = $conn->prepare("INSERT INTO campaign_targeting (campaign_id, countries, browsers, devices, os, connection_types) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt2->bind_param("isssss", $campaign_id, $countries, $browsers, $devices, $os, $connections);
    $stmt2->execute();

    // Langkah C: Insert ke tabel `creatives`
    $stmt3 = $conn->prepare("INSERT INTO creatives (campaign_id, name, bid_model, bid_amount, image_url, sizes) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt3->bind_param("issdss", $campaign_id, $creative_name, $bid_model, $bid_amount, $image_url, $creative_sizes);
    $stmt3->execute();

    // Jika semua query berhasil, commit transaksi
    $conn->commit();
    
    $_SESSION['success_message'] = "RTB Campaign '<strong>" . htmlspecialchars($campaign_name) . "</strong>' has been created successfully!";
    header('Location: rtb-campaigns.php');

} catch (Exception $e) {
    // Jika terjadi error, batalkan semua perubahan (rollback)
    $conn->rollback();
    
    $_SESSION['error_message'] = "An error occurred while creating the campaign: " . $e->getMessage();
    header('Location: rtb-campaigns.php');
} finally {
    // Tutup statement
    if (isset($stmt1)) $stmt1->close();
    if (isset($stmt2)) $stmt2->close();
    if (isset($stmt3)) $stmt3->close();
}

exit();
?>

