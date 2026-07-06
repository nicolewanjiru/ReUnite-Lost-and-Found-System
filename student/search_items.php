<?php
include '../includes/session.php';
include '../includes/config.php';
require_student();

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : "";
$category_filter = isset($_GET['category']) ? $_GET['category'] : "all";
$status_filter = isset($_GET['status']) ? $_GET['status'] : "all";
$location_filter = isset($_GET['location']) ? trim($_GET['location']) : "";

$where = array("1=1");

if($keyword !== ""){
    $safe_keyword = mysqli_real_escape_string($conn, $keyword);
    $where[] = "(item_name LIKE '%$safe_keyword%' OR description LIKE '%$safe_keyword%' OR location LIKE '%$safe_keyword%')";
}

if($category_filter === "lost" || $category_filter === "found"){
    $safe_category = mysqli_real_escape_string($conn, $category_filter);
    $where[] = "category='$safe_category'";
}

if($status_filter === "pending" || $status_filter === "approved" || $status_filter === "matched"){
    $safe_status = mysqli_real_escape_string($conn, $status_filter);
    $where[] = "status='$safe_status'";
}

if($location_filter !== ""){
    $safe_location = mysqli_real_escape_string($conn, $location_filter);
    $where[] = "location LIKE '%$safe_location%'";
}

$sql = "SELECT * FROM items
        WHERE " . implode(" AND ", $where) . "
        ORDER BY category ASC, date_reported DESC";

$items = mysqli_query($conn, $sql);

$locations = mysqli_query($conn, "SELECT DISTINCT location FROM items WHERE location<>'' ORDER BY location ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Item Catalog - ReUnite</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<?php include '../includes/navbar.php'; ?>

<div class="container wide-container">
    <div class="panel">
        <div class="page-heading">
            <span class="ui-icon">S</span>
            <div>
                <h1>Item Catalog</h1>
                <p>Browse all logged lost and found items. Sensitive verification details and match scores are kept for administrators only.</p>
            </div>
        </div>

        <form method="GET" class="catalog-filters">
            <input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Search by item, public description, or location">

            <select name="category">
                <option value="all" <?php echo $category_filter === "all" ? "selected" : ""; ?>>All categories</option>
                <option value="lost" <?php echo $category_filter === "lost" ? "selected" : ""; ?>>Lost items</option>
                <option value="found" <?php echo $category_filter === "found" ? "selected" : ""; ?>>Found items</option>
            </select>

            <select name="status">
                <option value="all" <?php echo $status_filter === "all" ? "selected" : ""; ?>>All statuses</option>
                <option value="pending" <?php echo $status_filter === "pending" ? "selected" : ""; ?>>Pending</option>
                <option value="approved" <?php echo $status_filter === "approved" ? "selected" : ""; ?>>Approved</option>
                <option value="matched" <?php echo $status_filter === "matched" ? "selected" : ""; ?>>Matched</option>
            </select>

            <select name="location">
                <option value="">All locations</option>
                <?php if($locations): ?>
                    <?php while($location = mysqli_fetch_assoc($locations)): ?>
                        <option value="<?php echo htmlspecialchars($location['location']); ?>" <?php echo $location_filter === $location['location'] ? "selected" : ""; ?>>
                            <?php echo htmlspecialchars($location['location']); ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>

            <button type="submit" class="btn">Filter</button>
            <a href="search_items.php" class="btn btn-secondary">Reset</a>
        </form>
    </div>

    <div class="catalog-grid">
        <?php if($items && mysqli_num_rows($items) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($items)): ?>
                <div class="catalog-card">
                    <div class="item-title-row">
                        <span class="category-chip category-<?php echo htmlspecialchars($row['category']); ?>">
                            <?php echo ucfirst(htmlspecialchars($row['category'])); ?>
                        </span>
                        <span class="status-badge status-<?php echo htmlspecialchars($row['status']); ?>">
                            <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                        </span>
                    </div>

                    <h3><?php echo htmlspecialchars($row['item_name']); ?></h3>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($row['date_reported']); ?></p>

                    <?php if($row['category'] === 'found' && $row['status'] === 'approved'): ?>
                        <a class="btn btn-small" href="claim_item.php?item_id=<?php echo $row['item_id']; ?>">Claim Found Item</a>
                    <?php elseif($row['category'] === 'lost'): ?>
                        <span class="muted">Lost report only</span>
                    <?php else: ?>
                        <span class="muted">Claim unavailable until approved</span>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="panel">
                <p class="empty-state">No items match those filters.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>
