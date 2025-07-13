<?php
// File: /rtb-handler.php - Complete optimized version with improved performance
// Last updated: 2025-07-12

// Konfigurasi eksekusi untuk mengurangi timeout
set_time_limit(3); // Berikan sedikit waktu lebih untuk eksekusi
ini_set('memory_limit', '128M'); // Pastikan memori cukup

// Nonaktifkan error reporting dan logging untuk performa
ini_set('display_errors', 0);
ini_set('log_errors', 0);

// Set batas waktu eksekusi untuk mencegah timeout
set_time_limit(5);

// Tangani error secara diam-diam agar tidak mengganggu output JSON
function silent_error_handler($errno, $errstr, $errfile, $errline) {
    return true; // Mencegah PHP menampilkan error
}
set_error_handler("silent_error_handler");

// Load konfigurasi database
require_once __DIR__ . '/config/database.php';

// Load helper files jika tersedia
if (file_exists(__DIR__ . '/includes/settings.php')) { require_once __DIR__ . '/includes/settings.php'; }
if (file_exists(__DIR__ . '/includes/visitor_detector.php')) { require_once __DIR__ . '/includes/visitor_detector.php'; }

// KONFIGURASI
define('FORCE_INTERNAL_CAMPAIGNS', false);
define('INTERNAL_PRIORITY_MULTIPLIER', 2);
define('ENABLE_CAMPAIGN_ROTATION', true);
define('DEBUG_VIDEO_MODE', false);
define('MIN_BID_PRICE', 0.0001); // Minimum valid bid price
define('FAIR_ROTATION', true);    // Enable fair rotation of RON campaigns
define('CACHE_TTL', 60);          // Cache time to live in seconds
define('SIMPLE_MODE_FOR_HIGH_BIDS', true); // Use simpler selection for high bids

// 2. FUNGSI UNTUK CACHE SEDERHANA BERBASIS FILE
function get_cache($key) {
    $cache_dir = __DIR__ . '/cache';
    if (!file_exists($cache_dir)) {
        @mkdir($cache_dir, 0755, true);
    }
    
    $cache_file = $cache_dir . '/' . md5($key) . '.cache';
    if (file_exists($cache_file) && (filemtime($cache_file) + CACHE_TTL > time())) {
        return unserialize(file_get_contents($cache_file));
    }
    return null;
}

function set_cache($key, $data) {
    $cache_dir = __DIR__ . '/cache';
    if (!file_exists($cache_dir)) {
        @mkdir($cache_dir, 0755, true);
    }
    
    $cache_file = $cache_dir . '/' . md5($key) . '.cache';
    file_put_contents($cache_file, serialize($data));
}

