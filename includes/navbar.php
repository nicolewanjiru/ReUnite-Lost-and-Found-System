<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($conn) || !$conn) {
    include_once __DIR__ . '/config.php';
}

$base = "/reunite";
$role = $_SESSION['role'] ?? null;
$email = $_SESSION['email'] ?? null;
$user_id = $_SESSION['user_id'] ?? 0;


if (file_exists(__DIR__ . '/notifications.php')) {
    include_once __DIR__ . '/notifications.php';
    if ($user_id > 0 && function_exists('get_unread_count')) {
        $unread_count = get_unread_count($conn, $user_id);
    } else {
        $unread_count = 0;
    }
} else {
    $unread_count = 0;
}
?>

<nav class="site-nav">
    <a class="nav-left" href="<?php echo $base; ?>/index.php">ReUnite</a>

    <div class="nav-right">
        <!-- Bell Icon with Unread Badge -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="<?php echo $base; ?>/notifications.php" class="nav-bell" title="Notifications">
                <i class="fa-solid fa-bell"></i>
                <?php if ($unread_count > 0): ?>
                    <span class="badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>

        
        <?php if (isset($_SESSION['user_id'])): ?>
            <span class="user-status">
                <span class="status-dot"></span>
                <?php echo htmlspecialchars($email ?? 'User'); ?>
            </span>
        <?php endif; ?>

        <a href="<?php echo $base; ?>/index.php">Home</a>

        <?php if ($role === 'admin'): ?>
            <a href="<?php echo $base; ?>/admin/dashboard.php">Admin</a>
            <a href="<?php echo $base; ?>/logout.php">Logout</a>
        <?php elseif ($role === 'student'): ?>
            <a href="<?php echo $base; ?>/student/dashboard.php">Dashboard</a>
            <a href="<?php echo $base; ?>/student/report_lost.php">Report Lost</a>
            <a href="<?php echo $base; ?>/student/report_found.php">Report Found</a>
            <a href="<?php echo $base; ?>/student/search_items.php">Search</a>
            <a href="<?php echo $base; ?>/student/my_reports.php">My Reports</a>
            <a href="<?php echo $base; ?>/logout.php">Logout</a>
        <?php else: ?>
            <a href="<?php echo $base; ?>/login.php">Login</a>
            <a href="<?php echo $base; ?>/register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>