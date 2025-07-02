<?php
// File: /publisher/withdraw.php (NEW)

require_once __DIR__ . '/init.php';

$publisher_id = $_SESSION['publisher_id'];

// Hitung total pendapatan
$total_earnings_q = $conn->query("SELECT SUM(s.cost * u.revenue_share / 100) FROM campaign_stats s JOIN zones z ON s.zone_id = z.id JOIN sites si ON z.site_id = si.id JOIN users u ON si.user_id = u.id WHERE u.id = {$publisher_id}");
$total_earnings = $total_earnings_q->fetch_row()[0] ?? 0;

// Hitung total yang sudah ditarik
$total_withdrawn_q = $conn->query("SELECT SUM(amount) FROM payouts WHERE user_id = {$publisher_id} AND status = 'completed'");
$total_withdrawn = $total_withdrawn_q->fetch_row()[0] ?? 0;

$current_balance = $total_earnings - $total_withdrawn;

// Ambil riwayat penarikan
$history = $conn->query("SELECT * FROM payouts WHERE user_id = {$publisher_id} ORDER BY requested_at DESC");

// Ambil info pembayaran user
$user_payout_info_q = $conn->query("SELECT payout_method, payout_details FROM users WHERE id = {$publisher_id}");
$user_payout_info = $user_payout_info_q->fetch_assoc();


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
                        <input type="number" name="amount" class="form-control" step="0.01" min="<?php echo get_setting('min_withdrawal_amount', $conn); ?>" ... >
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
                        <button type="submit" name="request_withdrawal" class="btn btn-primary" <?php if ($current_balance < 10) echo 'disabled'; ?>>Submit Request</button>
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
                        <?php if($history && $history->num_rows > 0): while($row = $history->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date("Y-m-d", strtotime($row['requested_at'])); ?></td>
                                <td>$<?php echo number_format($row['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['method']); ?></td>
                                <td><span class="badge bg-<?php echo ['pending'=>'warning text-dark','processing'=>'info','completed'=>'success','rejected'=>'danger'][$row['status']]; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                            </tr>
                        <?php endwhile; else: ?>
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