// Helper function untuk video debug (dinonaktifkan)
function debug_video($message) {
    if (!DEBUG_VIDEO_MODE) return;
    
    $log_dir = __DIR__ . '/logs';
    if (!file_exists($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }
    @file_put_contents($log_dir . '/video_debug.log', date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

// Deteksi browser, OS, dan perangkat dari bid request
function extract_device_info($bid_request) {
    $device_info = [
        'country' => 'XX',
        'os' => 'Unknown',
        'browser' => 'Unknown',
        'device' => 'Desktop'
    ];
    
    // Ekstrak negara dari data geo
    $device = $bid_request['device'] ?? [];
    $geo = $device['geo'] ?? [];
    if (!empty($geo['country'])) {
        $device_info['country'] = $geo['country'];
    }
    
    // Ekstrak tipe perangkat
    $deviceType = $device['devicetype'] ?? 2;
    if ($deviceType == 1) {
        $device_info['device'] = 'Desktop';
    } else if ($deviceType == 2) {
        $device_info['device'] = 'Mobile';
    } else if ($deviceType == 3) {
        $device_info['device'] = 'Tablet';
    } else if ($deviceType == 4) {
        $device_info['device'] = 'Connected TV';
    }
    
    // Ekstrak OS dan browser dari user agent
    $ua = $device['ua'] ?? '';
    if (!empty($ua)) {
        if (stripos($ua, 'windows') !== false) {
            $device_info['os'] = 'Windows';
        } elseif (stripos($ua, 'macintosh') !== false || stripos($ua, 'mac os') !== false) {
            $device_info['os'] = 'macOS';
        } elseif (stripos($ua, 'android') !== false) {
            $device_info['os'] = 'Android';
        } elseif (stripos($ua, 'iphone') !== false || stripos($ua, 'ipad') !== false || stripos($ua, 'ipod') !== false) {
            $device_info['os'] = 'iOS';
        } elseif (stripos($ua, 'linux') !== false) {
            $device_info['os'] = 'Linux';
        }
        
        if (stripos($ua, 'chrome') !== false && stripos($ua, 'edg') === false) {
            $device_info['browser'] = 'Chrome';
        } elseif (stripos($ua, 'firefox') !== false) {
            $device_info['browser'] = 'Firefox';
        } elseif (stripos($ua, 'safari') !== false && stripos($ua, 'chrome') === false) {
            $device_info['browser'] = 'Safari';
        } elseif (stripos($ua, 'edg') !== false) {
            $device_info['browser'] = 'Edge';
        } elseif (stripos($ua, 'opera') !== false || stripos($ua, 'opr') !== false) {
            $device_info['browser'] = 'Opera';
        }
    }
    
    return $device_info;
}

// Fallback untuk get_setting jika tidak tersedia
if (!function_exists('get_setting')) { 
    function get_setting($key, $conn) { 
        return 'https://' . ($_SERVER['HTTP_HOST'] ?? 'userpanel.clicterra.com'); 
    } 
}

// Konstanta untuk external campaigns
define('EXTERNAL_CAMPAIGN_ID', -1);
define('EXTERNAL_CREATIVE_ID', -1);

// Inisialisasi variabel log
$supply_source_id_for_log = 0; 
$zone_id_for_log = 0; 
$is_bid_sent_for_log = 0; 
$price_for_log = null;

// Headers untuk respons
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

// Validasi metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    http_response_code(405); 
    exit(json_encode(['id' => uniqid(), 'error' => 'Method Not Allowed'])); 
}

// Baca dan parse request body
try {
    $request_start_time = microtime(true);
    $request_body = file_get_contents('php://input');
    if (empty($request_body)) {
        http_response_code(400);
        exit(json_encode(['id' => uniqid(), 'error' => 'Empty Request']));
    }
    
    $bid_request = json_decode($request_body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400); 
        exit(json_encode(['id' => uniqid(), 'error' => 'Invalid JSON'])); 
    }
    
    $request_id = $bid_request['id'] ?? uniqid();
    $site = $bid_request['site'] ?? [];
    $domain_for_log = $site['domain'] ?? 'unknown.com';
    
    // Get visitor details
    $visitor_details_for_log = extract_device_info($bid_request);
    $country_for_log = $visitor_details_for_log['country'];
    
    // Validasi supply key
    $supply_key = $_GET['key'] ?? '';
    if (empty($supply_key)) {
        http_response_code(400);
        exit(json_encode(['id' => $request_id, 'error' => 'Missing supply key']));
    }
    
    // Dapatkan dan validasi supply source - Gunakan cache
    $supply_source = get_cache('supply_key_' . $supply_key);
    
    if ($supply_source === null) {
        $stmt_source = $conn->prepare("SELECT rs.id, rs.user_id, rs.default_zone_id, u.revenue_share FROM rtb_supply_sources rs JOIN users u ON rs.user_id = u.id WHERE rs.supply_key = ? AND rs.status = 'active'");
        
        if (!$stmt_source) {
            http_response_code(500);
            exit(json_encode(['id' => $request_id, 'error' => 'Database Error']));
        }
        
        $stmt_source->bind_param("s", $supply_key); 
        $stmt_source->execute();
        $supply_source = $stmt_source->get_result()->fetch_assoc(); 
        $stmt_source->close();
        
        // Cache hasil untuk permintaan selanjutnya
        if ($supply_source) {
            set_cache('supply_key_' . $supply_key, $supply_source);
        }
    }
    
    if (!$supply_source) {
        http_response_code(403);
        exit(json_encode(['id' => $request_id, 'error' => 'Invalid or Inactive Supply Key']));
    }
    
    $publisher_revenue_share = (float)($supply_source['revenue_share'] ?? 0);
    $supply_source_id_for_log = $supply_source['id'];
    $zone_id_for_log = $supply_source['default_zone_id'];
    
    if (empty($zone_id_for_log)) { 
        http_response_code(500); 
        exit(json_encode(['id' => $request_id, 'error' => 'Supply source is not configured with a default zone.'])); 
    }
    
    // Ekstraksi parameter request
    $imp = $bid_request['imp'][0] ?? null; 
    
    if (!$imp) {
        http_response_code(400);
        exit(json_encode(['id' => $request_id, 'error' => 'Invalid or missing impression object']));
    }
    
    $impid = $imp['id'] ?? '1';
    $is_video_request = isset($imp['video']);
    
    if ($is_video_request) { 
        $w = $imp['video']['w'] ?? 640; 
        $h = $imp['video']['h'] ?? 480; 
    } else { 
        $w = $imp['banner']['w'] ?? 0; 
        $h = $imp['banner']['h'] ?? 0; 
    }
    $req_size = "{$w}x{$h}";
    
    // LELANG RTB - DIOPTIMALKAN UNTUK KECEPATAN
    $best_bid_price = 0; 
    $best_bid_price_for_competition = 0;
    $winning_creative = null; 
    $winning_source = 'none'; 
    $winning_ssp_id = null;
    
    // Cek apakah masih ada cukup waktu untuk proses
    $time_elapsed = microtime(true) - $request_start_time;
    $still_have_time = ($time_elapsed < 0.3); // Batas waktu 300ms
    
    if ($still_have_time) {
        // 1. Internal Auction (RON) with optimized fair rotation
        $internal_candidates = [];
        if ($is_video_request) {
            // Gunakan cache untuk query video creatives jika memungkinkan
            $cache_key = 'video_creatives_' . $req_size;
            $internal_candidates = get_cache($cache_key);
            
            if ($internal_candidates === null) {
                $sql_internal = "SELECT v.*, c.id as campaign_id, c.ad_format_id, v.bid_model, v.bid_amount, c.internal_priority 
                                FROM video_creatives v 
                                JOIN campaigns c ON v.campaign_id = c.id 
                                WHERE c.status = 'active' AND v.status = 'active' AND c.serve_on_internal = 1
                                ORDER BY v.bid_amount DESC 
                                LIMIT 15";
                $result = $conn->query($sql_internal);
                
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $internal_candidates[] = $row;
                    }
                    
                    // Cache results for 1 minute
                    set_cache($cache_key, $internal_candidates);
                }
            }
        } else {
            // Prioritaskan size yang tepat dahulu
            $banner_sql = "SELECT cr.*, c.id as campaign_id, c.ad_format_id, c.internal_priority 
                          FROM creatives cr 
                          JOIN campaigns c ON cr.campaign_id = c.id 
                          WHERE c.status = 'active' 
                          AND c.serve_on_internal = 1
                          AND cr.status = 'active' 
                          AND (cr.sizes = '{$req_size}' OR cr.sizes = 'all')
                          ORDER BY cr.bid_amount DESC 
                          LIMIT 15";
                          
            $result = $conn->query($banner_sql);
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $internal_candidates[] = $row;
                }
                
                if (count($internal_candidates) == 0) {
                    $fallback_sql = "SELECT cr.*, c.id as campaign_id, c.ad_format_id, c.internal_priority 
                                   FROM creatives cr 
                                   JOIN campaigns c ON cr.campaign_id = c.id 
                                   WHERE c.status = 'active' 
                                   AND c.serve_on_internal = 1
                                   AND cr.status = 'active'
                                   ORDER BY cr.bid_amount DESC 
                                   LIMIT 15";
                    $fallback_result = $conn->query($fallback_sql);
                    
                    if ($fallback_result) {
                        while ($row = $fallback_result->fetch_assoc()) {
                            $internal_candidates[] = $row;
                        }
                    }
                }
            }
        }
        
        // OPTIMIZED FAIR ROTATION: Improved for performance
        if (count($internal_candidates) > 0) {
            // Set default winner to first candidate
            $internal_candidate = $internal_candidates[0];
            $is_high_bid = false;
            
            // Check if we have high bid campaign (fast path)
            foreach ($internal_candidates as $candidate) {
                if (isset($candidate['bid_amount']) && (float)$candidate['bid_amount'] >= 0.01) {
                    $internal_candidate = $candidate;
                    $is_high_bid = true;
                    break;
                }
            }
            
            if (FAIR_ROTATION && !$is_high_bid && count($internal_candidates) > 1) {
                // Cache key based on campaign IDs
                $campaign_ids = [];
                foreach ($internal_candidates as $candidate) {
                    $campaign_ids[] = $candidate['campaign_id'];
                }
                sort($campaign_ids); // Sort for consistent key
                $cache_key = 'campaign_stats_' . implode('_', $campaign_ids);
                
                // Try to get stats from cache first
                $campaign_stats = get_cache($cache_key);
                
                if ($campaign_stats === null) {
                    // Cache miss - fetch from database with limit
                    $campaign_stats = [];
                    
                    if (!empty($campaign_ids)) {
                        $campaign_ids_str = implode(',', $campaign_ids);
                        
                        // Simplified and optimized query
                        $stats_query = "SELECT campaign_id, SUM(impressions) as total_impressions 
                                       FROM campaign_stats 
                                       WHERE campaign_id IN ({$campaign_ids_str}) 
                                         AND stat_date = CURRENT_DATE()
                                       GROUP BY campaign_id
                                       LIMIT 50"; // Limit to prevent excessive results
                        
                        $stats_result = $conn->query($stats_query);
                        if ($stats_result) {
                            while ($row = $stats_result->fetch_assoc()) {
                                $campaign_stats[$row['campaign_id']] = (int)$row['total_impressions'];
                            }
                            
                            // Cache the result
                            set_cache($cache_key, $campaign_stats);
                        }
                    }
                }
                
                // Find campaigns with zero impressions - prioritize these
                $zero_impression_candidates = [];
                foreach ($internal_candidates as $idx => $candidate) {
                    $camp_id = $candidate['campaign_id'];
                    if (!isset($campaign_stats[$camp_id]) || $campaign_stats[$camp_id] == 0) {
                        $zero_impression_candidates[] = $idx;
                    }
                }
                
                // If we have campaigns with zero impressions, select one randomly
                if (!empty($zero_impression_candidates)) {
                    $selected_idx = $zero_impression_candidates[array_rand($zero_impression_candidates)];
                    $internal_candidate = $internal_candidates[$selected_idx];
                } 
                else {
                    // Simplified weighting algorithm for better performance
                    $weights = [];
                    $total_weight = 0;
                    
                    foreach ($internal_candidates as $idx => $candidate) {
                        $camp_id = $candidate['campaign_id'];
                        $bid = (float)($candidate['bid_amount'] ?? 0.0001);
                        $priority = (int)($candidate['internal_priority'] ?? 1);
                        $impressions = isset($campaign_stats[$camp_id]) ? $campaign_stats[$camp_id] : 0;
                        
                        // Simplified formula: bid * priority / (impressions + 1)
                        // This ensures campaigns with fewer impressions get higher weight
                        $weight = $bid * $priority / ($impressions + 1);
                        $weights[$idx] = $weight;
                        $total_weight += $weight;
                    }
                    
                    // Quick select using weights
                    if ($total_weight > 0) {
                        $random_value = mt_rand(0, (int)($total_weight * 1000)) / 1000;
                        $current_weight = 0;
                        $selected_idx = 0;
                        
                        foreach ($weights as $idx => $weight) {
                            $current_weight += $weight;
                            if ($random_value <= $current_weight) {
                                $selected_idx = $idx;
                                break;
                            }
                        }
                        
                        $internal_candidate = $internal_candidates[$selected_idx];
                    }
                }
            }
            
            // Set internal winner
            $internal_bid_amount = (float)($internal_candidate['bid_amount'] ?? 0);
            
            // Ensure bid amount is valid
            if ($internal_bid_amount >= MIN_BID_PRICE) {
                $best_bid_price = $internal_bid_amount;
                $best_bid_price_for_competition = $internal_bid_amount * INTERNAL_PRIORITY_MULTIPLIER;
                $winning_creative = $internal_candidate;
                $winning_source = 'internal';
            }
        }
        
        // Cek apakah masih ada waktu untuk external auction
        $time_elapsed = microtime(true) - $request_start_time;
        $still_have_time_for_external = ($time_elapsed < 0.35); // Lebih ketat untuk external
        
        // 2. External Auction (SSP) if internal winner is not forced and time permits
        if ($still_have_time_for_external && !FORCE_INTERNAL_CAMPAIGNS) {
            // Cache key for external partners
            $ext_cache_key = 'ssp_partners_' . ($is_video_request ? 'video' : 'banner');
            $ssp_partners = get_cache($ext_cache_key);
            
            if ($ssp_partners === null) {
                $endpoint_key = $is_video_request ? 'vast_endpoint_url' : 'endpoint_url';
                $ssp_partners = $conn->query("SELECT id, name, {$endpoint_key} FROM ssp_partners WHERE {$endpoint_key} IS NOT NULL AND {$endpoint_key} != ''")->fetch_all(MYSQLI_ASSOC);
                
                // Cache results
                if (!empty($ssp_partners)) {
                    set_cache($ext_cache_key, $ssp_partners);
                }
            }
            
            // Only try external if we have time and we're not in high-bid mode
            if (!empty($ssp_partners) && (!$is_high_bid || $best_bid_price < 0.01)) {
                foreach ($ssp_partners as $ssp) {
                    $endpoint_key = $is_video_request ? 'vast_endpoint_url' : 'endpoint_url';
                    $ssp_endpoint = $ssp[$endpoint_key];
                    
                    try {
                        $ch = curl_init($ssp_endpoint);
                        curl_setopt_array($ch, [
                            CURLOPT_POST => 1, 
                            CURLOPT_POSTFIELDS => $request_body, 
                            CURLOPT_RETURNTRANSFER => true, 
                            CURLOPT_HTTPHEADER => ['Content-Type: application/json'], 
                            CURLOPT_TIMEOUT_MS => 150,  // Faster timeout for external calls
                            CURLOPT_CONNECTTIMEOUT_MS => 100  // Faster connect timeout
                        ]);
                        $ssp_response_body = curl_exec($ch); 
                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
                        curl_close($ch);
                        
                        if ($http_code === 200 && !empty($ssp_response_body)) {
                            $ssp_bid = json_decode($ssp_response_body, true);
                            if (json_last_error() === JSON_ERROR_NONE && 
                                isset($ssp_bid['seatbid'][0]['bid'][0]['price'])) {
                                
                                $ssp_price = (float)($ssp_bid['seatbid'][0]['bid'][0]['price'] ?? 0);
                                
                                // Ensure external bid is valid
                                if ($ssp_price >= MIN_BID_PRICE && $ssp_price > $best_bid_price_for_competition) {
                                    $best_bid_price = $ssp_price;
                                    $best_bid_price_for_competition = $ssp_price;
                                    $winning_creative = $ssp_bid['seatbid'][0]['bid'][0];
                                    $winning_source = 'external'; 
                                    $winning_ssp_id = $ssp['id'];
                                }
                            }
                        }
                    } catch (Exception $e) {
                        // Silently ignore SSP errors and continue with auction
                    }
                }
            }
        }
    }
    
    // Build Response & Log Stats
    if ($winning_source !== 'none') {
        $publisher_price = $best_bid_price * ($publisher_revenue_share / 100.0);
        
        // Ensure price is positive and valid (to avoid Invalid Bid)
        if ($publisher_price < MIN_BID_PRICE) {
            $publisher_price = MIN_BID_PRICE;
        }
        
        $is_bid_sent_for_log = 1; 
        $price_for_log = $best_bid_price;
        $adm = ''; 
        $cid = ''; 
        $crid = ''; 
        $adomain = []; 
        $today = date('Y-m-d');
        
        if ($winning_source === 'internal') {
            $cid = (string)$winning_creative['campaign_id']; 
            $crid = (string)$winning_creative['id'];
            $adomain = !empty($winning_creative['landing_url']) ? [parse_url($winning_creative['landing_url'], PHP_URL_HOST)] : ['adstart.click'];
            $cost_for_impression = ($winning_creative['bid_model'] === 'cpm') ? ($best_bid_price / 1000.0) : 0.0;
            $ad_server_domain = get_setting('ad_server_domain', $conn);
            
            if ($is_video_request) {
                try {
                    // Generate VAST XML
                    $timestamp = time();
                    $video_click_url = $ad_server_domain . "/click.php?cid=" . $crid . 
                                      "&zone_id=" . $zone_id_for_log . 
                                      "&video=1&campaign_id=" . $cid . 
                                      "&timestamp=" . $timestamp;
                    $impression_url = $ad_server_domain . "/vast-pixel.php?cid=" . $crid . 
                                     "&zone_id=" . $zone_id_for_log . 
                                     "&campaign_id=" . $cid . 
                                     "&event=impression&timestamp=" . $timestamp;
                    
                    $adm = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
                    $adm .= "<VAST version=\"3.0\">\n";
                    $adm .= "  <Ad id=\"".$crid."\">\n";
                    
                    if ($winning_creative['vast_type'] === 'third_party' && strpos($winning_creative['video_url'], 'srv.aso1.net') !== false) {
                        $zone_param = '';
                        if (preg_match('/[?&]z=([0-9]+)/', $winning_creative['video_url'], $matches)) {
                            $zone_param = $matches[1];
                        }
                        
                        $proxy_url = $ad_server_domain . '/aso1-vast-proxy.php';
                        if (!empty($zone_param)) {
                            $proxy_url .= '?z=' . $zone_param;
                        }
                        
                        $adm = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
                        $adm .= "<VAST version=\"3.0\">\n";
                        $adm .= "  <Ad id=\"".$crid."\">\n";
                        $adm .= "    <Wrapper>\n";
                        $adm .= "      <AdSystem>Clicterra</AdSystem>\n";
                        $adm .= "      <VASTAdTagURI><![CDATA[".$proxy_url."]]></VASTAdTagURI>\n";
                        $adm .= "      <Impression><![CDATA[".$impression_url."]]></Impression>\n";
                        $adm .= "      <VideoClicks>\n";
                        $adm .= "        <ClickTracking><![CDATA[".$ad_server_domain."/vast-pixel.php?cid=".$crid."&zone_id=".$zone_id_for_log."&event=clickThrough&campaign_id=".$cid."&timestamp=".$timestamp."]]></ClickTracking>\n";
                        $adm .= "      </VideoClicks>\n";
                        $adm .= "    </Wrapper>\n";
                        $adm .= "  </Ad>\n";
                        $adm .= "</VAST>";
                    } 
                    else if ($winning_creative['vast_type'] === 'third_party') {
                        $adm .= "    <Wrapper>\n";
                        $adm .= "      <AdSystem>Clicterra</AdSystem>\n";
                        $adm .= "      <VASTAdTagURI><![CDATA[".$winning_creative['video_url']."]]></VASTAdTagURI>\n";
                        $adm .= "      <Impression><![CDATA[".$impression_url."]]></Impression>\n";
                        $adm .= "      <VideoClicks>\n";
                        $adm .= "        <ClickTracking><![CDATA[".$ad_server_domain."/vast-pixel.php?cid=".$crid."&zone_id=".$zone_id_for_log."&event=clickThrough&campaign_id=".$cid."&timestamp=".$timestamp."]]></ClickTracking>\n";
                        $adm .= "      </VideoClicks>\n";
                        $adm .= "    </Wrapper>\n";
                    } else {
                        $video_url = $winning_creative['video_url'];
                        if ($winning_creative['vast_type'] === 'upload' && !filter_var($video_url, FILTER_VALIDATE_URL)) { 
                            $video_url = $ad_server_domain . '/admin/' . ltrim($video_url, '/'); 
                        }
                        
                        $adm .= "    <InLine>\n";
                        $adm .= "      <AdSystem>Clicterra</AdSystem>\n";
                        $adm .= "      <AdTitle>Video Ad</AdTitle>\n";
                        $adm .= "      <Impression><![CDATA[".$impression_url."]]></Impression>\n";
                        $adm .= "      <Creatives>\n";
                        $adm .= "        <Creative>\n";
                        $adm .= "          <Linear>\n";
                        $adm .= "            <Duration>00:00:30</Duration>\n";
                        $adm .= "            <VideoClicks>\n";
                        $adm .= "              <ClickThrough><![CDATA[".$video_click_url."]]></ClickThrough>\n";
                        $adm .= "              <ClickTracking><![CDATA[".$ad_server_domain."/vast-pixel.php?cid=".$crid."&zone_id=".$zone_id_for_log."&event=clickThrough&campaign_id=".$cid."&timestamp=".$timestamp."]]></ClickTracking>\n";
                        $adm .= "            </VideoClicks>\n";
                        $adm .= "            <MediaFiles>\n";
                        $adm .= "              <MediaFile delivery=\"progressive\" type=\"video/mp4\" width=\"".$w."\" height=\"".$h."\"><![CDATA[".$video_url."]]></MediaFile>\n";
                        $adm .= "            </MediaFiles>\n";
                        $adm .= "          </Linear>\n";
                        $adm .= "        </Creative>\n";
                        $adm .= "      </Creatives>\n";
                        $adm .= "    </InLine>\n";
                    }
                    
                    $adm .= "  </Ad>\n";
                    $adm .= "</VAST>";
                } catch (Exception $e) {
                    // If VAST generation fails, use a fallback
                    $adm = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
                    $adm .= "<VAST version=\"3.0\">\n";
                    $adm .= "  <Ad id=\"".$crid."\">\n";
                    $adm .= "    <InLine>\n";
                    $adm .= "      <AdSystem>Clicterra</AdSystem>\n";
                    $adm .= "      <AdTitle>Fallback Video Ad</AdTitle>\n";
                    $adm .= "      <Creatives>\n";
                    $adm .= "        <Creative>\n";
                    $adm .= "          <Linear>\n";
                    $adm .= "            <Duration>00:00:15</Duration>\n";
                    $adm .= "            <MediaFiles>\n";
                    $adm .= "              <MediaFile delivery=\"progressive\" type=\"video/mp4\" width=\"640\" height=\"360\"><![CDATA[https://storage.googleapis.com/gvabox/media/samples/stock.mp4]]></MediaFile>\n";
                    $adm .= "            </MediaFiles>\n";
                    $adm .= "          </Linear>\n";
                    $adm .= "        </Creative>\n";
                    $adm .= "      </Creatives>\n";
                    $adm .= "    </InLine>\n";
                    $adm .= "  </Ad>\n";
                    $adm .= "</VAST>";
                }
            } else {
                // Banner creative handling - optimized
                $click_url = $ad_server_domain . "/click.php?cid=" . $crid . 
                          "&zone_id=" . $zone_id_for_log . 
                          "&campaign_id=" . $cid . 
                          "&timestamp=" . time();
                
                if ($winning_creative['creative_type'] === 'image' && !empty($winning_creative['landing_url'])) {
                    $image_url = $winning_creative['image_url'];
                    if (strpos($image_url, 'uploads/') === 0) { 
                        $image_url = $ad_server_domain . "/admin/" . $image_url; 
                    }
                    $adm = '<a href="' . $click_url . '" target="_blank" id="ad-click-link" rel="noopener"><img src="' . $image_url . '" alt="Ad" border="0" style="width:100%;height:auto;display:block;"></a>';
                } else {
                    $script_content = $winning_creative['script_content'];
                    
                    // Simplified script wrapper to avoid Invalid Bid issues
                    $adm = "<div>{$script_content}</div><script>document.addEventListener('click',function(){var i=new Image;i.src='{$click_url}';});</script>";
                }
            }
            
            // Record internal campaign stats - use ON DUPLICATE KEY
            $campaign_id = $cid; 
            $creative_id = $crid;
            $zone_id = $zone_id_for_log;
            $country = $visitor_details_for_log['country'];
            $os = $visitor_details_for_log['os'];
            $browser = $visitor_details_for_log['browser'];
            $device = $visitor_details_for_log['device'];
            $stat_date = $today;
            $impressions = 1;
            $cost = $cost_for_impression;
            
            // Escape strings
            $country = $conn->real_escape_string($country);
            $os = $conn->real_escape_string($os);
            $browser = $conn->real_escape_string($browser);
            $device = $conn->real_escape_string($device);
            $stat_date = $conn->real_escape_string($stat_date);
            
            // Special handling for video stats
            if ($is_video_request) {
                // Untuk video, tambahkan suffix unik untuk menghindari duplikasi
                $timestamp = time();
                $random_suffix = substr(md5(uniqid()), 0, 5);
                $modified_browser = $browser . "_v_" . $random_suffix;
                
                // Use ON DUPLICATE KEY UPDATE to prevent duplicate stats and be faster
                $video_stats_sql = "INSERT INTO campaign_stats 
                               (campaign_id, creative_id, zone_id, country, os, browser, device, stat_date, impressions, cost, is_video) 
                               VALUES 
                               ({$campaign_id}, {$creative_id}, {$zone_id}, '{$country}', '{$os}', '{$modified_browser}', '{$device}', '{$stat_date}', {$impressions}, {$cost}, 1)
                               ON DUPLICATE KEY UPDATE impressions = impressions + 1, cost = cost + {$cost}";
                
                $conn->query($video_stats_sql);
            } else {
                // Standard banner stats - use ON DUPLICATE KEY to prevent duplicate stats
                $sql = "INSERT INTO campaign_stats 
                      (campaign_id, creative_id, zone_id, country, os, browser, device, stat_date, impressions, cost) 
                      VALUES 
                      ({$campaign_id}, {$creative_id}, {$zone_id}, '{$country}', '{$os}', '{$browser}', '{$device}', '{$stat_date}', {$impressions}, {$cost})
                      ON DUPLICATE KEY UPDATE 
                      impressions = impressions + {$impressions},
                      cost = cost + {$cost}";
                
                $conn->query($sql);
            }
        } else { 
            // External winner handling - optimized
            $cid = $winning_creative['cid'] ?? 'external_campaign'; 
            $crid = $winning_creative['crid'] ?? 'external_creative';
            $adm = $winning_creative['adm'] ?? '';
            $adomain = $winning_creative['adomain'] ?? ['adstart.click']; // Fallback domain if empty
            $cost_for_impression = $best_bid_price / 1000.0;
            $campaign_id_var = EXTERNAL_CAMPAIGN_ID; 
            $creative_id_var = EXTERNAL_CREATIVE_ID;
            
            if ($is_video_request) {
                if (empty($adm) || $adm == "<VAST version=\"3.0\"/>" || $adm == '<VAST version="3.0"/>') {
                    $ad_server_domain = get_setting('ad_server_domain', $conn);
                    $timestamp = time();
                    
                    $video_click_url = $ad_server_domain . "/click.php?cid=" . $creative_id_var . 
                                      "&zone_id=" . $zone_id_for_log . 
                                      "&video=1&campaign_id=" . $campaign_id_var . 
                                      "&ssp_id=" . $winning_ssp_id . 
                                      "&timestamp=" . $timestamp;
    
                    $adm = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
                    $adm .= "<VAST version=\"3.0\">\n";
                    $adm .= "  <Ad id=\"external\">\n";
                    $adm .= "    <InLine>\n";
                    $adm .= "      <AdSystem>Clicterra</AdSystem>\n";
                    $adm .= "      <AdTitle>External Fallback Video</AdTitle>\n";
                    $adm .= "      <Impression><![CDATA[" . $ad_server_domain . "/vast-pixel.php?cid=" . $creative_id_var . "&zone_id=" . $zone_id_for_log . "&event=impression&ssp_id=" . $winning_ssp_id . "&timestamp=" . $timestamp . "]]></Impression>\n";
                    $adm .= "      <Creatives>\n";
                    $adm .= "        <Creative>\n";
                    $adm .= "          <Linear>\n";
                    $adm .= "            <Duration>00:00:15</Duration>\n";
                    $adm .= "            <VideoClicks>\n";
                    $adm .= "              <ClickThrough><![CDATA[" . $video_click_url . "]]></ClickThrough>\n";
                    $adm .= "              <ClickTracking><![CDATA[" . $ad_server_domain . "/vast-pixel.php?cid=" . $creative_id_var . "&zone_id=" . $zone_id_for_log . "&event=clickThrough&ssp_id=" . $winning_ssp_id . "&timestamp=" . $timestamp . "]]></ClickTracking>\n";
                    $adm .= "            </VideoClicks>\n";
                    $adm .= "            <MediaFiles>\n";
                    $adm .= "              <MediaFile delivery=\"progressive\" type=\"video/mp4\" width=\"" . $w . "\" height=\"" . $h . "\"><![CDATA[https://storage.googleapis.com/gvabox/media/samples/stock.mp4]]></MediaFile>\n";
                    $adm .= "            </MediaFiles>\n";
                    $adm .= "          </Linear>\n";
                    $adm .= "        </Creative>\n";
                    $adm .= "      </Creatives>\n";
                    $adm .= "    </InLine>\n";
                    $adm .= "  </Ad>\n";
                    $adm .= "</VAST>";
                } else {
                    $ad_server_domain = get_setting('ad_server_domain', $conn);
                    $timestamp = time();
                    if (strpos($adm, '<VideoClicks>') !== false && strpos($adm, '</VideoClicks>') !== false) {
                        $click_track = "<ClickTracking><![CDATA[" . $ad_server_domain . "/vast-pixel.php?cid=" . $creative_id_var . "&zone_id=" . $zone_id_for_log . "&event=clickThrough&ssp_id=" . $winning_ssp_id . "&timestamp=" . $timestamp . "]]></ClickTracking>\n";
                        
                        $adm = preg_replace('/<\/VideoClicks>/', $click_track . "</VideoClicks>", $adm, 1);
                    }
                }
            } else {
                // Clean up and simplify banner adm to avoid Invalid Bid
                $ad_server_domain = get_setting('ad_server_domain', $conn);
                $click_url = $ad_server_domain . "/click.php?cid=" . $creative_id_var . 
                           "&zone_id=" . $zone_id_for_log . 
                           "&campaign_id=" . $campaign_id_var . 
                           "&ssp_id=" . $winning_ssp_id . 
                           "&timestamp=" . time();
                
                // More aggressive cleaning for problematic content
                $adm = preg_replace('/\s+ontouchstart=["\'][^"\']*["\']/', '', $adm);
                $adm = preg_replace('/onclick=["\']var href=.*?this\.href\s*=\s*href.*?["\']/', '', $adm);
                $adm = preg_replace('/<img src="https:\/\/s\.optvz\.com\/cimp\.php\?data=.*?width="1" height="1"[^>]*>/', '', $adm);
                $adm = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $adm);
                
                // Extremely simplified wrapper to avoid overhead
                $adm = "<div id=\"e\">{$adm}</div><script>var i=new Image();i.src=\"{$click_url}&px=1\";document.getElementById(\"e\").addEventListener(\"click\",function(){var c=new Image();c.src=\"{$click_url}\";});</script>";
            }
            
            // Record external campaign stats - optimized with ON DUPLICATE KEY
            $campaign_id = $campaign_id_var;
            $creative_id = $creative_id_var;
            $ssp_id = $winning_ssp_id;
            $zone_id = $zone_id_for_log;
            $country = $visitor_details_for_log['country'];
            $os = $visitor_details_for_log['os'];
            $browser = $visitor_details_for_log['browser'];
            $device = $visitor_details_for_log['device'];
            $stat_date = $today;
            $impressions = 1;
            $cost = $cost_for_impression;
            
            // Escape strings
            $country = $conn->real_escape_string($country);
            $os = $conn->real_escape_string($os);
            $browser = $conn->real_escape_string($browser);
            $device = $conn->real_escape_string($device);
            $stat_date = $conn->real_escape_string($stat_date);
            
            if ($is_video_request) {
                // Use unique identifier for video impressions
                $timestamp = time();
                $random_suffix = substr(md5(uniqid()), 0, 5);
                $modified_browser = $browser . "_ev_" . $random_suffix;
                
                $video_stats_sql = "INSERT INTO campaign_stats 
                               (campaign_id, creative_id, ssp_partner_id, zone_id, country, os, browser, device, stat_date, impressions, cost, is_video) 
                               VALUES 
                               ({$campaign_id}, {$creative_id}, {$ssp_id}, {$zone_id}, '{$country}', '{$os}', '{$modified_browser}', '{$device}', '{$stat_date}', {$impressions}, {$cost}, 1)
                               ON DUPLICATE KEY UPDATE 
                               impressions = impressions + 1, cost = cost + {$cost}";
                
                $conn->query($video_stats_sql);
            } else {
                $sql = "INSERT INTO campaign_stats 
                      (campaign_id, creative_id, ssp_partner_id, zone_id, country, os, browser, device, stat_date, impressions, cost) 
                      VALUES 
                      ({$campaign_id}, {$creative_id}, {$ssp_id}, {$zone_id}, '{$country}', '{$os}', '{$browser}', '{$device}', '{$stat_date}', {$impressions}, {$cost})
                      ON DUPLICATE KEY UPDATE 
                      impressions = impressions + {$impressions},
                      cost = cost + {$cost}";
                
                $conn->query($sql);
            }
        }
        
        // Record RTB request - use simplified asynchronous approach
        if (mt_rand(1, 5) == 1) { // Log only ~20% of requests to reduce DB load
            if ($is_video_request) {
                $rtb_sql = "INSERT INTO rtb_requests (supply_source_id, zone_id, is_bid_sent, winning_price_cpm, country, source_domain, is_video) 
                           VALUES ({$supply_source_id_for_log}, {$zone_id_for_log}, {$is_bid_sent_for_log}, {$price_for_log}, '{$country_for_log}', '{$domain_for_log}', 1)";
                
                $conn->query($rtb_sql);
            } else {
                $stmt_log = $conn->prepare("INSERT INTO rtb_requests (supply_source_id, zone_id, is_bid_sent, winning_price_cpm, country, source_domain) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt_log) {
                    $stmt_log->bind_param("iiidss", $supply_source_id_for_log, $zone_id_for_log, $is_bid_sent_for_log, $price_for_log, $country_for_log, $domain_for_log);
                    $stmt_log->execute();
                    $stmt_log->close();
                }
            }
        }
        
        // Final VAST XML check - avoid empty VAST
        if ($is_video_request && (empty($adm) || $adm == "<VAST version=\"3.0\"/>" || $adm == '<VAST version="3.0"/>')) {
            $adm = '<?xml version="1.0" encoding="UTF-8"?><VAST version="3.0"><Ad id="1"><InLine><AdSystem>Clicterra</AdSystem><AdTitle>Emergency Fallback</AdTitle><Creatives><Creative><Linear><Duration>00:00:15</Duration><MediaFiles><MediaFile delivery="progressive" type="video/mp4" width="640" height="360"><![CDATA[https://storage.googleapis.com/gvabox/media/samples/stock.mp4]]></MediaFile></MediaFiles></Linear></Creative></Creatives></InLine></Ad></VAST>';
        }
        
        // CRITICAL: Make sure adm is not empty to prevent Invalid Bid
        if (empty($adm)) {
            http_response_code(204); // No bid is better than invalid bid
            exit();
        }
        
        // Check for invalid characters in adm
        $adm = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $adm);
        
        // Build and send response
        http_response_code(200);
        $response = [
            'id' => $request_id,
            'bidid' => uniqid('bid_'),
            'seatbid' => [[
                'bid' => [[
                    'id' => uniqid('bid_'),
                    'impid' => $impid,
                    'price' => (float)$publisher_price, // Force float
                    'adm' => $adm,
                    'adomain' => $adomain,
                    'cid' => (string)$cid, // Force string
                    'crid' => (string)$crid, // Force string
                    'w' => (int)$w, // Force integer
                    'h' => (int)$h  // Force integer
                ]]
            ]],
            'cur' => 'USD' // Currency is required by some platforms
        ];
        
        echo json_encode($response);
    } else {
        $is_bid_sent_for_log = 0;
        
        // Record no-bid, but only for a small sample (1 in 10)
        if (mt_rand(1, 10) == 1) {
            try {
                $no_bid_sql = "INSERT INTO rtb_requests (supply_source_id, zone_id, is_bid_sent, country, source_domain) VALUES (?, ?, 0, ?, ?)";
                $stmt_no_bid = $conn->prepare($no_bid_sql);
                if ($stmt_no_bid) {
                    $stmt_no_bid->bind_param("iiss", $supply_source_id_for_log, $zone_id_for_log, $country_for_log, $domain_for_log);
                    $stmt_no_bid->execute();
                    $stmt_no_bid->close();
                }
            } catch (Exception $e) {
                // Silently ignore stats recording errors on no-bid
            }
        }
        
        http_response_code(204); // No bid
    }
} catch (Exception $e) {
    // Log error to file but not to output
    $error_log_dir = __DIR__ . '/logs';
    if (!file_exists($error_log_dir)) {
        @mkdir($error_log_dir, 0755, true);
    }
    
    $error_message = date('Y-m-d H:i:s') . " - RTB Error: " . $e->getMessage() . 
                    " in " . $e->getFile() . " on line " . $e->getLine() . "\n";
    @file_put_contents($error_log_dir . '/rtb_errors.log', $error_message, FILE_APPEND);
    
    http_response_code(204); // Return No Bid on error
}

// Clean up
$conn->close();
exit();
?>
