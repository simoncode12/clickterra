<?php
// File: /admin/user.php

// Muat semua konfigurasi inti dan otentikasi
require_once __DIR__ . '/init.php';

// Query untuk mengambil semua pengguna
$sql = "SELECT id, username, email, role, revenue_share, status, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);

// Muat template header
require_once __DIR__ . '/templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mt-4 mb-0">User Management</h1>
    <div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="bi bi-person-plus-fill"></i> Add New User</button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-people-fill me-2"></i>User List
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Revenue Share (%)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <?php 
                                        $role = htmlspecialchars($row['role']);
                                        $role_badge = 'bg-secondary';
                                        if ($role == 'admin') $role_badge = 'bg-danger';
                                        if ($role == 'advertiser') $role_badge = 'bg-primary';
                                        if ($role == 'publisher') $role_badge = 'bg-success';
                                        echo "<span class=\"badge {$role_badge}\">" . ucfirst($role) . "</span>";
                                    ?>
                                </td>
                                <td><?php echo ($row['role'] == 'publisher') ? htmlspecialchars($row['revenue_share']) : 'N/A'; ?></td>
                                <td>
                                    <?php 
                                        $status = htmlspecialchars($row['status']);
                                        $status_badge = $status == 'active' ? 'bg-success' : 'bg-secondary';
                                        echo "<span class=\"badge {$status_badge}\">" . ucfirst($status) . "</span>";
                                    ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" title="Edit User"><i class="bi bi-pencil-fill"></i></button>
                                    <button class="btn btn-sm btn-danger" title="Delete User"><i class="bi bi-trash-fill"></i></button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal untuk Add New User -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="user-action.php" method="POST" id="userForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" name="role" id="roleSelect" onchange="toggleRevenueShare()" required>
                            <option value="advertiser">Advertiser</option>
                            <option value="publisher">Publisher</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3" id="revenueShareGroup" style="display:none;">
                        <label for="revenue_share" class="form-label">Revenue Share (%)</label>
                        <input type="number" class="form-control" name="revenue_share" min="0" max="100" value="0">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_user" class="btn btn-primary">Save User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleRevenueShare() {
    var roleSelect = document.getElementById('roleSelect');
    var revenueShareGroup = document.getElementById('revenueShareGroup');
    if (roleSelect.value === 'publisher') {
        revenueShareGroup.style.display = 'block';
    } else {
        revenueShareGroup.style.display = 'none';
    }
}
// Panggil fungsi saat halaman pertama kali dimuat untuk menyesuaikan dengan nilai default
document.addEventListener('DOMContentLoaded', toggleRevenueShare);
</script>


<?php 
$result->close();
require_once __DIR__ . '/templates/footer.php'; 
?>

