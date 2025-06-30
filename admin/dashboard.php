<?php
// File: /admin/dashboard.php (Updated)

// Muat semua konfigurasi inti dan otentikasi dalam satu langkah
require_once __DIR__ . '/init.php'; 

// Muat template header
require_once __DIR__ . '/templates/header.php';
?>

<h1 class="mt-4">Dashboard</h1>
<p>Selamat datang di panel admin AdServer Anda.</p>
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white mb-4">
            <div class="card-body">Total Campaigns</div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="#">View Details</a>
                <div class="small text-white"><i class="bi bi-chevron-right"></i></div>
            </div>
        </div>
    </div>
    <!-- Tambahkan card statistik lainnya di sini -->
</div>

<!-- Konten dashboard lainnya -->

<?php require_once __DIR__ . '/templates/footer.php'; ?>

