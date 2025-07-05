<?php
// File: /publisher/account.php (NEW)
require_once __DIR__ . '/init.php';

$user_id = $_SESSION['publisher_id'];
$stmt = $conn->prepare("SELECT username, email, payout_method, payout_details FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

require_once __DIR__ . '/templates/header.php';
?>
<h1 class="mt-4 mb-4">Account Settings</h1>

<?php if (isset($_SESSION['message'])): ?>
<div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
    <?php echo $_SESSION['message']; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['message'], $_SESSION['message_type']); endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Profile Information</div>
            <div class="card-body">
                <form action="account-action.php" method="POST">
                    <div class="mb-3"><label class="form-label">Username</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled></div>
                    <div class="mb-3"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required></div>
                    <hr>
                    <p class="text-muted">Change Password (leave blank if you don't want to change it)</p>
                    <div class="mb-3"><label class="form-label">New Password</label><input type="password" name="password" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Confirm New Password</label><input type="password" name="password_confirm" class="form-control"></div>
                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Payment Settings</div>
            <div class="card-body">
                 <form action="account-action.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                       <select name="payout_method" class="form-select">
                             <?php
                                $methods = explode("\n", get_setting('payment_methods', $conn));
                                   foreach ($methods as $method):
                                      $method = trim($method);
                                      if(empty($method)) continue;
                                            ?>
                                  <option value="<?php echo htmlspecialchars($method); ?>" <?php if($user['payout_method'] == $method) echo 'selected'; ?>>
                                  <?php echo htmlspecialchars($method); ?>
                                     </option>
                                         <?php endforeach; ?>
                                      </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Details</label>
                        <textarea name="payout_details" class="form-control" rows="4" placeholder="Contoh PayPal: john.doe@example.com&#10;Contoh Bank:&#10;Nama Bank: BCA&#10;No. Rekening: 1234567890&#10;Atas Nama: John Doe"><?php echo htmlspecialchars($user['payout_details']); ?></textarea>
                    </div>
                     <button type="submit" name="update_payment" class="btn btn-primary">Save Payment Settings</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>