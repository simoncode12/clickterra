<?php
// File: /admin/ron-creative.php

// Muat semua konfigurasi inti dan otentikasi
require_once __DIR__ . '/init.php';

// Validasi campaign_id dari URL
$campaign_id = filter_input(INPUT_GET, 'campaign_id', FILTER_VALIDATE_INT);
if (!$campaign_id) {
    // Jika tidak ada campaign_id yang valid, kembali ke laporan
    header('Location: ron-campaigns-report.php');
    exit();
}

// Ambil detail kampanye untuk ditampilkan di header halaman
$stmt_campaign = $conn->prepare("SELECT name FROM campaigns WHERE id = ? AND campaign_type = 'ron'");
$stmt_campaign->bind_param("i", $campaign_id);
$stmt_campaign->execute();
$campaign_result = $stmt_campaign->get_result();
$campaign = $campaign_result->fetch_assoc();

if (!$campaign) {
    // Jika kampanye tidak ditemukan, kembali ke laporan
    $_SESSION['error_message'] = "Campaign not found.";
    header('Location: ron-campaigns-report.php');
    exit();
}
$campaign_name = $campaign['name'];
$stmt_campaign->close();

// Ambil semua creative yang terhubung dengan campaign ini
$stmt_creatives = $conn->prepare("SELECT * FROM creatives WHERE campaign_id = ? ORDER BY created_at DESC");
$stmt_creatives->bind_param("i", $campaign_id);
$stmt_creatives->execute();
$creatives_result = $stmt_creatives->get_result();


// Muat template header
require_once __DIR__ . '/templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mt-4 mb-0">Manage Creatives</h1>
        <p class="text-muted">For Campaign: <strong><?php echo htmlspecialchars($campaign_name); ?></strong></p>
    </div>
    <div>
        <a href="ron-campaigns-report.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Report</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCreativeModal"><i class="bi bi-plus-circle"></i> Add New Creative</button>
    </div>
</div>


<div class="card">
    <div class="card-header">
        <i class="bi bi-image me-2"></i>Creative List
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Preview</th>
                        <th>Name</th>
                        <th>Bid</th>
                        <th>Size</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($creatives_result && $creatives_result->num_rows > 0): ?>
                        <?php while($row = $creatives_result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center">
                                    <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="Creative Preview" style="max-width: 100px; max-height: 50px; border-radius: 4px;">
                                </td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td>$<?php echo number_format($row['bid_amount'], 4); ?> <?php echo strtoupper($row['bid_model']); ?></td>
                                <td>
                                    <span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['sizes']); ?></span>
                                </td>
                                <td>
                                    <?php 
                                        $status = htmlspecialchars($row['status']);
                                        $badge_class = $status == 'active' ? 'bg-success' : 'bg-warning text-dark';
                                        echo "<span class=\"badge {$badge_class}\">" . ucfirst($status) . "</span>";
                                    ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-secondary" title="Edit Creative"><i class="bi bi-pencil-fill"></i></button>
                                    <button class="btn btn-sm btn-dark" title="Pause/Activate Creative"><i class="bi bi-pause-fill"></i></button>
                                    <button class="btn btn-sm btn-danger" title="Delete Creative"><i class="bi bi-trash-fill"></i></button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No creatives found for this campaign.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal untuk Add New Creative -->
<div class="modal fade" id="addCreativeModal" tabindex="-1" aria-labelledby="addCreativeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCreativeModalLabel">Add New Creative</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Form akan diproses oleh file terpisah atau di halaman ini sendiri -->
                <form action="ron-creative-action.php" method="POST">
                    <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
                    <div class="mb-3">
                        <label for="creative_name" class="form-label">Creative Name</label>
                        <input type="text" class="form-control" name="creative_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="bid_model" class="form-label">Bid Model</label>
                        <select class="form-select" name="bid_model" required>
                            <option value="cpc">CPC</option>
                            <option value="cpm">CPM</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="bid_amount" class="form-label">Bid Amount ($)</label>
                        <input type="number" step="0.0001" class="form-control" name="bid_amount" required>
                    </div>
                    <div class="mb-3">
                        <label for="creative_size" class="form-label">Banner Size</label>
                        <select class="form-select" name="creative_size" required>
                            <option value="">Choose size...</option>
                            <?php 
                            $banner_sizes = ['300x250', '300x100', '300x50', '300x500', '900x250', '728x90', '160x600'];
                            foreach ($banner_sizes as $size): ?>
                                <option value="<?php echo $size; ?>"><?php echo $size; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="image_url" class="form-label">Image URL</label>
                        <input type="url" class="form-control" name="image_url" placeholder="https://example.com/banner.jpg" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Creative</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<?php 
$stmt_creatives->close();
require_once __DIR__ . '/templates/footer.php'; 
?>

