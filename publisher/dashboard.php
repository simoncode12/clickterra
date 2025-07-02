<?php
// File: /publisher/dashboard.php (REDESIGNED - Inspired by Trafficstars)

require_once __DIR__ . '/init.php';

// --- 1. GET PUBLISHER INFO ---
$publisher_id = $_SESSION['publisher_id'];
$stmt_user = $conn->prepare("SELECT revenue_share FROM users WHERE id = ?");
$stmt_user->bind_param("i", $publisher_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();
$revenue_share = $user['revenue_share'] ?? 0;

// --- 2. DATA FOR SUMMARY CARDS ---
$base_query = "FROM campaign_stats s JOIN zones z ON s.zone_id = z.id JOIN sites si ON z.site_id = si.id WHERE si.user_id = ?";
function get_publisher_metric($conn, $sql) {
    global $publisher_id;
    $stmt = $conn->prepare($sql);
    if (!$stmt) return 0;
    $stmt->bind_param("i", $publisher_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result ? array_values($result)[0] : 0;
}

$today_earnings = get_publisher_metric($conn, "SELECT SUM(s.cost * {$revenue_share} / 100) {$base_query} AND s.stat_date = CURDATE()");
$this_month_earnings = get_publisher_metric($conn, "SELECT SUM(s.cost * {$revenue_share} / 100) {$base_query} AND MONTH(s.stat_date) = MONTH(CURDATE()) AND YEAR(s.stat_date) = YEAR(CURDATE())");
$total_earnings = get_publisher_metric($conn, "SELECT SUM(s.cost * {$revenue_share} / 100) {$base_query}");

// --- 3. DATA FOR CHART & DETAILED TABLE ---
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-29 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');

$details_sql = "
    SELECT 
        DATE(s.stat_date) AS stat_day,
        SUM(s.impressions) AS total_impressions,
        SUM(s.cost * {$revenue_share} / 100) AS total_earnings
    {$base_query} AND s.stat_date BETWEEN ? AND ?
    GROUP BY stat_day
    ORDER BY stat_day ASC
";
$stmt_details = $conn->prepare($details_sql);
$stmt_details->bind_param("iss", $publisher_id, $date_from, $date_to);
$stmt_details->execute();
$details_result = $stmt_details->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_details->close();

$chart_labels = []; $chart_earnings = []; $chart_impressions = [];
foreach ($details_result as $row) {
    $chart_labels[] = $row['stat_day'];
    $chart_earnings[] = round($row['total_earnings'], 6);
    $chart_impressions[] = $row['total_impressions'];
}
$chart_data_json = json_encode(['labels' => $chart_labels, 'earnings' => $chart_earnings, 'impressions' => $chart_impressions]);

// --- RENDER PAGE ---
require_once __DIR__ . '/templates/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
<style>
    body { background-color: #f0f2f5; }
    .card-stat { background-color: #fff; border: 1px solid #e9ecef; }
    .stat-title { font-size: 0.9rem; color: #6c757d; font-weight: 500; }
    .stat-value { font-size: 1.75rem; font-weight: 700; color: #343a40; }
    .stat-icon { font-size: 1.5rem; color: #adb5bd; }
</style>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card card-stat h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-title text-success">TODAY EARNINGS</div>
                        <div class="stat-value">$<?php echo number_format($today_earnings, 4); ?></div>
                    </div>
                    <i class="bi bi-calendar-day stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card card-stat h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-title text-primary">THIS MONTH EARNINGS</div>
                        <div class="stat-value">$<?php echo number_format($this_month_earnings, 2); ?></div>
                    </div>
                    <i class="bi bi-calendar-month stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card card-stat h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-title text-dark">TOTAL EARNINGS</div>
                        <div class="stat-value">$<?php echo number_format($total_earnings, 2); ?></div>
                    </div>
                    <i class="bi bi-wallet2 stat-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white border-0 pt-3 pb-0">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Overview</h5>
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-auto"><input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>"></div>
                    <div class="col-auto"><input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>"></div>
                    <div class="col-auto"><button type="submit" class="btn btn-sm btn-outline-primary">Apply</button></div>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div style="height: 400px;">
                <canvas id="overviewChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('overviewChart');
    if (ctx) {
        const chartData = <?php echo $chart_data_json; ?>;
        
        const gradientFill = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400);
        gradientFill.addColorStop(0, 'rgba(22, 163, 74, 0.4)');
        gradientFill.addColorStop(1, 'rgba(22, 163, 74, 0)');

        new Chart(ctx, {
            type: 'bar', // Tipe utama adalah bar
            data: {
                labels: chartData.labels,
                datasets: [
                {
                    type: 'line', // Dataset ini di-override menjadi tipe 'line'
                    label: 'Earnings ($)',
                    data: chartData.earnings,
                    yAxisID: 'yEarnings',
                    borderColor: 'rgb(22, 163, 74)',
                    backgroundColor: gradientFill,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 2,
                    pointBackgroundColor: 'rgb(22, 163, 74)'
                },
                {
                    type: 'bar', // Dataset ini tetap sebagai 'bar'
                    label: 'Impressions',
                    data: chartData.impressions,
                    yAxisID: 'yImpressions',
                    backgroundColor: 'rgba(54, 73, 93, 0.8)',
                    borderColor: 'rgba(54, 73, 93, 1)',
                    barPercentage: 0.6,
                    categoryPercentage: 0.7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    x: {
                        type: 'time',
                        time: { unit: 'day' },
                        grid: { display: false }
                    },
                    yImpressions: {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                        title: { display: true, text: 'Impressions' },
                        grid: { color: '#e9ecef', drawBorder: false }
                    },
                    yEarnings: {
                        type: 'linear',
                        position: 'right',
                        beginAtZero: true,
                        title: { display: true, text: 'Earnings (USD)' },
                        grid: { display: false },
                        ticks: { callback: value => '$' + value.toFixed(4) }
                    }
                },
                plugins: {
                    legend: { display: true, position: 'top', align: 'end' },
                    tooltip: {
                        backgroundColor: '#212529',
                        titleFont: { size: 14 },
                        bodyFont: { size: 12 },
                        padding: 12,
                        cornerRadius: 4,
                        boxPadding: 5
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