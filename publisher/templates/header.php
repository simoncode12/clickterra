<?php
// File: /publisher/templates/header.php (REBUILT FOR NEW LAYOUT)
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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <?php require_once __DIR__ . '/sidebar.php'; ?>
        <div class="main-content" id="main-content">
            <header class="top-header shadow-sm">
                <button class="btn btn-light d-lg-none" type="button" id="sidebar-toggle">
                    <i class="bi bi-list"></i>
                </button>
                <div class="ms-auto dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> Welcome, <strong><?php echo htmlspecialchars($_SESSION['publisher_username']); ?></strong>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="account.php"><i class="bi bi-gear-fill me-2"></i>Account Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </header>
            <main class="content">