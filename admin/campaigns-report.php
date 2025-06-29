<?php
// File: /admin/campaigns-report.php (KODE LENGKAP & FINAL)

require_once __DIR__ . '/init.php';

$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-6 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$group_by = $_GET['group_by'] ?? 'summary';
$selected_campaign_id = filter_input(INPUT_GET, 'campaign_id', FILTER_VALIDATE_INT);
$campaigns_list = $conn->query("SELECT id, name FROM campaigns ORDER BY name ASC");

$allowed_group_by = ['daily'=>'stat_date', 'country'=>'country', 'browser'=>'browser', 'os'=>'os', 'device'=>'device'];
$group_by_field = $allowed_group_by[$group_by] ?? null;

$select_fields = "c.name AS campaign_name, COALESCE(SUM(s.impressions), 0) AS total_impressions, COALESCE(SUM(s.clicks), 0) AS total_clicks, COALESCE(SUM(s.cost), 0) AS total_cost";
$group_by_clause = "GROUP BY c.id, c.name";
$order_by_clause = "ORDER BY total_impressions DESC, c.name ASC";
$main_column_header = "Campaign";

if ($group_by_field) {
    $select_fields = "s.{$group_by_field} as group_field, " . $select_fields;
    $group_by_clause = "GROUP BY s.{$group_by_field}, c.name";
    $order_by_clause = "ORDER BY s.{$group_by_field} ASC, total_impressions DESC";
    if ($group_by === 'daily') { $order_by_clause = "ORDER BY s.{$group_by_field} ASC"; }
    $main_column_header = ucfirst(str_replace('_', ' ', $group_by));
}

$sql = "SELECT {$select_fields} FROM campaigns c JOIN campaign_stats s ON c.id = s.campaign_id WHERE s.stat_date BETWEEN ? AND ?";
$params = [$date_from, $date_to]; $types = "ss";

if ($selected_campaign_id) { $sql .= " AND c.id = ?"; $params[] = $selected_campaign_id; $types .= "i"; }
$sql .= " {$group_by_clause} {$order_by_clause}";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$report_rows = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$chart_data = null;
if ($group_by === 'daily' && !empty($report_rows)) {
    $labels = []; $impressions_data = []; $clicks_data = []; $cost_data = [];
    $daily_summary = [];
    foreach($report_rows as $row) {
        $date = $row['group_field'];
        if (!isset($daily_summary[$date])) { $daily_summary[$date] = ['impressions' => 0, 'clicks' => 0, 'cost' => 0]; }
        $daily_summary[$date]['impressions'] += $row['total_impressions'];
        $daily_summary[$date]['clicks'] += $row['total_clicks'];
        $daily_summary[$date]['cost'] += $row['total_cost'];
    }
    ksort($daily_summary);
    foreach($daily_summary as $date => $data) { $labels[] = $date; $impressions_data[] = $data['impressions']; $clicks_data[] = $data['clicks']; $cost_data[] = $data['cost'];}
    $chart_data = json_encode(['labels' => $labels, 'impressions' => $impressions_data, 'clicks' => $clicks_data, 'cost' => $cost_data]);
}
?>

<?php require_once __DIR__ . '/templates/header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="mt-4 mb-0">Campaign Analytics Report</h1></div>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-filter me-2"></i>Filter Report</div>
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-lg-3 col-md-6"><label class="form-label">Campaign</label><select class="form-select" name="campaign_id"><option value="">All Campaigns</option><?php mysqli_data_seek($campaigns_list, 0); while($campaign = $campaigns_list->fetch_assoc()): ?><option value="<?php echo $campaign['id']; ?>" <?php if($selected_campaign_id == $campaign['id']) echo 'selected'; ?>><?php echo htmlspecialchars($campaign['name']); ?></option><?php endwhile; ?></select></div>
            <div class="col-lg-2 col-md-6"><label class="form-label">Group By</label><select class="form-select" name="group_by"><option value="summary" <?php if($group_by == 'summary') echo 'selected'; ?>>Summary</option><option value="daily" <?php if($group_by == 'daily') echo 'selected'; ?>>Daily</option><option value="country" <?php if($group_by == 'country') echo 'selected'; ?>>Country</option><option value="browser" <?php if($group_by == 'browser') echo 'selected'; ?>>Browser</option><option value="os" <?php if($group_by == 'os') echo 'selected'; ?>>OS</option><option value="device" <?php if($group_by == 'device') echo 'selected'; ?>>Device</option></select></div>
            <div class="col-lg-3 col-md-6"><label class="form-label">Date Range</label><div class="input-group"><input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>"><input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>"></div></div>
            <div class="col-lg-2 col-md-6"><label class="form-label">&nbsp;</label><button type="submit" class="btn btn-primary w-100">Apply Filter</button></div>
        </form>
    </div>
