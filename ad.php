<?php
// File: /ad.php (FINAL - Standardized and Corrected)

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/visitor_detector.php';

header('Content-Type: application/javascript');
header('Access-Control-Allow-Origin: *');

$zone_id = filter_input(INPUT_GET, 'zone_id', FILTER_VALIDATE_INT);
if (!$zone_id) { exit("/* No 'zone_id' parameter provided. */"); }

$stmt_zone = $conn->prepare("SELECT size, ad_format_id FROM zones WHERE id = ?");
$stmt_zone->bind_param("i", $zone_id);
$stmt_zone->execute();
$zone_info = $stmt_zone->get_result()->fetch_assoc();
$stmt_zone->close();
if (!$zone_info) { exit("/* Invalid Zone ID */"); }

$requested_ad_size = $zone_info['size'];
$zone_ad_format_id = $zone_info['ad_format_id'];

$sql_creatives = "SELECT cr.id, cr.campaign_id, cr.creative_type, cr.image_url, cr.landing_url, cr.script_content, cr.bid_model, cr.bid_amount FROM creatives cr JOIN campaigns c ON cr.campaign_id = c.id WHERE c.serve_on_internal = 1 AND c.status = 'active' AND cr.status = 'active' AND c.ad_format_id = ? AND (cr.sizes = ? OR cr.sizes = 'all') ORDER BY cr.bid_amount DESC, RAND() LIMIT 1";
$stmt_creatives = $conn->prepare($sql_creatives);
$stmt_creatives->bind_param("is", $zone_ad_format_id, $requested_ad_size);
$stmt_creatives->execute();
$winning_creative = $stmt_creatives->get_result()->fetch_assoc();
$stmt_creatives->close();

if ($winning_creative) {
    $cost = ($winning_creative['bid_model'] === 'cpm') ? ((float)$winning_creative['bid_amount'] / 1000.0) : 0.0;
    
    $visitor = get_visitor_details();
    $today = date('Y-m-d');

    $stmt_stats = $conn->prepare("INSERT INTO campaign_stats (campaign_id, creative_id, zone_id, country, os, browser, device, stat_date, impressions, cost) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?) ON DUPLICATE KEY UPDATE impressions = impressions + 1, cost = cost + VALUES(cost)");
    $stmt_stats->bind_param("iiisssssd", $winning_creative['campaign_id'], $winning_creative['id'], $zone_id, $visitor['country'], $visitor['os'], $visitor['browser'], $visitor['device'], $today, $cost);
    $stmt_stats->execute();
    $stmt_stats->close();

    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
    $click_url = $base_url . "/click.php?cid=" . $winning_creative['id'] . "&zone_id=" . $zone_id;
    $ad_html = '';

    if ($winning_creative['creative_type'] === 'image' && !empty($winning_creative['landing_url'])) {
        $image_source = htmlspecialchars($winning_creative['image_url']);
        if (strpos($image_source, 'uploads/') === 0) { $image_source = $base_url . "/admin/" . $image_source; }
        $ad_html = '<a href="' . $click_url . '" target="_blank" rel="noopener"><img src="' . $image_source . '" alt="Ad" border="0" style="width:100%;height:auto;display:block;"></a>';
    } elseif ($winning_creative['creative_type'] === 'script') {
        $ad_html = $winning_creative['script_content'];
    }

    if (!empty($ad_html)) {
        echo "document.write(" . json_encode($ad_html) . ");";
    }
} else {
    echo "/* No ad available */";
}

$conn->close();
exit();
?>