<?php
// File: /publisher/statistics.php (FINAL & OPTIMIZED - Dengan Tabel Agregasi dan Fitur Lengkap)

require_once __DIR__ . '/init.php';

// --- 1. SETUP & FILTER LOGIC ---
$publisher_id = $_SESSION['publisher_id'] ?? null;

// Jika publisher_id tidak ditemukan, arahkan atau tampilkan pesan error
if (!$publisher_id) {
    die("Akses tidak sah: Publisher ID tidak ditemukan.");
}

// Default ke 7 hari terakhir dari data yang tersedia (termasuk hari ini)
// Menggunakan hari ini untuk memberikan statistik yang lebih akurat dan up-to-date
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');

$group_by = $_GET['group_by'] ?? 'date';
$filter_site_id = filter_input(INPUT_GET, 'site_id', FILTER_VALIDATE_INT);
$filter_zone_id = filter_input(INPUT_GET, 'zone_id', FILTER_VALIDATE_INT);

// Ambil revenue_share dari database (bisa di-cache di init.php jika sering diakses)
$revenue_share_query = get_query_results($conn, "SELECT revenue_share FROM users WHERE id = ?", [$publisher_id], "i");
$revenue_share = $revenue_share_query[0]['revenue_share'] ?? 0;

// Ambil daftar situs dan zona milik publisher untuk dropdown filter
$sites_list = get_query_results($conn, "SELECT id, url FROM sites WHERE user_id = ? AND status = 'approved' ORDER BY url ASC", [$publisher_id], "i");
$zones_list = [];
if ($filter_site_id) {
    // Pastikan zona yang diambil juga milik publisher melalui site_id yang terkait dengan publisher_id
    $zones_list = get_query_results($conn, "SELECT z.id, z.name FROM zones z JOIN sites s ON z.site_id = s.id WHERE z.site_id = ? AND s.user_id = ?", [$filter_site_id, $publisher_id], "ii");
}

// --- 2. HYBRID QUERY: GABUNGKAN DATA HISTORIS DAN REALTIME ---
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

// Tentukan rentang tanggal untuk query historis dan realtime
$date_hist_from = ($date_from < $today) ? $date_from : $today;
$date_hist_to = ($date_to < $today) ? $date_to : $yesterday;

$group_by_select = "T.stat_date as group_field";
$group_by_clause = "GROUP BY T.stat_date";
$main_column_header = "Date";
$join_clause = "LEFT JOIN zones z ON T.zone_id = z.id LEFT JOIN sites si ON z.site_id = si.id";
$group_by_select_inner = ", stat_date";

switch ($group_by) {
    case 'site':
        $group_by_select = "si.url as group_field";
        $group_by_clause = "GROUP BY si.id, si.url"; // Group by ID juga untuk memastikan keunikan jika ada URL yang sama
        $main_column_header = "Site";
        $group_by_select_inner = ", zone_id";
        break;
    case 'zone':
        // Pastikan format group_field sesuai dengan kebutuhan Anda
        $group_by_select = "CONCAT(z.name, ' (', z.size, ')') as group_field";
        $group_by_clause = "GROUP BY z.id, z.name, z.size"; // Group by ID juga
        $main_column_header = "Zone";
        $group_by_select_inner = ", zone_id";
        break;
    case 'country':
        $group_by_select = "T.country as group_field";
        $group_by_clause = "GROUP BY T.country";
        $main_column_header = "Country";
        $group_by_select_inner = ", country";
        break;
}

// Query hybrid yang menggabungkan data historis dari stats_daily_summary
// dengan data realtime dari campaign_stats untuk hari ini
$sql = "
    SELECT
        {$group_by_select},
        SUM(T.total_impressions) AS total_impressions,
        SUM(T.total_clicks) AS total_clicks,
        SUM(T.total_earnings) AS total_earnings
    FROM (
        SELECT zone_id, impressions AS total_impressions, clicks AS total_clicks, publisher_payout AS total_earnings {$group_by_select_inner}
        FROM stats_daily_summary
        WHERE stat_date BETWEEN ? AND ?
        UNION ALL
        SELECT zone_id, impressions AS total_impressions, clicks AS total_clicks, (cost * ? / 100) AS total_earnings {$group_by_select_inner}
        FROM campaign_stats
        WHERE stat_date = ? AND stat_date >= ?
    ) AS T
    {$join_clause}
";

$params = [$date_hist_from, $date_hist_to, $revenue_share, $today, $date_from];
$types = "ssdss";
$where_clauses = ["si.user_id = ?"];
$params[] = $publisher_id;
$types .= "i";

