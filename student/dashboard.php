<?php
include '../includes/session.php';
include '../includes/config.php';
include '../includes/matching.php';
require_student();

$user_id = (int) $_SESSION['user_id'];
$match_alerts = array();

$found_result = mysqli_query($conn, "SELECT * FROM items WHERE category='found' AND status='approved' ORDER BY date_reported DESC");

if($found_result) {
    while($found_item = mysqli_fetch_assoc($found_result)) {
        $best_match = find_best_lost_match($conn, $user_id, $found_item);

        if($best_match['item'] && $best_match['score'] >= 80) {
            $found_item['match_score'] = $best_match['score'];
            $found_item['lost_item_name'] = $best_match['item']['item_name'];
            $match_alerts[] = $found_item;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard - ReUnite</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <div class="panel">
        <div class="page-heading">
            <span class="ui-icon">D</span>
            <div>
                <h1>Student Dashboard</h1>
                <p>Report items, browse the item catalog, and track your submissions.</p>
            </div>
        </div>

        <div class="actions action-grid">
            <a class="btn" href="report_lost.php">Report Lost Item</a>
            <a class="btn" href="report_found.php">Report Found Item</a>
            <a class="btn" href="search_items.php">Search Items</a>
            <a class="btn" href="my_reports.php">My Reports</a>
        </div>
    </div>

    <div class="panel">
        <div class="section-title">
            <span class="ui-icon">M</span>
            <div>
                <h2>Potential Match Alerts</h2>
                <p>Found items that may relate to your lost reports appear here. Exact match scores are reserved for admin verification.</p>
            </div>
        </div>

        <div class="item-list">
            <?php if(count($match_alerts) > 0): ?>
                <?php foreach($match_alerts as $item): ?>
                    <div class="item-card">
                        <div>
                            <div class="item-title-row">
                                <h3><?php echo htmlspecialchars($item['item_name']); ?></h3>
                                <span class="score-pill score-high">Potential match</span>
                            </div>
                            <p><strong>Matched lost report:</strong> <?php echo htmlspecialchars($item['lost_item_name']); ?></p>
                            <p><strong>Found at:</strong> <?php echo htmlspecialchars($item['location']); ?></p>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                        </div>
                        <a class="btn btn-small" href="claim_item.php?item_id=<?php echo $item['item_id']; ?>">Claim</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="empty-state">No high match alerts yet. Report a lost item or search found items manually.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>
