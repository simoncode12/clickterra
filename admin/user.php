<?php
// File: /admin/user.php (FINAL & SECURED)

require_once __DIR__ . '/init.php';

// Query untuk mengambil semua pengguna
$sql = "SELECT id, username, email, role, status, revenue_share FROM users ORDER BY username ASC";
$result = $conn->query($sql);
?>

<?php require_once __DIR__ . '/templates/header.php'; ?>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="bi bi-check-circle-fill"></i> <?php echo $_SESSION['success_message']; ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
<?php unset($_SESSION['success_message']); endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="bi bi-exclamation-triangle-fill"></i> <?php echo $_SESSION['error_message']; ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
<?php unset($_SESSION['error_message']); endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mt-4 mb-0">User Management</h1>
    <div><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="bi bi-person-plus-fill"></i> Add New User</button></div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-people-fill me-2"></i>User List</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Rev Share (%)</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo ucfirst($row['role']); ?></span></td>
                            <td><span class="badge bg-<?php echo ($row['status'] == 'active') ? 'success' : 'danger'; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                            <td><?php echo ($row['role'] === 'publisher') ? $row['revenue_share'] : 'N/A'; ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-btn" data-bs-toggle="modal" data-bs-target="#editUserModal" data-id="<?php echo $row['id']; ?>" data-username="<?php echo htmlspecialchars($row['username']); ?>" data-email="<?php echo htmlspecialchars($row['email']); ?>" data-role="<?php echo $row['role']; ?>" data-status="<?php echo $row['status']; ?>" data-revenue_share="<?php echo $row['revenue_share']; ?>" title="Edit User"><i class="bi bi-pencil-fill"></i></button>
                                
                                <?php // PERBAIKAN: Tombol delete tidak akan muncul sama sekali untuk user yang dilindungi ?>
                                <?php if ($row['id'] != $_SESSION['user_id'] && $row['id'] != 1): ?>
                                    <button class="btn btn-sm btn-danger delete-btn" data-bs-toggle="modal" data-bs-target="#deleteUserModal" data-id="<?php echo $row['id']; ?>" data-username="<?php echo htmlspecialchars($row['username']); ?>" title="Delete User"><i class="bi bi-trash-fill"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="7" class="text-center">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="addUserModalLabel">Add New User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="user-action.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Username</label><input type="text" class="form-control" name="username" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required></div>
                    <div class="mb-3"><label class="form-label">Password</label><input type="password" class="form-control" name="password" required></div>
                    <div class="mb-3"><label class="form-label">Role</label><select class="form-select" name="role" id="add-role" required><option value="advertiser">Advertiser</option><option value="publisher">Publisher</option><option value="admin">Admin</option></select></div>
                    <div class="mb-3" id="add-revenue-share-container" style="display:none;"><label class="form-label">Revenue Share (%)</label><input type="number" class="form-control" name="revenue_share" min="0" max="100" value="0"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" name="add_user" class="btn btn-primary">Save User</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Edit User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="user-action.php" method="POST"><div class="modal-body"><input type="hidden" name="id" id="edit-user-id"><div class="mb-3"><label class="form-label">Username</label><input type="text" class="form-control" name="username" id="edit-username" required></div><div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" id="edit-email" required></div><div class="mb-3"><label class="form-label">New Password (optional)</label><input type="password" class="form-control" name="password" placeholder="Leave blank to keep current password"></div><div class="mb-3"><label class="form-label">Role</label><select class="form-select" name="role" id="edit-role" required><option value="advertiser">Advertiser</option><option value="publisher">Publisher</option><option value="admin">Admin</option></select></div><div class="mb-3"><label class="form-label">Status</label><select class="form-select" name="status" id="edit-status" required><option value="active">Active</option><option value="inactive">Inactive</option></select></div><div class="mb-3" id="edit-revenue-share-container" style="display:none;"><label class="form-label">Revenue Share (%)</label><input type="number" class="form-control" name="revenue_share" id="edit-revenue-share" min="0" max="100" value="0"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" name="update_user" class="btn btn-primary">Update User</button></div></form></div></div></div>

<div class="modal fade" id="deleteUserModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Confirm Deletion</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="user-action.php" method="POST"><div class="modal-body"><input type="hidden" name="id" id="delete-user-id"><p>Are you sure you want to delete the user: <strong id="delete-user-username"></strong>?</p><p class="text-danger"><i class="bi bi-exclamation-triangle-fill"></i> This action is permanent and cannot be undone.</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="delete_user" class="btn btn-danger">Delete User</button></div></form></div></div></div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    function toggleRevenueShare(roleSelect, container) {
        if (roleSelect.value === 'publisher') { container.style.display = 'block'; } 
        else { container.style.display = 'none'; }
    }
    const addRoleSelect = document.getElementById('add-role');
    const addRevShareContainer = document.getElementById('add-revenue-share-container');
    addRoleSelect.addEventListener('change', () => toggleRevenueShare(addRoleSelect, addRevShareContainer));

    const editRoleSelect = document.getElementById('edit-role');
    const editRevShareContainer = document.getElementById('edit-revenue-share-container');
    editRoleSelect.addEventListener('change', () => toggleRevenueShare(editRoleSelect, editRevShareContainer));

    const editUserModal = document.getElementById('editUserModal');
    if(editUserModal){
        editUserModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            editUserModal.querySelector('#edit-user-id').value = button.dataset.id;
            editUserModal.querySelector('#edit-username').value = button.dataset.username;
            editUserModal.querySelector('#edit-email').value = button.dataset.email;
            editUserModal.querySelector('#edit-role').value = button.dataset.role;
            editUserModal.querySelector('#edit-status').value = button.dataset.status;
            editUserModal.querySelector('#edit-revenue-share').value = button.dataset.revenue_share;
            toggleRevenueShare(editUserModal.querySelector('#edit-role'), editUserModal.querySelector('#edit-revenue-share-container'));
        });
    }

    const deleteUserModal = document.getElementById('deleteUserModal');
    if(deleteUserModal){
        deleteUserModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            deleteUserModal.querySelector('#delete-user-id').value = button.dataset.id;
            deleteUserModal.querySelector('#delete-user-username').textContent = button.dataset.username;
        });
    }
});
</script>

<?php 
if(isset($result)) { $result->close(); }
require_once __DIR__ . '/templates/footer.php'; 
?>
