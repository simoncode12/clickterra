<?php
// File: /publisher/templates/header.php (UPDATED to display dynamic logo)

require_once __DIR__ . '/../init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publisher Portal</title>
    <?php $favicon_path = get_setting('site_favicon', $conn); ?>
    <?php if ($favicon_path && file_exists(__DIR__ . '/../../' . $favicon_path)): ?>
        <link rel="icon" href="../<?php echo htmlspecialchars($favicon_path); ?>" type="image/x-icon">
    <?php endif; ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <?php
                // Mengambil path logo dari pengaturan
                $logo_path_pub = get_setting('site_logo', $conn);
                // Cek apakah file logo benar-benar ada di server
                if ($logo_path_pub && file_exists(__DIR__ . '/../../' . $logo_path_pub)) {
                    // Tampilkan gambar logo
                    echo '<img src="../' . htmlspecialchars($logo_path_pub) . '" alt="Site Logo" style="max-height: 30px; width: auto;">';
                } else {
                    // Tampilkan teks default jika logo tidak ada
                    echo 'Publisher Portal';
                }
                ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publisherNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="publisherNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="statistics.php">Statistics</a></li>
                    <li class="nav-item"><a class="nav-link" href="sites.php">Sites & Zones</a></li>
                    <li class="nav-item"><a class="nav-link" href="withdraw.php">Payments</a></li>
                    <li class="nav-item"><a class="nav-link" href="referrals.php">Referrals</a></li>
                </ul>
                <div class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-fill"></i> Welcome, <strong><?php echo htmlspecialchars($_SESSION['publisher_username']); ?></strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="account.php">Account Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </div>
            </div>
        </div>
    </nav>
    <div class="container mt-4">