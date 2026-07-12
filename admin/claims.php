<?php
include '../includes/session.php';
include '../includes/config.php';
include '../includes/notifications.php';
require_admin();

$message = "";
$message_class = "";


if (isset($_POST['approve_claim'])) {
    $claim_id = (int) $_POST['claim_id'];
    $item_id = (int) $_POST['item_id'];
    $admin_note = mysqli_real_escape_string($conn, $_POST['admin_note']);

    $claim_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT claimant_id FROM claims WHERE claim_id=$claim_id"));
    $item_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT item_name FROM items WHERE item_id=$item_id"));
    $claimant_id = $claim_info ? $claim_info['claimant_id'] : 0;
    $item_name = $item_info ? $item_info['item_name'] : 'item';

    $claim_sql = "UPDATE claims SET status='approved', admin_note='$admin_note', date_decided=NOW() WHERE claim_id='$claim_id' AND status='pending'";
    $item_sql = "UPDATE items SET status='matched' WHERE item_id='$item_id'";
    $other_claims_sql = "UPDATE claims SET status='rejected', admin_note='Another claim was approved for this item.', date_decided=NOW() WHERE item_id='$item_id' AND claim_id<>'$claim_id' AND status='pending'";

    if (mysqli_query($conn, $claim_sql) && mysqli_query($conn, $item_sql) && mysqli_query($conn, $other_claims_sql)) {
        $message = "Claim approved and item marked as matched.";
        $message_class = "success";
        if ($claimant_id > 0) {
            $msg = "Your claim for '$item_name' has been approved. The item is now marked as matched.";
            add_notification($conn, $claimant_id, $msg, "/reunite/student/my_reports.php");
        }
    } else {
        $message = "Unable to approve claim.";
        $message_class = "error";
    }
}

if (isset($_POST['reject_claim'])) {
    $claim_id = (int) $_POST['claim_id'];
    $admin_note = mysqli_real_escape_string($conn, $_POST['admin_note']);

    $claim_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT claimant_id FROM claims WHERE claim_id=$claim_id"));
    $item_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT item_name FROM items WHERE item_id=(SELECT item_id FROM claims WHERE claim_id=$claim_id)"));
    $claimant_id = $claim_info ? $claim_info['claimant_id'] : 0;
    $item_name = $item_info ? $item_info['item_name'] : 'item';

    $sql = "UPDATE claims SET status='rejected', admin_note='$admin_note', date_decided=NOW() WHERE claim_id='$claim_id' AND status='pending'";
    if (mysqli_query($conn, $sql)) {
        $message = "Claim rejected.";
        $message_class = "success";
        if ($claimant_id > 0) {
            $msg = "Your claim for '$item_name' has been rejected. Reason: " . $admin_note;
            add_notification($conn, $claimant_id, $msg, "/reunite/student/my_reports.php");
        }
    } else {
        $message = "Unable to reject claim.";
        $message_class = "error";
    }
}

// Filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where = array("1=1");
if ($status_filter !== 'all') {
    $safe_status = mysqli_real_escape_string($conn, $status_filter);
    $where[] = "c.status='$safe_status'";
}
if ($search !== '') {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $where[] = "(f.item_name LIKE '%$safe_search%' OR f.description LIKE '%$safe_search%' OR u.email LIKE '%$safe_search%')";
}

$sql = "SELECT c.*, f.item_name AS found_name, f.description AS found_description, f.location AS found_location,
               l.item_name AS lost_name, l.description AS lost_description, l.location AS lost_location,
               u.email AS claimant_email
        FROM claims c
        JOIN items f ON c.item_id = f.item_id
        LEFT JOIN items l ON c.lost_item_id = l.item_id
        LEFT JOIN users u ON c.claimant_id = u.user_id
        WHERE " . implode(" AND ", $where) . "
        ORDER BY FIELD(c.status, 'pending', 'approved', 'rejected'), c.date_claimed DESC";

