<?php
// File: /admin/dashboard.php (DEFINITIVE FINAL - Complete & Fully Optimized)

require_once __DIR__ . '/init.php';

// --- 1. LOGIKA FILTER TANGGAL ---
$range = $_GET['range'] ?? 'today';
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

switch ($range) {
    case 'yesterday':
        $date_from = $date_to = $yesterday;
        break;
    case 'this_week':
        $date_from = date('Y-m-d', strtotime('monday this week'));
        $date_to = $today;
        break;
    case 'this_month':
        $date_from = date('Y-m-d', strtotime('first day of this month'));
        $date_to = $today;
        break;
    case 'custom':
        $date_from = $_GET['date_from'] ?? $today;
        $date_to = $_GET['date_to'] ?? $today;
        break;
    case 'today':
    default:
        $date_from = $date_to = $today;
        break;
}

// --- 2. LOGIKA PENGAMBILAN DATA (OPTIMIZED) ---
function get_query_results($conn, $sql, $params = [], $types = '') {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) { 
        error_log("SQL Prepare Error: " . $conn->error . " | Query: " . $sql);
        return []; 
    }
    if (!empty($params) && !empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $data;
}
function get_single_metric($conn, $sql, $params = [], $types = '') {
    $data = get_query_results($conn, $sql, $params, $types);
    return !empty($data) ? (float)array_values($data[0])[0] : 0;
}

// Queries menggunakan pendekatan hybrid (historis + realtime)
$today_date = date('Y-m-d');
$yesterday_date = date('Y-m-d', strtotime('-1 day'));

// Tentukan rentang tanggal untuk query historis dan realtime
$date_hist_from = ($date_from < $today_date) ? $date_from : $today_date;
$date_hist_to = ($date_to < $today_date) ? $date_to : $yesterday_date;

