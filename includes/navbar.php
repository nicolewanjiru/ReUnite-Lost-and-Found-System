<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base = "/reunite";
$role = $_SESSION['role'] ?? null;
?>

<nav class="site-nav">
    <a class="nav-left" href="<?php echo $base; ?>/index.php">ReUnite</a>

    <div class="nav-right">
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