if ($filter_site_id) {
    $where_clauses[] = "si.id = ?";
    $params[] = $filter_site_id;
    $types .= "i";
}
if ($filter_zone_id) {
    $where_clauses[] = "z.id = ?";
    $params[] = $filter_zone_id;
    $types .= "i";
}

$sql .= " WHERE " . implode(' AND ', $where_clauses);
$sql .= " {$group_by_clause} ORDER BY group_field DESC";
$report_rows = get_query_results($conn, $sql, $params, $types);

// --- 3. DATA UNTUK SUMMARY CARDS & CHART ---
$totals = ['impressions' => 0, 'clicks' => 0, 'earnings' => 0];
$chart_impressions_data = [];
$chart_earnings_data = [];
$daily_data_for_chart = [];

if ($group_by === 'date' && !empty($report_rows)) {
    foreach($report_rows as $row) {
        $daily_data_for_chart[$row['group_field']] = $row;
    }
}

// Hitung total keseluruhan
foreach($report_rows as $row) {
    $totals['impressions'] += $row['total_impressions'];
    $totals['clicks'] += $row['total_clicks'];
    $totals['earnings'] += $row['total_earnings'];
}

// Siapkan data untuk chart jika dikelompokkan berdasarkan tanggal
if ($group_by === 'date') {
    $period = new DatePeriod(new DateTime($date_from), new DateInterval('P1D'), (new DateTime($date_to))->modify('+1 day'));
    foreach ($period as $date) {
        $date_str = $date->format('Y-m-d');
        $impressions = (int)($daily_data_for_chart[$date_str]['total_impressions'] ?? 0);
        $earnings = (float)($daily_data_for_chart[$date_str]['total_earnings'] ?? 0);
        $chart_impressions_data[] = ['x' => $date_str, 'y' => $impressions];
        $chart_earnings_data[] = ['x' => $date_str, 'y' => round($earnings, 6)];
    }
}
$chart_data_json = json_encode(['impressions' => $chart_impressions_data, 'earnings' => $chart_earnings_data]);

$totals['ctr'] = ($totals['impressions'] > 0) ? ($totals['clicks'] / $totals['impressions']) * 100 : 0;
$totals['ecpm'] = ($totals['impressions'] > 0) ? ($totals['earnings'] / $totals['impressions']) * 1000 : 0;

require_once __DIR__ . '/templates/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

<h1 class="mt-4 mb-4">Detailed Statistics</h1>
<?php
$today = date('Y-m-d');
$includes_today = ($date_to >= $today);
?>
<?php if ($includes_today): ?>
<div class="alert alert-warning small">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>Note:</strong> Statistics for today (<?php echo date('j M Y'); ?>) are provisional and may change as data is updated in real-time. Historical data has been aggregated and is final.
</div>
<?php else: ?>
<div class="alert alert-info small">Note: All reports are based on summarized historical data for maximum performance.</div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4"><div class="card shadow-sm"><div class="card-body text-center"><div class="text-muted small text-uppercase">Impressions</div><h4 class="fw-bold mb-0"><?php echo number_format($totals['impressions']); ?></h4></div></div></div>
    <div class="col-lg-3 col-md-6 mb-4"><div class="card shadow-sm"><div class="card-body text-center"><div class="text-muted small text-uppercase">Clicks</div><h4 class="fw-bold mb-0"><?php echo number_format($totals['clicks']); ?></h4></div></div></div>
    <div class="col-lg-3 col-md-6 mb-4"><div class="card shadow-sm"><div class="card-body text-center"><div class="text-muted small text-uppercase">CTR</div><h4 class="fw-bold mb-0"><?php echo number_format($totals['ctr'], 2); ?>%</h4></div></div></div>
    <div class="col-lg-3 col-md-6 mb-4"><div class="card shadow-sm"><div class="card-body text-center"><div class="text-muted small text-uppercase">Earnings</div><h4 class="fw-bold mb-0 text-success">$<?php echo number_format($totals['earnings'], 6); ?></h4></div></div></div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-filter me-2"></i>Filter & Group Report</div>
    <div class="card-body">
        <form id="filterForm" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-3 col-md-6"><label class="form-label">Date Range</label><div class="input-group"><input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>"><input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>"></div></div>
            <div class="col-lg-2 col-md-6"><label class="form-label">Filter by Site</label><select class="form-select" name="site_id" onchange="document.getElementById('filterForm').submit();"><option value="">All My Sites</option><?php foreach($sites_list as $site): ?><option value="<?php echo $site['id']; ?>" <?php if($filter_site_id == $site['id']) echo 'selected'; ?>><?php echo htmlspecialchars($site['url']); ?></option><?php endforeach; ?></select></div>
            <div class="col-lg-2 col-md-6"><label class="form-label">Filter by Zone</label><select class="form-select" name="zone_id"><option value="">All Zones</option><?php foreach($zones_list as $zone): ?><option value="<?php echo $zone['id']; ?>" <?php if($filter_zone_id == $zone['id']) echo 'selected'; ?>><?php echo htmlspecialchars($zone['name']); ?></option><?php endforeach; ?></select></div>
            <div class="col-lg-2 col-md-6"><label class="form-label">Group By</label><select class="form-select" name="group_by"><option value="date" <?php if($group_by=='date') echo 'selected';?>>Date</option><option value="site" <?php if($group_by=='site') echo 'selected';?>>Site</option><option value="zone" <?php if($group_by=='zone') echo 'selected';?>>Zone</option><option value="country" <?php if($group_by=='country') echo 'selected';?>>Country</option></select></div>
            <div class="col-lg-3 col-md-12"><button type="submit" class="btn btn-primary w-100">Apply</button></div>
        </form>
    </div>
