<?php
// File: /scripts/aggregate_stats.php (FINAL & COMPLETE)

if (php_sapi_name() !== 'cli') {
    die("This script is only accessible from the command line (CLI).");
}
require_once __DIR__ . '/../config/database.php';

echo "==================================================\n";
echo "Starting Daily Maintenance Script: " . date('Y-m-d H:i:s') . "\n";
echo "==================================================\n\n";

// 1. Agregasi Data dari Kemarin
$yesterday = date('Y-m-d', strtotime('-1 day'));
echo "Aggregating data for date: {$yesterday}...\n";

$aggregation_sql = "
    INSERT INTO stats_daily_summary 
        (stat_date, campaign_id, creative_id, zone_id, ssp_partner_id, country, os, browser, device, impressions, clicks, cost, publisher_payout)
    SELECT
        s.stat_date, 
        s.campaign_id, 
        s.creative_id, 
        s.zone_id, 
        s.ssp_partner_id, 
        s.country, 
        s.os, 
        s.browser, 
        s.device,
        SUM(s.impressions) as total_impressions,
        SUM(s.clicks) as total_clicks,
        SUM(s.cost) as total_cost,
        SUM(s.cost * COALESCE(u.revenue_share, 0) / 100) as total_payout
    FROM campaign_stats s
    LEFT JOIN zones z ON s.zone_id = z.id
    LEFT JOIN sites si ON z.site_id = si.id
    LEFT JOIN users u ON si.user_id = u.id
    WHERE s.stat_date = ?
    GROUP BY 
        s.stat_date, s.campaign_id, s.creative_id, s.zone_id, s.ssp_partner_id, s.country, s.os, s.browser, s.device
    ON DUPLICATE KEY UPDATE
        impressions = VALUES(impressions),
        clicks = VALUES(clicks),
        cost = VALUES(cost),
        publisher_payout = VALUES(publisher_payout);
";

$stmt_agg = $conn->prepare($aggregation_sql);
if ($stmt_agg === false) {
    die("Error preparing aggregation statement: " . $conn->error . "\n");
}

$stmt_agg->bind_param("s", $yesterday);

if ($stmt_agg->execute()) {
    echo "Successfully aggregated " . $stmt_agg->affected_rows . " summary rows for yesterday.\n";
} else {
    echo "Error during aggregation: " . $stmt_agg->error . "\n";
}
$stmt_agg->close();


// 2. Pembersihan (Pruning) Data Lama
$prune_date = date('Y-m-d', strtotime('-30 days'));
echo "\nPruning raw data older than {$prune_date}...\n";

if ($conn->query("DELETE FROM campaign_stats WHERE stat_date < '{$prune_date}'")) {
    echo "Pruned campaign_stats table. Affected rows: " . $conn->affected_rows . "\n";
} else {
    echo "Error pruning campaign_stats: " . $conn->error . "\n";
}
if ($conn->query("DELETE FROM rtb_requests WHERE request_time < '{$prune_date} 00:00:00'")) {
    echo "Pruned rtb_requests table. Affected rows: " . $conn->affected_rows . "\n";
} else {
    echo "Error pruning rtb_requests: " . $conn->error . "\n";
}
if ($conn->query("DELETE FROM vast_events WHERE event_time < '{$prune_date} 00:00:00'")) {
    echo "Pruned vast_events table. Affected rows: " . $conn->affected_rows . "\n";
} else {
    echo "Error pruning vast_events: " . $conn->error . "\n";
}


echo "\n==================================================\n";
echo "Script finished successfully at: " . date('Y-m-d H:i:s') . "\n";
echo "==================================================\n";

$conn->close();
?>
