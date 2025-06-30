<?php
// File: /admin/ron-creative.php (FULL CODE - FINAL VERSION)

require_once __DIR__ . '/init.php';

// 1. Ambil dan validasi ID Kampanye dari URL
$campaign_id = filter_input(INPUT_GET, 'campaign_id', FILTER_VALIDATE_INT);
if (!$campaign_id) {
    $_SESSION['error_message'] = "Invalid or missing campaign ID.";
    header('Location: campaigns.php');
    exit();
}

// 2. Ambil detail kampanye untuk judul
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

// 3. Ambil daftar materi iklan (creatives) yang sudah ada untuk kampanye ini
$creatives_sql = "SELECT id, name, creative_type, bid_model, bid_amount, sizes, status, image_url, landing_url, script_content FROM creatives WHERE campaign_id = ? ORDER BY created_at DESC";
$stmt_creatives = $conn->prepare($creatives_sql);
$stmt_creatives->bind_param("i", $campaign_id);
$stmt_creatives->execute();
$creatives_result = $stmt_creatives->get_result();


// Data untuk form
$ad_sizes = [
    '300x250' => '300x250 - Medium Rectangle',
    '728x90' => '728x90 - Leaderboard',
    '160x600' => '160x600 - Wide Skyscraper',
    '320x50' => '320x50 - Mobile Leaderboard',
    '300x600' => '300x600 - Half Page',
    '970x250' => '970x250 - Billboard'
];
?>

<?php require_once __DIR__ . '/templates/header.php'; ?>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($_SESSION['success_message']); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
<?php unset($_SESSION['success_message']); endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($_SESSION['error_message']); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
<?php unset($_SESSION['error_message']); endif; ?>


