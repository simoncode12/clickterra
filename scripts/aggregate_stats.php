<?php
// File: /scripts/aggregate_stats.php (FINAL & CORRECTED)
// TUGAS: Meringkas data KEMARIN dan MEMBERSIHKAN data lama. Dijalankan sekali sehari.

if (php_sapi_name() !== 'cli') { die("Cannot be run from a browser."); }
require_once __DIR__ . '/../config/database.php';

echo "Starting Daily Maintenance Script: " . date('Y-m-d H:i:s') . "\n";

// 1. Agregasi Data dari Kemarin
$yesterday = date('Y-m-d', strtotime('-1 day'));
echo "Aggregating data for date: {$yesterday}...\n";
$aggregation_sql = "
    INSERT INTO stats_daily_summary (stat_date, campaign_id, creative_id, zone_id, ssp_partner_id, country, os, browser, device, impressions, clicks, cost, publisher_payout)
    SELECT
        s.stat_date, s.campaign_id, s.creative_id, s.zone_id, s.ssp_partner_id, s.country, s.os, s.browser, s.device,
        SUM(s.impressions), SUM(s.clicks), SUM(s.cost), SUM(s.cost * COALESCE(u.revenue_share, 0) / 100)
    FROM campaign_stats s
    LEFT JOIN zones z ON s.zone_id = z.id
    LEFT JOIN sites si ON z.site_id = si.id
    LEFT JOIN users u ON si.user_id = u.id
    WHERE s.stat_date = ?
    GROUP BY s.stat_date, s.campaign_id, s.creative_id, s.zone_id, s.ssp_partner_id, s.country, s.os, s.browser, s.device
    ON DUPLICATE KEY UPDATE
        impressions = VALUES(impressions), clicks = VALUES(clicks), cost = VALUES(cost), publisher_payout = VALUES(publisher_payout);
";
$stmt_agg = $conn->prepare($aggregation_sql);
if($stmt_agg === false) { die("Error preparing statement: ".$conn->error); }
$stmt_agg->bind_param("s", $yesterday);
if ($stmt_agg->execute()) { echo "Successfully aggregated " . $stmt_agg->affected_rows . " rows for yesterday.\n"; } 
else { echo "Error during aggregation: " . $stmt_agg->error . "\n"; }
$stmt_agg->close();

// 2. Pembersihan Data Lama
$prune_date = date('Y-m-d', strtotime('-30 days'));
echo "\nPruning raw data older than {$prune_date}...\n";
if ($conn->query("DELETE FROM campaign_stats WHERE stat_date < '{$prune_date}'")) { echo "Pruned campaign_stats. Rows: " . $conn->affected_rows . "\n"; }
if ($conn->query("DELETE FROM rtb_requests WHERE request_time < '{$prune_date} 00:00:00'")) { echo "Pruned rtb_requests. Rows: " . $conn->affected_rows . "\n"; }
if ($conn->query("DELETE FROM vast_events WHERE event_time < '{$prune_date} 00:00:00'")) { echo "Pruned vast_events. Rows: " . $conn->affected_rows . "\n"; }

echo "Script finished successfully.\n";
$conn->close();
?>
