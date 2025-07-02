<?php
// File: /admin/ron-report.php (FINAL - Corrected SQL Query)

require_once __DIR__ . '/init.php';

// --- Filter Logic ---
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-6 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$group_by = $_GET['group_by'] ?? 'campaign';
$selected_campaign_id = filter_input(INPUT_GET, 'campaign_id', FILTER_VALIDATE_INT);

$ron_campaigns_list = $conn->query("SELECT id, name FROM campaigns WHERE serve_on_internal = 1 ORDER BY name ASC");

// --- REVISED AND SIMPLIFIED MAIN QUERY ---
$group_by_select = "";
$group_by_clause = "";
$join_clause = "JOIN campaigns c ON s.campaign_id = c.id"; // Join campaigns by default
$main_column_header = "";

switch ($group_by) {
    case 'date': 
        $group_by_select = "s.stat_date as group_field";
        $group_by_clause = "GROUP BY s.stat_date";
        $main_column_header = "Date";
        break;
    case 'country': 
        $group_by_select = "s.country as group_field";
        $group_by_clause = "GROUP BY s.country";
        $main_column_header = "Country";
        break;
    case 'site': 
        $group_by_select = "si.url as group_field";
        $join_clause .= " LEFT JOIN zones z ON s.zone_id = z.id LEFT JOIN sites si ON z.site_id = si.id";
        $group_by_clause = "GROUP BY si.id, si.url";
        $main_column_header = "Site";
        break;
    default: // 'campaign'
        $group_by_select = "c.name as group_field";
        $group_by_clause = "GROUP BY c.id, c.name";
        $main_column_header = "Campaign";
        break;
}

$sql = "
    SELECT
        {$group_by_select},
        SUM(s.impressions) AS total_impressions,
        SUM(s.clicks) AS total_clicks,
        SUM(s.cost) AS total_cost
    FROM campaign_stats s
    {$join_clause}
    WHERE c.serve_on_internal = 1 
      AND s.stat_date BETWEEN ? AND ?
";
$params = [$date_from, $date_to];
$types = "ss";

if ($selected_campaign_id) {
    $sql .= " AND s.campaign_id = ?";
    $params[] = $selected_campaign_id;
    $types .= "i";
}

$sql .= " {$group_by_clause} ORDER BY total_cost DESC, total_impressions DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$report_rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once __DIR__ . '/templates/header.php';
?>

<h1 class="mt-4 mb-4">RON Campaign Analytics</h1>
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-filter me-2"></i>Filter Report</div>
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">RON Campaign</label><select class="form-select" name="campaign_id"><option value="">All RON Campaigns</option><?php if ($ron_campaigns_list->num_rows > 0): mysqli_data_seek($ron_campaigns_list, 0); while($c = $ron_campaigns_list->fetch_assoc()): ?><option value="<?php echo $c['id']; ?>" <?php if($selected_campaign_id == $c['id']) echo 'selected'; ?>><?php echo htmlspecialchars($c['name']); ?></option><?php endwhile; endif; ?></select></div>
            <div class="col-md-2"><label class="form-label">Group By</label><select class="form-select" name="group_by"><option value="campaign" <?php if($group_by == 'campaign') echo 'selected'; ?>>Campaign</option><option value="date" <?php if($group_by == 'date') echo 'selected'; ?>>Date</option><option value="country" <?php if($group_by == 'country') echo 'selected'; ?>>Country</option><option value="site" <?php if($group_by == 'site') echo 'selected'; ?>>Site</option></select></div>
            <div class="col-md-3"><label class="form-label">Date Range</label><div class="input-group"><input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>"><input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>"></div></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Apply Filter</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-table me-2"></i>Performance Data</div>
    <div class="card-body">
        <div class="table-responsive"><table class="table table-striped table-bordered table-hover">
            <thead class="table-dark"><tr><th><?php echo $main_column_header; ?></th><th>Impressions</th><th>Clicks</th><th>CTR</th><th>Cost ($)</th><th>eCPM ($)</th><th>eCPC ($)</th></tr></thead>
            <tbody>
            <?php if (!empty($report_rows)): $totals = ['impressions' => 0, 'clicks' => 0, 'cost' => 0]; foreach($report_rows as $row): 
                $totals['impressions'] += $row['total_impressions']; $totals['clicks'] += $row['total_clicks']; $totals['cost'] += $row['total_cost'];
                $ctr = ($row['total_impressions'] > 0) ? ($row['total_clicks'] / $row['total_impressions']) * 100 : 0;
                $ecpm = ($row['total_impressions'] > 0) ? ($row['total_cost'] / $row['total_impressions']) * 1000 : 0;
                $ecpc = ($row['total_clicks'] > 0) ? ($row['total_cost'] / $row['total_clicks']) : 0;
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['group_field'] ?? 'N/A'); ?></td>
                    <td><?php echo number_format($row['total_impressions']); ?></td>
                    <td><?php echo number_format($row['total_clicks']); ?></td>
                    <td><?php echo number_format($ctr, 2); ?>%</td>
                    <td><?php echo number_format($row['total_cost'], 6); ?></td>
                    <td><?php echo number_format($ecpm, 4); ?></td>
                    <td><?php echo number_format($ecpc, 4); ?></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="7" class="text-center">No data found for the selected filters.</td></tr>
            <?php endif; ?>
            </tbody>
            <?php if (!empty($report_rows)): 
                $total_ctr = ($totals['impressions'] > 0) ? ($totals['clicks'] / $totals['impressions']) * 100 : 0;
                $total_ecpm = ($totals['impressions'] > 0) ? ($totals['cost'] * 1000 / $totals['impressions']) : 0;
                $total_ecpc = ($totals['clicks'] > 0) ? ($totals['cost'] / $totals['clicks']) : 0;
            ?>
            <tfoot class="table-group-divider fw-bold">
                <tr><td>Total</td><td><?php echo number_format($totals['impressions']); ?></td><td><?php echo number_format($totals['clicks']); ?></td><td><?php echo number_format($total_ctr, 2); ?>%</td><td><?php echo number_format($totals['cost'], 6); ?></td><td><?php echo number_format($total_ecpm, 4); ?></td><td><?php echo number_format($total_ecpc, 4); ?></td></tr>
            </tfoot>
            <?php endif; ?>
        </table></div>
    </div>
</div>

<?php 
if (isset($ron_campaigns_list)) { $ron_campaigns_list->close(); }
require_once __DIR__ . '/templates/footer.php'; 
?>