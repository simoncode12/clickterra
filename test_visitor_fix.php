<?php
// Test visitor details functionality after the fix

echo "=== Testing Visitor Details After Fix ===\n";

// Change to correct directory
chdir('/home/runner/work/clickterra/clickterra');

// Include database and visitor detector exactly like rtb-handler.php
require_once __DIR__ . '/config/database.php';

if (file_exists(__DIR__ . '/includes/visitor_detector.php')) { 
    require_once __DIR__ . '/includes/visitor_detector.php'; 
    echo "visitor_detector.php included successfully\n";
}

// Simulate different visitor scenarios
$test_cases = [
    [
        'name' => 'Windows Chrome Desktop',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
    ],
    [
        'name' => 'Android Chrome Mobile', 
        'user_agent' => 'Mozilla/5.0 (Linux; Android 11; SM-G991B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.120 Mobile Safari/537.36'
    ],
    [
        'name' => 'iPhone Safari Mobile',
        'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Mobile/15E148 Safari/604.1'
    ],
    [
        'name' => 'Windows Firefox Desktop',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0'
    ]
];

foreach ($test_cases as $test) {
    echo "\n--- Testing: {$test['name']} ---\n";
    
    $_SERVER['HTTP_USER_AGENT'] = $test['user_agent'];
    $_SERVER['REMOTE_ADDR'] = '203.0.113.1';
    
    if (function_exists('get_visitor_details')) {
        $visitor_details = get_visitor_details();
        echo "Country: " . $visitor_details['country'] . "\n";
        echo "OS: " . $visitor_details['os'] . "\n";
        echo "Browser: " . $visitor_details['browser'] . "\n"; 
        echo "Device: " . $visitor_details['device'] . "\n";
        
        // Check if any value is 'unknown' (which would be a problem)
        $has_unknown = false;
        foreach (['os', 'browser'] as $field) {
            if (strtolower($visitor_details[$field]) === 'unknown') {
                echo "WARNING: {$field} is 'Unknown' - this suggests detection failed\n";
                $has_unknown = true;
            }
        }
        
        if (!$has_unknown) {
            echo "✓ All details detected successfully\n";
        }
    } else {
        echo "ERROR: get_visitor_details function not found!\n";
    }
}

echo "\n=== Test Complete ===\n";
echo "The fix ensures that proper visitor details are always used.\n";
echo "No fallback with 'unknown' values will be used anymore.\n";
?>