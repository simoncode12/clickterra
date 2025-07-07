<?php
// Test visitor details functionality after the fix - no database needed

echo "=== Testing Visitor Details After Fix (No DB) ===\n";

// Change to correct directory
chdir('/home/runner/work/clickterra/clickterra');

// Include visitor detector exactly like rtb-handler.php
if (file_exists(__DIR__ . '/includes/visitor_detector.php')) { 
    require_once __DIR__ . '/includes/visitor_detector.php'; 
    echo "visitor_detector.php included successfully\n";
} else {
    echo "ERROR: visitor_detector.php not found\n";
    exit(1);
}

// Test that function exists
if (!function_exists('get_visitor_details')) {
    echo "ERROR: get_visitor_details function not found after include\n";
    echo "This means the fix failed and fallback should have been available\n";
    exit(1);
}

echo "✓ get_visitor_details function found\n";

// Simulate different visitor scenarios that rtb-handler.php would encounter
$test_cases = [
    [
        'name' => 'Windows Chrome Desktop',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'expected_os' => 'Windows',
        'expected_browser' => 'Chrome', 
        'expected_device' => 'Desktop'
    ],
    [
        'name' => 'Android Chrome Mobile', 
        'user_agent' => 'Mozilla/5.0 (Linux; Android 11; SM-G991B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.120 Mobile Safari/537.36',
        'expected_os' => 'Android',
        'expected_browser' => 'Chrome',
        'expected_device' => 'Mobile'
    ],
    [
        'name' => 'iPhone Safari Mobile',
        'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Mobile/15E148 Safari/604.1',
        'expected_os' => 'iOS',
        'expected_browser' => 'Safari',
        'expected_device' => 'Mobile'
    ],
    [
        'name' => 'Windows Firefox Desktop',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
        'expected_os' => 'Windows',
        'expected_browser' => 'Firefox',
        'expected_device' => 'Desktop'
    ]
];

$all_passed = true;

foreach ($test_cases as $test) {
    echo "\n--- Testing: {$test['name']} ---\n";
    
    $_SERVER['HTTP_USER_AGENT'] = $test['user_agent'];
    $_SERVER['REMOTE_ADDR'] = '203.0.113.1';
    
    $visitor_details = get_visitor_details();
    
    echo "Results:\n";
    echo "  Country: " . $visitor_details['country'] . "\n";
    echo "  OS: " . $visitor_details['os'] . " (expected: {$test['expected_os']})\n";
    echo "  Browser: " . $visitor_details['browser'] . " (expected: {$test['expected_browser']})\n"; 
    echo "  Device: " . $visitor_details['device'] . " (expected: {$test['expected_device']})\n";
    
    // Validate results
    $passed = true;
    if ($visitor_details['os'] !== $test['expected_os']) {
        echo "  ❌ OS detection failed\n";
        $passed = false;
    }
    if ($visitor_details['browser'] !== $test['expected_browser']) {
        echo "  ❌ Browser detection failed\n";
        $passed = false;
    }
    if ($visitor_details['device'] !== $test['expected_device']) {
        echo "  ❌ Device detection failed\n"; 
        $passed = false;
    }
    
    if ($passed) {
        echo "  ✓ All detections correct\n";
    } else {
        $all_passed = false;
    }
    
    // Check for old fallback values
    if (strtolower($visitor_details['os']) === 'unknown' || 
        strtolower($visitor_details['browser']) === 'unknown') {
        echo "  ⚠️  Warning: Contains 'unknown' values (fallback was used)\n";
        $all_passed = false;
    }
}

echo "\n=== Test Summary ===\n";
if ($all_passed) {
    echo "✅ ALL TESTS PASSED\n";
    echo "Visitor details are being detected correctly.\n";
    echo "The fix removes the problematic fallback that was causing 'unknown' values.\n";
} else {
    echo "❌ SOME TESTS FAILED\n";
    echo "There may still be issues with visitor detection.\n";
}

echo "\nWhat was fixed:\n";
echo "- Removed fallback function that returned 'unknown' values\n";
echo "- Now relies entirely on the proper visitor_detector.php function\n";
echo "- This ensures accurate OS, Browser, and Device detection\n";
echo "- Campaign stats will now contain proper visitor details instead of 'Unknown'\n";
?>