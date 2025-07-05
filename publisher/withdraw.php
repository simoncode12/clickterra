<?php
// File: /publisher/withdraw.php (OPTIMIZED)

require_once __DIR__ . '/init.php';

$publisher_id = $_SESSION['publisher_id'] ?? null;

// Jika publisher_id tidak ditemukan, arahkan atau tampilkan pesan error
if (!$publisher_id) {
    die("Akses tidak sah: Publisher ID tidak ditemukan.");
}

// --- OPTIMASI PENTING: Hitung total pendapatan dari stats_daily_summary ---
// Ini jauh lebih cepat daripada campaign_stats jika volume data tinggi.
// Pastikan stats_daily_summary berisi publisher_payout dan diindeks dengan baik.
$total_earnings_q = get_query_results($conn, "
    SELECT SUM(T.publisher_payout) AS total_sum_earnings
    FROM stats_daily_summary AS T
    LEFT JOIN zones z ON T.zone_id = z.id
    LEFT JOIN sites si ON z.site_id = si.id
    WHERE si.user_id = ?
", [$publisher_id], "i");
$total_earnings = $total_earnings_q[0]['total_sum_earnings'] ?? 0;

// Hitung total yang sudah ditarik
// Query ini sudah cukup efisien jika tabel payouts terindeks dengan baik pada user_id dan status
$total_withdrawn_q = get_query_results($conn, "
    SELECT SUM(amount) AS total_sum_withdrawn
    FROM payouts
    WHERE user_id = ? AND status = 'completed'
", [$publisher_id], "i");
$total_withdrawn = $total_withdrawn_q[0]['total_sum_withdrawn'] ?? 0;

$current_balance = $total_earnings - $total_withdrawn;

// Ambil riwayat penarikan
// Batasi jumlah riwayat yang diambil untuk mencegah tampilan yang terlalu banyak
// Anda bisa tambahkan pagination jika riwayat sangat panjang
$history = get_query_results($conn, "
    SELECT requested_at, amount, method, status
    FROM payouts
    WHERE user_id = ?
    ORDER BY requested_at DESC
    LIMIT 200 -- Batasi untuk 200 riwayat terbaru, bisa disesuaikan
", [$publisher_id], "i");


// Ambil info pembayaran user
// Query ini juga sudah cukup efisien
$user_payout_info_q = get_query_results($conn, "
    SELECT payout_method, payout_details
    FROM users
    WHERE id = ?
", [$publisher_id], "i");
$user_payout_info = $user_payout_info_q[0] ?? ['payout_method' => '', 'payout_details' => ''];


require_once __DIR__ . '/templates/header.php';
?>

<h1 class="mt-4 mb-4">Payments & Withdrawals</h1>

<?php if (isset($_SESSION['message'])): ?>
<div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
    <?php echo $_SESSION['message']; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['message'], $_SESSION['message_type']); endif; ?>


<div class="row">
    <div class="col-md-5">
        <div class="card mb-4">
            <div class="card-header">Request New Withdrawal</div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="text-muted">AVAILABLE BALANCE</div>
                    <h2 class="fw-bold text-success">$<?php echo number_format($current_balance, 2); ?></h2>
                </div>
                <hr>
                <?php if (empty($user_payout_info['payout_method']) || empty($user_payout_info['payout_details'])): ?>
                    <div class="alert alert-warning">Please set up your payment details in <a href="account.php">Account Settings</a> before requesting a withdrawal.</div>
                <?php else: ?>
                <form action="withdraw-action.php" method="POST">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount to Withdraw</label>
                        <input type="number" id="amount" name="amount" class="form-control" step="0.01"
                               min="<?php echo number_format(get_setting('min_withdrawal_amount', $conn), 2, '.', ''); ?>"
                               max="<?php echo number_format($current_balance, 2, '.', ''); ?>"
                               placeholder="Enter amount" required>
                        <div class="form-text">Minimum withdrawal is $<?php echo number_format(get_setting('min_withdrawal_amount', $conn), 2); ?>.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment To:</label>
                        <div class="p-2 bg-light rounded">
                            <strong>Method:</strong> <?php echo htmlspecialchars($user_payout_info['payout_method']); ?><br>
                            <strong>Details:</strong> <?php echo nl2br(htmlspecialchars($user_payout_info['payout_details'])); ?>
                        </div>
                        <div class="form-text">To change this, go to <a href="account.php">Account Settings</a>.</div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="request_withdrawal" class="btn btn-primary"
                            <?php if ($current_balance < get_setting('min_withdrawal_amount', $conn)) echo 'disabled'; ?>>
                            Submit Request
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
             <div class="card-header">Withdrawal History</div>
             <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Date</th><th>Amount</th><th>Method</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php if(!empty($history)): foreach($history as $row): ?>
                            <tr>
                                <td><?php echo date("Y-m-d", strtotime($row['requested_at'])); ?></td>
                                <td>$<?php echo number_format($row['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['method']); ?></td>
                                <td><span class="badge bg-<?php echo ['pending'=>'warning text-dark','processing'=>'info','completed'=>'success','rejected'=>'danger'][$row['status']] ?? 'secondary'; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="4" class="text-center text-muted">No withdrawal history.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
             </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>