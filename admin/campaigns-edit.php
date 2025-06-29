<?php
// File: /admin/campaigns-edit.php (FINAL VERSION with Advanced Targeting)

require_once __DIR__ . '/init.php';

// 1. Validasi ID kampanye dari URL
$campaign_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$campaign_id) {
    $_SESSION['error_message'] = "Invalid campaign ID.";
    header('Location: campaigns.php');
    exit();
}

// 2. Ambil data kampanye utama
$stmt_campaign = $conn->prepare("SELECT * FROM campaigns WHERE id = ?");
$stmt_campaign->bind_param("i", $campaign_id);
$stmt_campaign->execute();
$campaign = $stmt_campaign->get_result()->fetch_assoc();
$stmt_campaign->close();

if (!$campaign) {
    $_SESSION['error_message'] = "Campaign not found.";
    header('Location: campaigns.php');
    exit();
}

// 3. Ambil data penargetan yang ada
$stmt_targeting = $conn->prepare("SELECT * FROM campaign_targeting WHERE campaign_id = ?");
$stmt_targeting->bind_param("i", $campaign_id);
$stmt_targeting->execute();
$targeting = $stmt_targeting->get_result()->fetch_assoc();
$stmt_targeting->close();

// 4. Ubah data targeting dari string ke array untuk pre-fill form
$targeted_countries = explode(',', $targeting['countries'] ?? '');
$targeted_browsers = explode(',', $targeting['browsers'] ?? '');
$targeted_devices = explode(',', $targeting['devices'] ?? '');
$targeted_os = explode(',', $targeting['os'] ?? '');
$targeted_connections = explode(',', $targeting['connection_types'] ?? '');

// 5. Siapkan data untuk dropdowns dan opsi targeting
$advertisers_result = $conn->query("SELECT id, username FROM users WHERE role = 'advertiser'");
$categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name");
$countries = ['Indonesia', 'Malaysia', 'Singapore', 'USA', 'United Kingdom', 'Australia', 'Japan', 'Germany', 'Canada'];
$browsers = ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera', 'Samsung Internet'];
$devices = ['Desktop', 'Mobile', 'Tablet'];
$os_list = ['Windows', 'macOS', 'Linux', 'Android', 'iOS'];
$connections = ['WiFi', 'Cellular'];
?>

<?php require_once __DIR__ . '/templates/header.php'; ?>
<h1 class="mt-4">Edit Campaign: <?php echo htmlspecialchars($campaign['name']); ?></h1>
<p>Perbarui detail kampanye, kanal penayangan, dan opsi penargetan.</p>

