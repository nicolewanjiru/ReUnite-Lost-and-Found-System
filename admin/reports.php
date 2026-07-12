<?php
include '../includes/session.php';
include '../includes/config.php';
require_admin();

// Fetch statistics
$summary = mysqli_fetch_assoc(mysqli_query($conn, "SELECT
    COUNT(*) AS total_items,
    SUM(report_type='lost') AS lost_items,
    SUM(report_type='found') AS found_items,
    SUM(status='matched') AS matched_items,
    SUM(status='donated') AS donated_items,
    SUM(status='returned') AS returned_items
    FROM items"));

$claim_summary = mysqli_fetch_assoc(mysqli_query($conn, "SELECT
    COUNT(*) AS total_claims,
    SUM(status='pending') AS pending_claims,
    SUM(status='approved') AS approved_claims,
    SUM(status='rejected') AS rejected_claims,
    ROUND(AVG(match_score), 2) AS avg_score
    FROM claims"));

// Monthly activity (last 12 months)
$monthly_activity = mysqli_query($conn, "SELECT DATE_FORMAT(date_reported, '%Y-%m') AS month_label,
                                               SUM(report_type='lost') AS lost_total,
                                               SUM(report_type='found') AS found_total,
                                               SUM(status='matched') AS matched_total,
                                               SUM(status='donated') AS donated_total,
                                               SUM(status='returned') AS returned_total
                                        FROM items
                                        GROUP BY DATE_FORMAT(date_reported, '%Y-%m')
                                        ORDER BY month_label DESC
                                        LIMIT 12");

// Top categories
$top_categories = mysqli_query($conn, "SELECT category, COUNT(*) AS total FROM items GROUP BY category ORDER BY total DESC LIMIT 5");

$page_title = "Reports & Analytics - ReUnite";
$page_heading = "Reports & Analytics";
$page_description = "Detailed statistics and charts for recovery planning.";
ob_start();
?>
<div class="stats-grid" style="margin-bottom:20px;">
    <div class="stat-card"><span>Total Items</span><strong><?php echo $summary['total_items']; ?></strong></div>
    <div class="stat-card"><span>Lost</span><strong><?php echo $summary['lost_items']; ?></strong></div>
    <div class="stat-card"><span>Found</span><strong><?php echo $summary['found_items']; ?></strong></div>
    <div class="stat-card"><span>Matched</span><strong><?php echo $summary['matched_items']; ?></strong></div>
    <div class="stat-card"><span>Donated</span><strong><?php echo $summary['donated_items']; ?></strong></div>
    <div class="stat-card"><span>Returned</span><strong><?php echo $summary['returned_items']; ?></strong></div>
</div>
<div class="stats-grid" style="margin-bottom:20px;">
    <div class="stat-card"><span>Total Claims</span><strong><?php echo $claim_summary['total_claims']; ?></strong></div>
    <div class="stat-card"><span>Pending</span><strong><?php echo $claim_summary['pending_claims']; ?></strong></div>
    <div class="stat-card"><span>Approved</span><strong><?php echo $claim_summary['approved_claims']; ?></strong></div>
    <div class="stat-card"><span>Rejected</span><strong><?php echo $claim_summary['rejected_claims']; ?></strong></div>
    <div class="stat-card"><span>Avg Score</span><strong><?php echo $claim_summary['avg_score']; ?>%</strong></div>
</div>
<!-- Monthly Activity Table with Export -->
<div class="table-wrap">
    <table id="monthlyTable">
        <thead><tr><th>Month</th><th>Lost</th><th>Found</th><th>Matched</th><th>Donated</th><th>Returned</th></tr></thead>
        <tbody>
            <?php if ($monthly_activity && mysqli_num_rows($monthly_activity) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($monthly_activity)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['month_label']); ?></td>
                        <td><?php echo $row['lost_total']; ?></td>
                        <td><?php echo $row['found_total']; ?></td>
                        <td><?php echo $row['matched_total']; ?></td>
                        <td><?php echo $row['donated_total']; ?></td>
                        <td><?php echo $row['returned_total']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="empty-state">No data.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <button onclick="exportCSV()" class="btn btn-small" style="margin-top:10px;">Export CSV</button>
</div>
<script>
function exportCSV() {
    let csv = 'Month,Lost,Found,Matched,Donated,Returned\n';
    document.querySelectorAll('#monthlyTable tbody tr').forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length === 6) {
            csv += [...cells].map(cell => cell.innerText.trim()).join(',') + '\n';
        }
    });
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'monthly_activity.csv';
    a.click();
    URL.revokeObjectURL(url);
}
</script>
<?php
$page_content = ob_get_clean();
include 'includes/page_template.php';
?>