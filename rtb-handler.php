<?php
// File: /rtb-handler.php (DEFINITIVE FINAL VERSION - All features and fixes included)

require_once __DIR__ . '/config/database.php';

// Muat helper-helper penting dengan fallback
if (file_exists(__DIR__ . '/includes/settings.php')) { require_once __DIR__ . '/includes/settings.php'; }
if (file_exists(__DIR__ . '/includes/visitor_detector.php')) { require_once __DIR__ . '/includes/visitor_detector.php'; }

// Hanya jalankan pemeriksaan fraud jika ini BUKAN panggilan internal dari ad.php/vast.php
if (!isset($_GET['internal_call'])) {
    if (file_exists(__DIR__ . '/includes/fraud_detector.php')) {
        require_once __DIR__ . '/includes/fraud_detector.php';
        if (is_fraudulent_request($conn)) {
            http_response_code(204);
            $conn->close();
            exit();
        }
    }
}

if (!function_exists('get_visitor_details')) { function get_visitor_details() { return ['country' => 'XX', 'os' => 'unknown', 'browser' => 'unknown', 'device' => 'unknown']; } }
if (!function_exists('get_setting')) { function get_setting($key, $conn) { return 'https://' . ($_SERVER['HTTP_HOST'] ?? 'userpanel.clicterra.com'); } }

define('EXTERNAL_CAMPAIGN_ID', -1);
define('EXTERNAL_CREATIVE_ID', -1);

// Inisialisasi variabel log
$supply_source_id_for_log = 0; $zone_id_for_log = 0; $is_bid_sent_for_log = 0; $price_for_log = null;
$visitor_details_for_log = get_visitor_details(); $country_for_log = $visitor_details_for_log['country'];

// --- Headers & Validasi Awal ---
header('Content-Type: application/json'); header('Access-Control-Allow-Origin: *');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit(json_encode(['id' => uniqid(), 'error' => 'Method Not Allowed'])); }
$request_body = file_get_contents('php://input');
$bid_request = json_decode($request_body, true);
$request_id = $bid_request['id'] ?? uniqid();
$site = $bid_request['site'] ?? [];
$domain_for_log = $site['domain'] ?? 'unknown.com';
if (json_last_error() !== JSON_ERROR_NONE) { http_response_code(400); exit(json_encode(['id' => $request_id, 'error' => 'Invalid JSON'])); }

// --- Validasi Supply Source & Ambil Revenue Share ---
$supply_key = $_GET['key'] ?? '';
$stmt_source = $conn->prepare("SELECT rs.id, rs.user_id, rs.default_zone_id, u.revenue_share FROM rtb_supply_sources rs JOIN users u ON rs.user_id = u.id WHERE rs.supply_key = ? AND rs.status = 'active'");
$stmt_source->bind_param("s", $supply_key); $stmt_source->execute();
$supply_source = $stmt_source->get_result()->fetch_assoc(); $stmt_source->close();
if (!$supply_source) {
    http_response_code(403);
    exit(json_encode(['id' => $request_id, 'error' => 'Invalid or Inactive Supply Key']));
}
$publisher_revenue_share = (float)($supply_source['revenue_share'] ?? 0);
$supply_source_id_for_log = $supply_source['id'];
$zone_id_for_log = $supply_source['default_zone_id'];
if (empty($zone_id_for_log)) { http_response_code(500); exit(json_encode(['id' => $request_id, 'error' => 'Supply source is not configured with a default zone.'])); }

// --- Ekstraksi Parameter Request ---
$imp = $bid_request['imp'][0] ?? null; $impid = $imp['id'] ?? '1';
$is_video_request = isset($imp['video']);
if ($is_video_request) { $w = $imp['video']['w'] ?? 640; $h = $imp['video']['h'] ?? 480; } 
else { $w = $imp['banner']['w'] ?? 0; $h = $imp['banner']['h'] ?? 0; }
$req_size = "{$w}x{$h}";

// --- LELANG KOMPETITIF PENUH ---
$best_bid_price = 0; $winning_creative = null; $winning_source = 'none'; $winning_ssp_id = null;

