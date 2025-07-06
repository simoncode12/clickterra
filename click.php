<?php
// File: /click.php (FINAL - Standardized and Corrected)

require_once __DIR__ . '/config/database.php';

// Muat helper terpusat untuk deteksi pengunjung yang akurat
require_once __DIR__ . '/includes/visitor_detector.php';

$creative_id = filter_input(INPUT_GET, 'cid', FILTER_VALIDATE_INT);
$zone_id = filter_input(INPUT_GET, 'zone_id', FILTER_VALIDATE_INT); // PERBAIKAN: Gunakan 'zone_id'

// Validasi input dasar
if (!$creative_id || !$zone_id) { 
    http_response_code(400);
    exit("Invalid tracking parameters."); 
}

// Ambil landing_url dan detail bid dari creative, pastikan kampanye aktif
$stmt_creative = $conn->prepare(
    "SELECT cr.landing_url, cr.campaign_id, cr.bid_model, cr.bid_amount 
     FROM creatives cr
     JOIN campaigns c ON cr.campaign_id = c.id
     WHERE cr.id = ? AND c.status = 'active' AND cr.status = 'active'"
);
$stmt_creative->bind_param("i", $creative_id);
$stmt_creative->execute();
$creative = $stmt_creative->get_result()->fetch_assoc();
$stmt_creative->close();

if (!$creative || empty($creative['landing_url'])) { 
    http_response_code(404);
    exit("Redirect URL not found or creative is not active."); 
}

// PERBAIKAN: Gunakan data pengunjung yang sebenarnya dari helper
$visitor = get_visitor_details();
$cost_of_click = ($creative['bid_model'] === 'cpc') ? (float)$creative['bid_amount'] : 0.0;
$today = date('Y-m-d');
$campaign_id = $creative['campaign_id'];

// Masukkan statistik klik ke database dengan data yang akurat
$stmt_stats = $conn->prepare(
    "INSERT INTO campaign_stats (campaign_id, creative_id, zone_id, country, os, browser, device, stat_date, clicks, cost) 
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?) 
     ON DUPLICATE KEY UPDATE clicks = clicks + 1, cost = cost + VALUES(cost)"
);
$stmt_stats->bind_param("iiisssssd", 
    $campaign_id, 
    $creative_id, 
    $zone_id, 
    $visitor['country'], 
    $visitor['os'], 
    $visitor['browser'], 
    $visitor['device'], 
    $today, 
    $cost_of_click
);
$stmt_stats->execute();
$stmt_stats->close();
$conn->close();

// Arahkan pengunjung ke landing page
header("Location: " . $creative['landing_url'], true, 302);
exit();
?>
