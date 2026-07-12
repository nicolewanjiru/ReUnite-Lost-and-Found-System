<?php

$base = "/reunite"; 
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="admin-sidebar">
    <div class="sidebar-brand">
        <span class="ui-icon" style="width:36px;height:36px;font-size:1rem;">A</span>
        <span>Admin Panel</span>
    </div>
    <nav class="sidebar-nav">
        <a href="<?php echo $base; ?>/admin/dashboard.php" class="sidebar-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-gauge-high sidebar-icon"></i> Dashboard
        </a>
        <a href="<?php echo $base; ?>/admin/claims.php" class="sidebar-link <?php echo ($current_page == 'claims.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-clipboard-list sidebar-icon"></i> Claims
        </a>
        <a href="<?php echo $base; ?>/admin/reports.php" class="sidebar-link <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-chart-bar sidebar-icon"></i> Reports
        </a>
        <a href="<?php echo $base; ?>/admin/listings.php" class="sidebar-link <?php echo ($current_page == 'listings.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-boxes-stacked sidebar-icon"></i> Listings
        </a>
        <a href="<?php echo $base; ?>/admin/users.php" class="sidebar-link <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-users sidebar-icon"></i> Users
        </a>
        <a href="<?php echo $base; ?>/admin/notifications.php" class="sidebar-link <?php echo ($current_page == 'notifications.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-bell sidebar-icon"></i> Notifications
        </a>
        <a href="<?php echo $base; ?>/admin/settings.php" class="sidebar-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-gear sidebar-icon"></i> Settings
        </a>
        <a href="<?php echo $base; ?>/logout.php" class="sidebar-link logout">
            <i class="fa-solid fa-right-from-bracket sidebar-icon"></i> Logout
        </a>
    </nav>
</aside>