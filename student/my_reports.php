<?php
include '../includes/session.php';
include '../includes/config.php';
require_student();

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM items
        WHERE user_id='$user_id'
        ORDER BY date_reported DESC";

$result = mysqli_query($conn, $sql);

$claims_sql = "SELECT c.*, f.item_name AS found_name, f.location AS found_location
               FROM claims c
               JOIN items f ON c.item_id = f.item_id
               WHERE c.claimant_id='$user_id'
               ORDER BY c.date_claimed DESC";

$claims_result = mysqli_query($conn, $claims_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Reports - ReUnite</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <div class="panel">
        <div class="page-heading">
            <span class="ui-icon">Y</span>
            <div>
                <h1>My Reports</h1>
                <p>All your lost/found submissions and claim decisions.</p>
            </div>
        </div>

        <div class="item-list">
            <?php if($result && mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="item-card">
                        <div>
                            <h3><?php echo htmlspecialchars($row['item_name']); ?></h3>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                            <p><strong>Date:</strong> <?php echo htmlspecialchars($row['date_reported']); ?></p>
                            <p><strong>Type:</strong> <?php echo ucfirst(htmlspecialchars($row['category'])); ?></p>
                        </div>

                        <span class="status-badge status-<?php echo htmlspecialchars($row['status']); ?>">
                            <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                        </span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="empty-state">No reports found yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel">
        <div class="section-title">
            <span class="ui-icon">Q</span>
            <div>
                <h2>My Claims</h2>
                <p>Track ownership claims submitted for admin verification.</p>
            </div>
        </div>

        <div class="item-list">
            <?php if($claims_result && mysqli_num_rows($claims_result) > 0): ?>
                <?php while($claim = mysqli_fetch_assoc($claims_result)): ?>
                    <div class="item-card">
                        <div>
                            <div class="item-title-row">
                                <h3><?php echo htmlspecialchars($claim['found_name']); ?></h3>
                                <span class="status-badge status-<?php echo htmlspecialchars($claim['status']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($claim['status'])); ?>
                                </span>
                            </div>
                            <p><strong>Found Location:</strong> <?php echo htmlspecialchars($claim['found_location']); ?></p>
                            <?php if(!empty($claim['admin_note'])): ?>
                                <p><strong>Admin Note:</strong> <?php echo htmlspecialchars($claim['admin_note']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="empty-state">No claims submitted yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>
