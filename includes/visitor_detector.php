<?php
// File: /includes/visitor_detector.php (FINAL & ROBUST - With Real IP Detection & Local GeoIP2 Database)

// Muat autoloader dari Composer jika ada
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use GeoIp2\Database\Reader;

/**
 * Mendeteksi alamat IP asli pengunjung, bahkan di belakang proxy atau CDN.
 * Memeriksa header dengan urutan prioritas.
 */
function get_real_ip_address() {
    $headers = [
        'HTTP_CF_CONNECTING_IP', // Cloudflare
        'HTTP_X_FORWARDED_FOR',  // Proxy standar
        'HTTP_X_REAL_IP',        // Nginx proxy
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'            // Fallback terakhir
    ];

    foreach ($headers as $header) {
        if (isset($_SERVER[$header]) && !empty($_SERVER[$header])) {
            $ip_list = explode(',', $_SERVER[$header]);
            $ip = trim($ip_list[0]);
            
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

/**
 * Fungsi utama untuk mendapatkan semua detail pengunjung.
 */
function get_visitor_details() {
    $ip_address = get_real_ip_address();
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
    $country = 'XX';
    $geoip_db_path = __DIR__ . '/../geoip/GeoLite2-City.mmdb';

    if (file_exists($geoip_db_path) && class_exists('\GeoIp2\Database\Reader')) {
        try {
            $reader = new Reader($geoip_db_path);
            $record = $reader->city($ip_address);
            $country = $record->country->isoCode ?? 'XX';
        } catch (\Exception $e) {
            error_log("GeoIP Lookup Error for IP {$ip_address}: " . $e->getMessage());
        }
    }

    $os = 'Unknown';
    if (preg_match('/windows/i', $user_agent)) $os = 'Windows';
    elseif (preg_match('/android/i', $user_agent)) $os = 'Android';
    elseif (preg_match('/linux/i', $user_agent)) $os = 'Linux';
    elseif (preg_match('/(iphone|ipad|ipod)/i', $user_agent)) $os = 'iOS';
    elseif (preg_match('/mac os/i', $user_agent)) $os = 'macOS';

    $browser = 'Unknown';
    if (preg_match('/firefox/i', $user_agent)) $browser = 'Firefox';
    elseif (preg_match('/chrome/i', $user_agent) && !preg_match('/edg/i', $user_agent)) $browser = 'Chrome';
    elseif (preg_match('/safari/i', $user_agent) && !preg_match('/chrome/i', $user_agent)) $browser = 'Safari';

    $device = 'Desktop';
    if (preg_match('/(tablet|ipad)|(android(?!.*mobi))/i', $user_agent)) { $device = 'Tablet'; } 
    elseif (preg_match('/mobi/i', $user_agent)) { $device = 'Mobile'; }
    
    return ['country' => $country, 'os' => $os, 'browser' => $browser, 'device' => $device];
}
