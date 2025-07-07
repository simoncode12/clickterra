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

    // Try local GeoIP database first
    if (file_exists($geoip_db_path) && class_exists('\GeoIp2\Database\Reader')) {
        try {
            $reader = new Reader($geoip_db_path);
            $record = $reader->city($ip_address);
            $country = $record->country->isoCode ?? 'XX';
        } catch (\Exception $e) {
            error_log("GeoIP Lookup Error for IP {$ip_address}: " . $e->getMessage());
        }
    }

    // Fallback: Use HTTP headers for country detection if available
    if ($country === 'XX') {
        // Check Cloudflare country header
        if (isset($_SERVER['HTTP_CF_IPCOUNTRY']) && strlen($_SERVER['HTTP_CF_IPCOUNTRY']) === 2) {
            $country = strtoupper($_SERVER['HTTP_CF_IPCOUNTRY']);
        }
        // Check other common country headers
        elseif (isset($_SERVER['HTTP_X_COUNTRY_CODE']) && strlen($_SERVER['HTTP_X_COUNTRY_CODE']) === 2) {
            $country = strtoupper($_SERVER['HTTP_X_COUNTRY_CODE']);
        }
        // Basic IP-to-country mapping for common ranges (very basic fallback)
        elseif (!filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            // For local/private IPs, keep XX
            $country = 'XX';
        } else {
            // For public IPs without detection, try basic regional detection
            $country = detect_country_by_ip_prefix($ip_address);
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

/**
 * Basic country detection by IP prefix (fallback method)
 */
function detect_country_by_ip_prefix($ip) {
    // Convert IP to long for range checking
    $ip_long = ip2long($ip);
    if ($ip_long === false) return 'XX';
    
    // Basic known IP ranges (this is a very simplified version)
    $ranges = [
        // Google DNS
        ['8.8.8.0', '8.8.8.255', 'US'],
        ['8.8.4.0', '8.8.4.255', 'US'],
        // Cloudflare DNS
        ['1.1.1.0', '1.1.1.255', 'US'],
        ['1.0.0.0', '1.0.0.255', 'US'],
        // Some Indonesian ranges (examples)
        ['103.10.0.0', '103.10.255.255', 'ID'],
        ['118.97.0.0', '118.97.255.255', 'ID'],
        ['202.43.0.0', '202.43.255.255', 'ID'],
    ];
    
    foreach ($ranges as $range) {
        $start = ip2long($range[0]);
        $end = ip2long($range[1]);
        if ($ip_long >= $start && $ip_long <= $end) {
            return $range[2];
        }
    }
    
    return 'XX'; // Unknown
}
