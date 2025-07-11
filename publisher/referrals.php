<?php
// File: /publisher/referrals.php (NEW)

require_once __DIR__ . '/init.php';

// --- Konfigurasi Program Referral ---
$commission_rate = 5; // Komisi 5%

// --- Dapatkan Info Publisher & Link Referral ---
$publisher_id = $_SESSION['publisher_id'];
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$referral_link = $base_url . '/register.php?ref=' . $publisher_id; // Asumsi ada halaman register.php

// --- Ambil Data Referral ---
$stmt = $conn->prepare("SELECT id, username, email, created_at FROM users WHERE referred_by = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $publisher_id);
$stmt->execute();
$referrals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total_commission = 0;

// --- Hitung Pendapatan & Komisi untuk setiap referral ---
foreach ($referrals as $key => $ref) {
    // Query untuk menghitung total pendapatan yang dihasilkan oleh user yang direferensikan
    $earning_sql = "
        SELECT SUM(s.cost * u_ref.revenue_share / 100) as total_earnings
        FROM campaign_stats s
        JOIN zones z ON s.zone_id = z.id
        JOIN sites si ON z.site_id = si.id
        JOIN users u_ref ON si.user_id = u_ref.id
        WHERE u_ref.id = ?
    ";
    $stmt_earning = $conn->prepare($earning_sql);
    $stmt_earning->bind_param("i", $ref['id']);
    $stmt_earning->execute();
    $earnings_result = $stmt_earning->get_result()->fetch_assoc();
    $stmt_earning->close();
    
    $referred_user_earnings = $earnings_result['total_earnings'] ?? 0;
    $commission_earned = $referred_user_earnings * ($commission_rate / 100);
    
    // Tambahkan data komisi ke array referral
    $referrals[$key]['earnings_generated'] = $referred_user_earnings;
    $referrals[$key]['commission_earned'] = $commission_earned;
    
    // Akumulasi total komisi
    $total_commission += $commission_earned;
}


require_once __DIR__ . '/templates/header.php';
?>

<h1 class="mt-4 mb-4">Referral Program</h1>

<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Your Unique Referral Link</h5>
                <p class="card-text">Share this link with other publishers. You will earn <?php echo $commission_rate; ?>% commission from all revenue they generate, for life!</p>
                <div class="input-group">
                    <input type="text" id="referralLink" class="form-control" value="<?php echo htmlspecialchars($referral_link); ?>" readonly>
                    <button class="btn btn-outline-secondary" type="button" id="copyBtn">Copy</button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <h6 class="text-muted">Total Referrals</h6>
                <h2 class="fw-bold"><?php echo count($referrals); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card h-100 bg-success text-white">
            <div class="card-body text-center">
                <h6 class="text-white-50">Total Commission Earned</h6>
                <h2 class="fw-bold">$<?php echo number_format($total_commission, 4); ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        Your Referred Users
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Join Date</th>
                        <th>Earnings Generated by Them ($)</th>
                        <th>Your Commission ($)</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(!empty($referrals)): foreach($referrals as $ref): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ref['username']); ?></td>
                        <td><?php echo htmlspecialchars($ref['email']); ?></td>
                        <td><?php echo date("Y-m-d", strtotime($ref['created_at'])); ?></td>
                        <td><?php echo number_format($ref['earnings_generated'], 4); ?></td>
                        <td class="text-success fw-bold"><?php echo number_format($ref['commission_earned'], 4); ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" class="text-center text-muted">You haven't referred anyone yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('copyBtn').addEventListener('click', function() {
    const linkInput = document.getElementById('referralLink');
    linkInput.select();
    document.execCommand('copy');
    this.textContent = 'Copied!';
    setTimeout(() => { this.textContent = 'Copy'; }, 2000);
});
</script>

<?php require_once __DIR__ . '/templates/footer.php'; ?>