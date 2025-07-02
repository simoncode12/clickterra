<?php
// File: /publisher/statistics.php (NEW)

require_once __DIR__ . '/init.php';

// --- Filter & Query Logic ---
$publisher_id = $_SESSION['publisher_id'];
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-6 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$group_by = $_GET['group_by'] ?? 'date';

$revenue_share = $conn->query("SELECT revenue_share FROM users WHERE id = {$publisher_id}")->fetch_assoc()['revenue_share'] ?? 0;

$group_by_field = "";
$main_column_header = "";
$join_clause = "LEFT JOIN zones z ON s.zone_id = z.id LEFT JOIN sites si ON z.site_id = si.id";

switch ($group_by) {
    case 'site': 
        $group_by_field = "si.url as group_field"; 
        $main_column_header = "Site"; 
        break;
    case 'zone': 
        $group_by_field = "CONCAT(si.url, ' - ', z.name, ' (', z.size, ')') as group_field"; 
        $main_column_header = "Zone"; 
        break;
    case 'country': 
        $group_by_field = "s.country as group_field"; 
        $main_column_header = "Country"; 
        break;
    default: // 'date'
        $group_by_field = "s.stat_date as group_field"; 
        $main_column_header = "Date"; 
        break;
}

$sql = "
    SELECT
        {$group_by_field},
        SUM(s.impressions) AS total_impressions,
        SUM(s.clicks) AS total_clicks,
        SUM(s.cost * {$revenue_share} / 100) AS total_earnings
    FROM campaign_stats s
    {$join_clause}
    WHERE si.user_id = ? 
      AND s.stat_date BETWEEN ? AND ?
    GROUP BY group_field
    ORDER BY group_field DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $publisher_id, $date_from, $date_to);
$stmt->execute();
$report_rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once __DIR__ . '/templates/header.php';
?>

<h1 class="mt-4 mb-4">Detailed Statistics</h1>
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-filter me-2"></i>Filter Report</div>
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">Group By</label><select class="form-select" name="group_by"><option value="date" <?php if($group_by == 'date') echo 'selected'; ?>>Date</option><option value="site" <?php if($group_by == 'site') echo 'selected'; ?>>Site</option><option value="zone" <?php if($group_by == 'zone') echo 'selected'; ?>>Zone</option><option value="country" <?php if($group_by == 'country') echo 'selected'; ?>>Country</option></select></div>
            <div class="col-md-4"><label class="form-label">Date Range</label><div class="input-group"><input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>"><input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>"></div></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Apply Filter</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive"><table class="table table-hover">
            <thead><tr><th><?php echo $main_column_header; ?></th><th>Impressions</th><th>Clicks</th><th>CTR</th><th>Earnings ($)</th></tr></thead>
            <tbody>
            <?php if (!empty($report_rows)): foreach($report_rows as $row): ?>
                <?php $ctr = ($row['total_impressions'] > 0) ? ($row['total_clicks'] / $row['total_impressions']) * 100 : 0; ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['group_field'] ?: 'N/A'); ?></td>
                    <td><?php echo number_format($row['total_impressions']); ?></td>
                    <td><?php echo number_format($row['total_clicks']); ?></td>
                    <td><?php echo number_format($ctr, 2); ?>%</td>
                    <td><?php echo number_format($row['total_earnings'], 6); ?></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="5" class="text-center text-muted py-3">No data found for the selected filters.</td></tr>
            <?php endif; ?>
            </tbody>
        </table></div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>