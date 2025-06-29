<?php
// File: /publisher/sites.php (UPDATED with Add Site functionality)

require_once __DIR__ . '/templates/header.php';

$publisher_id = $_SESSION['publisher_id'];

// Ambil semua situs milik publisher ini
$sites_sql = "SELECT id, url, status FROM sites WHERE user_id = ? ORDER BY url ASC";
$stmt_sites = $conn->prepare($sites_sql);
$stmt_sites->bind_param("i", $publisher_id);
$stmt_sites->execute();
$sites_result = $stmt_sites->get_result();
$sites = $sites_result->fetch_all(MYSQLI_ASSOC);
$stmt_sites->close();

// Ambil daftar kategori untuk form
$categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

// Base URL untuk Ad Tag Anda.
$base_adserver_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
?>

<?php if (isset($_SESSION['success_message'])): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo $_SESSION['success_message']; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION['success_message']); endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo $_SESSION['error_message']; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION['error_message']); endif; ?>


<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mt-4 mb-0">My Sites & Zones</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSiteModal">
        <i class="bi bi-plus-circle-fill"></i> Add New Site
    </button>
</div>

<?php if (empty($sites)): ?>
    <div class="alert alert-info">You have not submitted any sites yet. Click "Add New Site" to submit your first site for approval.</div>
<?php else: ?>
    <div class="accordion" id="sitesAccordion">
        <?php foreach ($sites as $site): ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading-site-<?php echo $site['id']; ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-site-<?php echo $site['id']; ?>">
                        <?php echo htmlspecialchars($site['url']); ?> 
                        <?php 
                            $status_class = 'secondary';
                            if ($site['status'] == 'approved') $status_class = 'success';
                            if ($site['status'] == 'rejected') $status_class = 'danger';
                            if ($site['status'] == 'pending') $status_class = 'warning text-dark';
                        ?>
                        <span class="ms-3 badge bg-<?php echo $status_class; ?>"><?php echo ucfirst($site['status']); ?></span>
                    </button>
                </h2>
                <div id="collapse-site-<?php echo $site['id']; ?>" class="accordion-collapse collapse" data-bs-parent="#sitesAccordion">
                    <div class="accordion-body">
                        <?php if ($site['status'] == 'approved'): ?>
                            <?php
                                // Ambil zona untuk situs ini
                                $zones_sql = "SELECT id, name, size FROM zones WHERE site_id = ?";
                                $stmt_zones = $conn->prepare($zones_sql);
                                $stmt_zones->bind_param("i", $site['id']);
                                $stmt_zones->execute();
                                $zones_result = $stmt_zones->get_result();
                            ?>
                            <h6>Ad Zones</h6>
                            <table class="table table-sm table-bordered">
                                <thead><tr><th>Zone Name</th><th>Size</th><th>Action</th></tr></thead>
                                <tbody>
                                    <?php if ($zones_result->num_rows > 0): while($zone = $zones_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($zone['name']); ?></td>
                                        <td><span class="badge bg-info"><?php echo $zone['size']; ?></span></td>
                                        <td>
                                            <button class="btn btn-sm btn-success get-tag-btn" data-bs-toggle="modal" data-bs-target="#getTagModal" data-zone-id="<?php echo $zone['id']; ?>">
                                                <i class="bi bi-code-slash"></i> Get Tag
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; else: ?>
                                    <tr><td colspan="3" class="text-center">No zones configured for this site.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?php $stmt_zones->close(); ?>
                        <?php elseif ($site['status'] == 'rejected'): ?>
                            <p class="text-danger">This site was rejected. Please contact support for more information.</p>
                        <?php else: ?>
                            <p class="text-muted">This site is currently awaiting approval. Ad zones will be available once the site is approved.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="modal fade" id="addSiteModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Submit New Site</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="site-action.php" method="POST">
        <div class="modal-body">
            <div class="mb-3">
                <label for="url" class="form-label">Site URL</label>
                <input type="url" class="form-control" name="url" id="url" placeholder="https://example.com" required>
            </div>
            <div class="mb-3">
                <label for="category_id" class="form-label">Site Category</label>
                <select class="form-select" name="category_id" id="category_id" required>
                    <option value="">Choose a category...</option>
                    <?php mysqli_data_seek($categories_result, 0); while($cat = $categories_result->fetch_assoc()): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="add_site" class="btn btn-primary">Submit for Approval</button>
        </div>
    </form>
</div></div></div>


<div class="modal fade" id="getTagModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Get Ad Tag</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <p>Copy and paste this code into your website where you want the ad to appear.</p>
        <textarea id="ad-tag-code" class="form-control" rows="4" readonly></textarea>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-primary" id="copy-tag-btn">Copy to Clipboard</button></div>
</div></div></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const getTagModal = document.getElementById('getTagModal');
    if (getTagModal) {
        const adTagTextarea = document.getElementById('ad-tag-code');
        const baseAdServerUrl = '<?php echo $base_adserver_url; ?>';

        getTagModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const zoneId = button.getAttribute('data-zone-id');
            const adTag = `<script src="${baseAdServerUrl}/ad.php?zone_id=${zoneId}"><\/script>`;
            adTagTextarea.value = adTag;
        });

        document.getElementById('copy-tag-btn').addEventListener('click', function() {
            adTagTextarea.select();
            navigator.clipboard.writeText(adTagTextarea.value).then(() => {
                const copyBtn = this;
                const originalText = copyBtn.textContent;
                copyBtn.textContent = 'Copied!';
                copyBtn.classList.add('btn-success');
                setTimeout(() => {
                    copyBtn.textContent = originalText;
                    copyBtn.classList.remove('btn-success');
                }, 2000);
            });
        });
    }
});
</script>

<?php 
require_once __DIR__ . '/templates/footer.php'; 
?>