// 1. Lelang Internal (RON)
$internal_candidate = null;
if ($is_video_request) {
    $sql_internal = "SELECT v.*, c.id as campaign_id, c.ad_format_id, v.bid_model, v.bid_amount FROM video_creatives v JOIN campaigns c ON v.campaign_id = c.id WHERE c.status = 'active' AND v.status = 'active' AND c.allow_external_rtb = 1 ORDER BY v.bid_amount DESC, RAND() LIMIT 1";
    $internal_candidate = $conn->query($sql_internal)->fetch_assoc();
} else {
    $sql_internal = "SELECT cr.*, c.id as campaign_id, c.ad_format_id FROM creatives cr JOIN campaigns c ON cr.campaign_id = c.id WHERE c.status = 'active' AND c.allow_external_rtb = 1 AND cr.status = 'active' AND (cr.sizes = ? OR cr.sizes = 'all') ORDER BY cr.bid_amount DESC, RAND() LIMIT 1";
    $stmt_internal = $conn->prepare($sql_internal);
    $stmt_internal->bind_param("s", $req_size);
    $stmt_internal->execute();
    $internal_candidate = $stmt_internal->get_result()->fetch_assoc();
    $stmt_internal->close();
}
if ($internal_candidate) {
    $best_bid_price = (float)($internal_candidate['bid_amount'] ?? 0);
    $winning_creative = $internal_candidate;
    $winning_source = 'internal';
}

// 2. Lelang Eksternal (SSP)
$endpoint_key = $is_video_request ? 'vast_endpoint_url' : 'endpoint_url';
$ssp_partners = $conn->query("SELECT id, name, {$endpoint_key} FROM ssp_partners WHERE {$endpoint_key} IS NOT NULL AND {$endpoint_key} != ''")->fetch_all(MYSQLI_ASSOC);
foreach ($ssp_partners as $ssp) {
    $ssp_endpoint = $ssp[$endpoint_key];
    $ch = curl_init($ssp_endpoint);
    curl_setopt_array($ch, [CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $request_body, CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_TIMEOUT_MS => 200]);
    $ssp_response_body = curl_exec($ch); $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($http_code === 200 && !empty($ssp_response_body)) {
        // error_log("SSP Response from " . $ssp['name'] . ": " . $ssp_response_body);
        $ssp_bid = json_decode($ssp_response_body, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $ssp_price = $ssp_bid['seatbid'][0]['bid'][0]['price'] ?? 0;
            if ($ssp_price > $best_bid_price) {
                $best_bid_price = $ssp_price; $winning_creative = $ssp_bid['seatbid'][0]['bid'][0];
                $winning_source = 'external'; $winning_ssp_id = $ssp['id'];
            }
        }
    }
}

