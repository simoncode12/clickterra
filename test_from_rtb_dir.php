<?php
// This script will be placed in the same directory as rtb-handler.php to test exact behavior

echo "=== Testing from rtb-handler.php directory ===\n";

echo "Current directory: " . __DIR__ . "\n";
echo "Script location: " . __FILE__ . "\n";

// Test the exact same paths that rtb-handler.php uses
$settings_path = __DIR__ . '/includes/settings.php';
$visitor_path = __DIR__ . '/includes/visitor_detector.php';

echo "Settings path: $settings_path\n";
echo "Settings exists: " . (file_exists($settings_path) ? 'YES' : 'NO') . "\n";

echo "Visitor path: $visitor_path\n";
echo "Visitor exists: " . (file_exists($visitor_path) ? 'YES' : 'NO') . "\n";

// Try the includes exactly as rtb-handler.php does
if (file_exists($settings_path)) { 
    require_once $settings_path; 
    echo "settings.php included\n";
} else {
    echo "settings.php NOT included\n";
}

if (file_exists($visitor_path)) { 
    require_once $visitor_path; 
    echo "visitor_detector.php included\n";
} else {
    echo "visitor_detector.php NOT included\n";
}

// Check if function exists
echo "get_visitor_details exists: " . (function_exists('get_visitor_details') ? 'YES' : 'NO') . "\n";

// If it doesn't exist, test the fallback
if (!function_exists('get_visitor_details')) { 
    function get_visitor_details() { 
        echo "FALLBACK CALLED!\n";
        return ['country' => 'XX', 'os' => 'unknown', 'browser' => 'unknown', 'device' => 'unknown']; 
    } 
    echo "Fallback function defined\n";
}

// Test the function
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
$result = get_visitor_details();
echo "Function result:\n";
print_r($result);
?>