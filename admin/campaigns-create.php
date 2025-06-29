<?php
// File: /admin/campaigns-create.php (FINAL with Full Targeting Options)

require_once __DIR__ . '/init.php';

// Data untuk dropdowns dan targeting
$ad_formats_result = $conn->query("SELECT id, name FROM ad_formats WHERE status = 1 ORDER BY name");
$advertisers_result = $conn->query("SELECT id, username FROM users WHERE role = 'advertiser'");
$categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name");

// Data statis untuk opsi targeting
$countries = ['Indonesia', 'Malaysia', 'Singapore', 'USA', 'United Kingdom', 'Australia', 'Japan', 'Germany', 'Canada'];
$browsers = ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera', 'Samsung Internet'];
$devices = ['Desktop', 'Mobile', 'Tablet'];
$os_list = ['Windows', 'macOS', 'Linux', 'Android', 'iOS'];
$connections = ['WiFi', 'Cellular'];

?>

<?php require_once __DIR__ . '/templates/header.php'; ?>
<h1 class="mt-4">Create New Unified Campaign</h1>
<p>Buat kampanye baru dengan pengaturan yang fleksibel dan penargetan canggih.</p>

<div class="card">
    <div class="card-body">
        <form action="campaigns-action.php" method="POST">
            
            <h4>Step 1: Choose Ad Format</h4>
            <div class="mb-3">
                <label class="form-label fw-bold">Ad Format</label>
                <select class="form-select" name="ad_format_id" required>
                    <option value="">Choose ad format...</option>
                    <?php while ($format = $ad_formats_result->fetch_assoc()): ?>
                    <option value="<?php echo $format['id']; ?>"><?php echo htmlspecialchars($format['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <hr class="my-4">

            <h4>Step 2: Campaign Details</h4>
            <div class="mb-3"><label class="form-label">Campaign Name</label><input type="text" class="form-control" name="name" required></div>
            <div class="mb-3"><label class="form-label">Advertiser</label><select class="form-select" name="advertiser_id" required><option value="">Choose...</option><?php while ($adv = $advertisers_result->fetch_assoc()): ?><option value="<?php echo $adv['id']; ?>"><?php echo htmlspecialchars($adv['username']); ?></option><?php endwhile; ?></select></div>
            <div class="mb-3"><label class="form-label">Category</label><select class="form-select" name="category_id" required><option value="">Choose...</option><?php mysqli_data_seek($categories_result, 0); while ($cat = $categories_result->fetch_assoc()): ?><option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option><?php endwhile; ?></select></div>

            <hr class="my-4">
            
            <h4>Step 3: Serving Channels</h4>
            <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" role="switch" id="serve_on_internal" name="serve_on_internal" value="1" checked>
                <label class="form-check-label" for="serve_on_internal"><strong>Serve on Internal Network</strong> (RON)</label>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" id="allow_external_rtb" name="allow_external_rtb" value="1">
                <label class="form-check-label" for="allow_external_rtb"><strong>Allow External RTB Bidding</strong></label>
            </div>

            <div id="internal-targeting-container">
                <hr class="my-4">
                <h4>Step 4: Internal Network Targeting</h4>
                <div class="accordion" id="targetingAccordion">

                    <div class="accordion-item">
                        <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGeo" aria-expanded="false"><i class="bi bi-globe-americas me-2"></i> Geography</button></h2>
                        <div id="collapseGeo" class="accordion-collapse collapse" data-bs-parent="#targetingAccordion">
                            <div class="accordion-body">
                                <div class="form-check"><input class="form-check-input select-all-trigger" type="checkbox" data-target-class="target-country"><label class="form-check-label fw-bold">Select All Countries</label></div><hr class="my-2">
                                <div class="targeting-grid"><?php foreach ($countries as $item):?><div class="form-check"><input class="form-check-input target-country" type="checkbox" name="countries[]" value="<?php echo $item;?>"><label class="form-check-label"><?php echo $item;?></label></div><?php endforeach;?></div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTech" aria-expanded="false"><i class="bi bi-cpu-fill me-2"></i> Technology</button></h2>
                        <div id="collapseTech" class="accordion-collapse collapse" data-bs-parent="#targetingAccordion">
                            <div class="accordion-body">
                                <p class="fw-bold">Browser:</p>
                                <div class="form-check"><input class="form-check-input select-all-trigger" type="checkbox" data-target-class="target-browser"><label class="form-check-label fw-bold">Select All Browsers</label></div><hr class="my-2">
                                <div class="targeting-grid"><?php foreach ($browsers as $item):?><div class="form-check"><input class="form-check-input target-browser" type="checkbox" name="browsers[]" value="<?php echo $item;?>"><label class="form-check-label"><?php echo $item;?></label></div><?php endforeach;?></div>
                                <p class="fw-bold mt-3">Operating System:</p>
                                <div class="form-check"><input class="form-check-input select-all-trigger" type="checkbox" data-target-class="target-os"><label class="form-check-label fw-bold">Select All OS</label></div><hr class="my-2">
                                <div class="targeting-grid"><?php foreach ($os_list as $item):?><div class="form-check"><input class="form-check-input target-os" type="checkbox" name="os[]" value="<?php echo $item;?>"><label class="form-check-label"><?php echo $item;?></label></div><?php endforeach;?></div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDevice" aria-expanded="false"><i class="bi bi-phone-fill me-2"></i> Device & Connection</button></h2>
                        <div id="collapseDevice" class="accordion-collapse collapse" data-bs-parent="#targetingAccordion">
                             <div class="accordion-body">
                                <p class="fw-bold">Device Type:</p>
                                <div class="form-check"><input class="form-check-input select-all-trigger" type="checkbox" data-target-class="target-device"><label class="form-check-label fw-bold">Select All Devices</label></div><hr class="my-2">
                                <div class="targeting-grid"><?php foreach ($devices as $item):?><div class="form-check"><input class="form-check-input target-device" type="checkbox" name="devices[]" value="<?php echo $item;?>"><label class="form-check-label"><?php echo $item;?></label></div><?php endforeach;?></div>
                                <p class="fw-bold mt-3">Connection Type:</p>
                                <div class="form-check"><input class="form-check-input select-all-trigger" type="checkbox" data-target-class="target-connection"><label class="form-check-label fw-bold">Select All Connections</label></div><hr class="my-2">
                                <div class="targeting-grid"><?php foreach ($connections as $item):?><div class="form-check"><input class="form-check-input target-connection" type="checkbox" name="connection_types[]" value="<?php echo $item;?>"><label class="form-check-label"><?php echo $item;?></label></div><?php endforeach;?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" name="create_campaign" class="btn btn-primary mt-4">Create Campaign & Continue</button>
        </form>
    </div>
</div>

<style>.targeting-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.5rem; }</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('serve_on_internal').addEventListener('change', function() { document.getElementById('internal-targeting-container').style.display = this.checked ? 'block' : 'none'; });
    document.querySelectorAll('.select-all-trigger').forEach(trigger => {
        trigger.addEventListener('change', function() {
            const targetClass = this.getAttribute('data-target-class');
            document.querySelectorAll('.' + targetClass).forEach(target => { target.checked = this.checked; });
        });
    });
});
</script>

<?php 
$ad_formats_result->close();
$advertisers_result->close();
$categories_result->close();
require_once __DIR__ . '/templates/footer.php'; 
?>