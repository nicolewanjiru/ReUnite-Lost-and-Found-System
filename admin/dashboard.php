<?php
include '../includes/session.php';
include '../includes/config.php';
include '../includes/notifications.php';
require_admin();

$message = "";
$message_class = "";

// Handle Claim Approval
if(isset($_POST['approve_claim'])){
    $claim_id = (int) $_POST['claim_id'];
    $item_id = (int) $_POST['item_id'];
    $admin_note = mysqli_real_escape_string($conn, $_POST['admin_note']);

    // Fetch claim details to get claimant_id and lost_item_id
    $claim_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT claimant_id, lost_item_id FROM claims WHERE claim_id=$claim_id"));
    $claimant_id = $claim_info ? $claim_info['claimant_id'] : 0;
    $lost_item_id = $claim_info ? $claim_info['lost_item_id'] : 0;

    // Fetch item name for notification
    $item_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT item_name FROM items WHERE item_id=$item_id"));
    $item_name = $item_info ? $item_info['item_name'] : 'item';

    $claim_sql = "UPDATE claims
                  SET status='approved', admin_note='$admin_note', date_decided=NOW()
                  WHERE claim_id='$claim_id'
                  AND status='pending'";

    $item_sql = "UPDATE items SET status='matched' WHERE item_id='$item_id'";

    // update the linked lost item to 'matched'
    $lost_item_sql = "";
    if ($lost_item_id > 0) {
        $lost_item_sql = "UPDATE items SET status='matched' WHERE item_id='$lost_item_id'";
    }

    $other_claims_sql = "UPDATE claims
                         SET status='rejected', admin_note='Another claim was approved for this item.', date_decided=NOW()
                         WHERE item_id='$item_id'
                         AND claim_id<>'$claim_id'
                         AND status='pending'";

   // Execute all queries
    $all_ok = mysqli_query($conn, $claim_sql) && mysqli_query($conn, $item_sql);
    if ($lost_item_sql) {
        $all_ok = $all_ok && mysqli_query($conn, $lost_item_sql);
    }
    $all_ok = $all_ok && mysqli_query($conn, $other_claims_sql);

    if ($all_ok) {
        $message = "Claim approved and items marked as matched.";
        $message_class = "success";

        // Notify claimant
        if ($claimant_id > 0) {
            $msg = "Your claim for '$item_name' has been approved! Please visit the Lost & Found Office (Room 104, Student Centre) to collect your item. Bring your student ID.";
            // Fixed 
            add_notification($conn, $claimant_id, $msg, "student/view_item.php?id=" . $item_id);
        }
    } else {
        $message = "Unable to approve claim.";
        $message_class = "error";
    }
}

// Handle Claim Rejection
if(isset($_POST['reject_claim'])){
    $claim_id = (int) $_POST['claim_id'];
    $admin_note = mysqli_real_escape_string($conn, $_POST['admin_note']);

    // Fetch claim and item details for notification before updating
    $claim_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT claimant_id, item_id FROM claims WHERE claim_id=$claim_id")); // ← Added item_id here
    $claimant_id = $claim_info ? $claim_info['claimant_id'] : 0;
    $item_id = $claim_info ? $claim_info['item_id'] : 0; // ← NEW: Get the item_id
    
    $item_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT item_name FROM items WHERE item_id=$item_id")); // ← Use $item_id
    $item_name = $item_info ? $item_info['item_name'] : 'item';

    $sql = "UPDATE claims
            SET status='rejected', admin_note='$admin_note', date_decided=NOW()
            WHERE claim_id='$claim_id'
            AND status='pending'";

    if(mysqli_query($conn, $sql)){
        $message = "Claim rejected.";
        $message_class = "success";

        // Notify claimant
        if ($claimant_id > 0) {
            $msg = "Your claim for '$item_name' has been rejected. Reason: " . $admin_note;
            // fixed 
            add_notification($conn, $claimant_id, $msg, "student/view_item.php?id=" . $item_id);
        }
    } else {
        $message = "Unable to reject claim.";
        $message_class = "error";
    }
}

