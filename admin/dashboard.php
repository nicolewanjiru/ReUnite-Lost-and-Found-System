<?php
include '../includes/session.php';
include '../includes/config.php';
require_admin();

$message = "";
$message_class = "success";

if(isset($_POST['approve_report'])){
    $item_id = (int) $_POST['item_id'];

    $sql = "UPDATE items
            SET status='approved'
            WHERE item_id='$item_id'
            AND status='pending'";

    if(mysqli_query($conn, $sql)){
        $message = "Report approved.";
    } else {
        $message = "Unable to approve report.";
        $message_class = "error";
    }
}

if(isset($_POST['reject_report'])){
    $item_id = (int) $_POST['item_id'];

    $sql = "DELETE FROM items
            WHERE item_id='$item_id'
            AND status='pending'";

    if(mysqli_query($conn, $sql)){
        $message = "Report rejected and removed.";
    } else {
        $message = "Unable to reject report.";
        $message_class = "error";
    }
}

if(isset($_POST['approve_claim'])){
    $claim_id = (int) $_POST['claim_id'];
    $item_id = (int) $_POST['item_id'];
    $admin_note = mysqli_real_escape_string($conn, $_POST['admin_note']);

    $claim_sql = "UPDATE claims
                  SET status='approved', admin_note='$admin_note', date_decided=NOW()
                  WHERE claim_id='$claim_id'
                  AND status='pending'";

    $item_sql = "UPDATE items SET status='matched' WHERE item_id='$item_id'";
    $other_claims_sql = "UPDATE claims
                         SET status='rejected', admin_note='Another claim was approved for this item.', date_decided=NOW()
                         WHERE item_id='$item_id'
                         AND claim_id<>'$claim_id'
                         AND status='pending'";

    if(mysqli_query($conn, $claim_sql) && mysqli_query($conn, $item_sql) && mysqli_query($conn, $other_claims_sql)){
        $message = "Claim approved and item marked as matched.";
    } else {
        $message = "Unable to approve claim.";
        $message_class = "error";
    }
}

if(isset($_POST['reject_claim'])){
    $claim_id = (int) $_POST['claim_id'];
    $admin_note = mysqli_real_escape_string($conn, $_POST['admin_note']);

    $sql = "UPDATE claims
            SET status='rejected', admin_note='$admin_note', date_decided=NOW()
            WHERE claim_id='$claim_id'
            AND status='pending'";

    if(mysqli_query($conn, $sql)){
        $message = "Claim rejected.";
    } else {
        $message = "Unable to reject claim.";
        $message_class = "error";
    }
}

$pending_reports = mysqli_query($conn, "SELECT * FROM items WHERE status='pending' ORDER BY date_reported DESC");
$all_reports = mysqli_query($conn, "SELECT * FROM items ORDER BY date_reported DESC LIMIT 12");

$claims = mysqli_query($conn, "SELECT c.*, f.item_name AS found_name, f.description AS found_description, f.location AS found_location,
                                      l.item_name AS lost_name, l.description AS lost_description, l.location AS lost_location,
                                      u.email AS claimant_email
                               FROM claims c
                               JOIN items f ON c.item_id = f.item_id
                               LEFT JOIN items l ON c.lost_item_id = l.item_id
                               LEFT JOIN users u ON c.claimant_id = u.user_id
                               ORDER BY FIELD(c.status, 'pending', 'approved', 'rejected'), c.date_claimed DESC");

$summary = mysqli_fetch_assoc(mysqli_query($conn, "SELECT
    COUNT(*) AS total_items,
    SUM(category='lost') AS lost_items,
    SUM(category='found') AS found_items,
    SUM(status='matched') AS matched_items
    FROM items"));

$claim_summary = mysqli_fetch_assoc(mysqli_query($conn, "SELECT
    COUNT(*) AS total_claims,
    SUM(status='pending') AS pending_claims,
    SUM(status='approved') AS approved_claims,
    SUM(status='rejected') AS rejected_claims,
    ROUND(AVG(match_score), 2) AS avg_score
    FROM claims"));

$top_lost = mysqli_query($conn, "SELECT item_name, COUNT(*) AS total
                                FROM items
                                WHERE category='lost'
                                GROUP BY item_name
                                ORDER BY total DESC, item_name ASC
                                LIMIT 5");

$location_hotspots = mysqli_query($conn, "SELECT location, COUNT(*) AS total
                                         FROM items
                                         GROUP BY location
                                         ORDER BY total DESC, location ASC
                                         LIMIT 5");

$monthly_activity = mysqli_query($conn, "SELECT DATE_FORMAT(date_reported, '%Y-%m') AS month_label,
                                               SUM(category='lost') AS lost_total,
                                               SUM(category='found') AS found_total,
                                               SUM(status='matched') AS matched_total
                                        FROM items
                                        GROUP BY DATE_FORMAT(date_reported, '%Y-%m')
                                        ORDER BY month_label DESC
                                        LIMIT 6");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - ReUnite</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container wide-container">
    <div class="panel">
        <div class="page-heading">
            <span class="ui-icon">A</span>
            <div>
                <h1>Admin Dashboard</h1>
                <p>Review reports, verify claims, and use system reports for planning.</p>
            </div>
        </div>

        <?php if($message != ""): ?>
            <p class="notice <?php echo $message_class; ?>"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

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
                <span>Recovered</span>
                <strong><?php echo (int) ($summary['matched_items'] ?? 0); ?></strong>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="section-title">
            <span class="ui-icon">V</span>
            <div>
                <h2>Pending Claims</h2>
                <p>Use the proof details and match score to approve strong claims, ideally 80% and above.</p>
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

    <div class="panel">
        <div class="section-title">
            <span class="ui-icon">R</span>
            <div>
                <h2>Report Review</h2>
                <p>Approve valid reports before students can claim them.</p>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($all_reports && mysqli_num_rows($all_reports) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($all_reports)): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['item_name']); ?></strong>
                                    <span><?php echo htmlspecialchars($row['description']); ?></span>
                                </td>
                                <td><?php echo ucfirst(htmlspecialchars($row['category'])); ?></td>
                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                <td><?php echo htmlspecialchars($row['date_reported']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo htmlspecialchars($row['status']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($row['status'] == 'pending'): ?>
                                        <form method="POST" class="inline-form">
                                            <input type="hidden" name="item_id" value="<?php echo $row['item_id']; ?>">
                                            <button type="submit" name="approve_report" class="btn btn-small">Approve</button>
                                            <button type="submit" name="reject_report" class="btn btn-small btn-danger">Reject</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="muted">Reviewed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty-state">No reports found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <div class="section-title">
            <span class="ui-icon">P</span>
            <div>
                <h2>Planning Reports</h2>
                <p>Outputs for statistics, recovery planning, and university service improvement.</p>
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

        <div class="table-wrap report-table">
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

<?php include '../includes/footer.php'; ?>

</body>
</html>
