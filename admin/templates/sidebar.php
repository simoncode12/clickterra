<?php
// File: /admin/templates/sidebar.php (UPDATED for new report page)
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header"><a href="dashboard.php" class="sidebar-brand">AdServer</a></div>
    <div class="sidebar-body">
        <ul class="sidebar-nav">
            <li class="sidebar-item"><a href="dashboard.php" class="sidebar-link"><i class="bi bi-grid-fill"></i><span>Dashboard</span></a></li>
            <li class="sidebar-item-title">MANAGEMENT</li>
            <li class="sidebar-item"><a href="user.php" class="sidebar-link"><i class="bi bi-people-fill"></i><span>Users</span></a></li>
            <li class="sidebar-item">
                <a href="#campaigns-menu" data-bs-toggle="collapse" class="sidebar-link collapsed"><i class="bi bi-megaphone-fill"></i><span>Campaigns</span><i class="bi bi-chevron-down dropdown-icon"></i></a>
                <ul id="campaigns-menu" class="sidebar-dropdown list-unstyled collapse">
                    <li class="sidebar-item"><a href="campaigns.php" class="sidebar-link">Manage Campaigns</a></li>
                    <li class="sidebar-item"><a href="campaigns-create.php" class="sidebar-link">Create New</a></li>
                </ul>
            </li>
            <li class="sidebar-item-title">PUBLISHER & SUPPLY</li>
            <li class="sidebar-item"><a href="site.php" class="sidebar-link"><i class="bi bi-globe"></i><span>Sites & Zones</span></a></li>
            <li class="sidebar-item"><a href="supply-partners.php" class="sidebar-link"><i class="bi bi-broadcast"></i><span>RTB Supply Partners</span></a></li>
            
            <li class="sidebar-item-title">PARTNERS & REPORTS</li>
            <li class="sidebar-item"><a href="ssp.php" class="sidebar-link"><i class="bi bi-person-badge-fill"></i><span>Demand Partners</span></a></li>
            <li class="sidebar-item">
                <a href="campaigns-report.php" class="sidebar-link"><i class="bi bi-bar-chart-line-fill"></i><span>Campaign Reports</span></a>
            </li>
            
            <li class="sidebar-item-title">SETTINGS</li>
            <li class="sidebar-item"><a href="category.php" class="sidebar-link"><i class="bi bi-tags-fill"></i><span>Categories</span></a></li>
        </ul>
    </div>
</aside>
