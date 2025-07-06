<?php
// File: /vast.php (FINAL - Proxy version to rtb-handler)

require_once __DIR__ . '/config/database.php';

// --- PEMERIKSAAN ANTI-FRAUD & HELPERS ---
if (file_exists(__DIR__ . '/includes/fraud_detector.php')) {
    require_once __DIR__ . '/includes/fraud_detector.php';
    if (is_fraudulent_request($conn)) {
        http_response_code(204); $conn->close(); exit();
    }
}
if (file_exists(__DIR__ . '/includes/settings.php')) {
    require_once __DIR__ . '/includes/settings.php';
}
if (!function_exists('get_setting')) {
    function get_setting($key, $conn) {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
        $host = $_SERVER['HTTP_HOST'] ?? 'userpanel.clicterra.com';
        return "{$protocol}://{$host}";
    }
}

// --- Fungsi untuk keluar dengan VAST kosong ---
function exitWithEmptyVast($conn) {
    header("Access-Control-Allow-Origin: *");
    header("Content-type: application/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?><VAST version="2.0"></VAST>';
    if ($conn) $conn->close();
    exit();
}

// --- 1. Validasi Input & Dapatkan Info Zona ---
$zone_id = filter_input(INPUT_GET, 'zone_id', FILTER_VALIDATE_INT);
if (!$zone_id) {
    exitWithEmptyVast($conn);
}

$stmt_zone = $conn->prepare(
    "SELECT z.size, s.user_id, s.url as site_url, rs.supply_key 
     FROM zones z 
     JOIN sites s ON z.site_id = s.id 
     JOIN rtb_supply_sources rs ON s.user_id = rs.user_id 
     WHERE z.id = ? AND rs.status = 'active' LIMIT 1"
);
if($stmt_zone) {
    $stmt_zone->bind_param("i", $zone_id);
    $stmt_zone->execute();
    $zone_info = $stmt_zone->get_result()->fetch_assoc();
    $stmt_zone->close();
} else {
    $zone_info = null;
}

if (!$zone_info || empty($zone_info['supply_key'])) {
    exitWithEmptyVast($conn);
}

// --- 2. Buat "Bid Request" VAST RTB Virtual ---
$mock_bid_request = [
    'id' => 'vast-wrapper-' . uniqid(),
    'imp' => [[
        'id' => '1',
        'video' => [ 'mimes' => ['video/mp4'], 'w' => 640, 'h' => 480 ],
        'tagid' => (string)$zone_id
    ]],
    'site' => [
        'id' => (string)$zone_id,
        'page' => $_SERVER['HTTP_REFERER'] ?? $zone_info['site_url'],
        'domain' => parse_url($_SERVER['HTTP_REFERER'] ?? $zone_info['site_url'], PHP_URL_HOST),
        'publisher' => [ 'id' => (string)$zone_info['user_id'] ]
    ],
    'device' => [ 'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '', 'ip' => $_SERVER['REMOTE_ADDR'] ?? '' ],
    'user' => [ 'id' => md5(($_SERVER['REMOTE_ADDR'] ?? '') . ($_SERVER['HTTP_USER_AGENT'] ?? '')) ],
    'at' => 1, 'tmax' => 500
];
$request_body_json = json_encode($mock_bid_request);

// --- 3. Panggil rtb-handler.php Secara Internal ---
$rtb_handler_domain = get_setting('ad_server_domain', $conn);
$rtb_handler_url = "{$rtb_handler_domain}/rtb-handler.php?key={$zone_info['supply_key']}&internal_call=1";

$ch = curl_init($rtb_handler_url);
curl_setopt_array($ch, [
    CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $request_body_json,
    CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 1
]);
$response_json = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// --- 4. Proses Respon dan Sajikan Iklan VAST ---
header("Access-Control-Allow-Origin: *");
header("Content-type: application/xml; charset=utf-8");

if ($http_code === 200 && !empty($response_json)) {
    $bid_response = json_decode($response_json, true);
    $ad_markup = $bid_response['seatbid'][0]['bid'][0]['adm'] ?? '';
    if (!empty($ad_markup)) {
        echo $ad_markup; // Sajikan VAST XML yang diterima dari rtb-handler
    } else {
        exitWithEmptyVast($conn);
    }
} else {
    exitWithEmptyVast($conn);
}

$conn->close();
exit();
?>
