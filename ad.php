<?php
// File: /ad.php
// Endpoint penayangan iklan untuk traffic internal (RON).
// Versi ini mengimplementasikan penargetan yang sama dengan rtb-handler.php
// dan memilih iklan internal dengan bid_amount tertinggi.

error_log("DEBUG: [" . date('Y-m-d H:i:s') . "] Ad Handler: Script started.");

require_once __DIR__ . '/config/database.php'; // Memuat koneksi database

error_log("DEBUG: [" . date('Y-m-d H:i:s') . "] Ad Handler: Database connection attempted.");

// Atur header agar respons adalah JavaScript (untuk document.write)
header('Content-Type: application/javascript');
header('Access-Control-Allow-Origin: *'); // Izinkan CORS untuk penayangan lintas domain

// Dapatkan ukuran iklan yang diminta dari parameter GET
$width = filter_input(INPUT_GET, 'w', FILTER_VALIDATE_INT);
$height = filter_input(INPUT_GET, 'h', FILTER_VALIDATE_INT);
$zone_id = filter_input(INPUT_GET, 'zid', FILTER_VALIDATE_INT); // ID Zona dari penerbit

$requested_ad_size = "{$width}x{$height}";

if ($width <= 0 || $height <= 0) {
    error_log("ERROR: [" . date('Y-m-d H:i:s') . "] Ad Handler: Invalid ad size requested: " . $requested_ad_size);
    echo "document.write('');";
    exit();
}

// --- Deteksi Properti Pengunjung ---
// Ini adalah deteksi dasar. Untuk produksi, pertimbangkan library geoloc (misal: GeoIP)
// dan deteksi user agent yang lebih canggih.

// Deteksi IP Pengunjung
$visitor_ip = $_SERVER['REMOTE_ADDR'];
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $visitor_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
// Ambil IP pertama jika ada beberapa (misal dari proxy)
$visitor_ip = explode(',', $visitor_ip)[0];

// Deteksi User Agent
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

// --- START PERUBAHAN UNTUK SEMUA TARGETING DARI DATABASE ---

// Ambil Negara dari DB
$country_map = [];
$stmt_countries = $conn->query("SELECT iso_alpha_3_code, name FROM geo_countries");
if ($stmt_countries) {
    while ($row = $stmt_countries->fetch_assoc()) {
        $country_map[strtoupper($row['iso_alpha_3_code'])] = $row['name'];
    }
    $stmt_countries->close();
} else {
    error_log("ERROR: [" . date('Y-m-d H:i:s') . "] Ad Handler: Failed to load countries from geo_countries table: " . $conn->error);
}

// Placeholder untuk deteksi negara berdasarkan IP. Anda perlu mengimplementasikan ini.
// Contoh sederhana:
// $visitor_country_code = 'IDN'; // Default atau hasil lookup GeoIP
// Untuk saat ini, kita akan menggunakan default atau logika sederhana
$visitor_country_code = 'UNKNOWN'; // Default
// Coba deteksi dari header (jika proxy/CDN menyediakannya)
if (isset($_SERVER['HTTP_CF_IPCOUNTRY'])) { // Cloudflare
    $visitor_country_code = $_SERVER['HTTP_CF_IPCOUNTRY'];
} elseif (isset($_SERVER['HTTP_X_REAL_IP'])) { // Nginx proxy
    // Anda perlu melakukan lookup GeoIP di sini
    // Misalnya: $visitor_country_code = geoip_country_code_by_name($visitor_ip);
    $visitor_country_code = 'IDN'; // Placeholder: Ganti dengan lookup GeoIP
}

$visitor_country = $country_map[strtoupper($visitor_country_code)] ?? $visitor_country_code;


// Ambil Browser dari DB
$browsers_db = [];
$browsers_result = $conn->query("SELECT name FROM geo_browsers ORDER BY name ASC");
if ($browsers_result) {
    while ($row = $browsers_result->fetch_assoc()) {
        $browsers_db[] = $row['name'];
    }
    $browsers_result->close();
} else {
    error_log("ERROR: [" . date('Y-m-d H:i:s') . "] Ad Handler: Failed to load browsers from geo_browsers table: " . $conn->error);
}
// Deteksi browser dari User Agent
$visitor_browser = 'UNKNOWN';
foreach ($browsers_db as $browser_name) {
    if (stripos($user_agent, $browser_name) !== false) {
        $visitor_browser = $browser_name;
        break;
    }
}
if ($visitor_browser === 'UNKNOWN' && !empty($user_agent)) {
    $visitor_browser = 'OTHER'; // Jika tidak cocok dengan yang ada di DB
}


