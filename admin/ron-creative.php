<?php
// File: /admin/ron-creative.php (FINAL - Restored "Add New" form and includes Bulk Actions)

require_once __DIR__ . '/init.php';

// 1. Ambil & Validasi Campaign ID
$campaign_id = filter_input(INPUT_GET, 'campaign_id', FILTER_VALIDATE_INT);
if (!$campaign_id) {
    $_SESSION['error_message'] = "Invalid campaign ID.";
    header('Location: campaigns.php');
    exit();
}

// 2. Ambil Detail Kampanye
$stmt_campaign = $conn->prepare("SELECT name FROM campaigns WHERE id = ?");
$stmt_campaign->bind_param("i", $campaign_id);
$stmt_campaign->execute();
$campaign = $stmt_campaign->get_result()->fetch_assoc();
if (!$campaign) {
    $_SESSION['error_message'] = "Campaign not found.";
    header('Location: campaigns.php');
    exit();
}
$campaign_name = $campaign['name'];
$stmt_campaign->close();

// 3. Ambil Daftar Creatives yang Sudah Ada
$creatives_sql = "SELECT id, name, creative_type, bid_model, bid_amount, sizes, status FROM creatives WHERE campaign_id = ? ORDER BY created_at DESC";
$stmt_creatives = $conn->prepare($creatives_sql);
$stmt_creatives->bind_param("i", $campaign_id);
$stmt_creatives->execute();
$creatives_result = $stmt_creatives->get_result();

// Data untuk form
$ad_sizes = [
    '300x250' => '300x250 - Medium Rectangle', '728x90' => '728x90 - Leaderboard',
    '160x600' => '160x600 - Wide Skyscraper', '320x50' => '320x50 - Mobile Leaderboard',
    '300x600' => '300x600 - Half Page', '970x250' => '970x250 - Billboard',
    'all' => 'All Sizes (for Script)'
];

