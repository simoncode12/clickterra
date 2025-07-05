<?php
// File: /admin/supply-partners.php (FINAL - Uses settings for endpoint URL and shows full URL)

require_once __DIR__ . '/init.php';

// Menggunakan domain dari settings untuk membangun base URL
$base_endpoint_url = get_setting('rtb_handler_domain', $conn) . "/rtb-handler.php";

// SQL untuk mengambil semua publisher dan status RTB mereka
$sql = "
    SELECT 
        u.id as user_id, 
        u.username, 
        u.email,
        s.id as source_id,
        s.supply_key,
        s.status,
        s.default_zone_id
    FROM 
        users u
    LEFT JOIN 
        rtb_supply_sources s ON u.id = s.user_id
    WHERE 
        u.role = 'publisher'
    ORDER BY 
        u.username ASC
";
$result = $conn->query($sql);
?>

<?php require_once __DIR__ . '/templates/header.php'; ?>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo $_SESSION['success_message']; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php unset($_SESSION['success_message']); endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo $_SESSION['error_message']; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php unset($_SESSION['error_message']); endif; ?>

<h1 class="mt-4 mb-4">RTB Supply Partners</h1>

<div class="card">
    <div class="card-header"><i class="bi bi-person-lines-fill me-2"></i>Manage Publishers as Supply Sources</div>
    <div class="card-body">
        <div class="table-responsive"><table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Publisher</th>
                    <th>RTB Supply Status</th>
                    <th>Generated Endpoint URL (to give them)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($row['username']); ?></strong><br>
                        <small><?php echo htmlspecialchars($row['email']); ?></small>
                    </td>
                    <td>
                        <?php if ($row['status']): ?>
                            <?php $status_class = ($row['status'] == 'active') ? 'bg-success' : 'bg-warning text-dark'; ?>
                            <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Not Activated</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['supply_key']): ?>
                            <?php
                                // Bangun URL lengkap secara dinamis
                                $generated_url = $base_endpoint_url . '?key=' . $row['supply_key'];
                                // Tambahkan zone_id secara otomatis jika ada
                                if (!empty($row['default_zone_id'])) {
                                    $generated_url .= '&zone_id=' . $row['default_zone_id'];
                                }
                            ?>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars($generated_url); ?>" readonly id="endpoint-<?php echo $row['source_id']; ?>">
                                <button class="btn btn-sm btn-outline-secondary copy-btn" type="button" data-target-id="endpoint-<?php echo $row['source_id']; ?>"><i class="bi bi-clipboard-fill"></i></button>
                            </div>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$row['status']): // Jika belum aktif ?>
                            <form action="supply-partners-action.php" method="POST" class="d-inline">
                                <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                <button type="submit" name="activate_supply_partner" class="btn btn-sm btn-primary"><i class="bi bi-check-circle-fill"></i> Activate for RTB</button>
                            </form>
                        <?php else: // Jika sudah aktif atau paused ?>
                            <div class="btn-group">
                                <form action="supply-partners-action.php" method="POST" class="d-inline">
                                    <input type="hidden" name="source_id" value="<?php echo $row['source_id']; ?>">
                                    <input type="hidden" name="new_status" value="<?php echo ($row['status'] == 'active') ? 'paused' : 'active'; ?>">
                                    <button type="submit" name="update_supply_status" class="btn btn-sm <?php echo ($row['status'] == 'active') ? 'btn-warning' : 'btn-success'; ?>" title="<?php echo ($row['status'] == 'active') ? 'Pause' : 'Resume'; ?>">
                                        <i class="bi <?php echo ($row['status'] == 'active') ? 'bi-pause-fill' : 'bi-play-fill'; ?>"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="4" class="text-center">No publishers found. Please add users with 'publisher' role first.</td></tr>
                <?php endif; ?>
            </tbody>
        </table></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target-id');
            const targetInput = document.getElementById(targetId);
            if (targetInput) {
                navigator.clipboard.writeText(targetInput.value).then(() => {
                    const originalIcon = this.innerHTML;
                    this.innerHTML = '<i class="bi bi-clipboard-check-fill text-success"></i>';
                    setTimeout(() => { this.innerHTML = originalIcon; }, 2000);
                });
            }
        });
    });
});
</script>

<?php $result->close(); require_once __DIR__ . '/templates/footer.php'; ?>