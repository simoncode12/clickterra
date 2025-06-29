<?php
// File: /admin/ssp.php (FIXED AND CORRECTED)

require_once __DIR__ . '/init.php';

$result = $conn->query("SELECT * FROM ssp_partners ORDER BY name ASC");
?>

<?php require_once __DIR__ . '/templates/header.php'; ?>
<?php if (isset($_SESSION['success_message'])): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo $_SESSION['success_message']; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION['success_message']); endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo $_SESSION['error_message']; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION['error_message']); endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mt-4 mb-0">Demand Partners (SSP)</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPartnerModal"><i class="bi bi-plus-circle"></i> Add New Demand Partner</button>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-person-badge-fill me-2"></i>Partner List (You Sell Traffic TO Them)</div>
    <div class="card-body">
        <div class="table-responsive"><table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Partner Name</th>
                    <th>Their Endpoint URL (Your system calls this)</th>
                    <th style="width: 15%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><small><?php echo htmlspecialchars($row['endpoint_url']); ?></small></td>
                    <td>
                        <button class="btn btn-sm btn-info edit-btn" data-bs-toggle="modal" data-bs-target="#editPartnerModal" data-id="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>" data-endpoint="<?php echo htmlspecialchars($row['endpoint_url']); ?>" title="Edit"><i class="bi bi-pencil-fill"></i></button>
                        <button class="btn btn-sm btn-danger delete-btn" data-bs-toggle="modal" data-bs-target="#deletePartnerModal" data-id="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>" title="Delete"><i class="bi bi-trash-fill"></i></button>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="3" class="text-center">No Demand Partners found. Add a partner you want to sell your traffic to.</td></tr>
                <?php endif; ?>
            </tbody>
        </table></div>
    </div>
</div>

<div class="modal fade" id="addPartnerModal" tabindex="-1" aria-labelledby="addPartnerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="addPartnerModalLabel">Add New Demand Partner</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="ssp-action.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Partner Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Their Endpoint URL</label>
                        <input type="url" class="form-control" name="endpoint_url" placeholder="http://rtb.partner.com/bid-request" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_partner" class="btn btn-primary">Add Partner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editPartnerModal" tabindex="-1" aria-labelledby="editPartnerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="editPartnerModalLabel">Edit Demand Partner</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="ssp-action.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3"><label class="form-label">Partner Name</label><input type="text" class="form-control" name="name" id="edit-name" required></div>
                    <div class="mb-3"><label class="form-label">Their Endpoint URL</label><input type="url" class="form-control" name="endpoint_url" id="edit-endpoint" required></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_partner" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deletePartnerModal" tabindex="-1" aria-labelledby="deletePartnerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="deletePartnerModalLabel">Confirm Deletion</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="ssp-action.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="delete-id">
                    <p>Are you sure you want to delete partner: <strong id="delete-name"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_partner" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editPartnerModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', e => {
            const btn = e.relatedTarget;
            editModal.querySelector('#edit-id').value = btn.dataset.id;
            editModal.querySelector('#edit-name').value = btn.dataset.name;
            editModal.querySelector('#edit-endpoint').value = btn.dataset.endpoint;
        });
    }

    const deleteModal = document.getElementById('deletePartnerModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', e => {
            const btn = e.relatedTarget;
            deleteModal.querySelector('#delete-id').value = btn.dataset.id;
            deleteModal.querySelector('#delete-name').textContent = btn.dataset.name;
        });
    }
});
</script>

<?php $result->close(); require_once __DIR__ . '/templates/footer.php'; ?>
