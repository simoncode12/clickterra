<?php
// File: /admin/dashboard.php (ENHANCED with Platform Profit per Partner)

require_once __DIR__ . '/init.php';

// --- DATA FETCHING FOR DASHBOARD ---

// 1. Tentukan rentang waktu (7 hari terakhir)
$date_7_days_ago = date('Y-m-d', strtotime('-6 days'));
$today = date('Y-m-d');

// Helper untuk menjalankan query dan mendapatkan satu hasil
function get_single_value($conn, $sql, $params = []) {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) { return 0; }
    if ($params) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result ? array_values($result)[0] : 0;
}

// 2. Data untuk Summary Cards
$total_revenue = get_single_value($conn, "SELECT SUM(cost) FROM campaign_stats WHERE stat_date BETWEEN ? AND ?", [$date_7_days_ago, $today]);
$total_impressions = get_single_value($conn, "SELECT SUM(impressions) FROM campaign_stats WHERE stat_date BETWEEN ? AND ?", [$date_7_days_ago, $today]);
$total_clicks = get_single_value($conn, "SELECT SUM(clicks) FROM campaign_stats WHERE stat_date BETWEEN ? AND ?", [$date_7_days_ago, $today]);
$total_requests = get_single_value($conn, "SELECT COUNT(id) FROM rtb_requests WHERE request_time BETWEEN ? AND ?", [$date_7_days_ago . ' 00:00:00', $today . ' 23:59:59']);

$platform_profit_sql = "
    SELECT SUM(s.cost * (100 - COALESCE(u.revenue_share, 0)) / 100) 
    FROM campaign_stats s
    JOIN zones z ON s.zone_id = z.id
    JOIN sites si ON z.site_id = si.id
    JOIN users u ON si.user_id = u.id
    WHERE s.stat_date BETWEEN ? AND ?
";
$platform_profit = get_single_value($conn, $platform_profit_sql, [$date_7_days_ago, $today]);

$fill_rate = ($total_requests > 0) ? ($total_impressions / $total_requests) * 100 : 0;
$ctr = ($total_impressions > 0) ? ($total_clicks / $total_impressions) * 100 : 0;

// 3. Data untuk Grafik Performa (7 hari terakhir)
// ... (Kode Chart tetap sama) ...
$chart_sql = "
    SELECT
        all_dates.stat_date,
        COALESCE(SUM(cs.impressions), 0) AS daily_impressions,
        COALESCE(SUM(cs.cost), 0) AS daily_revenue
    FROM (
        SELECT DATE_ADD(?, INTERVAL a+b DAY) AS stat_date
        FROM (SELECT 0 a UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6) t1,
             (SELECT 0 b) t2
    ) AS all_dates
    LEFT JOIN campaign_stats cs ON all_dates.stat_date = cs.stat_date
    WHERE all_dates.stat_date <= ?
    GROUP BY all_dates.stat_date
    ORDER BY all_dates.stat_date ASC
";
$stmt_chart = $conn->prepare($chart_sql);
$stmt_chart->bind_param("ss", $date_7_days_ago, $today);
$stmt_chart->execute();
$chart_result = $stmt_chart->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_chart->close();

$chart_labels = [];
$chart_impressions = [];
$chart_revenue = [];
foreach ($chart_result as $row) {
    $chart_labels[] = date("d M", strtotime($row['stat_date']));
    $chart_impressions[] = $row['daily_impressions'];
    $chart_revenue[] = round($row['daily_revenue'], 4);
}
$chart_data_json = json_encode([
    'labels' => $chart_labels,
    'impressions' => $chart_impressions,
    'revenue' => $chart_revenue
]);


// 4. Data untuk Top 5 Lists
$top_campaigns_sql = "SELECT c.name, SUM(s.cost) as revenue FROM campaign_stats s JOIN campaigns c ON s.campaign_id = c.id WHERE s.campaign_id > 0 AND s.stat_date BETWEEN ? AND ? GROUP BY c.id, c.name ORDER BY revenue DESC LIMIT 5";
$top_campaigns_stmt = $conn->prepare($top_campaigns_sql);
$top_campaigns_stmt->bind_param("ss", $date_7_days_ago, $today);
$top_campaigns_stmt->execute();
$top_campaigns = $top_campaigns_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$top_campaigns_stmt->close();

