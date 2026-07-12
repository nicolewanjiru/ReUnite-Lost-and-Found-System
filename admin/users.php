<?php
include '../includes/session.php';
include '../includes/config.php';
require_admin();

$message = "";
$message_class = "";

// Handle role change
if (isset($_POST['update_role'])) {
    $user_id = (int) $_POST['user_id'];
    $new_role = mysqli_real_escape_string($conn, $_POST['role']);
    if ($new_role === 'admin' || $new_role === 'student') {
        $sql = "UPDATE users SET role='$new_role' WHERE user_id=$user_id AND user_id != " . $_SESSION['user_id'];
        if (mysqli_query($conn, $sql)) {
            $message = "User role updated.";
            $message_class = "success";
        } else {
            $message = "Unable to update user.";
            $message_class = "error";
        }
    }
}

// Handle user deletion (with cascade delete)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $user_id = (int) $_GET['id'];

    // Prevent deleting yourself
    if ($user_id == $_SESSION['user_id']) {
        $message = "You cannot delete your own account.";
        $message_class = "error";
    } else {
        // Step 1: Delete all claims that reference any item owned by this user
        $delete_claims_on_items = "DELETE FROM claims WHERE item_id IN (SELECT item_id FROM items WHERE user_id = $user_id)";
        mysqli_query($conn, $delete_claims_on_items);

        // Step 2: Delete claims made by this user (if any still exist)
        $delete_claims_by_user = "DELETE FROM claims WHERE claimant_id = $user_id";
        mysqli_query($conn, $delete_claims_by_user);

        // Step 3: Delete items owned by this user
        $delete_items = "DELETE FROM items WHERE user_id = $user_id";
        mysqli_query($conn, $delete_items);

        // Step 4: Delete notifications for this user
        $delete_notifs = "DELETE FROM notifications WHERE user_id = $user_id";
        mysqli_query($conn, $delete_notifs);

        // Step 5: Finally, delete the user
        $delete_user = "DELETE FROM users WHERE user_id = $user_id";
        if (mysqli_query($conn, $delete_user)) {
            $message = "User and all associated data deleted.";
            $message_class = "success";
        } else {
            $message = "Unable to delete user – they may still have related records. Please contact support.";
            $message_class = "error";
        }
    }
}

// Filter and list users
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : 'all';

$where = array("1=1");
if ($search !== '') {
    $safe = mysqli_real_escape_string($conn, $search);
    $where[] = "(email LIKE '%$safe%')";
}
if ($role_filter !== 'all') {
    $safe_role = mysqli_real_escape_string($conn, $role_filter);
    $where[] = "role='$safe_role'";
}
$sql = "SELECT * FROM users WHERE " . implode(" AND ", $where) . " ORDER BY user_id DESC";
$users = mysqli_query($conn, $sql);

$page_title = "Manage Users - ReUnite";
$page_heading = "Manage Users";
$page_description = "View, edit roles, or delete student accounts.";
ob_start();
if(isset($_GET['message'])): ?>
    <p class="notice warning"><?php echo htmlspecialchars($_GET['message']); ?></p>
<?php endif; ?>
<?php if($message != ""): ?>
    <p class="notice <?php echo $message_class; ?>"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($users && mysqli_num_rows($users) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td><?php echo $row['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo ucfirst($row['role']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                <select name="role">
                                    <option value="student" <?php echo $row['role']=='student'?'selected':''; ?>>Student</option>
                                    <option value="admin" <?php echo $row['role']=='admin'?'selected':''; ?>>Admin</option>
                                </select>
                                <button type="submit" name="update_role" class="btn btn-small">Update</button>
                            </form>
                            <?php if ($row['user_id'] != $_SESSION['user_id']): ?>
                                <a href="users.php?action=delete&id=<?php echo $row['user_id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('WARNING: This will delete the user, their items, claims, and notifications. Are you sure?')">Delete</a>
                            <?php else: ?>
                                <span class="muted">(You)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" class="empty-state">No users found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$page_content = ob_get_clean();
include 'includes/page_template.php';
?>