<?php
// File: /admin/ron-creative-edit.php (FULL CODE)

require_once __DIR__ . '/init.php';

$creative_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$creative_id) {
    $_SESSION['error_message'] = "Invalid creative ID.";
    header('Location: ron-campaigns.php');
    exit();
}

// Ambil data creative yang akan di-edit
$stmt = $conn->prepare("SELECT * FROM creatives WHERE id = ?");
$stmt->bind_param("i", $creative_id);
$stmt->execute();
$creative = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$creative) {
    $_SESSION['error_message'] = "Creative not found.";
    header('Location: ron-campaigns.php');
    exit();
}

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

<h1 class="mt-4 mb-4">Edit Creative: <?php echo htmlspecialchars($creative['name']); ?></h1>

<div class="card">
    <div class="card-body">
        <form action="ron-creative-action.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="creative_id" value="<?php echo $creative['id']; ?>">
            <input type="hidden" name="campaign_id" value="<?php echo $creative['campaign_id']; ?>">
            <input type="hidden" name="creative_type" value="<?php echo $creative['creative_type']; ?>"> <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Creative Name</label>
                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($creative['name']); ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bid Model</label>
                    <select class="form-select" name="bid_model" required>
                        <option value="cpc" <?php if($creative['bid_model'] == 'cpc') echo 'selected'; ?>>CPC</option>
                        <option value="cpm" <?php if($creative['bid_model'] == 'cpm') echo 'selected'; ?>>CPM</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bid Amount ($)</label>
                    <input type="number" step="0.0001" class="form-control" name="bid_amount" value="<?php echo $creative['bid_amount']; ?>" required>
                </div>
            </div>
            
            <hr class="my-4">
            <h4>Creative Details (Type: <?php echo ucfirst($creative['creative_type']); ?>)</h4>

            <?php if ($creative['creative_type'] == 'image'): ?>
                <fieldset>
                    <div class="mb-3">
                        <label class="form-label">Current Image Preview</label>
                        <div><img src="<?php echo htmlspecialchars($creative['image_url']); ?>" alt="Preview" style="max-width: 300px; max-height: 250px; border: 1px solid #ddd; padding: 5px;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Replace File (Optional)</label>
                        <input class="form-control" type="file" name="creative_file">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Or Update Image URL</label>
                        <input type="url" class="form-control" name="image_url" value="<?php echo htmlspecialchars($creative['image_url']); ?>" placeholder="https://example.com/banner.jpg">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ad Size</label>
                        <select class="form-select" name="sizes">
                            <?php foreach($ad_sizes as $value => $label): ?>
                                <option value="<?php echo $value; ?>" <?php if($creative['sizes'] == $value) echo 'selected'; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Landing Page URL</label>
                        <input type="url" class="form-control" name="landing_url" value="<?php echo htmlspecialchars($creative['landing_url']); ?>" required>
                    </div>
                </fieldset>
            <?php else: // creative_type is 'script' ?>
                <fieldset>
                    <div class="mb-3">
                        <label class="form-label">HTML / Script Content</label>
                        <textarea class="form-control" name="script_content" rows="10" required><?php echo htmlspecialchars($creative['script_content']); ?></textarea>
                    </div>
                </fieldset>
            <?php endif; ?>

            <hr>
            <a href="ron-creative.php?campaign_id=<?php echo $creative['campaign_id']; ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" name="update_creative" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>