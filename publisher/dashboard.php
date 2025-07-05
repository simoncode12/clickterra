<?php
// File: /publisher/dashboard.php (FINAL & FIXED - SQL Syntax Error Corrected)

require_once __DIR__ . '/init.php';

// --- 1. GET PUBLISHER INFO ---
$publisher_id = $_SESSION['publisher_id'];
$stmt_user = $conn->prepare("SELECT revenue_share FROM users WHERE id = ?");
$stmt_user->bind_param("i", $publisher_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();
$revenue_share = (float)($user['revenue_share'] ?? 0);


// --- 2. SETUP FILTER & DATA FETCHING (OPTIMIZED) ---
$range = $_GET['range'] ?? 'this_week';
$today = date('Y-m-d');

switch ($range) {
    case 'today':
        $date_from = $date_to = $today;
        break;
    case 'yesterday':
        $date_from = $date_to = date('Y-m-d', strtotime('-1 day'));
        break;
    case 'this_month':
        $date_from = date('Y-m-d', strtotime('first day of this month'));
        $date_to = $today;
        break;
    case 'custom':
        $date_from = $_GET['date_from'] ?? $today;
        $date_to = $_GET['date_to'] ?? $today;
        break;
    case 'this_week':
    default:
        $date_from = date('Y-m-d', strtotime('monday this week'));
        $date_to = $today;
        break;
}

// Helper
function get_query_results($conn, $sql, $params = [], $types = '') {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) { return []; }
    if (!empty($params)) { $stmt->bind_param($types, ...$params); }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $data;
}

// Semua query sekarang menggunakan pendekatan hybrid (historis + realtime)
$today_date = date('Y-m-d');
$yesterday_date = date('Y-m-d', strtotime('-1 day'));

// Tentukan rentang tanggal untuk query historis dan realtime
$date_hist_from = ($date_from < $today_date) ? $date_from : $today_date;
$date_hist_to = ($date_to < $today_date) ? $date_to : $yesterday_date;

// Ambil revenue_share untuk perhitungan earnings dari campaign_stats
$revenue_share_query = get_query_results($conn, "SELECT revenue_share FROM users WHERE id = ?", [$publisher_id], "i");
$revenue_share = $revenue_share_query[0]['revenue_share'] ?? 0;

$base_summary_query = "FROM stats_daily_summary sds JOIN zones z ON sds.zone_id = z.id JOIN sites si ON z.site_id = si.id WHERE si.user_id = ?";

// Data untuk kartu ringkasan - menggunakan query hybrid
$summary_sql = "
    SELECT 
        SUM(T.impressions) as total_impressions, 
        SUM(T.clicks) as total_clicks, 
        SUM(T.earnings) as total_earnings
    FROM (
        SELECT sds.impressions, sds.clicks, sds.publisher_payout as earnings
        FROM stats_daily_summary sds 
        JOIN zones z ON sds.zone_id = z.id 
        JOIN sites si ON z.site_id = si.id 
        WHERE si.user_id = ? AND sds.stat_date BETWEEN ? AND ?
        UNION ALL
        SELECT cs.impressions, cs.clicks, (cs.cost * ? / 100) as earnings
        FROM campaign_stats cs
        JOIN zones z ON cs.zone_id = z.id 
        JOIN sites si ON z.site_id = si.id 
        WHERE si.user_id = ? AND cs.stat_date = ? AND cs.stat_date >= ?
    ) AS T
";
$summary_data = get_query_results($conn, $summary_sql, [$publisher_id, $date_hist_from, $date_hist_to, $revenue_share, $publisher_id, $today_date, $date_from], "issdsis")[0] ?? [];
$total_impressions = $summary_data['total_impressions'] ?? 0;
$total_clicks = $summary_data['total_clicks'] ?? 0;
$total_earnings = $summary_data['total_earnings'] ?? 0;
$total_ctr = ($total_impressions > 0) ? ($total_clicks / $total_impressions) * 100 : 0;

// Data untuk chart - menggunakan query hybrid
$chart_sql = "
    SELECT 
        T.stat_date, 
        SUM(T.impressions) as daily_impressions, 
        SUM(T.earnings) as daily_earnings
    FROM (
        SELECT sds.stat_date, sds.impressions, sds.publisher_payout as earnings
        FROM stats_daily_summary sds 
        JOIN zones z ON sds.zone_id = z.id 
        JOIN sites si ON z.site_id = si.id 
        WHERE si.user_id = ? AND sds.stat_date BETWEEN ? AND ?
        UNION ALL
        SELECT cs.stat_date, cs.impressions, (cs.cost * ? / 100) as earnings
        FROM campaign_stats cs
        JOIN zones z ON cs.zone_id = z.id 
        JOIN sites si ON z.site_id = si.id 
        WHERE si.user_id = ? AND cs.stat_date = ? AND cs.stat_date >= ?
    ) AS T
    GROUP BY T.stat_date 
    ORDER BY T.stat_date ASC
";
$chart_result = get_query_results($conn, $chart_sql, [$publisher_id, $date_hist_from, $date_hist_to, $revenue_share, $publisher_id, $today_date, $date_from], "issdsis");

