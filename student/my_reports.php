<?php
include '../includes/session.php';
include '../includes/config.php';
require_student();

$user_id = (int) $_SESSION['user_id'];


$sql = "SELECT * FROM items
        WHERE user_id='$user_id'
        ORDER BY date_reported DESC";
$result = mysqli_query($conn, $sql);


$claims_sql = "SELECT c.*, f.item_name AS found_name, f.category AS found_category, f.location AS found_location,
                      l.item_name AS lost_name, l.category AS lost_category
               FROM claims c
               JOIN items f ON c.item_id = f.item_id
               LEFT JOIN items l ON c.lost_item_id = l.item_id
               WHERE c.claimant_id='$user_id'
               ORDER BY c.date_claimed DESC";
$claims_result = mysqli_query($conn, $claims_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Reports - ReUnite</title>
    <link rel="stylesheet" href="../css/style.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <div class="panel">
        <div class="page-heading">
            
            <div>
                <h1>My Reports</h1>
                <p>All your lost/found submissions and claim history.</p>
            </div>
        </div>

        <!-- My Reports Section -->
        <div class="section-title">
            
            
        </div>

        <div class="item-list">
            <?php if($result && mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="item-card">
                        <div>
                            <div class="item-title-row">
                                <h3><?php echo htmlspecialchars($row['item_name']); ?></h3>
                                <span class="category-chip <?php echo ($row['report_type'] === 'lost') ? 'category-lost' : 'category-found'; ?>">
                                    <?php echo ucfirst(htmlspecialchars($row['report_type'])); ?>
                                </span>
                                <?php if($row['status'] !== 'pending'): ?>
                                    <span class="status-badge status-<?php echo htmlspecialchars($row['status']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p><strong>Category:</strong> <?php echo htmlspecialchars($row['category']); ?></p>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                            <p><strong>Date:</strong> <?php echo htmlspecialchars($row['date_reported']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="empty-state">No reports found yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- My Claims Section -->
    <div class="panel">
        <div class="section-title">
            <span class="ui-icon">Q</span>
            <div>
                <h2>Your Claims</h2>
                <p>Claims you have submitted for found items.</p>
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
                            <p><strong>Found Category:</strong> <?php echo htmlspecialchars($claim['found_category']); ?></p>
                            <p><strong>Found Location:</strong> <?php echo htmlspecialchars($claim['found_location']); ?></p>
                            <?php if(!empty($claim['lost_name'])): ?>
                                <p><strong>Linked Lost Report:</strong> <?php echo htmlspecialchars($claim['lost_name']); ?> (<?php echo htmlspecialchars($claim['lost_category']); ?>)</p>
                            <?php else: ?>
                                <p><strong>Linked Lost Report:</strong> Not linked</p>
                            <?php endif; ?>
                            <p><strong>Match Score:</strong> <?php echo htmlspecialchars($claim['match_score']); ?>%</p>
                            <?php if(!empty($claim['admin_note'])): ?>
                                <p><strong>Admin Note:</strong> <?php echo htmlspecialchars($claim['admin_note']); ?></p>
                            <?php endif; ?>
                            <p><strong>Submitted on:</strong> <?php echo htmlspecialchars($claim['date_claimed']); ?></p>
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