<div class="card">
    <div class="card-body">
        <form action="campaigns-action.php" method="POST">
            <input type="hidden" name="campaign_id" value="<?php echo $campaign['id']; ?>">

            <h4>Campaign Details</h4>
            <div class="mb-3"><label class="form-label">Campaign Name</label><input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($campaign['name']); ?>" required></div>
            <div class="mb-3"><label class="form-label">Advertiser</label><select class="form-select" name="advertiser_id" required><option value="">Choose...</option><?php while ($adv = $advertisers_result->fetch_assoc()): ?><option value="<?php echo $adv['id']; ?>" <?php if($adv['id'] == $campaign['advertiser_id']) echo 'selected'; ?>><?php echo htmlspecialchars($adv['username']); ?></option><?php endwhile; ?></select></div>
            <div class="mb-3"><label class="form-label">Category</label><select class="form-select" name="category_id" required><option value="">Choose...</option><?php mysqli_data_seek($categories_result, 0); while ($cat = $categories_result->fetch_assoc()): ?><option value="<?php echo $cat['id']; ?>" <?php if($cat['id'] == $campaign['category_id']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['name']); ?></option><?php endwhile; ?></select></div>

            <hr class="my-4">
            <h4>Serving Channels</h4>
            <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" role="switch" id="serve_on_internal" name="serve_on_internal" value="1" <?php if($campaign['serve_on_internal']) echo 'checked'; ?>>
                <label class="form-check-label" for="serve_on_internal"><strong>Serve on Internal Network</strong> (RON)</label>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" id="allow_external_rtb" name="allow_external_rtb" value="1" <?php if($campaign['allow_external_rtb']) echo 'checked'; ?>>
                <label class="form-check-label" for="allow_external_rtb"><strong>Allow External RTB Bidding</strong></label>
            </div>

            <div id="internal-targeting-container" style="<?php if(!$campaign['serve_on_internal']) echo 'display: none;'; ?>">
                <hr class="my-4">
                <h4>Internal Network Targeting</h4>
                <div class="accordion" id="targetingAccordion">

                    <div class="accordion-item">
                        <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGeo">Geography</button></h2>
                        <div id="collapseGeo" class="accordion-collapse collapse" data-bs-parent="#targetingAccordion">
                            <div class="accordion-body">
                                <div class="form-check"><input class="form-check-input select-all-trigger" type="checkbox" data-target-class="target-country"><label class="form-check-label fw-bold">Select All Countries</label></div><hr class="my-2">
                                <div class="targeting-grid"><?php foreach ($countries as $item):?><div class="form-check"><input class="form-check-input target-country" type="checkbox" name="countries[]" value="<?php echo $item;?>" <?php if(in_array($item, $targeted_countries)) echo 'checked'; ?>><label class="form-check-label"><?php echo $item;?></label></div><?php endforeach;?></div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTech">Technology</button></h2>
                        <div id="collapseTech" class="accordion-collapse collapse" data-bs-parent="#targetingAccordion">
                            <div class="accordion-body">
                                <p class="fw-bold">Browser:</p>
                                <div class="form-check"><input class="form-check-input select-all-trigger" type="checkbox" data-target-class="target-browser"><label class="form-check-label fw-bold">Select All Browsers</label></div><hr class="my-2">
                                <div class="targeting-grid"><?php foreach ($browsers as $item):?><div class="form-check"><input class="form-check-input target-browser" type="checkbox" name="browsers[]" value="<?php echo $item;?>" <?php if(in_array($item, $targeted_browsers)) echo 'checked'; ?>><label class="form-check-label"><?php echo $item;?></label></div><?php endforeach;?></div>
                                <p class="fw-bold mt-3">Operating System:</p>
                                <div class="form-check"><input class="form-check-input select-all-trigger" type="checkbox" data-target-class="target-os"><label class="form-check-label fw-bold">Select All OS</label></div><hr class="my-2">
                                <div class="targeting-grid"><?php foreach ($os_list as $item):?><div class="form-check"><input class="form-check-input target-os" type="checkbox" name="os[]" value="<?php echo $item;?>" <?php if(in_array($item, $targeted_os)) echo 'checked'; ?>><label class="form-check-label"><?php echo $item;?></label></div><?php endforeach;?></div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDevice">Device & Connection</button></h2>
                        <div id="collapseDevice" class="accordion-collapse collapse" data-bs-parent="#targetingAccordion">
                             <div class="accordion-body">
                                <p class="fw-bold">Device Type:</p>
                                <div class="form-check"><input class="form-check-input select-all-trigger" type="checkbox" data-target-class="target-device"><label class="form-check-label fw-bold">Select All Devices</label></div><hr class="my-2">
                                <div class="targeting-grid"><?php foreach ($devices as $item):?><div class="form-check"><input class="form-check-input target-device" type="checkbox" name="devices[]" value="<?php echo $item;?>" <?php if(in_array($item, $targeted_devices)) echo 'checked'; ?>><label class="form-check-label"><?php echo $item;?></label></div><?php endforeach;?></div>
                                <p class="fw-bold mt-3">Connection Type:</p>
                                <div class="form-check"><input class="form-check-input select-all-trigger" type="checkbox" data-target-class="target-connection"><label class="form-check-label fw-bold">Select All Connections</label></div><hr class="my-2">
                                <div class="targeting-grid"><?php foreach ($connections as $item):?><div class="form-check"><input class="form-check-input target-connection" type="checkbox" name="connection_types[]" value="<?php echo $item;?>" <?php if(in_array($item, $targeted_connections)) echo 'checked'; ?>><label class="form-check-label"><?php echo $item;?></label></div><?php endforeach;?></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <button type="submit" name="update_campaign" class="btn btn-primary mt-4">Save Changes</button>
            <a href="campaigns.php" class="btn btn-secondary mt-4">Cancel</a>
        </form>
    </div>
</div>
<style>
.targeting-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.5rem; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const internalSwitch = document.getElementById('serve_on_internal');
    const targetingContainer = document.getElementById('internal-targeting-container');
    internalSwitch.addEventListener('change', function() {
        targetingContainer.style.display = this.checked ? 'block' : 'none';
    });
    document.querySelectorAll('.select-all-trigger').forEach(trigger => {
        trigger.addEventListener('change', function() {
            const targetClass = this.getAttribute('data-target-class');
            const isChecked = this.checked;
            // Uncheck "Select All" if an individual item is unchecked
            document.querySelectorAll('.' + targetClass).forEach(target => {
                target.checked = isChecked;
            });
        });
    });
});
</script>
<?php 
$advertisers_result->close();
$categories_result->close();
require_once __DIR__ . '/templates/footer.php'; 
?>