$total_revenue = get_single_metric($conn, "
    SELECT SUM(T.cost) FROM (
        SELECT cost FROM stats_daily_summary WHERE stat_date BETWEEN ? AND ?
        UNION ALL
        SELECT cost FROM campaign_stats WHERE stat_date = ? AND stat_date >= ?
    ) AS T
", [$date_hist_from, $date_hist_to, $today_date, $date_from], "ssss");

$total_impressions = get_single_metric($conn, "
    SELECT SUM(T.impressions) FROM (
        SELECT impressions FROM stats_daily_summary WHERE stat_date BETWEEN ? AND ?
        UNION ALL
        SELECT impressions FROM campaign_stats WHERE stat_date = ? AND stat_date >= ?
    ) AS T
", [$date_hist_from, $date_hist_to, $today_date, $date_from], "ssss");

$total_clicks = get_single_metric($conn, "
    SELECT SUM(T.clicks) FROM (
        SELECT clicks FROM stats_daily_summary WHERE stat_date BETWEEN ? AND ?
        UNION ALL
        SELECT clicks FROM campaign_stats WHERE stat_date = ? AND stat_date >= ?
    ) AS T
", [$date_hist_from, $date_hist_to, $today_date, $date_from], "ssss");

$platform_profit = get_single_metric($conn, "
    SELECT SUM(T.cost - T.publisher_payout) FROM (
        SELECT cost, publisher_payout FROM stats_daily_summary WHERE stat_date BETWEEN ? AND ?
        UNION ALL
        SELECT cost, 0 as publisher_payout FROM campaign_stats WHERE stat_date = ? AND stat_date >= ?
    ) AS T
", [$date_hist_from, $date_hist_to, $today_date, $date_from], "ssss");

// Data untuk Grafik Performa
$chart_sql = "
    SELECT T.stat_date, SUM(T.impressions) AS daily_impressions, SUM(T.cost) AS daily_revenue 
    FROM (
        SELECT stat_date, impressions, cost FROM stats_daily_summary WHERE stat_date BETWEEN ? AND ?
        UNION ALL
        SELECT stat_date, impressions, cost FROM campaign_stats WHERE stat_date = ? AND stat_date >= ?
    ) AS T
    GROUP BY T.stat_date 
    ORDER BY T.stat_date ASC
";
$chart_result = get_query_results($conn, $chart_sql, [$date_hist_from, $date_hist_to, $today_date, $date_from], "ssss");

// Data untuk Top 5 Lists
$top_campaigns_sql = "
    SELECT c.name, SUM(T.cost) as revenue 
    FROM (
        SELECT campaign_id, cost FROM stats_daily_summary WHERE stat_date BETWEEN ? AND ?
        UNION ALL
        SELECT campaign_id, cost FROM campaign_stats WHERE stat_date = ? AND stat_date >= ?
    ) AS T
    JOIN campaigns c ON T.campaign_id = c.id 
    WHERE T.campaign_id > 0 
    GROUP BY c.id, c.name 
    ORDER BY revenue DESC 
    LIMIT 5
";
$top_campaigns = get_query_results($conn, $top_campaigns_sql, [$date_hist_from, $date_hist_to, $today_date, $date_from], "ssss");

$top_supply_sql = "
    SELECT rs.name, SUM(T.cost) as total_revenue, SUM(T.cost - T.publisher_payout) as platform_profit 
    FROM (
        SELECT zone_id, cost, publisher_payout FROM stats_daily_summary WHERE stat_date BETWEEN ? AND ?
        UNION ALL
        SELECT zone_id, cost, 0 as publisher_payout FROM campaign_stats WHERE stat_date = ? AND stat_date >= ?
    ) AS T
    LEFT JOIN zones z ON T.zone_id = z.id 
    LEFT JOIN sites si ON z.site_id = si.id 
    LEFT JOIN rtb_supply_sources rs ON si.user_id = rs.user_id 
    WHERE rs.id IS NOT NULL 
    GROUP BY rs.id, rs.name 
    ORDER BY total_revenue DESC 
    LIMIT 5
";
$top_supply = get_query_results($conn, $top_supply_sql, [$date_hist_from, $date_hist_to, $today_date, $date_from], "ssss");

// Proses data untuk Chart.js
$chart_impressions_data = []; $chart_revenue_data = [];
if (!empty($chart_result)) {
    $period = new DatePeriod(new DateTime($date_from), new DateInterval('P1D'), (new DateTime($date_to))->modify('+1 day'));
    $daily_data = array_column($chart_result, null, 'stat_date');
    foreach ($period as $date) {
        $date_str = $date->format('Y-m-d');
        $chart_impressions_data[] = ['x' => $date_str, 'y' => (int)($daily_data[$date_str]['daily_impressions'] ?? 0)];
        $chart_revenue_data[] = ['x' => $date_str, 'y' => round((float)($daily_data[$date_str]['daily_revenue'] ?? 0), 6)];
    }
}
$chart_data_json = json_encode(['impressions' => $chart_impressions_data, 'revenue' => $chart_revenue_data]);

require_once __DIR__ . '/templates/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mt-4 mb-0">Dashboard Overview</h1>
    <div class="d-flex align-items-center">
        <div class="btn-group me-3" role="group">
            <a href="?range=today" class="btn btn-sm <?php echo ($range == 'today') ? 'btn-primary' : 'btn-outline-secondary'; ?>">Today</a>
            <a href="?range=yesterday" class="btn btn-sm <?php echo ($range == 'yesterday') ? 'btn-primary' : 'btn-outline-secondary'; ?>">Yesterday</a>
            <a href="?range=this_week" class="btn btn-sm <?php echo ($range == 'this_week') ? 'btn-primary' : 'btn-outline-secondary'; ?>">This Week</a>
            <a href="?range=this_month" class="btn btn-sm <?php echo ($range == 'this_month') ? 'btn-primary' : 'btn-outline-secondary'; ?>">This Month</a>
        </div>
        <form method="GET" class="d-flex align-items-center">
            <input type="hidden" name="range" value="custom">
            <input type="date" class="form-control form-control-sm me-2" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
            <span class="me-2 text-muted">to</span>
            <input type="date" class="form-control form-control-sm me-2" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
            <button type="submit" class="btn btn-sm btn-secondary">Apply</button>
        </form>
    </div>
</div>
<?php
$includes_today = ($date_to >= $today_date);
?>
<?php if ($includes_today): ?>
<div class="alert alert-warning small">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>Note:</strong> Statistics for today (<?php echo date('j M Y'); ?>) include real-time data and may change throughout the day. Historical data has been aggregated and is final.
</div>
<?php else: ?>
<div class="alert alert-info small">
    <i class="bi bi-info-circle-fill"></i> Note: Dashboard data is summarized for performance and shows historical aggregated data.
</div>
<?php endif; ?>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-primary shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Revenue</div><div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($total_revenue, 4); ?></div></div><div class="col-auto"><i class="bi bi-cash-coin fs-2 text-gray-300"></i></div></div></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-dark shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Platform Profit</div><div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($platform_profit, 4); ?></div></div><div class="col-auto"><i class="bi bi-building fs-2 text-gray-300"></i></div></div></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-success text-uppercase mb-1">Impressions</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_impressions); ?></div></div><div class="col-auto"><i class="bi bi-eye-fill fs-2 text-gray-300"></i></div></div></div></div></div>
    <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-info shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">Clicks</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_clicks); ?></div></div><div class="col-auto"><i class="bi bi-cursor-fill fs-2 text-gray-300"></i></div></div></div></div></div>
</div>

<div class="row"><div class="col-12"><div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Performance Trend</h6></div><div class="card-body"><div class="chart-area" style="height: 320px;"><canvas id="performanceChart"></canvas></div></div></div></div></div>

<div class="row">
    <div class="col-lg-6 mb-4"><div class="card shadow"><div class="card-header py-3"><h6 class="m-0 font-weight-bold">Top 5 Campaigns by Revenue</h6></div><div class="card-body"><ul class="list-group list-group-flush"><?php if(!empty($top_campaigns)): foreach($top_campaigns as $c): ?><li class="list-group-item d-flex justify-content-between align-items-center"><?php echo htmlspecialchars($c['name']); ?><span class="badge bg-primary rounded-pill">$<?php echo number_format($c['revenue'], 4); ?></span></li><?php endforeach; else: ?><li class="list-group-item">No data available for this period.</li><?php endif; ?></ul></div></div></div>
    <div class="col-lg-6 mb-4"><div class="card shadow"><div class="card-header py-3"><h6 class="m-0 font-weight-bold">Top 5 Supply Partners</h6></div><div class="card-body"><ul class="list-group list-group-flush"><?php if(!empty($top_supply)): foreach($top_supply as $s): ?><li class="list-group-item"><div class="d-flex justify-content-between"><span><?php echo htmlspecialchars($s['name']); ?></span><strong>$<?php echo number_format($s['total_revenue'], 4); ?></strong></div><div class="d-flex justify-content-between text-muted small"><span>Platform Profit</span><span>$<?php echo number_format($s['platform_profit'], 4); ?></span></div></li><?php endforeach; else: ?><li class="list-group-item">No data available for this period.</li><?php endif; ?></ul></div></div></div>
</div>


<style>.border-left-primary{border-left:.25rem solid #4e73df!important}.border-left-success{border-left:.25rem solid #1cc88a!important}.border-left-info{border-left:.25rem solid #36b9cc!important}.border-left-dark{border-left:.25rem solid #5a5c69!important}.text-xs{font-size:.7rem}.text-gray-300{color:#dddfeb!important}</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('performanceChart');
    if (ctx && <?php echo !empty($chart_data_json) ? 'true' : 'false'; ?>) {
        const chartData = <?php echo $chart_data_json; ?>;
        new Chart(ctx, { 
            type: 'line', 
            data: {
                datasets: [{ 
                    label: 'Revenue ($)', yAxisID: 'yRevenue', borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)', fill: true,
                    data: chartData.revenue,
                    tension: 0.3 
                }, { 
                    label: 'Impressions', yAxisID: 'yImpressions', borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.05)', fill: true,
                    data: chartData.impressions,
                    tension: 0.3
                }] 
            }, 
            options: { 
                maintainAspectRatio: false, responsive: true, interaction: { mode: 'index', intersect: false }, 
                scales: { 
                    x: { type: 'time', time: { unit: 'day', tooltipFormat: 'd MMM' }, grid: { display: false } }, 
                    yRevenue: { type: 'linear', position: 'left', title: { display: true, text: 'Revenue (USD)' }, ticks: { callback: value => '$' + value.toFixed(4) } }, 
                    yImpressions: { type: 'linear', position: 'right', title: { display: true, text: 'Impressions' }, grid: { drawOnChartArea: false }, ticks: { callback: value => new Intl.NumberFormat().format(value) } } 
                }, 
                plugins: { 
                    tooltip: { callbacks: { label: function(context) { let label = context.dataset.label || ''; if (label) { label += ': '; } if (context.parsed.y !== null) { if(context.dataset.yAxisID === 'yRevenue') { label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y); } else { label += new Intl.NumberFormat().format(context.parsed.y); } } return label; } } } 
                } 
            } 
        });
    }
});
</script>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
