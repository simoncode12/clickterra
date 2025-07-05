<?php
// File: /publisher/templates/sidebar.php (NEW)
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="dashboard.php" class="sidebar-brand">
             <?php
                $logo_path = get_setting('site_logo', $conn);
                // Kita perlu path relatif dari folder publisher ke root
                $logo_display_path = '../' . $logo_path;
                if ($logo_path && file_exists(__DIR__ . '/../../' . $logo_path)) {
                    echo '<img src="' . htmlspecialchars($logo_display_path) . '" alt="Site Logo" style="max-height: 40px; width: auto;">';
                } else {
                    echo 'Publisher';
                }
            ?>
        </a>
    </div>
    <div class="sidebar-body">
        <ul class="sidebar-nav">
            <li class="sidebar-item">
                <a href="dashboard.php" class="sidebar-link <?php if($current_page == 'dashboard.php') echo 'active'; ?>">
                    <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="statistics.php" class="sidebar-link <?php if($current_page == 'statistics.php') echo 'active'; ?>">
                    <i class="bi bi-bar-chart-line-fill"></i><span>Statistics</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="sites.php" class="sidebar-link <?php if($current_page == 'sites.php') echo 'active'; ?>">
                    <i class="bi bi-globe2"></i><span>Sites & Zones</span>
                </a>
            </li>
             <li class="sidebar-item">
                <a href="withdraw.php" class="sidebar-link <?php if($current_page == 'withdraw.php') echo 'active'; ?>">
                    <i class="bi bi-wallet2"></i><span>Payments</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="referrals.php" class="sidebar-link <?php if($current_page == 'referrals.php') echo 'active'; ?>">
                    <i class="bi bi-people-fill"></i><span>Referrals</span>
                </a>
            </li>
        </ul>
    </div>
</aside>