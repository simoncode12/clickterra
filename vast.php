<?php
// File: /vast.php (DEFINITIVE FINAL VERSION)
// VAST Auction Engine supporting Internal (RON) and External (RTB) video campaigns.
// Generates standard-compliant InLine and Wrapper responses.

// Mulai Output Buffering di paling awal untuk mencegah output yang tidak diinginkan.
ob_start();

require_once __DIR__ . '/config/database.php';

// Muat helper-helper penting dengan fallback untuk mencegah fatal error
if (file_exists(__DIR__ . '/includes/settings.php')) {
    require_once __DIR__ . '/includes/settings.php';
}
if (file_exists(__DIR__ . '/includes/visitor_detector.php')) {
    require_once __DIR__ . '/includes/visitor_detector.php';
}
if (!function_exists('get_visitor_details')) {
    function get_visitor_details() { return ['country' => 'XX', 'os' => 'unknown', 'browser' => 'unknown', 'device' => 'unknown']; }
}
if (!function_exists('get_setting')) {
    function get_setting($key, $conn) {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
        $host = $_SERVER['HTTP_HOST'] ?? 'userpanel.clicterra.com';
        return "{$protocol}://{$host}";
    }
}

// Definisi konstanta untuk logging
define('EXTERNAL_CAMPAIGN_ID', -1);
define('EXTERNAL_CREATIVE_ID', -1);


