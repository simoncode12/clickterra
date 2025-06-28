<?php
// File: /admin/rtb-campaigns-report.php

// Muat semua konfigurasi inti dan otentikasi
require_once __DIR__ . '/init.php';

// Query untuk mengambil data kampanye RTB beserta nama advertiser dan kategori
$sql = "SELECT 
            c.id, 
            c.name AS campaign_name, 
            c.status, 
            c.created_at,
            u.username AS advertiser_name,
            cat.name AS category_name
        FROM 
            campaigns AS c
        JOIN 
            users AS u ON c.advertiser_id = u.id
        JOIN 
            categories AS cat ON c.category_id = cat.id
        WHERE 
            c.campaign_type = 'rtb' -- Perbedaan utama ada di sini
        ORDER BY 
            c.created_at DESC";

$result = $conn->query($sql);

// Muat template header
require_once __DIR__ . '/templates/header.php';
?>

<h1 class="mt-4">RTB Campaigns Report</h1>
<p>Daftar semua kampanye Real-Time Bidding yang telah dibuat.</p>

<div class="card">
    <div class="card-header">
        <i class="bi bi-broadcast me-2"></i>RTB Campaign List
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Campaign Name</th>
                        <th>Advertiser</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['campaign_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['advertiser_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                <td>
                                    <?php 
                                        $status = htmlspecialchars($row['status']);
                                        $badge_class = 'bg-secondary';
                                        if ($status == 'active') {
                                            $badge_class = 'bg-success';
                                        } elseif ($status == 'paused') {
                                            $badge_class = 'bg-warning text-dark';
                                        }
                                        echo "<span class=\"badge {$badge_class}\">" . ucfirst($status) . "</span>";
                                    ?>
                                </td>
                                <td><?php echo date('d M Y, H:i', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <a href="rtb-creative.php?campaign_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info" title="View Creatives">
                                        <i class="bi bi-image"></i>
                                    </a>
                                    <button class="btn btn-sm btn-warning" title="Edit Campaign">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                     <button class="btn btn-sm btn-danger" title="Delete Campaign">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No RTB campaigns found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<?php 
$result->close();
require_once __DIR__ . '/templates/footer.php'; 
?>

