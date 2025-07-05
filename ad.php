<?php
// File: /ad.php (FINAL - Proxy version with Anti-Fraud check integrated)

require_once __DIR__ . '/config/database.php';

// --- LANGKAH 1: PEMERIKSAAN ANTI-FRAUD ---
// "Penjaga" ini ditempatkan di paling atas untuk memblokir traffic buruk secepat mungkin.
if (file_exists(__DIR__ . '/includes/fraud_detector.php')) {
    require_once __DIR__ . '/includes/fraud_detector.php';
    if (is_fraudulent_request($conn)) {
        // Jika terdeteksi fraud, hentikan eksekusi dengan senyap.
        http_response_code(204); // 204 No Content
        $conn->close();
        exit();
    }
}

// Muat helper lain setelah lolos dari pemeriksaan fraud
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


// --- Headers ---
header('Content-Type: application/javascript');
header('Access-Control-Allow-Origin: *');


// --- Fungsi untuk keluar dengan senyap ---
function exit_silently($message = "Ad serving failed") {
    error_log("Ad.php Exit: " . $message);
    echo "/* " . htmlspecialchars($message) . " */";
    exit();
}


// --- 1. Validasi Input & Dapatkan Info Zona ---
$zone_id = filter_input(INPUT_GET, 'zone_id', FILTER_VALIDATE_INT);
if (!$zone_id) {
    exit_silently("No 'zone_id' parameter provided.");
}

$stmt_zone = $conn->prepare(
    "SELECT z.size, s.user_id, s.url as site_url, rs.supply_key 
     FROM zones z 
     JOIN sites s ON z.site_id = s.id 
     JOIN rtb_supply_sources rs ON s.user_id = rs.user_id 
     WHERE z.id = ? AND rs.status = 'active' LIMIT 1"
);
$stmt_zone->bind_param("i", $zone_id);
$stmt_zone->execute();
$zone_info = $stmt_zone->get_result()->fetch_assoc();
$stmt_zone->close();

if (!$zone_info || empty($zone_info['supply_key'])) {
    exit_silently("Zone or active supply key not found for Zone ID: {$zone_id}");
}

// --- 2. Buat "Bid Request" RTB Virtual ---
$size = explode('x', $zone_info['size']);
$width = $size[0] ?? 300;
$height = $size[1] ?? 250;

$mock_bid_request = [
    'id' => 'ron-wrapper-' . uniqid(),
    'imp' => [
        [
            'id' => '1',
            'banner' => [
                'w' => (int)$width,
                'h' => (int)$height,
            ],
            'tagid' => (string)$zone_id
        ]
    ],
    'site' => [
        'id' => (string)$zone_id,
        'page' => $_SERVER['HTTP_REFERER'] ?? $zone_info['site_url'],
        'domain' => parse_url($_SERVER['HTTP_REFERER'] ?? $zone_info['site_url'], PHP_URL_HOST),
        'publisher' => [
            'id' => (string)$zone_info['user_id']
        ]
    ],
    'device' => [
        'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ],
    'user' => [
        'id' => md5(($_SERVER['REMOTE_ADDR'] ?? '') . ($_SERVER['HTTP_USER_AGENT'] ?? ''))
    ],
    'at' => 1,
    'tmax' => 500
];
$request_body_json = json_encode($mock_bid_request);


// --- 3. Panggil rtb-handler.php Secara Internal ---
$rtb_handler_domain = get_setting('rtb_handler_domain', $conn);
$rtb_handler_url = "{$rtb_handler_domain}/rtb-handler.php?key={$zone_info['supply_key']}&internal_call=1";

$ch = curl_init($rtb_handler_url);
curl_setopt_array($ch, [
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => $request_body_json,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 1 // 1 second timeout
]);
$response_json = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);


// --- 4. Proses Respon dan Sajikan Iklan ---
if ($http_code === 200 && !empty($response_json)) {
    $bid_response = json_decode($response_json, true);
    $ad_markup = $bid_response['seatbid'][0]['bid'][0]['adm'] ?? '';

    if (!empty($ad_markup)) {
        echo "document.write(" . json_encode($ad_markup) . ");";
    } else {
        exit_silently("Auction won but Ad Markup was empty.");
    }
} else {
    // Jika tidak ada bid (204) atau terjadi error
    exit_silently("No ad available from auction (HTTP: {$http_code}).");
}

$conn->close();
exit();
?>