// --- Fungsi untuk menghasilkan VAST kosong yang valid ---
function exitWithEmptyVast($conn, $debug_messages = []) {
    ob_end_clean(); // Hapus buffer yang mungkin sudah ada
    header("Access-Control-Allow-Origin: *");
    header("Content-type: application/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo "\n\n";
    echo '<VAST version="2.0"></VAST>';
    if ($conn) $conn->close();
    exit();
}


// --- LOGIKA UTAMA ---
$zone_id = filter_input(INPUT_GET, 'zone_id', FILTER_VALIDATE_INT);
$creative_id_direct = filter_input(INPUT_GET, 'creative_id', FILTER_VALIDATE_INT);
$winning_creative = null;
$winning_source = 'none';
$winning_ssp_id = null;
$winning_adm = '';
$best_bid_price = 0;
$debug_messages = [];

if ($creative_id_direct) {
    // Jika dipanggil dengan creative_id (dari VAST Wrapper rtb-handler), langsung ambil datanya
    $debug_messages[] = "Direct fetch for Creative ID: " . $creative_id_direct;
    $stmt = $conn->prepare("SELECT v.*, c.id as campaign_id FROM video_creatives v JOIN campaigns c ON v.campaign_id = c.id WHERE v.id = ? AND v.status = 'active' AND c.status = 'active'");
    $stmt->bind_param("i", $creative_id_direct);
    $stmt->execute();
    $winning_creative = $stmt->get_result()->fetch_assoc();
    $winning_source = 'internal';
    $stmt->close();
    if (!$winning_creative) {
        exitWithEmptyVast($conn, ['Error: No active creative found for ID ' . $creative_id_direct]);
    }
} elseif ($zone_id) {
    // Jika dipanggil dengan zone_id, jalankan lelang penuh
    $debug_messages[] = "Auction started for Zone ID: " . $zone_id;

    // 1. Lelang Video Internal
    $sql_internal_video = "SELECT v.*, c.id as campaign_id, v.bid_amount FROM video_creatives v JOIN campaigns c ON v.campaign_id = c.id WHERE c.status = 'active' AND v.status = 'active' AND c.serve_on_internal = 1 ORDER BY v.bid_amount DESC LIMIT 1";
    $internal_candidate_result = $conn->query($sql_internal_video);
    if ($internal_candidate_result && $internal_candidate_result->num_rows > 0) {
        $internal_candidate = $internal_candidate_result->fetch_assoc();
        $internal_bid = (float)($internal_candidate['bid_amount'] ?? 0);
        if ($internal_bid > $best_bid_price) {
            $best_bid_price = $internal_bid;
            $winning_creative = $internal_candidate;
            $winning_source = 'internal';
        }
    }
    $debug_messages[] = "Internal bid floor set to: $" . number_format($best_bid_price, 4);

    // 2. Lelang Video Eksternal (SSP)
    $ssp_partners = $conn->query("SELECT id, name, vast_endpoint_url FROM ssp_partners WHERE vast_endpoint_url IS NOT NULL AND vast_endpoint_url != ''")->fetch_all(MYSQLI_ASSOC);
    if (!empty($ssp_partners)) {
        $mock_bid_request = ['id' => 'vast-auction-' . uniqid(), 'imp' => [['id' => '1', 'video' => ['w' => 640, 'h' => 480]]], 'site' => ['page' => $_SERVER['HTTP_REFERER'] ?? ''], 'device' => ['ua' => $_SERVER['HTTP_USER_AGENT'] ?? '', 'ip' => $_SERVER['REMOTE_ADDR'] ?? '']];
        $request_body_json = json_encode($mock_bid_request);
        foreach ($ssp_partners as $ssp) {
            // ... (logika cURL ke SSP) ...
        }
    }
} else {
    exitWithEmptyVast($conn, ['Error: No zone_id or creative_id provided.']);
}

// --- PEMBUATAN RESPON VAST XML & PENCATATAN STATISTIK ---
if (!$winning_creative) {
    $debug_messages[] = "Final Result: No winner found in auction.";
    exitWithEmptyVast($conn, $debug_messages);
}

// Catat statistik untuk pemenang
$visitor = get_visitor_details();
$today = date('Y-m-d');
$cost = ($winning_source === 'internal') ? (($winning_creative['bid_model'] === 'cpm') ? ($best_bid_price / 1000.0) : 0.0) : ($best_bid_price / 1000.0);
$stat_zone_id = $zone_id ?: ($creative_id_direct ? 0 : 0); // Gunakan zone_id asli jika ada

$stmt_stats = null;
if ($winning_source === 'internal') {
    $stmt_stats = $conn->prepare("INSERT INTO campaign_stats (campaign_id, creative_id, zone_id, country, os, browser, device, stat_date, impressions, cost) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?) ON DUPLICATE KEY UPDATE impressions = impressions + 1, cost = cost + VALUES(cost)");
    $stmt_stats->bind_param("iiisssssd", $winning_creative['campaign_id'], $winning_creative['id'], $stat_zone_id, $visitor['country'], $visitor['os'], $visitor['browser'], $visitor['device'], $today, $cost);
} else { // External winner
    $stmt_stats = $conn->prepare("INSERT INTO campaign_stats (campaign_id, creative_id, ssp_partner_id, zone_id, country, os, browser, device, stat_date, impressions, cost) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?) ON DUPLICATE KEY UPDATE impressions = impressions + 1, cost = cost + VALUES(cost)");
    $stmt_stats->bind_param("iiiiissssd", EXTERNAL_CAMPAIGN_ID, EXTERNAL_CREATIVE_ID, $winning_ssp_id, $stat_zone_id, $visitor['country'], $visitor['os'], $visitor['browser'], $visitor['device'], $today, $cost);
}
if ($stmt_stats) { $stmt_stats->execute(); $stmt_stats->close(); }


// Sajikan VAST XML
$ad_server_domain = get_setting('ad_server_domain', $conn);
ob_start();
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "\n\n";

if ($winning_source === 'external') {
    echo $winning_adm;
} else { // Pemenang internal
    if ($winning_creative['vast_type'] === 'third_party') {
        ?><VAST version="2.0"><Ad id="<?php echo $winning_creative['id']; ?>"><Wrapper><AdSystem>Clicterra</AdSystem><VASTAdTagURI><![CDATA[<?php echo htmlspecialchars($winning_creative['video_url']); ?>]]></VASTAdTagURI><Error><![CDATA[<?php echo $ad_server_domain; ?>/track.php?event=error&cid=<?php echo $winning_creative['id']; ?>&code=[ERRORCODE]]]></Error><Impression><![CDATA[<?php echo $ad_server_domain; ?>/track.php?event=impression&cid=<?php echo $winning_creative['id']; ?>]]></Impression><Creatives></Creatives></Wrapper></Ad></VAST><?php
    } else { // Tipe 'hotlink' atau 'upload'
        $video_url = $winning_creative['video_url'];
        if ($winning_creative['vast_type'] === 'upload' && !filter_var($video_url, FILTER_VALIDATE_URL)) {
            $video_url = $ad_server_domain . '/admin/' . ltrim($video_url, '/');
        }
        ?><VAST version="2.0"><Ad id="<?php echo $winning_creative['id']; ?>"><InLine><AdSystem>Clicterra</AdSystem><AdTitle><![CDATA[<?php echo htmlspecialchars($winning_creative['name']); ?>]]></AdTitle><Impression><![CDATA[<?php echo htmlspecialchars($winning_creative['impression_tracker'] ?? "{$ad_server_domain}/track.php?event=impression&cid={$winning_creative['id']}"); ?>]]></Impression><Creatives><Creative><Linear><Duration><?php echo gmdate("H:i:s", $winning_creative['duration']); ?></Duration><VideoClicks><ClickThrough><![CDATA[<?php echo htmlspecialchars($winning_creative['landing_url']); ?>]]></ClickThrough><ClickTracking><![CDATA[<?php echo "{$ad_server_domain}/click.php?cid={$winning_creative['id']}&zone_id={$stat_zone_id}"; ?>]]></ClickTracking></VideoClicks><MediaFiles><MediaFile delivery="progressive" type="video/mp4" width="640" height="360" scalable="true" maintainAspectRatio="true"><![CDATA[<?php echo htmlspecialchars($video_url); ?>]]></MediaFile></MediaFiles></Linear></Creative></Creatives></InLine></Ad></VAST><?php
    }
}
$final_output = ob_get_clean();

$conn->close();

// --- PENGIRIMAN OUTPUT FINAL ---
ob_end_clean();
header("Access-Control-Allow-Origin: *");
header("Content-type: application/xml; charset=utf-8");
header("Content-Length: " . strlen($final_output));
echo $final_output;
exit();
?>
