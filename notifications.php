<?php
include 'includes/session.php';
include 'includes/config.php';
include 'includes/notifications.php';
require_login();

$user_id = (int) $_SESSION['user_id'];


mark_all_read($conn, $user_id);


$notifications_result = get_notifications($conn, $user_id);

$page_title = "Notifications - ReUnite";
$page_heading = "Notifications";
$page_description = "All your notifications in one place.";
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container">
    <div class="panel">
        <div class="page-heading">
            <span class="ui-icon"></span>
            <div>
                <h1><?php echo htmlspecialchars($page_heading); ?></h1>
                <p><?php echo htmlspecialchars($page_description); ?></p>
            </div>
        </div>

        <?php if ($notifications_result && mysqli_num_rows($notifications_result) > 0): ?>
            <div class="item-list">
                <?php while ($notif = mysqli_fetch_assoc($notifications_result)): ?>
                    <div class="item-card <?php echo $notif['is_read'] ? 'read' : 'unread'; ?>" style="<?php echo !$notif['is_read'] ? 'border-left: 4px solid #4a8fe7;' : ''; ?>">
                        <div>
                            <p><?php echo htmlspecialchars($notif['message']); ?></p>
                            <p class="muted" style="font-size:0.85rem;">
                                <?php echo date('d M Y, H:i', strtotime($notif['date_sent'])); ?>
                                <?php if (!$notif['is_read']): ?>
                                    <span class="status-badge status-pending" style="margin-left:10px;">New</span>
                                <?php endif; ?>
                            </p>
                            <?php if (!empty($notif['link'])): ?>
                                <a href="<?php echo htmlspecialchars($notif['link']); ?>" class="text-link" style="margin-top:8px;">View Details →</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="empty-state">You have no notifications yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>