<?php
// File: /admin/campaigns.php (FINAL VERSION)

require_once __DIR__ . '/init.php';

$sql = "
    SELECT 
        c.id, c.name, c.status, 
        c.serve_on_internal, c.allow_external_rtb,
        u.username AS advertiser_name,
        af.name AS ad_format_name
    FROM campaigns c
    JOIN users u ON c.advertiser_id = u.id
    LEFT JOIN ad_formats af ON c.ad_format_id = af.id
    ORDER BY c.created_at DESC";
$result = $conn->query($sql);
?>

<?php require_once __DIR__ . '/templates/header.php'; ?>
<?php if (isset($_SESSION['success_message'])): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><i class="bi bi-check-circle-fill me-2"></i><?php echo $_SESSION['success_message']; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION['success_message']); endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $_SESSION['error_message']; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION['error_message']); endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mt-4 mb-0">Campaign Management</h1>
    <div>
        <a href="campaigns-create.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Create New Campaign</a>
    </div>
</div>
<div class="card">
    <div class="card-header"><i class="bi bi-table me-2"></i>All Campaigns</div>
    <div class="card-body">
        <div class="table-responsive"><table class="table table-striped table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Campaign Name</th>
                    <th>Advertiser</th>
                    <th>Format</th>
                    <th>Serving Channels</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['advertiser_name']); ?></td>
                        <td><span class="badge bg-dark"><?php echo htmlspecialchars($row['ad_format_name']); ?></span></td>
                        <td>
                            <?php if($row['serve_on_internal']): ?><span class="badge bg-success">Internal</span><?php endif; ?>
                            <?php if($row['allow_external_rtb']): ?><span class="badge bg-info text-dark">External RTB</span><?php endif; ?>
                        </td>
                        <td>
                            <?php $status_class = ($row['status'] == 'active') ? 'bg-success' : 'bg-warning text-dark'; ?>
                            <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="ron-creative.php?campaign_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" title="Manage Creatives"><i class="bi bi-images"></i></a>
                                <a href="campaigns-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info" title="Edit Campaign"><i class="bi bi-pencil-fill"></i></a>
                                <button class="btn btn-sm <?php echo ($row['status'] == 'active') ? 'btn-warning' : 'btn-success'; ?> status-btn" data-bs-toggle="modal" data-bs-target="#statusModal" data-id="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>" data-status="<?php echo $row['status']; ?>" title="<?php echo ($row['status'] == 'active') ? 'Pause' : 'Activate'; ?>"><i class="bi <?php echo ($row['status'] == 'active') ? 'bi-pause-fill' : 'bi-play-fill'; ?>"></i></button>
                                <button class="btn btn-sm btn-danger delete-btn" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>" title="Delete Campaign"><i class="bi bi-trash-fill"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; else: ?><tr><td colspan="6" class="text-center">No campaigns found.</td></tr><?php endif; ?>
            </tbody>
        </table></div>
    </div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Confirm Status Change</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="campaigns-action.php" method="POST"><div class="modal-body"><input type="hidden" name="id" id="status-campaign-id"><input type="hidden" name="current_status" id="status-campaign-current-status"><p>Are you sure you want to <strong id="status-action-text"></strong> the campaign: <strong id="status-campaign-name"></strong>?</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="update_campaign_status" class="btn btn-primary" id="status-confirm-btn">Confirm</button></div></form></div></div></div>
<div class="modal fade" id="deleteModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Confirm Deletion</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="campaigns-action.php" method="POST"><div class="modal-body"><input type="hidden" name="id" id="delete-campaign-id"><p>Are you sure you want to delete the campaign: <strong id="delete-campaign-name"></strong>?</p><p class="text-danger"><i class="bi bi-exclamation-triangle-fill"></i> This will also delete all associated creatives and targeting settings.</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="delete_campaign" class="btn btn-danger">Delete</button></div></form></div></div></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const statusModal = document.getElementById('statusModal');
    if(statusModal) {
        statusModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            statusModal.querySelector('#status-campaign-id').value = button.dataset.id;
            statusModal.querySelector('#status-campaign-name').textContent = button.dataset.name;
            const status = button.dataset.status;
            const actionText = status === 'active' ? 'pause' : 'activate';
            statusModal.querySelector('#status-action-text').textContent = actionText;
            statusModal.querySelector('#status-campaign-current-status').value = status;
            const confirmBtn = statusModal.querySelector('#status-confirm-btn');
            confirmBtn.className = 'btn ' + (status === 'active' ? 'btn-warning' : 'btn-success');
            confirmBtn.textContent = 'Yes, ' + actionText.charAt(0).toUpperCase() + actionText.slice(1);
        });
    }
    const deleteModal = document.getElementById('deleteModal');
    if(deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            deleteModal.querySelector('#delete-campaign-id').value = button.dataset.id;
            deleteModal.querySelector('#delete-campaign-name').textContent = button.dataset.name;
        });
    }
});
</script>

<?php $result->close(); require_once __DIR__ . '/templates/footer.php'; ?>