// Proses data untuk Chart.js
$chart_impressions_data = []; $chart_earnings_data = [];
if (!empty($chart_result)) {
    $period = new DatePeriod(new DateTime($date_from), new DateInterval('P1D'), (new DateTime($date_to))->modify('+1 day'));
    $daily_data = array_column($chart_result, null, 'stat_date');
    foreach ($period as $date) {
        $date_str = $date->format('Y-m-d');
        $chart_impressions_data[] = ['x' => $date_str, 'y' => (int)($daily_data[$date_str]['daily_impressions'] ?? 0)];
        $chart_earnings_data[] = ['x' => $date_str, 'y' => round((float)($daily_data[$date_str]['daily_earnings'] ?? 0), 6)];
    }
}
$chart_data_json = json_encode(['impressions' => $chart_impressions_data, 'earnings' => $chart_earnings_data]);


require_once __DIR__ . '/templates/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mt-4 mb-0">Dashboard</h1>
    <div class="d-flex align-items-center">
        <div class="btn-group me-3" role="group">
            <a href="?range=today" class="btn btn-sm <?php echo ($range == 'today') ? 'btn-dark' : 'btn-outline-dark'; ?>">Today</a>
            <a href="?range=yesterday" class="btn btn-sm <?php echo ($range == 'yesterday') ? 'btn-dark' : 'btn-outline-dark'; ?>">Yesterday</a>
            <a href="?range=this_week" class="btn btn-sm <?php echo ($range == 'this_week') ? 'btn-dark' : 'btn-outline-dark'; ?>">This Week</a>
            <a href="?range=this_month" class="btn btn-sm <?php echo ($range == 'this_month') ? 'btn-dark' : 'btn-outline-dark'; ?>">This Month</a>
        </div>
        <form method="GET" class="d-flex align-items-center">
            <input type="hidden" name="range" value="custom">
            <input type="date" class="form-control form-control-sm me-2" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
            <span class="me-2 text-muted">to</span>
            <input type="date" class="form-control form-control-sm me-2" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
            <button type="submit" class="btn btn-sm btn-primary">Apply</button>
        </form>
    </div>
</div>

<?php
$includes_today = ($date_to >= $today_date);
?>
<?php if ($includes_today): ?>
<div class="alert alert-warning small mb-4">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>Note:</strong> Statistics for today (<?php echo date('j M Y'); ?>) are provisional and may change as data is updated in real-time. Historical data has been aggregated and is final.
</div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card card-stat h-100"><div class="card-body d-flex justify-content-between align-items-center"><div><div class="stat-title">Est. Earnings</div><div class="stat-value text-success">$<?php echo number_format($total_earnings, 4); ?></div></div><i class="bi bi-cash-coin stat-icon"></i></div></div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card card-stat h-100"><div class="card-body d-flex justify-content-between align-items-center"><div><div class="stat-title">Impressions</div><div class="stat-value"><?php echo number_format($total_impressions); ?></div></div><i class="bi bi-eye-fill stat-icon"></i></div></div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card card-stat h-100"><div class="card-body d-flex justify-content-between align-items-center"><div><div class="stat-title">Clicks</div><div class="stat-value"><?php echo number_format($total_clicks); ?></div></div><i class="bi bi-cursor-fill stat-icon"></i></div></div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card card-stat h-100"><div class="card-body d-flex justify-content-between align-items-center"><div><div class="stat-title">CTR</div><div class="stat-value"><?php echo number_format($total_ctr, 2); ?>%</div></div><i class="bi bi-pie-chart-fill stat-icon"></i></div></div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white border-0 pt-3 pb-0"><h5 class="card-title mb-0">Performance Overview</h5></div>
    <div class="card-body p-4">
        <div style="height: 400px;">
            <canvas id="performanceChart"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('performanceChart');
    if (ctx) {
        const chartData = <?php echo $chart_data_json; ?>;
        const gradientFill = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400);
        gradientFill.addColorStop(0, 'rgba(54, 162, 235, 0.5)');
        gradientFill.addColorStop(1, 'rgba(54, 162, 235, 0.05)');

        new Chart(ctx, {
            type: 'line',
            data: {
                datasets: [{
                    label: 'Impressions',
                    data: chartData.impressions,
                    yAxisID: 'yViews',
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: gradientFill,
                    fill: 'start',
                    tension: 0.4,
                    pointRadius: 2,
                    pointBackgroundColor: 'rgb(54, 162, 235)'
                }, {
                    label: 'Earnings ($)',
                    data: chartData.earnings,
                    yAxisID: 'yEarnings',
                    borderColor: 'rgb(33, 37, 41)',
                    fill: false,
                    tension: 0.4,
                    pointRadius: 2,
                    pointBackgroundColor: 'rgb(33, 37, 41)',
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false, axis: 'x' },
                plugins: { legend: { display: true, position: 'bottom' } },
                scales: {
                    x: { type: 'time', time: { unit: 'day', tooltipFormat: 'd MMM, yyyy' }, grid: { display: false } },
                    yViews: { type: 'linear', position: 'left', beginAtZero: true, title: { display: true, text: 'Impressions' }, grid: { color: '#f0f2f5' } },
                    yEarnings: { type: 'linear', position: 'right', beginAtZero: true, title: { display: true, text: 'Earnings (USD)' }, grid: { display: false }, ticks: { callback: value => '$' + value.toFixed(4) } }
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
