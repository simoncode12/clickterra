<?php
// File: /vast.php (FINAL & COMPLETE - Full VAST Auction Engine with Standard Compliant Output)

// Mulai Output Buffering untuk mencegah error XML
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

// --- Fungsi untuk menghasilkan VAST kosong yang valid ---
function exitWithEmptyVast($conn, $debug_messages = []) {
    // Bersihkan buffer yang mungkin sudah ada
    ob_end_clean();
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
$debug_messages = [];

if (!$zone_id) {
    exitWithEmptyVast($conn, ['Error: No zone_id provided.']);
}
$debug_messages[] = "Auction started for Zone ID: " . $zone_id;

// --- LELANG KOMPETITIF PENUH UNTUK VIDEO ---
$best_bid_price = 0;
$winning_creative = null;
$winning_source = 'none'; // 'internal' or 'external'
$winning_ssp_id = null;
$winning_adm = '';

// 1. Lelang Video Internal untuk Menentukan Floor Price
$sql_internal_video = "SELECT v.*, c.id as campaign_id, v.bid_amount, v.bid_model FROM video_creatives v JOIN campaigns c ON v.campaign_id = c.id WHERE c.status = 'active' AND v.status = 'active' AND c.serve_on_internal = 1 ORDER BY v.bid_amount DESC LIMIT 1";
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
    $mock_bid_request = [
        'id' => 'vast-auction-' . uniqid(),
        'imp' => [['id' => '1', 'video' => ['w' => 640, 'h' => 480, 'mimes' => ['video/mp4']]]],
        'site' => ['page' => $_SERVER['HTTP_REFERER'] ?? 'https://unknown.com', 'domain' => parse_url($_SERVER['HTTP_REFERER'] ?? 'https://unknown.com', PHP_URL_HOST)],
        'device' => ['ua' => $_SERVER['HTTP_USER_AGENT'] ?? '', 'ip' => $_SERVER['REMOTE_ADDR'] ?? '']
    ];
    $request_body_json = json_encode($mock_bid_request);

    foreach ($ssp_partners as $ssp) {
        $debug_messages[] = "Calling SSP: " . $ssp['name'];
        $ch = curl_init($ssp['vast_endpoint_url']);
        curl_setopt_array($ch, [CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $request_body_json, CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_TIMEOUT_MS => 400]);
        $ssp_response_body = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200 && !empty($ssp_response_body)) {
            $ssp_bid = json_decode($ssp_response_body, true);
            $ssp_price = $ssp_bid['seatbid'][0]['bid'][0]['price'] ?? 0;
            $debug_messages[] = "SSP '{$ssp['name']}' responded with bid price: $" . number_format($ssp_price, 4);
            if ($ssp_price > $best_bid_price) {
                $debug_messages[] = "--> New winner: " . $ssp['name'];
                $best_bid_price = $ssp_price;
                $winning_adm = $ssp_bid['seatbid'][0]['bid'][0]['adm'] ?? '';
                $winning_source = 'external';
                $winning_ssp_id = $ssp['id'];
            }
        } else {
            $debug_messages[] = "SSP '{$ssp['name']}' gave no valid bid (HTTP: {$http_code}).";
        }
    }
}

// --- PEMBUATAN RESPON VAST XML & PENCATATAN STATISTIK ---
if ($winning_source === 'none') {
    $debug_messages[] = "Final Result: No winner found in auction.";
    exitWithEmptyVast($conn, $debug_messages);
}

$debug_messages[] = "Final Winner: " . $winning_source . " with bid $" . number_format($best_bid_price, 4);

// Catat statistik untuk pemenang
$visitor = get_visitor_details();
$today = date('Y-m-d');
$cost = $best_bid_price / 1000.0;
$stmt_stats = null;

