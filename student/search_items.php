<?php
include '../includes/session.php';
include '../includes/config.php';
require_student();

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : "";
$category_filter = isset($_GET['category']) ? $_GET['category'] : "all";
$report_type_filter = isset($_GET['report_type']) ? $_GET['report_type'] : "all";

$where = array("1=1");

// Keyword search (name, description, location)
if($keyword !== ""){
    $safe_keyword = mysqli_real_escape_string($conn, $keyword);
    $where[] = "(item_name LIKE '%$safe_keyword%' OR description LIKE '%$safe_keyword%' OR location LIKE '%$safe_keyword%')";
}


if($category_filter !== "all"){
    $safe_category = mysqli_real_escape_string($conn, $category_filter);
    $where[] = "category='$safe_category'";
}


if($report_type_filter !== "all"){
    $safe_type = mysqli_real_escape_string($conn, $report_type_filter);
    $where[] = "report_type='$safe_type'";
}



$sql = "SELECT * FROM items
        WHERE " . implode(" AND ", $where) . "
        ORDER BY date_reported DESC";

$items = mysqli_query($conn, $sql);


$categories = mysqli_query($conn, "SELECT DISTINCT category FROM items WHERE category<>'' ORDER BY category ASC");


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
                <p>Browse all lost and found reports. Found items can be claimed if you believe they are yours.</p>
            </div>
        </div>

        <form method="GET" class="catalog-filters">
            <input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Search by name, description, or location">

            <select name="category">
                <option value="all" <?php echo $category_filter === "all" ? "selected" : ""; ?>>All Categories</option>
                <?php if($categories): ?>
                    <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $category_filter === $cat['category'] ? "selected" : ""; ?>>
                            <?php echo htmlspecialchars($cat['category']); ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>

            <select name="report_type">
                <option value="all" <?php echo $report_type_filter === "all" ? "selected" : ""; ?>>Lost & Found</option>
                <option value="lost" <?php echo $report_type_filter === "lost" ? "selected" : ""; ?>>Lost</option>
                <option value="found" <?php echo $report_type_filter === "found" ? "selected" : ""; ?>>Found</option>
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
                        <!-- Display report type as badge -->
                        <span class="category-chip <?php echo ($row['report_type'] === 'lost') ? 'category-lost' : 'category-found'; ?>">
                            <?php echo ucfirst(htmlspecialchars($row['report_type'])); ?>
                        </span>
                       
                        <span class="muted" style="font-size:0.9rem;">
                            <?php echo htmlspecialchars($row['category'] ?? 'Other'); ?>
                        </span>
                    </div>

                    <h3><?php echo htmlspecialchars($row['item_name']); ?></h3>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($row['date_reported']); ?></p>

                    <?php
                    
                    $can_claim = ($row['report_type'] === 'found' && 
                                  $row['status'] !== 'matched' && 
                                  $row['status'] !== 'donated' && 
                                  $row['status'] !== 'returned');
                    ?>
                    <?php if($can_claim): ?>
                        <a class="btn btn-small" href="claim_item.php?item_id=<?php echo $row['item_id']; ?>">Claim Item</a>
                    <?php elseif($row['report_type'] === 'found'): ?>
                        <span class="muted">Already claimed</span>
                    <?php else: ?>
                        <span class="muted"></span>
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