// Fetch all claims
$claims = mysqli_query($conn, "SELECT c.*, f.item_name AS found_name, f.description AS found_description, f.location AS found_location,
                                      l.item_name AS lost_name, l.description AS lost_description, l.location AS lost_location,
                                      u.email AS claimant_email
                               FROM claims c
                               JOIN items f ON c.item_id = f.item_id
                               LEFT JOIN items l ON c.lost_item_id = l.item_id
                               LEFT JOIN users u ON c.claimant_id = u.user_id
                               ORDER BY FIELD(c.status, 'pending', 'approved', 'rejected'), c.date_claimed DESC");

// Statistics
$summary = mysqli_fetch_assoc(mysqli_query($conn, "SELECT
    COUNT(*) AS total_items,
    SUM(report_type='lost') AS lost_items,
    SUM(report_type='found') AS found_items,
    SUM(status='matched') AS matched_items
    FROM items"));

$claim_summary = mysqli_fetch_assoc(mysqli_query($conn, "SELECT
    COUNT(*) AS total_claims,
    SUM(status='pending') AS pending_claims,
    SUM(status='approved') AS approved_claims,
    SUM(status='rejected') AS rejected_claims,
    ROUND(AVG(match_score), 2) AS avg_score
    FROM claims"));

// Top lost items
$top_lost = mysqli_query($conn, "SELECT item_name, COUNT(*) AS total
                                FROM items
                                WHERE report_type='lost'
                                GROUP BY item_name
                                ORDER BY total DESC, item_name ASC
                                LIMIT 5");

// Location hotspots
$location_hotspots = mysqli_query($conn, "SELECT location, COUNT(*) AS total
                                         FROM items
                                         WHERE location<>''
                                         GROUP BY location
                                         ORDER BY total DESC, location ASC
                                         LIMIT 5");

// Monthly activity
$monthly_activity = mysqli_query($conn, "SELECT DATE_FORMAT(date_reported, '%Y-%m') AS month_label,
                                               SUM(report_type='lost') AS lost_total,
                                               SUM(report_type='found') AS found_total,
                                               SUM(status='matched') AS matched_total
                                        FROM items
                                        GROUP BY DATE_FORMAT(date_reported, '%Y-%m')
                                        ORDER BY month_label DESC
                                        LIMIT 6");

// Category distribution for pie chart
$category_dist = mysqli_query($conn, "SELECT category, COUNT(*) AS total
                                     FROM items
                                     WHERE category IS NOT NULL AND category != ''
                                     GROUP BY category
                                     ORDER BY total DESC");
$category_labels = [];
$category_data = [];
if($category_dist){
    while($row = mysqli_fetch_assoc($category_dist)){
        $category_labels[] = $row['category'];
        $category_data[] = (int)$row['total'];
    }
}
if(empty($category_labels)){
    $category_labels = ['No data'];
    $category_data = [0];
}

// Monthly bar chart data
$monthly_data = [];
if($monthly_activity){
    $rows = [];
    while($row = mysqli_fetch_assoc($monthly_activity)){
        $rows[] = $row;
    }
    $rows = array_reverse($rows);
    foreach($rows as $row){
        $monthly_data['labels'][] = $row['month_label'];
        $monthly_data['lost'][] = (int)$row['lost_total'];
        $monthly_data['found'][] = (int)$row['found_total'];
        $monthly_data['matched'][] = (int)$row['matched_total'];
    }
}
if(empty($monthly_data)){
    $monthly_data['labels'] = ['No data'];
    $monthly_data['lost'] = [0];
    $monthly_data['found'] = [0];
    $monthly_data['matched'] = [0];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - ReUnite</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
                    <span class="ui-icon">A</span>
                    <div>
                        <h1>Admin Dashboard</h1>
                        <p>Review and verify student claims. Use the reports for planning.</p>
                    </div>
                </div>

                <?php if($message != ""): ?>
                    <p class="notice <?php echo htmlspecialchars($message_class); ?>"><?php echo htmlspecialchars($message); ?></p>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <span>Total Items</span>
                        <strong><?php echo (int) ($summary['total_items'] ?? 0); ?></strong>
                    </div>
                    <div class="stat-card">
                        <span>Lost Reports</span>
                        <strong><?php echo (int) ($summary['lost_items'] ?? 0); ?></strong>
                    </div>
                    <div class="stat-card">
                        <span>Found Reports</span>
                        <strong><?php echo (int) ($summary['found_items'] ?? 0); ?></strong>
                    </div>
                    <div class="stat-card">
                        <span>Matched / Claimed</span>
                        <strong><?php echo (int) ($summary['matched_items'] ?? 0); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Pending Claims Section -->
            <div class="panel">
                <div class="section-title">
                    <span class="ui-icon">V</span>
                    <div>
                        <h2>Pending Claims</h2>
                        <p>Review the proof, match score, and lost report to approve or reject.</p>
                    </div>
                </div>

                <div class="item-list">
                    <?php if($claims && mysqli_num_rows($claims) > 0): ?>
                        <?php while($claim = mysqli_fetch_assoc($claims)): ?>
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
                        <p class="empty-state">No claims have been submitted yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Planning Reports -->
            <div class="panel">
                <div class="section-title">
                    <span class="ui-icon">P</span>
                    <div>
                        <h2>Planning Reports</h2>
                        <p>Statistics for recovery planning and service improvement.</p>
                    </div>
                </div>

                
                <div class="reports-grid">
                    <div class="report-card">
                        <h3>Often Lost Items</h3>
                        <?php if($top_lost && mysqli_num_rows($top_lost) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($top_lost)): ?>
                                <div class="report-row">
                                    <span><?php echo htmlspecialchars($row['item_name']); ?></span>
                                    <strong><?php echo (int) $row['total']; ?></strong>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="empty-state">No lost item data yet.</p>
                        <?php endif; ?>
                    </div>

                    <div class="report-card">
                        <h3>Location Hotspots</h3>
                        <?php if($location_hotspots && mysqli_num_rows($location_hotspots) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($location_hotspots)): ?>
                                <div class="report-row">
                                    <span><?php echo htmlspecialchars($row['location']); ?></span>
                                    <strong><?php echo (int) $row['total']; ?></strong>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="empty-state">No location data yet.</p>
                        <?php endif; ?>
                    </div>

                    <div class="report-card">
                        <h3>Claim Outcomes</h3>
                        <div class="report-row"><span>Total Claims</span><strong><?php echo (int) ($claim_summary['total_claims'] ?? 0); ?></strong></div>
                        <div class="report-row"><span>Pending</span><strong><?php echo (int) ($claim_summary['pending_claims'] ?? 0); ?></strong></div>
                        <div class="report-row"><span>Approved</span><strong><?php echo (int) ($claim_summary['approved_claims'] ?? 0); ?></strong></div>
                        <div class="report-row"><span>Rejected</span><strong><?php echo (int) ($claim_summary['rejected_claims'] ?? 0); ?></strong></div>
                        <div class="report-row"><span>Average Score</span><strong><?php echo htmlspecialchars($claim_summary['avg_score'] ?? 0); ?>%</strong></div>
                    </div>
                </div>

            
                <div class="reports-grid" style="margin-top:20px;">
                    <div class="report-card" style="grid-column: span 2;">
                        <h3>Item Category Distribution</h3>
                        <div style="position:relative; height:250px; max-width:400px; margin:0 auto;">
                            <canvas id="categoryPieChart"></canvas>
                        </div>
                    </div>
                    <div class="report-card">
                        <h3>Monthly Activity (Last 6 Months)</h3>
                        <div style="position:relative; height:200px;">
                            <canvas id="monthlyBarChart"></canvas>
                        </div>
                    </div>
                </div>

                
                <div class="table-wrap report-table" style="margin-top:20px;">
                    <table>
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Lost Reports</th>
                                <th>Found Reports</th>
                                <th>Recovered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($monthly_activity && mysqli_num_rows($monthly_activity) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($monthly_activity)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['month_label']); ?></td>
                                        <td><?php echo (int) $row['lost_total']; ?></td>
                                        <td><?php echo (int) $row['found_total']; ?></td>
                                        <td><?php echo (int) $row['matched_total']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="empty-state">No monthly activity yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctxPie = document.getElementById('categoryPieChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($category_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($category_data); ?>,
                backgroundColor: ['#2563eb','#7c3aed','#ec4899','#f59e0b','#10b981','#6366f1','#ef4444'],
                borderColor: '#1e293b',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: '#e2e8f0' } }
            }
        }
    });

    const ctxBar = document.getElementById('monthlyBarChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($monthly_data['labels']); ?>,
            datasets: [
                {
                    label: 'Lost',
                    data: <?php echo json_encode($monthly_data['lost']); ?>,
                    backgroundColor: 'rgba(236, 72, 153, 0.7)',
                    borderColor: '#ec4899',
                    borderWidth: 1
                },
                {
                    label: 'Found',
                    data: <?php echo json_encode($monthly_data['found']); ?>,
                    backgroundColor: 'rgba(37, 99, 235, 0.7)',
                    borderColor: '#2563eb',
                    borderWidth: 1
                },
                {
                    label: 'Recovered',
                    data: <?php echo json_encode($monthly_data['matched']); ?>,
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: '#10b981',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: '#e2e8f0' } }
            },
            scales: {
                x: {
                    ticks: { color: '#cbd5e1' },
                    grid: { color: 'rgba(255,255,255,0.05)' }
                },
                y: {
                    ticks: { color: '#cbd5e1', stepSize: 1 },
                    grid: { color: 'rgba(255,255,255,0.05)' },
                    beginAtZero: true
                }
            }
        }
    });
});
</script>

</body>
</html>