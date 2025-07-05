<?php
// File: /scripts/aggregate_realtime.php (FINAL & CORRECTED)
// TUGAS: Meringkas data HARI INI saja. Dijalankan setiap 5 menit.

if (php_sapi_name() !== 'cli') { die("Cannot be run from a browser."); }
require_once __DIR__ . '/../config/database.php';

$today = date('Y-m-d');
echo "Starting real-time aggregation for today ({$today}) at " . date('H:i:s') . "\n";

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
        impressions = VALUES(impressions),
        clicks = VALUES(clicks),
        cost = VALUES(cost),
        publisher_payout = VALUES(publisher_payout);
";
$stmt_agg = $conn->prepare($aggregation_sql);
if($stmt_agg === false) { die("Error preparing statement: ".$conn->error); }
$stmt_agg->bind_param("s", $today);
if ($stmt_agg->execute()) { echo "Successfully aggregated/updated " . $stmt_agg->affected_rows . " real-time rows.\n"; } 
else { echo "Error during real-time aggregation: " . $stmt_agg->error . "\n"; }
$stmt_agg->close();
$conn->close();
?>