$claims = mysqli_query($conn, $sql);

$page_title = "Manage Claims - ReUnite";
$page_heading = "Manage Claims";
$page_description = "View and manage all student claims.";
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="admin-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="admin-main">
        <div class="container wide-container">
            <div class="panel">
                <div class="page-heading">
                    <span class="ui-icon"></span>
                    <div>
                        <h1><?php echo $page_heading; ?></h1>
                        <p><?php echo $page_description; ?></p>
                    </div>
                </div>

                <?php if($message != ""): ?>
                    <p class="notice <?php echo $message_class; ?>"><?php echo htmlspecialchars($message); ?></p>
                <?php endif; ?>

                <!-- Filters -->
                <form method="GET" class="catalog-filters" style="margin-bottom:20px;">
                    <input type="text" name="search" placeholder="Search by item or claimant email..." value="<?php echo htmlspecialchars($search); ?>">
                    <select name="status">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                    <button type="submit" class="btn btn-small">Filter</button>
                    <a href="claims.php" class="btn btn-small btn-secondary">Reset</a>
                </form>

                
                <div class="item-list">
                    <?php if ($claims && mysqli_num_rows($claims) > 0): ?>
                        <?php while ($claim = mysqli_fetch_assoc($claims)): ?>
                            <div class="claim-card">
                                <div class="claim-main">
                                    <div>
                                        <div class="item-title-row">
                                            <h3><?php echo htmlspecialchars($claim['found_name']); ?></h3>
                                            <span class="score-pill <?php echo $claim['match_score'] >= 80 ? 'score-high' : 'score-low'; ?>">
                                                <?php echo htmlspecialchars($claim['match_score']); ?>% match
                                            </span>
                                            <span class="status-badge status-<?php echo htmlspecialchars($claim['status']); ?>">
                                                <?php echo ucfirst(htmlspecialchars($claim['status'])); ?>
                                            </span>
                                        </div>
                                        <p><strong>Claimant:</strong> <?php echo htmlspecialchars($claim['claimant_email'] ?? 'Unknown'); ?></p>
                                        <p><strong>Found item:</strong> <?php echo htmlspecialchars($claim['found_description']); ?> at <?php echo htmlspecialchars($claim['found_location']); ?></p>
                                        <p><strong>Lost report:</strong> <?php echo htmlspecialchars($claim['lost_name'] ?? 'Not linked'); ?> - <?php echo htmlspecialchars($claim['lost_description'] ?? ''); ?></p>
                                        <p><strong>Private proof:</strong> <?php echo htmlspecialchars($claim['proof']); ?></p>
                                        <?php if(!empty($claim['proof_photo'])): ?>
                                            <p><strong>Proof photo:</strong> <a class="text-link" href="../<?php echo htmlspecialchars($claim['proof_photo']); ?>" target="_blank">Open uploaded photo</a></p>
                                            <img class="proof-thumb" src="../<?php echo htmlspecialchars($claim['proof_photo']); ?>" alt="Claim proof photo">
                                        <?php endif; ?>
                                        <?php if(!empty($claim['admin_note'])): ?>
                                            <p><strong>Admin note:</strong> <?php echo htmlspecialchars($claim['admin_note']); ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <?php if($claim['status'] == 'pending'): ?>
                                        <form method="POST" class="decision-form">
                                            <input type="hidden" name="claim_id" value="<?php echo $claim['claim_id']; ?>">
                                            <input type="hidden" name="item_id" value="<?php echo $claim['item_id']; ?>">
                                            <textarea name="admin_note" placeholder="Decision note for this claim"></textarea>
                                            <div class="inline-form">
                                                <button type="submit" name="approve_claim" class="btn btn-small">Approve Claim</button>
                                                <button type="submit" name="reject_claim" class="btn btn-small btn-danger">Reject Claim</button>
                                            </div>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="empty-state">No claims match the filters.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>