<h1 class="mt-4 mb-4">Manage Creatives for "<?php echo htmlspecialchars($campaign_name); ?>"</h1>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-images me-2"></i>Existing Creatives</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Bid</th>
                        <th>Size</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($creatives_result->num_rows > 0): ?>
                        <?php while($creative = $creatives_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($creative['name']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo ucfirst($creative['creative_type']); ?></span></td>
                                <td>$<?php echo number_format($creative['bid_amount'], 4); ?> <?php echo strtoupper($creative['bid_model']); ?></td>
                                <td><span class="badge bg-info"><?php echo $creative['sizes']; ?></span></td>
                                <td>
                                    <?php $status_class = ($creative['status'] == 'active') ? 'bg-success' : 'bg-warning text-dark'; ?>
                                    <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($creative['status']); ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-success preview-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#previewModal"
                                            data-creative-name="<?php echo htmlspecialchars($creative['name']); ?>"
                                            data-creative-type="<?php echo $creative['creative_type']; ?>"
                                            data-image-url="<?php echo htmlspecialchars($creative['image_url']); ?>"
                                            data-landing-url="<?php echo htmlspecialchars($creative['landing_url']); ?>"
                                            data-script-content="<?php echo htmlspecialchars($creative['script_content']); ?>"
                                            data-size="<?php echo htmlspecialchars($creative['sizes']); ?>"
                                            title="Preview Creative">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                    <a href="ron-creative-edit.php?id=<?php echo $creative['id']; ?>" class="btn btn-sm btn-info" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                                    <button class="btn btn-sm <?php echo ($creative['status'] == 'active') ? 'btn-warning' : 'btn-success'; ?> status-btn" data-bs-toggle="modal" data-bs-target="#statusCreativeModal" data-id="<?php echo $creative['id']; ?>" data-name="<?php echo htmlspecialchars($creative['name']); ?>" data-status="<?php echo $creative['status']; ?>" title="<?php echo ($creative['status'] == 'active') ? 'Pause' : 'Activate'; ?>"><i class="bi <?php echo ($creative['status'] == 'active') ? 'bi-pause-fill' : 'bi-play-fill'; ?>"></i></button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-bs-toggle="modal" data-bs-target="#deleteCreativeModal" data-id="<?php echo $creative['id']; ?>" data-name="<?php echo htmlspecialchars($creative['name']); ?>" title="Delete"><i class="bi bi-trash-fill"></i></button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No creatives found for this campaign yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="card">
    <div class="card-header"><i class="bi bi-plus-circle-fill me-2"></i>Create New Creative</div>
    <div class="card-body">
        <form action="ron-creative-action.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Creative Name</label>
                    <input type="text" class="form-control" name="name" placeholder="e.g., Summer Sale Banner" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bid Model</label>
                    <select class="form-select" name="bid_model" required><option value="cpc">CPC</option><option value="cpm">CPM</option></select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bid Amount ($)</label>
                    <input type="number" step="0.0001" class="form-control" name="bid_amount" placeholder="e.g., 0.05" required>
                </div>
            </div>

            <hr class="my-4">

            <div class="mb-3">
                <label class="form-label">Creative Type</label>
                <div class="form-check"><input class="form-check-input creative-type-trigger" type="radio" name="creative_type" id="type_image" value="image" checked><label class="form-check-label" for="type_image">Image / Video</label></div>
                <div class="form-check"><input class="form-check-input creative-type-trigger" type="radio" name="creative_type" id="type_script" value="script"><label class="form-check-label" for="type_script">HTML5 / Script Tag</label></div>
            </div>

            <fieldset id="image-fields-container">
                <div class="mb-3"><label class="form-label">Upload File (JPG, GIF, PNG, MP4)</label><input class="form-control" type="file" name="creative_file"></div>
                <div class="mb-3"><label class="form-label">Or Hotlink URL</label><input type="url" class="form-control" name="image_url" placeholder="https://example.com/banner.jpg"></div>
                <div class="mb-3"><label class="form-label">Ad Size</label><select class="form-select" name="sizes"><?php foreach($ad_sizes as $value => $label): ?><option value="<?php echo $value; ?>"><?php echo $label; ?></option><?php endforeach; ?></select></div>
                <div class="mb-3"><label class="form-label">Landing Page URL (Required for Image)</label><input type="url" class="form-control" name="landing_url" placeholder="https://your-landing-page.com" required></div>
            </fieldset>
            
            <fieldset id="script-fields-container" style="display:none;">
                <div class="mb-3"><label class="form-label">HTML / Script Content</label><textarea class="form-control" name="script_content" rows="8" placeholder="Paste your ad tag here..."></textarea></div>
            </fieldset>

            <button type="submit" name="add_creative" class="btn btn-primary">Create Creative</button>
            <a href="campaigns.php" class="btn btn-secondary">Back to Campaigns</a>
        </form>
    </div>
</div>

<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="previewModalLabel">Creative Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="preview-content" class="text-center bg-light p-3" style="min-height: 250px; display: flex; justify-content: center; align-items: center;">
          </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="statusCreativeModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Confirm Status Change</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form action="ron-creative-action.php" method="POST">
            <div class="modal-body">
                <input type="hidden" name="creative_id" id="status-creative-id">
                <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
                <input type="hidden" name="current_status" id="status-creative-current-status">
                <p>Are you sure you want to <strong id="status-creative-action-text"></strong> the creative: <strong id="status-creative-name"></strong>?</p>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="update_creative_status" class="btn btn-primary" id="status-creative-confirm-btn">Confirm</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="deleteCreativeModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Confirm Deletion</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form action="ron-creative-action.php" method="POST">
            <div class="modal-body">
                <input type="hidden" name="creative_id" id="delete-creative-id">
                <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
                <p>Are you sure you want to delete this creative: <strong id="delete-creative-name"></strong>?</p>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="delete_creative" class="btn btn-danger">Delete</button></div>
        </form>
    </div></div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    // Logika untuk form Add Creative (menampilkan/menyembunyikan field)
    const imageFieldsContainer = document.getElementById('image-fields-container');
    const scriptFieldsContainer = document.getElementById('script-fields-container');
    const landingUrlInput = imageFieldsContainer.querySelector('input[name="landing_url"]');
    const scriptContentInput = scriptFieldsContainer.querySelector('textarea[name="script_content"]');

    function handleCreativeTypeChange(selectedValue) {
        if (selectedValue === 'image') {
            imageFieldsContainer.style.display = 'block';
            scriptFieldsContainer.style.display = 'none';
            landingUrlInput.required = true;
            scriptContentInput.required = false;
        } else { // script
            imageFieldsContainer.style.display = 'none';
            scriptFieldsContainer.style.display = 'block';
            landingUrlInput.required = false;
            scriptContentInput.required = true;
        }
    }
    
    document.querySelectorAll('.creative-type-trigger').forEach(radio => {
        radio.addEventListener('change', function() { handleCreativeTypeChange(this.value); });
    });
    handleCreativeTypeChange(document.querySelector('.creative-type-trigger:checked').value);


    // Logika untuk Modal Preview
    const previewModal = document.getElementById('previewModal');
    previewModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const creativeName = button.getAttribute('data-creative-name');
        const creativeType = button.getAttribute('data-creative-type');
        const imageUrl = button.getAttribute('data-image-url');
        const landingUrl = button.getAttribute('data-landing-url');
        const scriptContent = button.getAttribute('data-script-content');
        const size = button.getAttribute('data-size');

        const modalTitle = previewModal.querySelector('.modal-title');
        const previewContent = previewModal.querySelector('#preview-content');

        modalTitle.textContent = 'Preview: ' + creativeName;
        previewContent.innerHTML = '';

        if (creativeType === 'image') {
            const link = document.createElement('a');
            link.href = landingUrl;
            link.target = '_blank';
            const img = document.createElement('img');
            img.src = imageUrl;
            img.classList.add('img-fluid');
            img.style.maxHeight = '400px';
            img.alt = creativeName;
            link.appendChild(img);
            previewContent.appendChild(link);
        } else if (creativeType === 'script') {
            const iframe = document.createElement('iframe');
            if (size && size !== 'all' && size.includes('x')) {
                const [width, height] = size.split('x');
                iframe.width = width;
                iframe.height = height;
            } else {
                iframe.width = '100%';
                iframe.height = '250';
            }
            iframe.style.border = 'none';
            iframe.srcdoc = scriptContent;
            previewContent.appendChild(iframe);
        }
    });

    // Logika untuk Modal Status Creative
    const statusCreativeModal = document.getElementById('statusCreativeModal');
    statusCreativeModal.addEventListener('show.bs.modal', function(e) {
        const btn = e.relatedTarget;
        const id = btn.getAttribute('data-id');
        const name = btn.getAttribute('data-name');
        const status = btn.getAttribute('data-status');
        const actionText = status === 'active' ? 'pause' : 'activate';
        
        statusCreativeModal.querySelector('#status-creative-id').value = id;
        statusCreativeModal.querySelector('#status-creative-name').textContent = name;
        statusCreativeModal.querySelector('#status-creative-action-text').textContent = actionText;
        statusCreativeModal.querySelector('#status-creative-current-status').value = status;
        
        const confirmBtn = statusCreativeModal.querySelector('#status-creative-confirm-btn');
        confirmBtn.className = 'btn ' + (status === 'active' ? 'btn-warning' : 'btn-success');
        confirmBtn.textContent = 'Yes, ' + actionText.charAt(0).toUpperCase() + actionText.slice(1);
    });

    // Logika untuk Modal Delete Creative
    const deleteCreativeModal = document.getElementById('deleteCreativeModal');
    deleteCreativeModal.addEventListener('show.bs.modal', function(e) {
        const btn = e.relatedTarget;
        deleteCreativeModal.querySelector('#delete-creative-id').value = btn.getAttribute('data-id');
        deleteCreativeModal.querySelector('#delete-creative-name').textContent = btn.getAttribute('data-name');
    });
});
</script>

<?php 
$stmt_creatives->close();
require_once __DIR__ . '/templates/footer.php'; 
?>
