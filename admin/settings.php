<?php
// File: /admin/settings.php (NEW)
require_once __DIR__ . '/init.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();
    try {
        // Update text-based settings
        $settings_to_update = [
            'ad_server_domain',
            'rtb_handler_domain',
            'min_withdrawal_amount',
            'payment_methods'
        ];
        $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        foreach ($settings_to_update as $key) {
            if (isset($_POST[$key])) {
                $value = trim($_POST[$key]);
                $stmt->bind_param("ss", $value, $key);
                $stmt->execute();
            }
        }
        $stmt->close();
        
        // Handle logo upload
        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] == 0) {
            $target_dir = __DIR__ . "/assets/img/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
            $filename = "logo." . pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['site_logo']['tmp_name'], $target_dir . $filename);
            $conn->query("UPDATE settings SET setting_value = 'assets/img/{$filename}' WHERE setting_key = 'site_logo'");
        }

        // Handle favicon upload
        if (isset($_FILES['site_favicon']) && $_FILES['site_favicon']['error'] == 0) {
             $target_dir = __DIR__ . "/assets/img/";
             $filename = "favicon." . pathinfo($_FILES['site_favicon']['name'], PATHINFO_EXTENSION);
             move_uploaded_file($_FILES['site_favicon']['tmp_name'], $target_dir . $filename);
             $conn->query("UPDATE settings SET setting_value = 'assets/img/{$filename}' WHERE setting_key = 'site_favicon'");
        }

        $conn->commit();
        $_SESSION['success_message'] = "Settings updated successfully.";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error updating settings: " . $e->getMessage();
    }
    header("Location: settings.php");
    exit();
}

require_once __DIR__ . '/templates/header.php';
?>

<h1 class="mt-4 mb-4">Platform Settings</h1>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<form method="POST" action="settings.php" enctype="multipart/form-data">
    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">Site Branding</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Site Logo</label><br>
                        <img src="<?php echo htmlspecialchars(get_setting('site_logo', $conn)); ?>" alt="Current Logo" style="max-height: 50px; background: #f0f0f0; padding: 5px; margin-bottom: 10px;">
                        <input type="file" name="site_logo" class="form-control">
                        <div class="form-text">Recommended size: 150x40 pixels.</div>
                    </div>
                     <div class="mb-3">
                        <label class="form-label">Site Favicon</label><br>
                         <img src="<?php echo htmlspecialchars(get_setting('site_favicon', $conn)); ?>" alt="Current Favicon" style="max-height: 32px; margin-bottom: 10px;">
                        <input type="file" name="site_favicon" class="form-control">
                        <div class="form-text">Must be a .ico, .png or .gif file.</div>
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">Publisher Settings</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Minimum Withdrawal Amount ($)</label>
                        <input type="number" step="0.01" name="min_withdrawal_amount" class="form-control" value="<?php echo htmlspecialchars(get_setting('min_withdrawal_amount', $conn)); ?>">
                    </div>
                     <div class="mb-3">
                        <label class="form-label">Available Payment Methods</label>
                        <textarea class="form-control" name="payment_methods" rows="4" placeholder="Satu metode per baris, contoh:&#10;PayPal&#10;Bank Transfer"><?php echo htmlspecialchars(get_setting('payment_methods', $conn)); ?></textarea>
                        <div class="form-text">One method per line. This will be shown as options to the publisher.</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">Ad Serving Domains</div>
                <div class="card-body">
                    <p class="text-muted">Set domain for Ad Tags and RTB Endpoints. Do not include a trailing slash.</p>
                    <div class="mb-3">
                        <label class="form-label">Ad Tag Domain (for RON)</label>
                        <input type="url" name="ad_server_domain" class="form-control" placeholder="https://ad.yourdomain.com" value="<?php echo htmlspecialchars(get_setting('ad_server_domain', $conn)); ?>">
                        <div class="form-text">Example: `&lt;script src="`<strong><?php echo htmlspecialchars(get_setting('ad_server_domain', $conn)); ?></strong>`/ad.php?zone_id=1"&gt;`</div>
                    </div>
                     <div class="mb-3">
                        <label class="form-label">RTB Handler Domain</label>
                        <input type="url" name="rtb_handler_domain" class="form-control" placeholder="https://rtb.yourdomain.com" value="<?php echo htmlspecialchars(get_setting('rtb_handler_domain', $conn)); ?>">
                         <div class="form-text">Example: `<strong><?php echo htmlspecialchars(get_setting('rtb_handler_domain', $conn)); ?></strong>/rtb-handler.php?key=...`</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-lg">Save All Settings</button>
</form>

<?php require_once __DIR__ . '/templates/footer.php'; ?>