<?php
// File: /includes/visitor_detector.php (FINAL & CORRECTED)
// Pusat logika untuk mendeteksi detail pengunjung secara konsisten.

function get_visitor_details() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
    
    // Deteksi Negara - Prioritaskan header dari CDN/Proxy seperti Cloudflare
    $country = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? 'XX'; // 'XX' untuk unknown

    // Deteksi OS
    $os = 'Unknown';
    if (preg_match('/windows nt 10/i', $user_agent)) $os = 'Windows 10';
    elseif (preg_match('/windows/i', $user_agent)) $os = 'Windows';
    elseif (preg_match('/android/i', $user_agent)) $os = 'Android';
    elseif (preg_match('/linux/i', $user_agent)) $os = 'Linux';
    elseif (preg_match('/iphone|ipad|ipod/i', $user_agent)) $os = 'iOS';
    elseif (preg_match('/mac os x/i', $user_agent)) $os = 'macOS';

    // Deteksi Browser
    $browser = 'Unknown';
    if (preg_match('/firefox/i', $user_agent)) $browser = 'Firefox';
    elseif (preg_match('/chrome/i', $user_agent) && !preg_match('/edg/i', $user_agent)) $browser = 'Chrome';
    elseif (preg_match('/safari/i', $user_agent) && !preg_match('/chrome/i', $user_agent)) $browser = 'Safari';
    elseif (preg_match('/edg/i', $user_agent)) $browser = 'Edge';
    elseif (preg_match('/opera|opr/i', $user_agent)) $browser = 'Opera';

    // Deteksi Tipe Perangkat
    $device = 'Desktop';
    if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $user_agent)) {
        $device = 'Tablet';
    } 
    // --- PERBAIKAN ADA DI BARIS DI BAWAH INI ---
    elseif (preg_match('/(mobi|phone|blackberry|opera mini|fennec|minimo|symbian|psp|nintendo ds)/i', $user_agent)) {
        $device = 'Mobile';
    }
    
    return [
        'country' => $country,
        'os'      => $os,
        'browser' => $browser,
        'device'  => $device
    ];
}