</div>

<?php if ($group_by === 'daily' && $chart_data && !empty(json_decode($chart_data)->labels)): ?>
<div class="card mb-4"><div class="card-header"><i class="bi bi-graph-up me-2"></i>Performance Chart</div><div class="card-body"><canvas id="performanceChart"></canvas></div></div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><i class="bi bi-table me-2"></i>Performance Data</div>
    <div class="card-body">
        <div class="table-responsive"><table class="table table-striped table-bordered table-hover">
            <thead class="table-dark"><tr><th><?php echo $main_column_header; ?></th><?php if($group_by_field): ?><th>Campaign</th><?php endif; ?><th>Impr.</th><th>Clicks</th><th>CTR</th><th>Cost ($)</th><th>eCPM ($)</th><th>eCPC ($)</th></tr></thead>
            <tbody>
                <?php if (!empty($report_rows)): $grand_total = ['impressions' => 0, 'clicks' => 0, 'cost' => 0]; foreach($report_rows as $row): ?>
                    <?php
                        $ctr = ($row['total_impressions'] > 0) ? ($row['total_clicks'] / $row['total_impressions']) * 100 : 0;
                        $ecpm = ($row['total_impressions'] > 0) ? ($row['total_cost'] / $row['total_impressions']) * 1000 : 0;
                        $ecpc = ($row['total_clicks'] > 0) ? ($row['total_cost'] / $row['total_clicks']) : 0;
                        $grand_total['impressions'] += $row['total_impressions']; $grand_total['clicks'] += $row['total_clicks']; $grand_total['cost'] += $row['total_cost'];
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['group_field'] ?? $row['campaign_name']); ?></td>
                        <?php if($group_by_field): ?><td><?php echo htmlspecialchars($row['campaign_name']); ?></td><?php endif; ?>
                        <td><?php echo number_format($row['total_impressions']); ?></td>
                        <td><?php echo number_format($row['total_clicks']); ?></td>
                        <td><?php echo number_format($ctr, 2); ?>%</td>
                        <td><?php echo number_format($row['total_cost'], 4); ?></td>
                        <td><?php echo number_format($ecpm, 4); ?></td>
                        <td><?php echo number_format($ecpc, 4); ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="8" class="text-center">No performance data found for the selected filter.</td></tr>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($report_rows)): ?>
            <tfoot class="table-group-divider fw-bold">
                <?php 
                    $total_ctr = ($grand_total['impressions'] > 0) ? ($grand_total['clicks'] / $grand_total['impressions']) * 100 : 0;
                    $total_ecpm = ($grand_total['impressions'] > 0) ? ($grand_total['cost'] / $grand_total['impressions']) * 1000 : 0;
                    $total_ecpc = ($grand_total['clicks'] > 0) ? ($grand_total['cost'] / $grand_total['clicks']) : 0;
                ?>
                <tr><td colspan="<?php echo $group_by_field ? 2 : 1; ?>">Total</td><td><?php echo number_format($grand_total['impressions']); ?></td><td><?php echo number_format($grand_total['clicks']); ?></td><td><?php echo number_format($total_ctr, 2); ?>%</td><td><?php echo number_format($grand_total['cost'], 4); ?></td><td><?php echo number_format($total_ecpm, 4); ?></td><td><?php echo number_format($total_ecpc, 4); ?></td></tr>
            </tfoot>
            <?php endif; ?>
        </table></div>
    </div>
</div>

<script>
<?php if ($group_by === 'daily' && $chart_data): ?>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('performanceChart');
    if(ctx) {
        const chartData = <?php echo $chart_data; ?>;
        if (chartData.labels && chartData.labels.length > 0) {
            new Chart(ctx, { 
                type: 'line', 
                data: { 
                    labels: chartData.labels, 
                    datasets: [
                        { label: 'Impressions', data: chartData.impressions, borderColor: 'rgba(54, 162, 235, 1)', tension: 0.1, yAxisID: 'y' }, 
                        { label: 'Clicks', data: chartData.clicks, borderColor: 'rgba(255, 99, 132, 1)', tension: 0.1, yAxisID: 'y1' }
                    ] 
                }, 
                options: { 
                    responsive: true, interaction: { mode: 'index', intersect: false }, 
                    scales: { 
                        x: { type: 'time', time: { unit: 'day' } },
                        y: { type: 'linear', display: true, position: 'left', title: { display: true, text: 'Impressions' } }, 
                        y1: { type: 'linear', display: true, position: 'right', title: { display: true, text: 'Clicks' }, grid: { drawOnChartArea: false } } 
                    } 
                } 
            });
        }
    }
});
<?php endif; ?>
</script>

<?php 
$campaigns_list->close();
require_once __DIR__ . '/templates/footer.php'; 
?>