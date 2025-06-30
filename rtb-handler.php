<?php
// File: /rtb-handler.php

error_log("DEBUG: [" . date('Y-m-d H:i:s') . "] RTB Handler: Script started.");

require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['id' => uniqid('bidreq_'), 'error' => 'Method Not Allowed. Only POST requests are accepted.']);
    exit();
}

// 1. Validasi Supply Key
$supply_key = $_GET['key'] ?? '';
if (empty($supply_key)) {
    http_response_code(400);
    echo json_encode(['id' => uniqid('bidreq_'), 'error' => 'Supply Key is missing.']);
    exit();
}
$stmt_source = $conn->prepare("SELECT id, user_id, name, status FROM rtb_supply_sources WHERE supply_key = ?");
$stmt_source->bind_param("s", $supply_key);
$stmt_source->execute();
$supply_source = $stmt_source->get_result()->fetch_assoc();
$stmt_source->close();
if (!$supply_source || $supply_source['status'] !== 'active') {
    http_response_code(403);
    echo json_encode(['id' => uniqid('bidreq_'), 'error' => 'Invalid or inactive Supply Key.']);
    exit();
}

// 2. Parse input bid request
$request_body = file_get_contents('php://input');
$bid_request = json_decode($request_body, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['id' => uniqid('bidreq_'), 'error' => 'Invalid JSON in request body.']);
    exit();
}

// Ekstrak parameter
$request_id = $bid_request['id'] ?? uniqid('bidreq_');
$impid = $bid_request['imp'][0]['id'] ?? '1';
$requested_ad_width = $bid_request['imp'][0]['banner']['w'] ?? 0;
$requested_ad_height = $bid_request['imp'][0]['banner']['h'] ?? 0;
$requested_ad_size = "{$requested_ad_width}x{$requested_ad_height}";
$zone_id_for_stats = filter_input(INPUT_GET, 'zone_id', FILTER_VALIDATE_INT) ?? 0;
if ($zone_id_for_stats === 0 && isset($bid_request['imp'][0]['tagid'])) {
    $zone_id_for_stats = filter_var($bid_request['imp'][0]['tagid'], FILTER_VALIDATE_INT);
    if ($zone_id_for_stats === false) $zone_id_for_stats = 0;
}

// Targeting parameter
$visitor_country_for_query = strtoupper($bid_request['device']['geo']['country'] ?? 'UNKNOWN');

// =====================
// Cari Creative Internal
// =====================
$best_bid_price = 0;
$winning_creative = null;
$winning_source = 'internal';

// Perbaikan join targeting dan query negara!
$sql_creatives = "
    SELECT
        cr.id, cr.campaign_id, cr.creative_type, cr.image_url, cr.landing_url,
        cr.script_content, cr.bid_model, cr.bid_amount, cr.sizes, cr.name
    FROM creatives cr
    JOIN campaigns c ON cr.campaign_id = c.id
    LEFT JOIN campaign_targeting ct ON c.id = ct.campaign_id
    WHERE
        c.status = 'active'
        AND c.allow_external_rtb = 1
        AND cr.status = 'active'
        AND (cr.sizes = ? OR cr.sizes = 'all')
        AND (
            ct.countries IS NULL OR ct.countries = '' OR FIND_IN_SET(?, ct.countries)
        )
    ORDER BY cr.bid_amount DESC
    LIMIT 1
";

$stmt_creatives = $conn->prepare($sql_creatives);
if ($stmt_creatives === false) {
    error_log("ERROR: [" . date('Y-m-d H:i:s') . "] RTB Handler: Failed to prepare creative query: " . $conn->error);
    http_response_code(500);
    echo json_encode(['id' => uniqid('bidreq_'), 'error' => 'Internal Server Error.']);
    exit();
}
$stmt_creatives->bind_param("ss", $requested_ad_size, $visitor_country_for_query);
$stmt_creatives->execute();
$result_creatives = $stmt_creatives->get_result();
$internal_winning_creative = $result_creatives->fetch_assoc();
$result_creatives->free_result();
$stmt_creatives->close();

