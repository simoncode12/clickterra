<?php
// Pemanggilan 'require_once __DIR__ . '/../../config/database.php';' telah dihapus dari sini
// karena file init.php sekarang yang bertanggung jawab untuk memuatnya.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdServer Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <?php include_once 'sidebar.php'; ?>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-primary" id="menu-toggle"><i class="bi bi-list"></i></button>
                    <div class="ms-auto">
                        <span class="navbar-text">
                            Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?>
                        </span>
                        <a href="logout.php" class="btn btn-outline-danger ms-2"><i class="bi bi-box-arrow-right"></i> Logout</a>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-4">

