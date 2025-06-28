<?php
// File: /admin/ron-campaigns.php (Updated with Category)

// Muat semua konfigurasi inti dan otentikasi
require_once __DIR__ . '/init.php';

// Data statis untuk form
$countries = ['Indonesia', 'Malaysia', 'Singapore', 'USA', 'United Kingdom', 'Australia'];
$browsers = ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera'];
$devices = ['Desktop', 'Mobile', 'Tablet'];
$os = ['Windows', 'macOS', 'Linux', 'Android', 'iOS'];
$connections = ['WiFi', '3G', '4G', '5G'];
$banner_sizes = ['300x250', '300x100', '300x50', '300x500', '900x250', '728x90', '160x600'];

// Ambil data untuk dropdown dari DB
$advertisers_result = $conn->query("SELECT id, username FROM users WHERE role = 'advertiser'");
$categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name"); // <-- BARIS BARU

// Muat template header setelah semua data siap
require_once __DIR__ . '/templates/header.php';
?>

<h1 class="mt-4">Create RON Campaign</h1>
<p>Buat kampanye Run of Network baru melalui langkah-langkah berikut.</p>

<?php
// Tampilkan pesan sukses atau error jika ada dari file action
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}
?>

<div class="card wizard-card">
    <div class="card-body">
        <form action="ron-campaigns-action.php" method="POST" id="ronCampaignForm">
            <!-- Step 1: Campaign Details -->
            <div id="step1" class="form-section active">
                <h4>Step 1: Campaign Details</h4>
                <div class="mb-3">
                    <label for="campaign_name" class="form-label">Campaign Name</label>
                    <input type="text" class="form-control" id="campaign_name" name="campaign_name" required>
                </div>
                <div class="mb-3">
                    <label for="advertiser_id" class="form-label">Advertiser</label>
                    <select class="form-select" id="advertiser_id" name="advertiser_id" required>
                        <option value="">Choose Advertiser...</option>
                        <?php mysqli_data_seek($advertisers_result, 0); ?>
                        <?php while ($adv = $advertisers_result->fetch_assoc()): ?>
                            <option value="<?php echo $adv['id']; ?>"><?php echo htmlspecialchars($adv['username']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <!-- KODE YANG DITAMBAHKAN -->
                <div class="mb-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select class="form-select" id="category_id" name="category_id" required>
                        <option value="">Choose Category...</option>
                        <?php while ($cat = $categories_result->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <!-- AKHIR KODE YANG DITAMBAHKAN -->
                
                 <div class="mb-3">
                    <label class="form-label">Ad Format</label>
                    <input type="text" class="form-control" value="Banner" readonly>
                </div>
                <button type="button" class="btn btn-primary" onclick="nextStep(2)">Next <i class="bi bi-arrow-right"></i></button>
            </div>

            <!-- Step 2: Targeting -->
            <div id="step2" class="form-section">
                <h4>Step 2: Targeting Options</h4>
                
                <!-- Countries -->
                <div class="mb-3"><label class="form-label fw-bold">Country</label><br><div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="selectAllCountry" onclick="toggleSelectAll(this, 'countries[]')"><label class="form-check-label" for="selectAllCountry">✅ Select All</label></div><hr class="my-2"><?php foreach ($countries as $item): ?><div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="countries[]" value="<?php echo $item; ?>"><label class="form-check-label"><?php echo $item; ?></label></div><?php endforeach; ?></div>
                <!-- Browsers -->
                <div class="mb-3"><label class="form-label fw-bold">Browser</label><br><div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="selectAllBrowser" onclick="toggleSelectAll(this, 'browsers[]')"><label class="form-check-label" for="selectAllBrowser">✅ Select All</label></div><hr class="my-2"><?php foreach ($browsers as $item): ?><div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="browsers[]" value="<?php echo $item; ?>"><label class="form-check-label"><?php echo $item; ?></label></div><?php endforeach; ?></div>
                <!-- Devices -->
                <div class="mb-3"><label class="form-label fw-bold">Device</label><br><div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="selectAllDevice" onclick="toggleSelectAll(this, 'devices[]')"><label class="form-check-label" for="selectAllDevice">✅ Select All</label></div><hr class="my-2"><?php foreach ($devices as $item): ?><div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="devices[]" value="<?php echo $item; ?>"><label class="form-check-label"><?php echo $item; ?></label></div><?php endforeach; ?></div>
                <!-- OS -->
                <div class="mb-3"><label class="form-label fw-bold">Operating System</label><br><div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="selectAllOS" onclick="toggleSelectAll(this, 'os[]')"><label class="form-check-label" for="selectAllOS">✅ Select All</label></div><hr class="my-2"><?php foreach ($os as $item): ?><div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="os[]" value="<?php echo $item; ?>"><label class="form-check-label"><?php echo $item; ?></label></div><?php endforeach; ?></div>
                <!-- Connections -->
                <div class="mb-3"><label class="form-label fw-bold">Connection Type</label><br><div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="selectAllConnection" onclick="toggleSelectAll(this, 'connections[]')"><label class="form-check-label" for="selectAllConnection">✅ Select All</label></div><hr class="my-2"><?php foreach ($connections as $item): ?><div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="connections[]" value="<?php echo $item; ?>"><label class="form-check-label"><?php echo $item; ?></label></div><?php endforeach; ?></div>

                <button type="button" class="btn btn-secondary" onclick="prevStep(1)"><i class="bi bi-arrow-left"></i> Previous</button>
                <button type="button" class="btn btn-primary" onclick="nextStep(3)">Next <i class="bi bi-arrow-right"></i></button>
            </div>

            <!-- Step 3: Creative -->
            <div id="step3" class="form-section">
                <h4>Step 3: Create Creative</h4>
                <div class="mb-3">
                    <label for="creative_name" class="form-label">Creative Name</label>
                    <input type="text" class="form-control" id="creative_name" name="creative_name" required>
                </div>
                <div class="mb-3">
                    <label for="bid_model" class="form-label">Bid Model (CPC/CPM)</label>
                    <select class="form-select" id="bid_model" name="bid_model" required>
                        <option value="cpc">CPC</option>
                        <option value="cpm">CPM</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="bid_amount" class="form-label">Bid Amount ($)</label>
                    <input type="number" step="0.0001" class="form-control" id="bid_amount" name="bid_amount" required>
                </div>
                <div class="mb-3">
                    <label for="creative_size" class="form-label">Banner Size</label>
                    <select class="form-select" id="creative_size" name="creative_size" required>
                        <option value="">Choose size...</option>
                         <?php foreach ($banner_sizes as $size): ?>
                            <option value="<?php echo $size; ?>"><?php echo $size; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="image_url" class="form-label">Image URL</label>
                    <input type="url" class="form-control" id="image_url" name="image_url" placeholder="https://example.com/banner.jpg" required>
                </div>
                
                <button type="button" class="btn btn-secondary" onclick="prevStep(2)"><i class="bi bi-arrow-left"></i> Previous</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle-fill"></i> Create Campaign</button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentStep = 1;

    function showStep(step) {
        document.querySelectorAll('.form-section').forEach(section => {
            section.classList.remove('active');
        });
        document.getElementById('step' + step).classList.add('active');
    }

    function nextStep(step) {
        // Simple validation for required fields in the current step before proceeding
        let currentForm = document.getElementById('step' + currentStep);
        let inputs = currentForm.querySelectorAll('input[required], select[required]');
        let valid = true;
        for(let i=0; i < inputs.length; i++) {
            if(!inputs[i].value) {
                inputs[i].classList.add('is-invalid');
                valid = false;
            } else {
                inputs[i].classList.remove('is-invalid');
            }
        }
        
        if (valid) {
            currentStep = step;
            showStep(currentStep);
        }
    }

    function prevStep(step) {
        currentStep = step;
        showStep(currentStep);
    }
</script>

<?php require_once __DIR__ . '/templates/footer.php'; ?>