// --- PERUBAHAN UTAMA: Query untuk Top Supply sekarang menghitung Gross Revenue dan Profit ---
$top_supply_sql = "
    SELECT 
        rs.name, 
        SUM(s.cost) as total_revenue,
        SUM(s.cost * (100 - u.revenue_share) / 100) as platform_profit
    FROM campaign_stats s 
    JOIN zones z ON s.zone_id = z.id 
    JOIN sites si ON z.site_id = si.id 
    JOIN rtb_supply_sources rs ON si.user_id = rs.user_id
    JOIN users u ON si.user_id = u.id
    WHERE s.stat_date BETWEEN ? AND ? 
    GROUP BY rs.id, rs.name 
    ORDER BY total_revenue DESC 
    LIMIT 5
";
$top_supply_stmt = $conn->prepare($top_supply_sql);
$top_supply_stmt->bind_param("ss", $date_7_days_ago, $today);
$top_supply_stmt->execute();
$top_supply = $top_supply_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$top_supply_stmt->close();


require_once __DIR__ . '/templates/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mt-4 mb-0">Dashboard Overview</h1>
    <div class="text-muted">Last 7 Days</div>
</div>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Revenue</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($total_revenue, 4); ?></div>
                </div>
                <div class="col-auto"><i class="bi bi-cash-coin fs-2 text-gray-300"></i></div>
            </div></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-dark shadow h-100 py-2">
            <div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Platform Profit</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($platform_profit, 4); ?></div>
                </div>
                <div class="col-auto"><i class="bi bi-building fs-2 text-gray-300"></i></div>
            </div></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Impressions</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_impressions); ?></div>
                </div>
                <div class="col-auto"><i class="bi bi-eye-fill fs-2 text-gray-300"></i></div>
            </div></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
             <div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Clicks</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_clicks); ?></div>
                </div>
                <div class="col-auto"><i class="bi bi-cursor-fill fs-2 text-gray-300"></i></div>
            </div></div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Revenue & Impressions Trend</h6>
            </div>
            <div class="card-body">
                <div class="chart-area" style="height: 320px;">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold">Top 5 Campaigns by Revenue</h6></div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php if(!empty($top_campaigns)): foreach($top_campaigns as $c): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($c['name']); ?>
                            <span class="badge bg-primary rounded-pill">$<?php echo number_format($c['revenue'], 4); ?></span>
                        </li>
                    <?php endforeach; else: ?>
                        <li class="list-group-item">No campaign data available.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
         <div class="card shadow">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold">Top 5 Supply Partners</h6></div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php if(!empty($top_supply)): foreach($top_supply as $s): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span><?php echo htmlspecialchars($s['name']); ?></span>
                                <strong>$<?php echo number_format($s['total_revenue'], 4); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between text-muted small">
                                <span>Platform Profit</span>
                                <span>$<?php echo number_format($s['platform_profit'], 4); ?></span>
                            </div>
                        </li>
                    <?php endforeach; else: ?>
                        <li class="list-group-item">No supply partner data available.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>


<style>.border-left-primary{border-left:.25rem solid #4e73df!important}.border-left-success{border-left:.25rem solid #1cc88a!important}.border-left-info{border-left:.25rem solid #36b9cc!important}.border-left-warning{border-left:.25rem solid #f6c23e!important}.border-left-dark{border-left:.25rem solid #5a5c69!important}.text-xs{font-size:.7rem}.text-gray-300{color:#dddfeb!important}</style>

<script>
// ... (Kode Javascript untuk Chart tetap sama) ...
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('performanceChart');
    if (ctx) {
        const chartData = <?php echo $chart_data_json; ?>;
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Revenue ($)',
                    yAxisID: 'yRevenue',
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    fill: true,
                    data: chartData.revenue,
                    tension: 0.3
                }, {
                    label: 'Impressions',
                    yAxisID: 'yImpressions',
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.05)',
                    fill: true,
                    data: chartData.impressions,
                    tension: 0.3
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    x: { grid: { display: false } },
                    yRevenue: {
                        type: 'linear',
                        position: 'left',
                        title: { display: true, text: 'Revenue (USD)' },
                        ticks: { callback: value => '$' + value.toFixed(4) }
                    },
                    yImpressions: {
                        type: 'linear',
                        position: 'right',
                        title: { display: true, text: 'Impressions' },
                        grid: { drawOnChartArea: false },
                        ticks: { callback: value => new Intl.NumberFormat().format(value) }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed.y !== null) {
                                    if(context.dataset.yAxisID === 'yRevenue') {
                                        label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
                                    } else {
                                        label += new Intl.NumberFormat().format(context.parsed.y);
                                    }
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>


<?php 
require_once __DIR__ . '/templates/footer.php'; 
?>