</div>

<?php if ($group_by === 'date' && !empty($chart_data_json) && !empty($report_rows)): ?>
<div class="card mb-4 shadow-sm"><div class="card-header bg-white"><h5 class="mb-0">Performance Chart</h5></div><div class="card-body"><canvas id="performanceChart" style="height: 300px; width: 100%;"></canvas></div></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive"><table class="table table-hover">
            <thead class="table-light"><tr><th><?php echo $main_column_header; ?></th><th>Impressions</th><th>Clicks</th><th>CTR</th><th>eCPM ($)</th><th>Earnings ($)</th></tr></thead>
            <tbody>
            <?php if (!empty($report_rows)): foreach($report_rows as $row): ?>
                <?php
                    $ctr = ($row['total_impressions'] > 0) ? ($row['total_clicks'] / $row['total_impressions']) * 100 : 0;
                    $ecpm = ($row['total_impressions'] > 0) ? ($row['total_earnings'] / $row['total_impressions']) * 1000 : 0;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['group_field'] ?? 'N/A'); ?></td>
                    <td><?php echo number_format($row['total_impressions']); ?></td>
                    <td><?php echo number_format($row['total_clicks']); ?></td>
                    <td><?php echo number_format($ctr, 2); ?>%</td>
                    <td><?php echo number_format($ecpm, 4); ?></td>
                    <td class="fw-bold text-success"><?php echo number_format($row['total_earnings'], 6); ?></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No data found for the selected filters.</td></tr>
            <?php endif; ?>
            </tbody>
             <tfoot class="table-light fw-bold">
                <tr><td>Total</td><td><?php echo number_format($totals['impressions']); ?></td><td><?php echo number_format($totals['clicks']); ?></td><td><?php echo number_format($totals['ctr'], 2); ?>%</td><td><?php echo number_format($totals['ecpm'], 4); ?></td><td class="text-success"><?php echo number_format($totals['earnings'], 6); ?></td></tr>
            </tfoot>
        </table></div>
    </div>
</div>

<script>
<?php if ($group_by === 'date' && !empty($chart_data_json)): ?>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('performanceChart');
    if (ctx) {
        const chartData = <?php echo $chart_data_json; ?>;
        new Chart(ctx, {
            type: 'line',
            data: {
                datasets: [{
                    label: 'Earnings ($)', yAxisID: 'yEarnings', borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)', fill: true,
                    data: chartData.earnings, tension: 0.3
                }, {
                    label: 'Impressions', yAxisID: 'yImpressions', borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)', fill: true,
                    data: chartData.impressions, tension: 0.3
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                scales: {
                    x: { type: 'time', time: { unit: 'day', tooltipFormat: 'd MMM' }, grid: { display: false } },
                    yEarnings: { type: 'linear', position: 'right', grid: { drawOnChartArea: false }, title: {display: true, text: 'Earnings ($)'}, ticks: { callback: value => '$' + value.toFixed(4) } },
                    yImpressions: { type: 'linear', position: 'left', title: {display: true, text: 'Impressions'}, ticks: { callback: value => new Intl.NumberFormat().format(value) } }
                }
            }
        });
    }
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/templates/footer.php'; ?>