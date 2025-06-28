<?php
// File: /admin/rtb-campaigns.php (Updated with Category)

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
$categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name");

// Muat template header setelah semua data siap
require_once __DIR__ . '/templates/header.php';
?>

<h1 class="mt-4">Create RTB Campaign</h1>
<p>Buat kampanye Real-Time Bidding baru melalui langkah-langkah berikut.</p>

<?php
// Tampilkan pesan sukses atau error jika ada
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
        <form action="rtb-campaigns-action.php" method="POST" id="rtbCampaignForm">
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
                        <?php mysqli_data_seek($categories_result, 0); ?>
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

            <!-- Step 2: Targeting (kode tetap sama) -->
            <div id="step2" class="form-section">
                 <h4>Step 2: Targeting Options</h4>
                <!-- ... Konten targeting ... -->
                <button type="button" class="btn btn-secondary" onclick="prevStep(1)"><i class="bi bi-arrow-left"></i> Previous</button>
                <button type="button" class="btn btn-primary" onclick="nextStep(3)">Next <i class="bi bi-arrow-right"></i></button>
            </div>

            <!-- Step 3: Creative (kode tetap sama) -->
            <div id="step3" class="form-section">
                <h4>Step 3: Create Creative</h4>
                 <!-- ... Konten creative ... -->
                 <div class="mb-3">
                    <label class="form-label fw-bold">Banner Size</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="selectAllSizes" onclick="toggleSelectAll(this, 'creative_sizes[]')">
                        <label class="form-check-label" for="selectAllSizes">✅ Select All</label>
                    </div>
                    <hr class="my-2">
                    <?php foreach ($banner_sizes as $size): ?>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="creative_sizes[]" value="<?php echo $size; ?>">
                        <label class="form-check-label"><?php echo $size; ?></label>
                    </div>
                    <?php endforeach; ?>
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