// Ambil OS dari DB
$os_list_db = [];
$os_list_result = $conn->query("SELECT name FROM geo_os ORDER BY name ASC");
if ($os_list_result) {
    while ($row = $os_list_result->fetch_assoc()) {
        $os_list_db[] = $row['name'];
    }
    $os_list_result->close();
} else {
    error_log("ERROR: [" . date('Y-m-d H:i:s') . "] Ad Handler: Failed to load OS from geo_os table: " . $conn->error);
}
// Deteksi OS dari User Agent
$visitor_os = 'UNKNOWN';
foreach ($os_list_db as $os_name) {
    if (stripos($user_agent, $os_name) !== false) {
        $visitor_os = $os_name;
        break;
    }
}
if ($visitor_os === 'UNKNOWN' && !empty($user_agent)) {
    $visitor_os = 'OTHER'; // Jika tidak cocok dengan yang ada di DB
}


// Ambil Devices dari DB
$devices_db = [];
$devices_result = $conn->query("SELECT name FROM geo_devices ORDER BY name ASC");
if ($devices_result) {
    while ($row = $devices_result->fetch_assoc()) {
        $devices_db[] = $row['name'];
    }
    $devices_result->close();
} else {
    error_log("ERROR: [" . date('Y-m-d H:i:s') . "] Ad Handler: Failed to load devices from geo_devices table: " . $conn->error);
}
// Deteksi Device Type dari User Agent (sederhana)
$visitor_device = 'UNKNOWN';
if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobile))/i', $user_agent)) {
    $visitor_device = 'Tablet';
} elseif (preg_match('/(mobi|android|iphone|blackberry|fennec)/i', $user_agent)) {
    $visitor_device = 'Mobile';
} else {
    $visitor_device = 'Desktop';
}
// Pastikan device yang terdeteksi ada di DB
if (!in_array($visitor_device, $devices_db) && $visitor_device !== 'UNKNOWN') {
    $visitor_device = 'OTHER';
}


// Ambil Connection Types dari DB
$connections_db = [];
$connections_result = $conn->query("SELECT name FROM geo_connections ORDER BY name ASC");
if ($connections_result) {
    while ($row = $connections_result->fetch_assoc()) {
        $connections_db[] = $row['name'];
    }
    $connections_result->close();
} else {
    error_log("ERROR: [" . date('Y-m-d H:i:s') . "] Ad Handler: Failed to load connections from geo_connections table: " . $conn->error);
}
// Deteksi Connection Type (sangat sulit tanpa info dari ISP/mobile carrier)
// Untuk tujuan ini, kita akan asumsikan 'WiFi' atau 'Cellular' atau 'Broadband'
// atau Anda bisa mencoba mendeteksi dari IP range jika Anda memiliki database ISP.
$visitor_connection = 'Broadband'; // Default umum
// Jika Anda ingin lebih spesifik, Anda perlu integrasi dengan layanan pihak ketiga atau database IP.
// Untuk demo, kita bisa asumsikan default ini.
if (!in_array($visitor_connection, $connections_db) && $visitor_connection !== 'UNKNOWN') {
    $visitor_connection = 'OTHER';
}

// --- AKHIR PERUBAHAN UNTUK SEMUA TARGETING DARI DATABASE ---

error_log("DEBUG: [" . date('Y-m-d H:i:s') . "] Ad Handler: Detected Params -> AdSize: " . $requested_ad_size . ", Country: " . $visitor_country . ", OS: " . $visitor_os . ", Browser: " . $visitor_browser . ", Device: " . $visitor_device . ", Connection: " . $visitor_connection . ", Zone ID: " . $zone_id);


// 4. Cari iklan internal terbaik yang memenuhi syarat berdasarkan Targeting
$best_bid_price = 0;
$winning_creative = null;

$sql_creatives = "
    SELECT
        cr.id, cr.campaign_id, cr.creative_type, cr.image_url, cr.landing_url,
        cr.script_content, cr.bid_model, cr.bid_amount, cr.sizes, cr.name
    FROM creatives cr
    JOIN campaigns c ON cr.campaign_id = c.id
    LEFT JOIN campaign_targeting ct ON c.id = ct.campaign_id
    WHERE
        c.status = 'active'
        AND c.serve_on_internal = 1 -- Hanya kampanye yang diizinkan tayang di jaringan internal
        AND cr.status = 'active'
        AND (cr.sizes = ? OR cr.sizes = 'all')
        AND (ct.countries IS NULL OR ct.countries = '' OR FIND_IN_SET(?, ct.countries))
        AND (ct.browsers IS NULL OR ct.browsers = '' OR FIND_IN_SET(?, ct.browsers))
        AND (ct.devices IS NULL OR ct.devices = '' OR FIND_IN_SET(?, ct.devices))
        AND (ct.os IS NULL OR ct.os = '' OR FIND_IN_SET(?, ct.os))
        AND (ct.connection_types IS NULL OR ct.connection_types = '' OR FIND_IN_SET(?, ct.connection_types))
    ORDER BY cr.bid_amount DESC
    LIMIT 1;