require_once __DIR__ . '/templates/header.php';
?>
<?php if (isset($_SESSION['success_message'])): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($_SESSION['success_message']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION['success_message']); endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($_SESSION['error_message']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION['error_message']); endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mt-4 mb-0">Manage Creatives for "<?php echo htmlspecialchars($campaign_name); ?>"</h1>
    <a href="campaigns.php" class="btn btn-secondary">Back to Campaigns</a>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-images me-2"></i>Existing Creatives</div>
    <div class="card-body">
        <form action="ron-creative-action.php" method="POST" id="bulk-action-form">
            <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead><tr><th style="width: 5%;"><input class="form-check-input" type="checkbox" id="select-all-checkbox"></th><th>Name</th><th>Type</th><th>Bid</th><th>Size</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php if ($creatives_result->num_rows > 0): ?>
                            <?php while($creative = $creatives_result->fetch_assoc()): ?>
                                <tr>
                                    <td><input class="form-check-input creative-checkbox" type="checkbox" name="creative_ids[]" value="<?php echo $creative['id']; ?>"></td>
                                    <td><?php echo htmlspecialchars($creative['name']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo ucfirst($creative['creative_type']); ?></span></td>
                                    <td>$<?php echo number_format($creative['bid_amount'], 4); ?> <?php echo strtoupper($creative['bid_model']); ?></td>
                                    <td><span class="badge bg-info"><?php echo $creative['sizes']; ?></span></td>
                                    <td><span class="badge bg-<?php echo ($creative['status'] == 'active') ? 'success' : 'warning text-dark'; ?>"><?php echo ucfirst($creative['status']); ?></span></td>
                                    <td><a href="ron-creative-edit.php?id=<?php echo $creative['id']; ?>" class="btn btn-sm btn-info" title="Edit"><i class="bi bi-pencil-fill"></i></a></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">No creatives found for this campaign yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex align-items-center mt-3">
                <div class="me-3"><strong>For selected:</strong></div>
                <div class="col-auto"><select name="bulk_action" class="form-select form-select-sm"><option value="">Choose action...</option><option value="activate">Activate</option><option value="pause">Pause</option><option value="delete">Delete</option></select></div>
                <div class="col-auto ms-2"><button type="submit" name="apply_bulk_action" class="btn btn-sm btn-primary">Apply</button></div>
            </div>
        </form>
        <hr>
        <form action="ron-creative-action.php" method="POST" class="row g-3 align-items-end" id="bulk-bid-form">
            <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
            <div class="col-md-4"><label class="form-label">Bulk Update Bid for Selected</label><input type="number" step="0.0001" name="new_bid_amount" class="form-control" placeholder="Enter new bid amount" required></div>
            <div class="col-auto"><button type="submit" name="update_bulk_bids" class="btn btn-warning">Update Bids</button></div>
            <div id="hidden-inputs-for-bids"></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-plus-circle-fill me-2"></i>Create New Creative</div>
    <div class="card-body">
        <form action="ron-creative-action.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
            <div class="row"><div class="col-md-12 mb-3"><label class="form-label">Creative Name</label><input type="text" class="form-control" name="name" placeholder="e.g., Summer Sale Banner" required></div></div>
            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label">Bid Model</label><select class="form-select" name="bid_model" required><option value="cpc">CPC</option><option value="cpm">CPM</option></select></div>
                <div class="col-md-6 mb-3"><label class="form-label">Bid Amount ($)</label><input type="number" step="0.0001" class="form-control" name="bid_amount" placeholder="e.g., 0.05" required></div>
            </div>
            <hr class="my-4">
            <div class="mb-3">
                <label class="form-label">Creative Type</label>
                <div class="form-check"><input class="form-check-input creative-type-trigger" type="radio" name="creative_type" id="type_image" value="image" checked><label class="form-check-label" for="type_image">Image</label></div>
                <div class="form-check"><input class="form-check-input creative-type-trigger" type="radio" name="creative_type" id="type_script" value="script"><label class="form-check-label" for="type_script">HTML5 / Script Tag</label></div>
            </div>
            <fieldset id="image-fields-container">
                <div class="mb-3"><label class="form-label">Upload File (JPG, GIF, PNG)</label><input class="form-control" type="file" name="creative_file"></div>
                <div class="mb-3"><label class="form-label">Or Hotlink URL</label><input type="url" class="form-control" name="image_url" placeholder="https://example.com/banner.jpg"></div>
                <div class="mb-3"><label class="form-label">Ad Size</label><select class="form-select" name="sizes"><?php foreach($ad_sizes as $value => $label): ?><option value="<?php echo $value; ?>"><?php echo $label; ?></option><?php endforeach; ?></select></div>
                <div class="mb-3"><label class="form-label">Landing Page URL</label><input type="url" class="form-control" name="landing_url" placeholder="https://your-landing-page.com"></div>
            </fieldset>
            <fieldset id="script-fields-container" style="display:none;">
                <div class="mb-3"><label class="form-label">HTML / Script Content</label><textarea class="form-control" name="script_content" rows="8" placeholder="Paste your ad tag here..."></textarea></div>
            </fieldset>
            <button type="submit" name="add_creative" class="btn btn-primary mt-3">Create Creative</button>
        </form>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    // Logika untuk checkbox "Select All"
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    if(selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            document.querySelectorAll('.creative-checkbox').forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // Logika untuk sinkronisasi checkbox ke form bulk bid
    const bulkBidForm = document.getElementById('bulk-bid-form');
    if(bulkBidForm) {
        bulkBidForm.addEventListener('submit', function() {
            const hiddenInputsContainer = document.getElementById('hidden-inputs-for-bids');
            hiddenInputsContainer.innerHTML = ''; // Kosongkan dulu
            document.querySelectorAll('.creative-checkbox:checked').forEach(checkbox => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'creative_ids[]';
                hiddenInput.value = checkbox.value;
                hiddenInputsContainer.appendChild(hiddenInput);
            });
        });
    }

    // Logika untuk menampilkan/menyembunyikan field di form "Add New"
    const imageFieldsContainer = document.getElementById('image-fields-container');
    const scriptFieldsContainer = document.getElementById('script-fields-container');
    const landingUrlInput = imageFieldsContainer.querySelector('input[name="landing_url"]');
    const scriptContentInput = scriptFieldsContainer.querySelector('textarea[name="script_content"]');

    function handleCreativeTypeChange(selectedValue) {
        if (selectedValue === 'image') {
            imageFieldsContainer.style.display = 'block';
            scriptFieldsContainer.style.display = 'none';
            landingUrlInput.required = true;
            if(scriptContentInput) scriptContentInput.required = false;
        } else { // script
            imageFieldsContainer.style.display = 'none';
            scriptFieldsContainer.style.display = 'block';
            landingUrlInput.required = false;
            if(scriptContentInput) scriptContentInput.required = true;
        }
    }
    
    document.querySelectorAll('.creative-type-trigger').forEach(radio => {
        radio.addEventListener('change', function() { handleCreativeTypeChange(this.value); });
    });
    // Panggil saat load untuk state awal
    const initialType = document.querySelector('.creative-type-trigger:checked');
    if (initialType) {
        handleCreativeTypeChange(initialType.value);
    }
});
</script>

<?php 
if (isset($stmt_creatives)) { $stmt_creatives->close(); }
require_once __DIR__ . '/templates/footer.php'; 
?>
