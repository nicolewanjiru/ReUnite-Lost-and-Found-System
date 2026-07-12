<?php
include '../includes/session.php';
include '../includes/config.php';
include '../includes/notifications.php';
require_admin();

$message = "";
$message_class = "";

// Broadcast notification to all students
if (isset($_POST['broadcast'])) {
    $broadcast_msg = mysqli_real_escape_string($conn, $_POST['broadcast_msg']);
    $link = mysqli_real_escape_string($conn, $_POST['broadcast_link'] ?? '');
    if (!empty($broadcast_msg)) {
        $students = mysqli_query($conn, "SELECT user_id FROM users WHERE role='student'");
        if ($students) {
            while ($s = mysqli_fetch_assoc($students)) {
                add_notification($conn, $s['user_id'], $broadcast_msg, $link);
            }
            $message = "Broadcast sent to all students.";
            $message_class = "success";
        } else {
            $message = "No students found.";
            $message_class = "warning";
        }
    } else {
        $message = "Message cannot be empty.";
        $message_class = "error";
    }
}

// Filter notifications by user or type
$user_filter = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$where = array("1=1");
if ($user_filter > 0) {
    $where[] = "user_id=$user_filter";
}
$sql = "SELECT n.*, u.email FROM notifications n JOIN users u ON n.user_id = u.user_id WHERE " . implode(" AND ", $where) . " ORDER BY n.date_sent DESC LIMIT 100";
$notifs = mysqli_query($conn, $sql);

$page_title = "Notifications - ReUnite";
$page_heading = "Notifications";
$page_description = "View all system notifications and broadcast to students.";
ob_start();
if(isset($_GET['message'])): ?>
    <p class="notice warning"><?php echo htmlspecialchars($_GET['message']); ?></p>
<?php endif; ?>
<!-- Broadcast Form -->
<div class="panel" style="margin-bottom:20px;background:rgba(255,255,255,0.05);">
    <h3>Broadcast to All Students</h3>
    <form method="POST">
        <textarea name="broadcast_msg" placeholder="Enter broadcast message..." required></textarea>
        <input type="text" name="broadcast_link" placeholder="Optional link (e.g., /student/dashboard.php)">
        <button type="submit" name="broadcast" class="btn">Send Broadcast</button>
    </form>
</div>
<!-- Notifications List -->
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Message</th>
                <th>Date</th>
                <th>Read</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($notifs && mysqli_num_rows($notifs) > 0): ?>
                <?php while ($n = mysqli_fetch_assoc($notifs)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($n['email']); ?></td>
                        <td><?php echo htmlspecialchars($n['message']); ?></td>
                        <td><?php echo date('d M Y, H:i', strtotime($n['date_sent'])); ?></td>
                        <td><?php echo $n['is_read'] ? 'Yes' : 'No'; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" class="empty-state">No notifications found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$page_content = ob_get_clean();
include 'includes/page_template.php';
?>