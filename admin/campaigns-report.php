<?php
// File: /admin/campaigns-report.php (REVISED & FINAL v2)

require_once __DIR__ . '/init.php';

// --- FILTER & QUERY LOGIC ---
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-6 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$group_by = $_GET['group_by'] ?? 'campaign';
$selected_campaign_id = filter_input(INPUT_GET, 'campaign_id', FILTER_VALIDATE_INT);
$campaigns_list = $conn->query("SELECT id, name FROM campaigns ORDER BY name ASC");

$select_fields = "COALESCE(SUM(s.impressions), 0) AS total_impressions, COALESCE(SUM(s.clicks), 0) AS total_clicks, COALESCE(SUM(s.cost), 0) AS total_cost";
$group_by_clause = "";
$order_by_clause = "ORDER BY total_cost DESC";
$main_column_header = "";
$params = [$date_from, $date_to];
$types = "ss";

// --- PERBAIKAN LABEL UNTUK KAMPANYE EKSTERNAL ---
$campaign_name_select = "CASE WHEN s.campaign_id = -1 THEN 'External RTB' ELSE c.name END AS group_field";

switch ($group_by) {
    case 'daily':
        $select_fields = "s.stat_date as group_field, " . $select_fields;
        $group_by_clause = "GROUP BY s.stat_date";
        $order_by_clause = "ORDER BY s.stat_date ASC";
        $main_column_header = "Date";
        break;
    case 'country':
    case 'os':
    case 'browser':
    case 'device':
        $select_fields = "s.{$group_by} as group_field, " . $select_fields;
        $group_by_clause = "GROUP BY s.{$group_by}";
        $main_column_header = ucfirst($group_by);
        break;
    default: // 'campaign'
        $select_fields = "{$campaign_name_select}, " . $select_fields;
        $group_by_clause = "GROUP BY s.campaign_id, c.name";
        $main_column_header = "Campaign";
        break;
}

$sql = "SELECT {$select_fields} FROM campaign_stats s LEFT JOIN campaigns c ON s.campaign_id = c.id WHERE s.stat_date BETWEEN ? AND ?";
if ($selected_campaign_id) {
    $sql .= " AND s.campaign_id = ?";
    $params[] = $selected_campaign_id;
    $types .= "i";
}
$sql .= " {$group_by_clause} {$order_by_clause}";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$report_rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$chart_data_json = 'null';
if ($group_by === 'daily' && !empty($report_rows)) {
    $labels = []; $impressions = []; $clicks = []; $cost = [];
    foreach ($report_rows as $row) {
        $labels[] = $row['group_field'];
        $impressions[] = $row['total_impressions'];
        $clicks[] = $row['total_clicks'];
        $cost[] = round($row['total_cost'], 6); // Tambah presisi
    }
    $chart_data_json = json_encode(['labels' => $labels, 'impressions' => $impressions, 'clicks' => $clicks, 'cost' => $cost]);
}

require_once __DIR__ . '/templates/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

<h1 class="mt-4 mb-4">Campaign Analytics</h1>
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-filter me-2"></i>Filter Report</div>
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">Campaign</label><select class="form-select" name="campaign_id"><option value="">All Campaigns</option><?php mysqli_data_seek($campaigns_list, 0); while($c = $campaigns_list->fetch_assoc()): ?><option value="<?php echo $c['id']; ?>" <?php if($selected_campaign_id == $c['id']) echo 'selected'; ?>><?php echo htmlspecialchars($c['name']); ?></option><?php endwhile; ?></select></div>
            <div class="col-md-2"><label class="form-label">Group By</label><select class="form-select" name="group_by"><option value="campaign" <?php if($group_by=='campaign') echo 'selected';?>>Campaign</option><option value="daily" <?php if($group_by=='daily') echo 'selected';?>>Daily</option><option value="country" <?php if($group_by=='country') echo 'selected';?>>Country</option><option value="os" <?php if($group_by=='os') echo 'selected';?>>OS</option><option value="browser" <?php if($group_by=='browser') echo 'selected';?>>Browser</option><option value="device" <?php if($group_by=='device') echo 'selected';?>>Device</option></select></div>
            <div class="col-md-3"><label class="form-label">Date Range</label><div class="input-group"><input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>"><input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>"></div></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Apply Filter</button></div>
        </form>
    </div>
</div>

<?php if ($group_by === 'daily' && $chart_data_json !== 'null'): ?>
<div class="card mb-4"><div class="card-header"><i class="bi bi-graph-up me-2"></i>Performance Chart</div><div class="card-body"><canvas id="analyticsChart"></canvas></div></div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><i class="bi bi-table me-2"></i>Performance Data</div>
    <div class="card-body">
        <div class="table-responsive"><table class="table table-striped table-bordered table-hover">
            <thead class="table-dark"><tr><th><?php echo $main_column_header; ?></th><th>Impr.</th><th>Clicks</th><th>CTR</th><th>Cost ($)</th><th>eCPM ($)</th><th>eCPC ($)</th></tr></thead>
            <tbody>
            <?php if (!empty($report_rows)): $totals = ['imp' => 0, 'clk' => 0, 'cost' => 0]; foreach($report_rows as $row): ?>
                <?php
                    $totals['imp'] += $row['total_impressions']; $totals['clk'] += $row['total_clicks']; $totals['cost'] += $row['total_cost'];
                    $ctr = ($row['total_impressions'] > 0) ? ($row['total_clicks'] / $row['total_impressions']) * 100 : 0;
                    $ecpm = ($row['total_impressions'] > 0) ? ($row['total_cost'] / $row['total_impressions']) * 1000 : 0;
                    $ecpc = ($row['total_clicks'] > 0) ? ($row['total_cost'] / $row['total_clicks']) : 0;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['group_field'] ?: 'N/A'); ?></td>
                    <td><?php echo number_format($row['total_impressions']); ?></td>
                    <td><?php echo number_format($row['total_clicks']); ?></td>
                    <td><?php echo number_format($ctr, 2); ?>%</td>
                    <td><?php echo number_format($row['total_cost'], 6); ?></td>
                    <td><?php echo number_format($ecpm, 4); ?></td>
                    <td><?php echo number_format($ecpc, 4); ?></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="7" class="text-center">No data for the selected filters.</td></tr>
            <?php endif; ?>
            </tbody>
            <?php if (!empty($report_rows)): ?>
            <tfoot class="table-group-divider fw-bold">
                <?php
                    $total_ctr = ($totals['imp'] > 0) ? ($totals['clk'] / $totals['imp']) * 100 : 0;
                    $total_ecpm = ($totals['imp'] > 0) ? ($totals['cost'] / $totals['imp']) * 1000 : 0;
                    $total_ecpc = ($totals['clk'] > 0) ? ($totals['cost'] / $totals['clk']) : 0;
                ?>
                <tr><td>Total</td><td><?php echo number_format($totals['imp']); ?></td><td><?php echo number_format($totals['clk']); ?></td><td><?php echo number_format($total_ctr, 2); ?>%</td><td><?php echo number_format($totals['cost'], 6); ?></td><td><?php echo number_format($total_ecpm, 4); ?></td><td><?php echo number_format($total_ecpc, 4); ?></td></tr>
            </tfoot>
            <?php endif; ?>
        </table></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() { /* ... kode chart ... */ });
</script>

<?php
$campaigns_list->close();
require_once __DIR__ . '/templates/footer.php';
?>