if ($winning_source === 'internal') {
    $cost = ($winning_creative['bid_model'] === 'cpm') ? $cost : 0.0;
    $stmt_stats = $conn->prepare("INSERT INTO campaign_stats (campaign_id, creative_id, zone_id, country, os, browser, device, stat_date, impressions, cost) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?) ON DUPLICATE KEY UPDATE impressions = impressions + 1, cost = cost + VALUES(cost)");
    $stmt_stats->bind_param("iiisssssd", $winning_creative['campaign_id'], $winning_creative['id'], $zone_id, $visitor['country'], $visitor['os'], $visitor['browser'], $visitor['device'], $today, $cost);
} else { // External winner
    $stmt_stats = $conn->prepare("INSERT INTO campaign_stats (campaign_id, creative_id, ssp_partner_id, zone_id, country, os, browser, device, stat_date, impressions, cost) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?) ON DUPLICATE KEY UPDATE impressions = impressions + 1, cost = cost + VALUES(cost)");
    $stmt_stats->bind_param("iiiiissssd", EXTERNAL_CAMPAIGN_ID, EXTERNAL_CREATIVE_ID, $winning_ssp_id, $zone_id, $visitor['country'], $visitor['os'], $visitor['browser'], $visitor['device'], $today, $cost);
}
if($stmt_stats) {
    $stmt_stats->execute();
    $stmt_stats->close();
}

// Sajikan VAST XML
$ad_server_domain = get_setting('ad_server_domain', $conn);
ob_start(); // Mulai buffer baru untuk XML bersih
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "\n\n";

if ($winning_source === 'external') {
    echo $winning_adm;
} else {
    if ($winning_creative['vast_type'] === 'third_party') {
        ?><VAST version="2.0"><Ad id="<?php echo $winning_creative['id']; ?>"><Wrapper><AdSystem>Clicterra</AdSystem><VASTAdTagURI><![CDATA[<?php echo htmlspecialchars($winning_creative['video_url']); ?>]]></VASTAdTagURI><Error><![CDATA[<?php echo $ad_server_domain; ?>/track.php?event=error&cid=<?php echo $winning_creative['id']; ?>&code=[ERRORCODE]]]></Error><Impression><![CDATA[<?php echo $ad_server_domain; ?>/track.php?event=impression&cid=<?php echo $winning_creative['id']; ?>]]></Impression><Creatives></Creatives></Wrapper></Ad></VAST><?php
    } else {
        $video_url = $winning_creative['video_url'];
        if ($winning_creative['vast_type'] === 'upload' && !filter_var($video_url, FILTER_VALIDATE_URL)) {
            $video_url = $ad_server_domain . '/admin/' . ltrim($video_url, '/');
        }
        ?><VAST version="2.0"><Ad id="<?php echo $winning_creative['id']; ?>"><InLine><AdSystem>Clicterra</AdSystem><AdTitle><![CDATA[<?php echo htmlspecialchars($winning_creative['name']); ?>]]></AdTitle><Impression><![CDATA[<?php echo htmlspecialchars($winning_creative['impression_tracker'] ?? "{$ad_server_domain}/track.php?event=impression&cid={$winning_creative['id']}"); ?>]]></Impression><Creatives><Creative><Linear><Duration><?php echo gmdate("H:i:s", $winning_creative['duration']); ?></Duration><VideoClicks><ClickThrough><![CDATA[<?php echo htmlspecialchars($winning_creative['landing_url']); ?>]]></ClickThrough><ClickTracking><![CDATA[<?php echo "{$ad_server_domain}/click.php?cid={$winning_creative['id']}&zone_id={$zone_id}"; ?>]]></ClickTracking></VideoClicks><MediaFiles><MediaFile delivery="progressive" type="video/mp4" width="640" height="360" scalable="true" maintainAspectRatio="true"><![CDATA[<?php echo htmlspecialchars($video_url); ?>]]></MediaFile></MediaFiles></Linear></Creative></Creatives></InLine></Ad></VAST><?php
    }
}
$final_output = ob_get_clean(); // Tangkap XML bersih

$conn->close();

// --- PENGIRIMAN OUTPUT FINAL ---
ob_end_clean(); // Hapus buffer utama (membuang spasi/error)
header("Access-Control-Allow-Origin: *");
header("Content-type: application/xml; charset=utf-8");
header("Content-Length: " . strlen($final_output));
echo $final_output;
exit();
?>
