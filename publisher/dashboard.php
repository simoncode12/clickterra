<?php
// File: /publisher/dashboard.php (KODE LENGKAP & FINAL)

require_once __DIR__ . '/init.php'; // init.php memastikan hanya publisher yang login bisa akses

// --- LOGIKA PENGAMBILAN DATA ---

// 1. Dapatkan ID dan Revenue Share publisher yang sedang login
$publisher_id = $_SESSION['publisher_id'];
$stmt_user = $conn->prepare("SELECT revenue_share FROM users WHERE id = ?");
$stmt_user->bind_param("i", $publisher_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user = $user_result->fetch_assoc();
$stmt_user->close();

// Atur revenue share default ke 70% jika tidak ada di database
$revenue_share = $user['revenue_share'] ?? 70;

// 2. Atur filter tanggal, default 7 hari terakhir
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-6 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');

// 3. Query untuk mengambil data statistik HANYA untuk publisher ini per hari
$sql = "
    SELECT 
        s.stat_date,
        COALESCE(SUM(s.impressions), 0) AS total_impressions,
        COALESCE(SUM(s.clicks), 0) AS total_clicks,
        COALESCE(SUM(s.cost), 0) AS total_revenue -- 'cost' bagi advertiser adalah 'revenue' bagi platform
    FROM campaign_stats s
    JOIN zones z ON s.zone_id = z.id
    JOIN sites si ON z.site_id = si.id
    WHERE 
        si.user_id = ? 
        AND s.stat_date BETWEEN ? AND ?
    GROUP BY 
        s.stat_date
    ORDER BY 
        s.stat_date ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $publisher_id, $date_from, $date_to);
$stmt->execute();
$result = $stmt->get_result();
$report_rows = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 4. Hitung total keseluruhan dan siapkan data untuk chart
$grand_total = ['impressions' => 0, 'clicks' => 0, 'revenue' => 0, 'earnings' => 0];
$chart_labels = [];
$chart_earnings = [];

foreach ($report_rows as $row) {
    // Hitung pendapatan publisher berdasarkan revenue share
    $publisher_earning = $row['total_revenue'] * ($revenue_share / 100);

    // Akumulasi total
    $grand_total['impressions'] += $row['total_impressions'];
    $grand_total['clicks'] += $row['total_clicks'];
    $grand_total['revenue'] += $row['total_revenue'];
    $grand_total['earnings'] += $publisher_earning;

    // Siapkan data untuk grafik
    $chart_labels[] = $row['stat_date'];
    $chart_earnings[] = round($publisher_earning, 4);
}
// Hitung CTR keseluruhan
$grand_total['ctr'] = ($grand_total['impressions'] > 0) ? ($grand_total['clicks'] / $grand_total['impressions']) * 100 : 0;
// Encode data chart ke format JSON
$chart_data = json_encode(['labels' => $chart_labels, 'earnings' => $chart_earnings]);

// Hubungan ke template header harus di bawah logika PHP
require_once __DIR__ . '/templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mt-4 mb-0">Dashboard</h1>
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-auto">
            <label for="date_from" class="form-label">From</label>
            <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
        </div>
        <div class="col-auto">
            <label for="date_to" class="form-label">To</label>
            <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h6 class="card-subtitle mb-2 text-muted">Impressions</h6>
                <h2 class="card-title"><?php echo number_format($grand_total['impressions']); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h6 class="card-subtitle mb-2 text-muted">Clicks</h6>
                <h2 class="card-title"><?php echo number_format($grand_total['clicks']); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h6 class="card-subtitle mb-2 text-muted">CTR</h6>
                <h2 class="card-title"><?php echo number_format($grand_total['ctr'], 2); ?>%</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card shadow-sm bg-success text-white">
            <div class="card-body text-center">
                <h6 class="card-subtitle mb-2 text-white-50">Est. Earnings</h6>
                <h2 class="card-title">$<?php echo number_format($grand_total['earnings'], 4); ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header">
        Earnings Trend
    </div>
    <div class="card-body">
        <canvas id="earningsChart"></canvas>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        Daily Statistics
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Impressions</th>
                        <th>Clicks</th>
                        <th>CTR (%)</th>
                        <th>Est. Earnings ($)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($report_rows)): foreach(array_reverse($report_rows) as $row): ?>
                        <?php 
                            $ctr = ($row['total_impressions'] > 0) ? ($row['total_clicks'] / $row['total_impressions']) * 100 : 0;
                            $earnings = $row['total_revenue'] * ($revenue_share / 100);
                        ?>
                        <tr>
                            <td><?php echo $row['stat_date']; ?></td>
                            <td><?php echo number_format($row['total_impressions']); ?></td>
                            <td><?php echo number_format($row['total_clicks']); ?></td>
                            <td><?php echo number_format($ctr, 2); ?>%</td>
                            <td><?php echo number_format($earnings, 4); ?></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="5" class="text-center">No data available for the selected period.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('earningsChart');
    if(ctx) {
        const chartData = <?php echo $chart_data; ?>;
        new Chart(ctx, { 
            type: 'line', 
            data: { 
                labels: chartData.labels, 
                datasets: [{ 
                    label: 'Publisher Earnings ($)', 
                    data: chartData.earnings, 
                    borderColor: 'rgba(25, 135, 84, 1)',
                    backgroundColor: 'rgba(25, 135, 84, 0.2)',
                    fill: true,
                    tension: 0.1
                }] 
            }, 
            options: { 
                responsive: true,
                maintainAspectRatio: false,
                scales: { 
                    x: { 
                        type: 'time', 
                        time: { 
                            unit: 'day', 
                            tooltipFormat: 'MMM dd, yyyy' 
                        },
                        title: { display: true, text: 'Date' }
                    },
                    y: { 
                        beginAtZero: true, 
                        title: { display: true, text: 'Earnings (USD)' },
                        ticks: { 
                            callback: function(value) { return '$' + value.toFixed(4); } 
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