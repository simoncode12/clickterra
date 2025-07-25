<?php
// File: /publisher/sites.php (REDESIGNED - Modern UI with Enhanced UX)

require_once __DIR__ . '/init.php';

$publisher_id = $_SESSION['publisher_id'];

// Ambil data situs milik publisher
$sites_result = $conn->query("SELECT id, url, status, created_at FROM sites WHERE user_id = {$publisher_id} ORDER BY created_at DESC");

// Ambil domain dari pengaturan untuk membuat Ad Tag
$base_ad_server_url = get_setting('ad_server_domain', $conn);

// Daftar ukuran iklan yang umum untuk dropdown
$ad_sizes = [
    '300x250' => '300x250 - Medium Rectangle', 
    '728x90' => '728x90 - Leaderboard',
    '160x600' => '160x600 - Wide Skyscraper', 
    '300x50 ' => '300x50 - Mobile Leaderboard',
    '300x100' => '300x100- Square',
    '300x500 ' => '300x500 - Half Page', 
    '900x250 ' => '900x250 - Billboard',
    'all' => 'All Sizes (for Script)'
];

// Ambil daftar kategori dan format iklan untuk form modal
$categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
$ad_formats_result = $conn->query("SELECT id, name FROM ad_formats WHERE status = 1 ORDER BY name ASC");
$ad_formats_array = $ad_formats_result ? $ad_formats_result->fetch_all(MYSQLI_ASSOC) : [];

// Hitung jumlah situs per status
$stats = [
    'total' => 0,
    'approved' => 0,
    'pending' => 0,
    'rejected' => 0
];

if ($sites_result && $sites_result->num_rows > 0) {
    $stats['total'] = $sites_result->num_rows;
    mysqli_data_seek($sites_result, 0);
    while($site = $sites_result->fetch_assoc()) {
        if (isset($stats[$site['status']])) {
            $stats[$site['status']]++;
        }
    }
    mysqli_data_seek($sites_result, 0);
}

?>
<?php require_once __DIR__ . '/templates/header.php'; ?>

<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
        <h4 class="fw-bold mb-1">Sites & Zones</h4>
        <p class="text-muted mb-0">Manage your websites and ad placements</p>
    </div>
    
    <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addSiteModal">
        <i class="bi bi-plus-circle"></i> Add New Site
    </button>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert custom-alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
    <div class="d-flex">
        <div class="me-3">
            <i class="bi bi-check-circle-fill fs-4"></i>
        </div>
        <div>
            <strong>Success!</strong>
            <p class="mb-0"><?php echo $_SESSION['success_message']; ?></p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['success_message']); endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert custom-alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
    <div class="d-flex">
        <div class="me-3">
            <i class="bi bi-exclamation-triangle-fill fs-4"></i>
        </div>
        <div>
            <strong>Error!</strong>
            <p class="mb-0"><?php echo $_SESSION['error_message']; ?></p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['error_message']); endif; ?>

<!-- Site Stats Overview -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stat h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-title">Total Sites</div>
                    <div class="stat-value"><?php echo $stats['total']; ?></div>
                </div>
                <i class="bi bi-globe stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stat h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-title">Approved</div>
                    <div class="stat-value text-success"><?php echo $stats['approved']; ?></div>
                </div>
                <i class="bi bi-check-circle stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stat h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-title">Pending</div>
                    <div class="stat-value text-warning"><?php echo $stats['pending']; ?></div>
                </div>
                <i class="bi bi-hourglass-split stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stat h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-title">Rejected</div>
                    <div class="stat-value text-danger"><?php echo $stats['rejected']; ?></div>
                </div>
                <i class="bi bi-x-circle stat-icon"></i>
            </div>
        </div>
    </div>
</div>

<!-- Sites Empty State -->
<?php if (!$sites_result || $sites_result->num_rows == 0): ?>
<div class="card shadow-sm mb-4">
    <div class="card-body text-center py-5">
        <div class="mb-3">
            <i class="bi bi-globe fs-1 text-muted"></i>
        </div>
        <h5 class="mb-2">No Sites Added Yet</h5>
        <p class="text-muted mb-4">You haven't submitted any websites to your publisher account.</p>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSiteModal">
            <i class="bi bi-plus-circle me-2"></i> Add Your First Site
        </button>
    </div>