// --- Pembangunan Respon & Pencatatan Statistik ---
if ($winning_source !== 'none') {
    $publisher_price = $best_bid_price * ($publisher_revenue_share / 100.0);
    $is_bid_sent_for_log = 1; $price_for_log = $best_bid_price;
    $adm = ''; $cid = ''; $crid = ''; $adomain = []; $today = date('Y-m-d');
    
    if ($winning_source === 'internal') {
        $cid = (string)$winning_creative['campaign_id']; $crid = (string)$winning_creative['id'];
        $adomain = !empty($winning_creative['landing_url']) ? [parse_url($winning_creative['landing_url'], PHP_URL_HOST)] : [];
        $cost_for_impression = ($winning_creative['bid_model'] === 'cpm') ? ($best_bid_price / 1000.0) : 0.0;
        $ad_server_domain = get_setting('ad_server_domain', $conn);
        if ($is_video_request) {
            ob_start(); echo '<?xml version="1.0" encoding="UTF-8"?>';
            if ($winning_creative['vast_type'] === 'third_party') {
                ?><VAST version="2.0"><Ad id="<?php echo $crid; ?>"><Wrapper><AdSystem>Clicterra</AdSystem><VASTAdTagURI><![CDATA[<?php echo htmlspecialchars($winning_creative['video_url']); ?>]]></VASTAdTagURI><Error/><Impression/></Wrapper></Ad></VAST><?php
            } else {
                $video_url = $winning_creative['video_url'];
                if ($winning_creative['vast_type'] === 'upload' && !filter_var($video_url, FILTER_VALIDATE_URL)) { $video_url = $ad_server_domain . '/admin/' . ltrim($video_url, '/'); }
                ?><VAST version="2.0"><Ad id="<?php echo $crid; ?>"><InLine><AdSystem>Clicterra</AdSystem><AdTitle><![CDATA[<?php echo htmlspecialchars($winning_creative['name']); ?>]]></AdTitle><Impression><![CDATA[<?php echo "{$ad_server_domain}/track.php?event=impression&cid={$crid}"; ?>]]></Impression><Creatives><Creative><Linear><Duration><?php echo gmdate("H:i:s", $winning_creative['duration']); ?></Duration><VideoClicks><ClickThrough><![CDATA[<?php echo htmlspecialchars($winning_creative['landing_url']); ?>]]></ClickThrough><ClickTracking><![CDATA[<?php echo "{$ad_server_domain}/click.php?cid={$crid}&zone_id={$zone_id_for_log}"; ?>]]></ClickTracking></VideoClicks><MediaFiles><MediaFile delivery="progressive" type="video/mp4" width="640" height="360"><![CDATA[<?php echo htmlspecialchars($video_url); ?>]]></MediaFile></MediaFiles></Linear></Creative></Creatives></InLine></Ad></VAST><?php
            }
            $adm = ob_get_clean();
        } else {
            $click_url = $ad_server_domain . "/click.php?cid=" . $crid . "&zone_id=" . $zone_id_for_log;
            if ($winning_creative['creative_type'] === 'image' && !empty($winning_creative['landing_url'])) {
                $image_source = htmlspecialchars($winning_creative['image_url']);
                if (strpos($image_source, 'uploads/') === 0) { $image_source = $ad_server_domain . "/admin/" . $image_source; }
                $adm = '<a href="' . $click_url . '" target="_blank" rel="noopener"><img src="' . $image_source . '" alt="Ad" border="0" style="width:100%;height:auto;display:block;"></a>';
            } else { $adm = $winning_creative['script_content']; }
        }
        $stmt_stats = $conn->prepare("INSERT INTO campaign_stats (campaign_id, creative_id, zone_id, country, os, browser, device, stat_date, impressions, cost) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?) ON DUPLICATE KEY UPDATE impressions = impressions + 1, cost = cost + VALUES(cost)");
        $stmt_stats->bind_param("iiisssssd", $cid, $crid, $zone_id_for_log, $visitor_details_for_log['country'], $visitor_details_for_log['os'], $visitor_details_for_log['browser'], $visitor_details_for_log['device'], $today, $cost_for_impression);
    
    } else { // External Winner
        $cid = $winning_creative['cid'] ?? 'external_campaign'; $crid = $winning_creative['crid'] ?? 'external_creative';
        $adm = $winning_creative['adm'] ?? ''; $adomain = $winning_creative['adomain'] ?? [];
        $cost_for_impression = $best_bid_price / 1000.0;
        $campaign_id_var = EXTERNAL_CAMPAIGN_ID; $creative_id_var = EXTERNAL_CREATIVE_ID;
        $stmt_stats = $conn->prepare("INSERT INTO campaign_stats (campaign_id, creative_id, ssp_partner_id, zone_id, country, os, browser, device, stat_date, impressions, cost) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?) ON DUPLICATE KEY UPDATE impressions = impressions + 1, cost = cost + VALUES(cost)");
        $stmt_stats->bind_param("iiiiissssd", $campaign_id_var, $creative_id_var, $winning_ssp_id, $zone_id_for_log, $visitor_details_for_log['country'], $visitor_details_for_log['os'], $visitor_details_for_log['browser'], $visitor_details_for_log['device'], $today, $cost_for_impression);
    }
    
    if (isset($stmt_stats) && $stmt_stats) { $stmt_stats->execute(); $stmt_stats->close(); }
    
    http_response_code(200);
    echo json_encode(['id' => $request_id, 'seatbid' => [['bid' => [['id' => uniqid('bid_'), 'impid' => $impid, 'price' => (float)$publisher_price, 'adm' => $adm, 'adomain' => $adomain, 'cid' => $cid, 'crid' => $crid, 'w' => $w, 'h' => $h]], 'seat' => 'clicterra_dps']]]);

} else {
    $is_bid_sent_for_log = 0;
    http_response_code(204);
}

// === FINAL LOGGING STEP ===
$stmt_log = $conn->prepare("INSERT INTO rtb_requests (supply_source_id, zone_id, is_bid_sent, winning_price_cpm, country, source_domain) VALUES (?, ?, ?, ?, ?, ?)");
$stmt_log->bind_param("iiidss", $supply_source_id_for_log, $zone_id_for_log, $is_bid_sent_for_log, $price_for_log, $country_for_log, $domain_for_log);
$stmt_log->execute();
$stmt_log->close();

$conn->close();
exit();
?>
