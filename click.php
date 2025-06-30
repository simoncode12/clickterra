<?php
// File: /click.php (KODE LENGKAP & FINAL)

require_once __DIR__ . '/config/database.php';

$creative_id = filter_input(INPUT_GET, 'cid', FILTER_VALIDATE_INT);
$zone_id = filter_input(INPUT_GET, 'zid', FILTER_VALIDATE_INT);
if (!$creative_id || !$zone_id) { exit("Invalid tracking parameters."); }

$stmt_creative = $conn->prepare("SELECT landing_url, campaign_id, bid_model, bid_amount FROM creatives WHERE id = ?");
$stmt_creative->bind_param("i", $creative_id);
$stmt_creative->execute();
$creative = $stmt_creative->get_result()->fetch_assoc();
$stmt_creative->close();
if (!$creative || empty($creative['landing_url'])) { exit("Redirect URL not found."); }

// PENTING: Implementasi deteksi data pengunjung di sini.
$visitor_country = 'ID'; // Placeholder
$visitor_os = 'Windows'; // Placeholder
$visitor_browser = 'Chrome'; // Placeholder
$visitor_device = 'Desktop'; // Placeholder

$cost_of_event = ($creative['bid_model'] === 'cpc') ? $creative['bid_amount'] : 0;
$today = date('Y-m-d');
$campaign_id = $creative['campaign_id'];

$stmt_stats = $conn->prepare(
    "INSERT INTO campaign_stats (campaign_id, creative_id, zone_id, country, os, browser, device, stat_date, clicks, cost) 
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)
     ON DUPLICATE KEY UPDATE clicks = clicks + 1, cost = cost + VALUES(cost)"
);
$stmt_stats->bind_param("iiisssssd", 
    $campaign_id, $creative_id, $zone_id, 
    $visitor_country, $visitor_os, $visitor_browser, $visitor_device, 
    $today, $cost_of_event
);
$stmt_stats->execute();
$stmt_stats->close();
$conn->close();

header("Location: " . $creative['landing_url'], true, 302);
exit();
?>