</div>
<?php endif; ?>

<!-- Sites List -->
<?php if ($sites_result && $sites_result->num_rows > 0): mysqli_data_seek($sites_result, 0); while($site = $sites_result->fetch_assoc()): 
    $status_class = ['approved' => 'success', 'rejected' => 'danger', 'pending' => 'warning'][$site['status']] ?? 'secondary';
    $status_icon = ['approved' => 'check-circle', 'rejected' => 'x-circle', 'pending' => 'hourglass-split'][$site['status']] ?? 'question-circle';
?>
<div class="card shadow-sm mb-4 overflow-hidden site-card animate__animated animate__fadeIn">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <div class="site-icon me-3">
                <img src="https://www.google.com/s2/favicons?domain=<?php echo parse_url($site['url'], PHP_URL_HOST); ?>&sz=32" alt="Site Icon" onerror="this.src='assets/images/globe-icon.png';" width="32" height="32">
            </div>
            <div>
                <h5 class="mb-0 fw-semibold">
                    <a href="<?php echo htmlspecialchars($site['url']); ?>" target="_blank" class="text-decoration-none text-dark">
                        <?php echo htmlspecialchars(parse_url($site['url'], PHP_URL_HOST)); ?>
                    </a>
                </h5>
                <div class="small text-muted"><?php echo htmlspecialchars($site['url']); ?></div>
            </div>
            <div class="ms-3">
                <span class="badge status-badge bg-<?php echo $status_class; ?> bg-opacity-10 text-<?php echo $status_class; ?>">
                    <i class="bi bi-<?php echo $status_icon; ?> me-1"></i>
                    <?php echo ucfirst($site['status']); ?>
                </span>
            </div>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            <?php if ($site['status'] == 'approved'): ?>
                <button class="btn btn-sm btn-primary add-zone-btn" data-bs-toggle="modal" data-bs-target="#addZoneModal" data-site-id="<?php echo $site['id']; ?>">
                    <i class="bi bi-plus-lg me-1"></i> Add Zone
                </button>
            <?php elseif ($site['status'] == 'pending'): ?>
                <span class="small text-muted">Submitted <?php echo date('M d, Y', strtotime($site['created_at'])); ?></span>
            <?php endif; ?>
            
            <div class="dropdown">
                <button class="btn btn-sm btn-light" type="button" id="siteActions<?php echo $site['id']; ?>" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="siteActions<?php echo $site['id']; ?>">
                    <li><a class="dropdown-item" href="<?php echo htmlspecialchars($site['url']); ?>" target="_blank"><i class="bi bi-box-arrow-up-right me-2"></i>Visit Site</a></li>
                    <?php if ($site['status'] == 'rejected'): ?>
                    <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-trash me-2"></i>Delete Site</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    
    <?php if ($site['status'] == 'approved'): ?>
        <?php
            // Perbaikan: Menggunakan LEFT JOIN untuk memastikan semua zona tampil
            $zones_sql = "SELECT z.id, z.name, z.size, af.name as ad_format_name FROM zones z LEFT JOIN ad_formats af ON z.ad_format_id = af.id WHERE z.site_id = ?";
            $stmt_zones = $conn->prepare($zones_sql);
            $stmt_zones->bind_param("i", $site['id']);
            $stmt_zones->execute();
            $zones_result = $stmt_zones->get_result();
            $zone_count = $zones_result->num_rows;
        ?>
        
        <div class="px-3 py-2 bg-light border-top border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <div class="small fw-medium">
                    <i class="bi bi-grid me-1"></i> Zones (<?php echo $zone_count; ?>)
                </div>
                <?php if ($zone_count > 0): ?>
                <button class="btn btn-sm btn-link p-0 zone-collapse-btn" type="button" data-bs-toggle="collapse" data-bs-target="#zoneCollapse<?php echo $site['id']; ?>">
                    <i class="bi bi-chevron-down"></i>
                </button>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($zone_count > 0): ?>
        <div class="collapse show" id="zoneCollapse<?php echo $site['id']; ?>">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Zone Name</th>
                            <th>Ad Format</th>
                            <th>Size</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($zone = $zones_result->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-medium"><?php echo htmlspecialchars($zone['name']); ?></div>
                                <div class="small text-muted">ID: <?php echo $zone['id']; ?></div>
                            </td>
                            <td>
                                <?php 
                                    $format_name = $zone['ad_format_name'] ?? 'N/A';
                                    $format_class = '';
                                    
                                    switch(strtolower($format_name)):
                                        case 'banner':
                                            $format_class = 'success';
                                            $format_icon = 'badge-ad';
                                            break;
                                        case 'video':
                                            $format_class = 'danger';
                                            $format_icon = 'film';
                                            break;
                                        case 'popunder':
                                            $format_class = 'warning';
                                            $format_icon = 'window-stack';
                                            break;
                                        default:
                                            $format_class = 'secondary';
                                            $format_icon = 'display';
                                    endswitch;
                                ?>
                                <span class="badge bg-<?php echo $format_class; ?> bg-opacity-10 text-<?php echo $format_class; ?>">
                                    <i class="bi bi-<?php echo $format_icon; ?> me-1"></i> <?php echo htmlspecialchars($format_name); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($zone['size']): ?>
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-aspect-ratio me-1"></i> <?php echo $zone['size']; ?>
                                </span>
                                <?php else: ?>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">Not applicable</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <?php
                                    // Logika untuk menampilkan tombol yang relevan saja
                                    $format_name = strtolower($zone['ad_format_name'] ?? '');
                                    if ($format_name === 'video'):
                                ?>
                                <button class="btn btn-sm btn-outline-danger get-vast-tag-btn" data-bs-toggle="modal" data-bs-target="#getVastTagModal" data-zone-id="<?php echo $zone['id']; ?>">
                                    <i class="bi bi-film me-1"></i> VAST Tag
                                </button>
                                <?php else: // Untuk Banner, Popunder, dll. ?>
                                <button class="btn btn-sm btn-outline-primary get-tag-btn" data-bs-toggle="modal" data-bs-target="#getTagModal" data-zone-id="<?php echo $zone['id']; ?>">
                                    <i class="bi bi-code-slash me-1"></i> Get Code
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="card-body text-center py-4 border-top">
            <p class="text-muted mb-3">No ad zones have been created for this site yet.</p>
            <button class="btn btn-sm btn-primary add-zone-btn" data-bs-toggle="modal" data-bs-target="#addZoneModal" data-site-id="<?php echo $site['id']; ?>">
                <i class="bi bi-plus-lg me-1"></i> Add Your First Zone
            </button>
        </div>
        <?php endif; ?>
        <?php $stmt_zones->close(); ?>
        
    <?php elseif ($site['status'] == 'pending'): ?>
        <div class="card-body border-top">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="status-icon bg-warning text-dark">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
                <div>
                    <h6 class="mb-1">Site Pending Review</h6>
                    <p class="text-muted mb-0">This site is awaiting approval. You can add zones after it has been approved by an administrator.</p>
                </div>
            </div>
        </div>
    <?php else: // rejected ?>
        <div class="card-body border-top">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="status-icon bg-danger text-white">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>
                <div>
                    <h6 class="mb-1">Site Rejected</h6>
                    <p class="text-danger mb-0">This site was rejected. Please contact support for more information or try submitting a different site.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php endwhile; endif; ?>

<!-- Add Site Modal -->
<div class="modal fade" id="addSiteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-globe2 me-2 text-primary"></i> Submit New Site
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="site-action.php" method="POST">
                <div class="modal-body">
                    <div class="mb-4">
                        <div class="alert alert-info border-0 d-flex" style="background-color: rgba(13, 110, 253, 0.1);">
                            <i class="bi bi-info-circle text-primary me-2 fs-5"></i>
                            <div class="small">
                                <strong>Important:</strong> The site must be live and accessible before submission. After approval, you'll be able to create ad zones.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="url" class="form-label small fw-medium">Site URL</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-link-45deg"></i></span>
                            <input type="url" class="form-control" name="url" id="url" placeholder="https://example.com" required>
                        </div>
                        <div class="form-text">Enter the full URL including https://</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label small fw-medium">Site Category</label>
                        <select class="form-select" name="category_id" id="category_id" required>
                            <option value="">Choose a category...</option>
                            <?php if($categories_result) { mysqli_data_seek($categories_result, 0); while($cat = $categories_result->fetch_assoc()){ echo "<option value='{$cat['id']}'>".htmlspecialchars($cat['name'])."</option>"; } } ?>
                        </select>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="termsCheckbox" required>
                        <label class="form-check-label small" for="termsCheckbox">
                            I confirm this site complies with the <a href="#" target="_blank">Publisher Terms of Service</a>
                        </label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_site" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i> Submit for Approval
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Zone Modal -->
<div class="modal fade" id="addZoneModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-grid me-2 text-primary"></i> Add New Zone
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="zone-action.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="site_id" id="modal_site_id">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Ad Format</label>
                        <select class="form-select" name="ad_format_id" id="ad_format_selector_pub" required>
                            <option value="">Select a format...</option>
                            <?php foreach ($ad_formats_array as $format): ?>
                                <option value="<?php echo $format['id']; ?>" data-format-name="<?php echo strtolower(htmlspecialchars($format['name'])); ?>">
                                    <?php echo htmlspecialchars($format['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Zone Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-tag"></i></span>
                            <input type="text" class="form-control" name="name" placeholder="e.g., Homepage Leaderboard" required>
                        </div>
                        <div class="form-text">Create a descriptive name for this ad placement</div>
                    </div>
                    
                    <div class="mb-3" id="ad_size_container_pub">
                        <label class="form-label small fw-medium">Ad Size</label>
                        <select class="form-select" name="size" id="ad_size_selector_pub" required>
                            <option value="">Select a Size</option>
                            <?php foreach($ad_sizes as $value => $label): ?>
                                <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Standard ad sizes perform better</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_zone" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Create Zone
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Get Tag Modal -->
<div class="modal fade" id="getTagModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-code-slash me-2 text-primary"></i> Get Display Ad Tag
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border mb-3">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="bi bi-lightbulb text-warning fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Implementation Guide</h6>
                            <p class="small mb-0">Copy this code and place it where you want the ad to appear on your website.</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-light p-3 rounded-3 mb-3">
                    <pre class="mb-0"><code id="ad-tag-preview" class="language-html"></code></pre>
                </div>
                
                <textarea id="ad-tag-code" class="form-control" rows="4" readonly style="font-family: monospace; font-size: 13px;"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="copy-tag-btn">
                    <i class="bi bi-clipboard me-1"></i> Copy Code
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Get VAST Tag Modal -->
<div class="modal fade" id="getVastTagModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-film me-2 text-danger"></i> Get VAST Ad Tag
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border mb-3">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="bi bi-info-circle text-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">VAST Tag Information</h6>
                            <p class="small mb-0">Copy this VAST URL and paste it into your VAST-compatible video player settings.</p>
                        </div>
                    </div>
                </div>
                
                <div class="input-group mb-3">
                    <input type="text" id="vast-tag-url" class="form-control" readonly>
                    <button class="btn btn-primary" type="button" id="copy-vast-btn">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
                
                <div class="card">
                    <div class="card-header py-2">
                        <span class="small fw-medium">Implementation Example</span>
                    </div>
                    <div class="card-body bg-light p-3">
                        <pre class="small mb-0"><code class="language-html">&lt;video id="my-video" controls&gt;
  &lt;source src="video.mp4" type="video/mp4"&gt;
&lt;/video&gt;

&lt;script&gt;
  // Initialize player with VAST tag
  const player = videojs('my-video');
  player.vast({
    url: '<span class="text-primary" id="vast-tag-example"></span>'
  });
&lt;/script&gt;</code></pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <a href="https://support.clicterra.com/vast-integration" target="_blank" class="btn btn-outline-primary">
                    <i class="bi bi-question-circle me-1"></i> Integration Help
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.site-card {
    transition: all 0.3s ease;
    border-radius: 12px;
}

.site-card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}

.site-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.status-badge {
    font-weight: 500;
    padding: 0.35rem 0.65rem;
    border-radius: 6px;
}

.status-icon {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 1.2rem;
}

.zone-collapse-btn {
    color: var(--text-muted);
    transition: all 0.2s;
}

.zone-collapse-btn:hover {
    color: var(--dark);
}

code {
    font-size: 13px;
}

.custom-alert-success {
    background-color: rgba(74, 222, 128, 0.1);
    border: 1px solid rgba(74, 222, 128, 0.2);
    border-left: 4px solid var(--success-color);
    border-radius: 8px;
}

.custom-alert-danger {
    background-color: rgba(244, 63, 94, 0.1);
    border: 1px solid rgba(244, 63, 94, 0.2);
    border-left: 4px solid var(--danger-color);
    border-radius: 8px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const copyToClipboard = (textareaId, buttonId) => {
        const textarea = document.getElementById(textareaId);
        const button = document.getElementById(buttonId);
        if(!textarea || !button) return;
        
        button.addEventListener('click', function() {
            textarea.select();
            navigator.clipboard.writeText(textarea.value).then(() => {
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="bi bi-check2"></i> Copied!';
                button.classList.add('btn-success');
                setTimeout(() => { 
                    button.innerHTML = originalText;
                    button.classList.remove('btn-success');
                }, 2000);
            });
        });
    };

    document.body.addEventListener('click', function(event) {
        const baseAdServerUrl = '<?php echo rtrim($base_ad_server_url, '/'); ?>';
        
        if (event.target && event.target.closest('.get-tag-btn')) {
            const button = event.target.closest('.get-tag-btn');
            const adTagTextarea = document.getElementById('ad-tag-code');
            const adTagPreview = document.getElementById('ad-tag-preview');
            const zoneId = button.dataset.zoneId;
            const adTag = `<script src="${baseAdServerUrl}/ad.php?zone_id=${zoneId}"><\/script>`;
            if(adTagTextarea) adTagTextarea.value = adTag;
            if(adTagPreview) adTagPreview.textContent = adTag;
        }

        if (event.target && event.target.closest('.get-vast-tag-btn')) {
            const button = event.target.closest('.get-vast-tag-btn');
            const vastTagInput = document.getElementById('vast-tag-url');
            const vastTagExample = document.getElementById('vast-tag-example');
            const zoneId = button.dataset.zoneId;
            const vastTag = `${baseAdServerUrl}/vast.php?zone_id=${zoneId}`;
            if(vastTagInput) vastTagInput.value = vastTag;
            if(vastTagExample) vastTagExample.textContent = vastTag;
        }
    });

    copyToClipboard('ad-tag-code', 'copy-tag-btn');
    copyToClipboard('vast-tag-url', 'copy-vast-btn');
    
    const addZoneModal = document.getElementById('addZoneModal');
    if (addZoneModal) {
        addZoneModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (button && button.classList.contains('add-zone-btn')) {
                const siteId = button.getAttribute('data-site-id');
                const modalSiteIdInput = addZoneModal.querySelector('#modal_site_id');
                if(modalSiteIdInput) modalSiteIdInput.value = siteId;
            }
        });
    }

    const formatSelectorPub = document.getElementById('ad_format_selector_pub');
    const sizeContainerPub = document.getElementById('ad_size_container_pub');
    const sizeSelectorPub = document.getElementById('ad_size_selector_pub');

    function toggleAdSizeField() {
        if (!formatSelectorPub || !sizeContainerPub || !sizeSelectorPub) return;
        const selectedOption = formatSelectorPub.options[formatSelectorPub.selectedIndex];
        const formatName = selectedOption.getAttribute('data-format-name');
        
        if (formatName === 'video' || formatName === 'popunder') {
            sizeContainerPub.style.display = 'none';
            sizeSelectorPub.required = false;
            sizeSelectorPub.value = '';
        } else {
            sizeContainerPub.style.display = 'block';
            sizeSelectorPub.required = true;
        }
    }

    if (formatSelectorPub) {
        formatSelectorPub.addEventListener('change', toggleAdSizeField);
        toggleAdSizeField(); // Jalankan saat halaman dimuat
    }
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Animate stats on page load
    const statValues = document.querySelectorAll('.stat-value');
    statValues.forEach((stat, index) => {
        stat.style.opacity = 0;
        setTimeout(() => {
            stat.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            stat.style.opacity = 1;
            stat.style.transform = 'translateY(0)';
        }, 100 * index);
    });
});
</script>

<?php 
if (isset($sites_result)) { $sites_result->close(); }
if (isset($categories_result)) { $categories_result->close(); }
if (isset($ad_formats_result)) { $ad_formats_result->close(); }
require_once __DIR__ . '/templates/footer.php'; 
?>