if ($internal_winning_creative) {
    $best_bid_price = (float)$internal_winning_creative['bid_amount'];
    $winning_creative = $internal_winning_creative;
    $winning_source = 'internal';
}

// =====================
// Cari Creative Eksternal SSP (Partner RTB)
// =====================
$ssp_partners = [];
$stmt_ssp = $conn->prepare("SELECT id, name, endpoint_url, partner_key FROM ssp_partners");
$stmt_ssp->execute();
$result_ssp = $stmt_ssp->get_result();
while ($row = $result_ssp->fetch_assoc()) {
    $ssp_partners[] = $row;
}
$result_ssp->free_result();
$stmt_ssp->close();

$external_winning_bid_info = null;

foreach ($ssp_partners as $ssp) {
    $outbound_bid_request_json = json_encode($bid_request);
    $ch = curl_init($ssp['endpoint_url']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $outbound_bid_request_json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 150);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 100);
    $ssp_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_errno = curl_errno($ch);
    curl_close($ch);

    if ($curl_errno || $http_code !== 200) continue;
    $ssp_bid_response = json_decode($ssp_response, true);
    if (json_last_error() !== JSON_ERROR_NONE) continue;

    $ssp_bid_price = $ssp_bid_response['seatbid'][0]['bid'][0]['price'] ?? 0;
    $ssp_adm = $ssp_bid_response['seatbid'][0]['bid'][0]['adm'] ?? '';

    if ($ssp_bid_price > $best_bid_price) {
        $best_bid_price = $ssp_bid_price;
        $winning_creative = [
            'id' => 'external_' . $ssp['id'],
            'campaign_id' => 'external_' . $ssp['id'],
            'creative_type' => 'external',
            'script_content' => $ssp_adm,
            'bid_model' => 'cpm',
            'bid_amount' => $ssp_bid_price,
            'landing_url' => $ssp_bid_response['seatbid'][0]['bid'][0]['nurl'] ?? $ssp_bid_response['seatbid'][0]['bid'][0]['burl'] ?? null,
            'name' => $ssp['name'] . ' Ad'
        ];
        $winning_source = 'external';
        $external_winning_bid_info = [
            'ssp_id' => $ssp['id'],
            'ssp_name' => $ssp['name'],
            'price' => $ssp_bid_price,
            'adm' => $ssp_adm,
            'adomain' => $ssp_bid_response['seatbid'][0]['bid'][0]['adomain'] ?? []
        ];
    }
}

// ==============
// Buat Response
// ==============
$bid_response = ['id' => $request_id, 'seatbid' => []];
if ($winning_creative) {
    $bid_response['seatbid'][] = [
        'bid' => [
            [
                'id' => uniqid('bid_'),
                'impid' => $impid,
                'price' => (float)$best_bid_price,
                'adm' => $winning_source === 'internal' ? $winning_creative['script_content'] : $external_winning_bid_info['adm'],
                'cid' => (string)($winning_source === 'internal' ? $winning_creative['campaign_id'] : 'ext_ssp_' . ($external_winning_bid_info['ssp_id'] ?? '')),
                'crid' => (string)($winning_source === 'internal' ? $winning_creative['id'] : 'ext_bid_' . uniqid()),
                'w' => (int)$requested_ad_width,
                'h' => (int)$requested_ad_height,
                'adomain' => $winning_source === 'internal'
                    ? [parse_url($winning_creative['landing_url'] ?? '', PHP_URL_HOST)]
                    : ($external_winning_bid_info['adomain'] ?? [])
            ]
        ],
        'seat' => 'clicterra_seat'
    ];
    http_response_code(200);
    echo json_encode($bid_response);
} else {
    http_response_code(204);
}

$conn->close();
exit();

