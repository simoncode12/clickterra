<?php
// File: /ad.php (KODE LENGKAP & FINAL)

require_once __DIR__ . '/config/database.php';

header('Content-Type: application/javascript');
header('Cache-Control: no-cache, must-revalidate');

$zone_id = filter_input(INPUT_GET, 'zone_id', FILTER_VALIDATE_INT);
if (!$zone_id) { exit("/* AdServer: Zone ID not provided. */"); }

$stmt_zone = $conn->prepare("SELECT size FROM zones WHERE id = ?");
$stmt_zone->bind_param("i", $zone_id);
$stmt_zone->execute();
$zone = $stmt_zone->get_result()->fetch_assoc();
$stmt_zone->close();
if (!$zone) { exit("/* AdServer: Zone not found. */"); }
$zone_size = $zone['size'];

// PENTING: Implementasi deteksi data pengunjung di sini.
// Untuk produksi, gunakan library seperti GeoIP2 dan matomo/device-detector.
$visitor_country = 'ID'; // Placeholder
$visitor_os = 'Windows'; // Placeholder
$visitor_browser = 'Chrome'; // Placeholder
$visitor_device = 'Desktop'; // Placeholder

$sql = "
    SELECT 
        cr.id, cr.campaign_id, cr.creative_type, cr.image_url, cr.landing_url, 
        cr.script_content, cr.bid_model, cr.bid_amount
    FROM creatives cr
    JOIN campaigns c ON cr.campaign_id = c.id
    WHERE 
        c.status = 'active' AND c.serve_on_internal = 1 AND cr.status = 'active'
        AND (cr.sizes = ? OR cr.sizes = 'all')
";
$stmt_creatives = $conn->prepare($sql);
$stmt_creatives->bind_param("s", $zone_size);
$stmt_creatives->execute();
$creatives_result = $stmt_creatives->get_result();
$eligible_creatives = $creatives_result->fetch_all(MYSQLI_ASSOC);
$stmt_creatives->close();

if (empty($eligible_creatives)) { exit("/* AdServer: No eligible ads found. */"); }
$winning_creative = $eligible_creatives[array_rand($eligible_creatives)];

$cost_of_event = ($winning_creative['bid_model'] === 'cpm') ? ($winning_creative['bid_amount'] / 1000) : 0;
$today = date('Y-m-d');

$stmt_stats = $conn->prepare(
    "INSERT INTO campaign_stats (campaign_id, creative_id, zone_id, country, os, browser, device, stat_date, impressions, cost) 
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)
     ON DUPLICATE KEY UPDATE impressions = impressions + 1, cost = cost + VALUES(cost)"
);
$stmt_stats->bind_param("iiisssssd", 
    $winning_creative['campaign_id'], $winning_creative['id'], $zone_id, 
    $visitor_country, $visitor_os, $visitor_browser, $visitor_device, 
    $today, $cost_of_event
);
$stmt_stats->execute();
$stmt_stats->close();

$ad_html = '';
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];

if ($winning_creative['creative_type'] === 'image') {
    $click_url = $base_url . "/click.php?cid=" . $winning_creative['id'] . "&zid=" . $zone_id;
    $image_source = htmlspecialchars($winning_creative['image_url']);
    if (strpos($image_source, 'uploads/') === 0) { $image_source = $base_url . "/admin/" . $image_source; }
    $ad_html = '<a href="' . $click_url . '" target="_blank" rel="noopener noreferrer"><img src="' . $image_source . '" alt="Advertisement" border="0" style="max-width:100%; height:auto;" /></a>';
} elseif ($winning_creative['creative_type'] === 'script') {
    $ad_html = $winning_creative['script_content'];
}

echo "document.write(" . json_encode($ad_html, JSON_HEX_APOS | JSON_HEX_QUOT) . ");";
$conn->close();
?>