";

$stmt_creatives = $conn->prepare($sql_creatives);
if ($stmt_creatives === false) {
    error_log("ERROR: [" . date('Y-m-d H:i:s') . "] Ad Handler: Failed to prepare internal creative selection query: " . $conn->error);
    echo "document.write('');";
    exit();
}

$stmt_creatives->bind_param("ssssss",
    $requested_ad_size,
    $visitor_country,
    $visitor_browser,
    $visitor_device,
    $visitor_os,
    $visitor_connection
);

error_log("DEBUG: [" . date('Y-m-d H:i:s') . "] Ad Handler: Executing creative query.");
if (!$stmt_creatives->execute()) {
    error_log("ERROR: [" . date('Y-m-d H:i:s') . "] Ad Handler: Internal creative query execution failed: " . $stmt_creatives->error);
    echo "document.write('');";
    exit();
}

$winning_creative = $stmt_creatives->get_result()->fetch_assoc();
$stmt_creatives->close();

error_log("DEBUG: [" . date('Y-m-d H:i:s') . "] Ad Handler: Internal creative query completed. Winning Creative (var_export): " . var_export($winning_creative, true));

if ($winning_creative) {
    $best_bid_price = (float)$winning_creative['bid_amount'];
    error_log("DEBUG: [" . date('Y-m-d H:i:s') . "] Ad Handler: Winning internal creative found with bid: " . $best_bid_price);

    $today = date('Y-m-d');
    $cost_for_advertiser = ($winning_creative['bid_model'] === 'cpm') ? ($winning_creative['bid_amount'] / 1000) : 0; // Hitung cost per impresi

    // Catat statistik impresi
    error_log("DEBUG: [" . date('Y-m-d H:i:s') . "] Ad Handler: Preparing stats insert for Campaign ID: " . $winning_creative['campaign_id'] . ", Creative ID: " . $winning_creative['id']);
    $stmt_stats = $conn->prepare(
        "INSERT INTO campaign_stats (campaign_id, creative_id, zone_id, country, os, browser, device, stat_date, impressions, cost)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)
         ON DUPLICATE KEY UPDATE impressions = impressions + 1, cost = cost + VALUES(cost)"
    );
    if ($stmt_stats === false) {
        error_log("ERROR: [" . date('Y-m-d H:i:s') . "] Ad Handler: Failed to prepare campaign_stats insert: " . $conn->error);
    } else {
        $stmt_stats->bind_param("iiisssssd",
            $winning_creative['campaign_id'], $winning_creative['id'], $zone_id,
            $visitor_country, $visitor_os, $visitor_browser, $visitor_device,
            $today, $cost_for_advertiser
        );
        if (!$stmt_stats->execute()) {
             error_log("ERROR: [" . date('Y-m-d H:i:s') . "] Ad Handler: Failed to execute campaign_stats insert: " . $stmt_stats->error);
        }
        $stmt_stats->close();
    }
    error_log("DEBUG: [" . date('Y-m-d H:i:s') . "] Ad Handler: Stats insertion attempt finished.");

    // Bangun Ad Markup (ADM)
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
    $click_url = $base_url . "/click.php?cid=" . $winning_creative['id'] . "&zid=" . $zone_id;

    $ad_html = '';
    if ($winning_creative['creative_type'] === 'image') {
        $image_source = htmlspecialchars($winning_creative['image_url']);
        // Pastikan URL gambar lengkap jika disimpan relatif
        if (strpos($image_source, 'uploads/') === 0) {
            $image_source = $base_url . "/admin/" . $image_source;
        }
        $ad_html = '<a href="' . $click_url . '" target="_blank" rel="noopener noreferrer"><img src="' . $image_source . '" alt="Advertisement" border="0" style="max-width:100%; height:auto;" /></a>';
    } elseif ($winning_creative['creative_type'] === 'script') {
        $ad_html = $winning_creative['script_content'];
    }

    // Output JavaScript untuk menulis iklan ke halaman
    // Gunakan JSON.stringify untuk menangani karakter khusus dalam HTML/JS
    echo "document.write(" . json_encode($ad_html) . ");";

} else {
    error_log("DEBUG: [" . date('Y-m-d H:i:s') . "] Ad Handler: No matching internal creative found for size " . $requested_ad_size . " and targeting.");
    echo "document.write('');";
}

$conn->close();
error_log("DEBUG: [" . date('Y-m-d H:i:s') . "] Ad Handler: Script finished and database closed.");
exit();
?>