<?php
// File: /admin/supply-report.php (REVISED with Group by Domain, Clicks, CTR)

require_once __DIR__ . '/init.php';

// --- Filter Logic ---
$date_from = $_GET['date_from'] ?? date('Y-m-d');
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$group_by = $_GET['group_by'] ?? 'partner';
$selected_partner_id = filter_input(INPUT_GET, 'partner_id', FILTER_VALIDATE_INT);

$partners_list = $conn->query("SELECT id, name FROM rtb_supply_sources ORDER BY name ASC");

// --- Main Query ---
$group_by_field = "";
switch ($group_by) {
    case 'date': $group_by_field = "DATE(rr.request_time)"; $main_column_header = "Date"; break;
    case 'country': $group_by_field = "rr.country"; $main_column_header = "Country"; break;
    case 'domain': $group_by_field = "rr.source_domain"; $main_column_header = "Source Domain"; break;
    default: $group_by_field = "rs.name"; $main_column_header = "Supply Partner"; break;
}

$sql = "
    SELECT
        {$group_by_field} as group_field,
        COUNT(rr.id) AS total_requests,
        SUM(rr.is_bid_sent) AS total_impressions,
        (
            SELECT COALESCE(SUM(cs.clicks), 0) 
            FROM campaign_stats cs 
            JOIN zones z ON cs.zone_id = z.id 
            JOIN sites si ON z.site_id = si.id
            JOIN rtb_supply_sources rrs ON si.user_id = rrs.user_id
            WHERE rrs.id = rs.id AND DATE(cs.stat_date) = DATE(rr.request_time)
        ) as total_clicks,
        AVG(rr.winning_price_cpm) AS avg_cpm,
        SUM(CASE WHEN rr.is_bid_sent = 1 THEN rr.winning_price_cpm / 1000 ELSE 0 END) AS total_revenue
    FROM rtb_requests rr
    LEFT JOIN rtb_supply_sources rs ON rr.supply_source_id = rs.id
    WHERE rr.request_time BETWEEN ? AND ?
";
$params = [$date_from . ' 00:00:00', $date_to . ' 23:59:59'];
$types = "ss";

if ($selected_partner_id) {
    $sql .= " AND rr.supply_source_id = ?";
    $params[] = $selected_partner_id;
    $types .= "i";
}
$sql .= " GROUP BY group_field ORDER BY total_revenue DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$report_rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once __DIR__ . '/templates/header.php';
?>

<h1 class="mt-4 mb-4">RTB Supply Partner Analytics</h1>
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-filter me-2"></i>Filter Report</div>
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">Supply Partner</label><select class="form-select" name="partner_id"><option value="">All Partners</option><?php if ($partners_list->num_rows > 0): mysqli_data_seek($partners_list, 0); while($partner = $partners_list->fetch_assoc()): ?><option value="<?php echo $partner['id']; ?>" <?php if($selected_partner_id == $partner['id']) echo 'selected'; ?>><?php echo htmlspecialchars($partner['name']); ?></option><?php endwhile; endif; ?></select></div>
            <div class="col-md-2"><label class="form-label">Group By</label><select class="form-select" name="group_by"><option value="partner" <?php if($group_by == 'partner') echo 'selected'; ?>>Partner</option><option value="date" <?php if($group_by == 'date') echo 'selected'; ?>>Date</option><option value="country" <?php if($group_by == 'country') echo 'selected'; ?>>Country</option><option value="domain" <?php if($group_by == 'domain') echo 'selected'; ?>>Source Domain</option></select></div>
            <div class="col-md-3"><label class="form-label">Date Range</label><div class="input-group"><input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>"><input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>"></div></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Apply Filter</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-bar-chart-line-fill me-2"></i>Performance Data</div>
    <div class="card-body">
        <div class="table-responsive"><table class="table table-striped table-bordered table-hover">
            <thead class="table-dark"><tr><th><?php echo $main_column_header; ?></th><th>Requests</th><th>Impressions</th><th>Clicks</th><th>CTR</th><th>Fill Rate</th><th>Avg. CPM ($)</th><th>Revenue ($)</th></tr></thead>
            <tbody>
            <?php if (!empty($report_rows)): $totals = ['requests' => 0, 'impressions' => 0, 'clicks' => 0, 'revenue' => 0]; foreach($report_rows as $row): 
                $totals['requests'] += $row['total_requests']; $totals['impressions'] += $row['total_impressions']; $totals['clicks'] += $row['total_clicks']; $totals['revenue'] += $row['total_revenue'];
                $fill_rate = ($row['total_requests'] > 0) ? ($row['total_impressions'] / $row['total_requests']) * 100 : 0;
                $ctr = ($row['total_impressions'] > 0) ? ($row['total_clicks'] / $row['total_impressions']) * 100 : 0;
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['group_field'] ?: 'N/A'); ?></td>
                    <td><?php echo number_format($row['total_requests']); ?></td>
                    <td><?php echo number_format($row['total_impressions']); ?></td>
                    <td><?php echo number_format($row['total_clicks']); ?></td>
                    <td><?php echo number_format($ctr, 2); ?>%</td>
                    <td><?php echo number_format($fill_rate, 2); ?>%</td>
                    <td><?php echo number_format($row['avg_cpm'], 4); ?></td>
                    <td><?php echo number_format($row['total_revenue'], 6); ?></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="8" class="text-center">No data found for the selected filters.</td></tr>
            <?php endif; ?>
            </tbody>
            <?php if (!empty($report_rows)): 
                $total_fill_rate = ($totals['requests'] > 0) ? ($totals['impressions'] / $totals['requests']) * 100 : 0;
                $total_ctr = ($totals['impressions'] > 0) ? ($totals['clicks'] / $totals['impressions']) * 100 : 0;
                $overall_ecpm = ($totals['impressions'] > 0) ? ($totals['revenue'] * 1000 / $totals['impressions']) : 0;
            ?>
            <tfoot class="table-group-divider fw-bold">
                <tr><td>Total</td><td><?php echo number_format($totals['requests']); ?></td><td><?php echo number_format($totals['impressions']); ?></td><td><?php echo number_format($totals['clicks']); ?></td><td><?php echo number_format($total_ctr, 2); ?>%</td><td><?php echo number_format($total_fill_rate, 2); ?>%</td><td><?php echo number_format($overall_ecpm, 4); ?></td><td><?php echo number_format($totals['revenue'], 6); ?></td></tr>
            </tfoot>
            <?php endif; ?>
        </table></div>
    </div>
</div>

<?php 
$partners_list->close();
require_once __DIR__ . '/templates/footer.php'; 
?>