<?php
include '../includes/session.php';
include '../includes/config.php';
require_admin();

$message = "";
$message_class = "";

// Handle actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$item_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Delete item
if ($action === 'delete' && $item_id > 0) {
    $del_sql = "DELETE FROM items WHERE item_id=$item_id";
    if (mysqli_query($conn, $del_sql)) {
        $message = "Item deleted successfully.";
        $message_class = "success";
    } else {
        $message = "Unable to delete item.";
        $message_class = "error";
    }
}

// Donate item -> set status to archived and record date
if ($action === 'donate' && $item_id > 0) {
    $donate_sql = "UPDATE items SET status='archived', archived_at=NOW() WHERE item_id=$item_id";
    if (mysqli_query($conn, $donate_sql)) {
        $message = "Item marked as donated and archived. It can no longer be edited.";
        $message_class = "success";
    } else {
        $message = "Unable to mark as donated.";
        $message_class = "error";
    }
}

// Handle edit form submission – block if archived
if (isset($_POST['edit_submit'])) {
    $edit_id = (int) $_POST['item_id'];
    
    // Check if item is archived
    $check = mysqli_query($conn, "SELECT status FROM items WHERE item_id=$edit_id");
    $row = mysqli_fetch_assoc($check);
    if ($row && $row['status'] === 'archived') {
        $message = "Cannot edit an archived item.";
        $message_class = "error";
    } else {
        $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $location = mysqli_real_escape_string($conn, $_POST['location']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);

        $update_sql = "UPDATE items SET 
                       item_name='$item_name', 
                       description='$description', 
                       category='$category', 
                       location='$location', 
                       status='$status' 
                       WHERE item_id=$edit_id";
        if (mysqli_query($conn, $update_sql)) {
            $message = "Item updated successfully.";
            $message_class = "success";
        } else {
            $message = "Unable to update item.";
            $message_class = "error";
        }
    }
}

// Filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$report_type_filter = isset($_GET['report_type']) ? $_GET['report_type'] : 'all';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';

$where = array("1=1");
if ($search !== '') {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $where[] = "(item_name LIKE '%$safe_search%' OR description LIKE '%$safe_search%' OR location LIKE '%$safe_search%')";
}
if ($report_type_filter !== 'all') {
    $safe_type = mysqli_real_escape_string($conn, $report_type_filter);
    $where[] = "report_type='$safe_type'";
}
if ($status_filter !== 'all') {
    $safe_status = mysqli_real_escape_string($conn, $status_filter);
    $where[] = "status='$safe_status'";
}
if ($category_filter !== 'all') {
    $safe_cat = mysqli_real_escape_string($conn, $category_filter);
    $where[] = "category='$safe_cat'";
}

$sql = "SELECT * FROM items WHERE " . implode(" AND ", $where) . " ORDER BY date_reported DESC";
$items = mysqli_query($conn, $sql);

// For edit modal, fetch item details if requested
$edit_item = null;
if (isset($_GET['edit']) && (int)$_GET['edit'] > 0) {
    $edit_id = (int) $_GET['edit'];
    $edit_result = mysqli_query($conn, "SELECT * FROM items WHERE item_id=$edit_id");
    if ($edit_result && mysqli_num_rows($edit_result) == 1) {
        $edit_item = mysqli_fetch_assoc($edit_result);
    }
}

$page_title = "Manage Listings - ReUnite";
$page_heading = "Manage Listings";
$page_description = "View, edit, delete, or mark items as donated.";
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
                    <input type="text" name="search" placeholder="Search items..." value="<?php echo htmlspecialchars($search); ?>">
                    <select name="report_type">
                        <option value="all" <?php echo $report_type_filter === 'all' ? 'selected' : ''; ?>>All Types</option>
                        <option value="lost" <?php echo $report_type_filter === 'lost' ? 'selected' : ''; ?>>Lost</option>
                        <option value="found" <?php echo $report_type_filter === 'found' ? 'selected' : ''; ?>>Found</option>
                    </select>
                    <select name="status">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="matched" <?php echo $status_filter === 'matched' ? 'selected' : ''; ?>>Matched</option>
                        <option value="archived" <?php echo $status_filter === 'archived' ? 'selected' : ''; ?>>Archived (Donated)</option>
                        <option value="returned" <?php echo $status_filter === 'returned' ? 'selected' : ''; ?>>Returned</option>
                    </select>
                    <select name="category">
                        <option value="all" <?php echo $category_filter === 'all' ? 'selected' : ''; ?>>All Categories</option>
                        <?php
                        $cats = mysqli_query($conn, "SELECT DISTINCT category FROM items WHERE category<>'' ORDER BY category");
                        if ($cats) {
                            while ($c = mysqli_fetch_assoc($cats)) {
                                $selected = ($category_filter == $c['category']) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($c['category']) . "\" $selected>" . htmlspecialchars($c['category']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                    <button type="submit" class="btn btn-small">Filter</button>
                    <a href="listings.php" class="btn btn-small btn-secondary">Reset</a>
                </form>

                <!-- Item Table -->
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Item</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($items && mysqli_num_rows($items) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($items)): ?>
                                    <tr>
                                        <td><?php echo $row['item_id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['item_name']); ?></strong>
                                            <span class="muted"><?php echo htmlspecialchars($row['description']); ?></span>
                                        </td>
                                        <td><?php echo ucfirst($row['report_type']); ?></td>
                                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                                        <td><?php echo htmlspecialchars($row['date_reported']); ?></td>
                                        <td><span class="status-badge status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                        <td>
                                            <?php if ($row['status'] !== 'archived'): ?>
                                                <a href="listings.php?edit=<?php echo $row['item_id']; ?>" class="btn btn-small">Edit</a>
                                            <?php else: ?>
                                                <span class="muted">Locked</span>
                                            <?php endif; ?>

                                            <?php if ($row['status'] !== 'archived' && $row['status'] !== 'matched'): ?>
                                                <a href="listings.php?action=donate&id=<?php echo $row['item_id']; ?>" class="btn btn-small btn-secondary" onclick="return confirm('Mark this item as donated?')">Donate</a>
                                            <?php endif; ?>

                                            <a href="listings.php?action=delete&id=<?php echo $row['item_id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Delete this item permanently?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="empty-state">No items found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Edit Modal -->
<?php if ($edit_item): ?>
    <?php if ($edit_item['status'] === 'archived'): ?>
        <div class="modal-overlay" id="editModal" style="display:flex;">
            <div class="modal-content">
                <h2>Cannot Edit Archived Item</h2>
                <p>This item has been donated and is locked. No changes are allowed.</p>
                <a href="listings.php" class="btn btn-secondary">Go Back</a>
            </div>
        </div>
    <?php else: ?>
        <div class="modal-overlay" id="editModal" style="display:flex;">
            <div class="modal-content">
                <h2>Edit Item</h2>
                <form method="POST">
                    <input type="hidden" name="item_id" value="<?php echo $edit_item['item_id']; ?>">
                    <input type="text" name="item_name" value="<?php echo htmlspecialchars($edit_item['item_name']); ?>" required>
                    <textarea name="description" required><?php echo htmlspecialchars($edit_item['description']); ?></textarea>
                    <select name="category" required>
                        <?php
                        $categories = ['Electronics','Clothing','Documents','Keys','Bags','Accessories','Other'];
                        foreach ($categories as $cat) {
                            $sel = ($cat == $edit_item['category']) ? 'selected' : '';
                            echo "<option value=\"$cat\" $sel>$cat</option>";
                        }
                        ?>
                    </select>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($edit_item['location']); ?>" required>
                    <select name="status" required>
                        <?php
                        $statuses = ['pending','matched','archived','returned'];
                        foreach ($statuses as $st) {
                            $sel = ($st == $edit_item['status']) ? 'selected' : '';
                            echo "<option value=\"$st\" $sel>" . ucfirst($st) . "</option>";
                        }
                        ?>
                    </select>
                    <div style="display:flex; gap:10px; margin-top:10px;">
                        <button type="submit" name="edit_submit" class="btn">Update</button>
                        <a href="listings.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
    <style>
    .modal-overlay {
        position: fixed;
        top:0; left:0; width:100%; height:100%;
        background: rgba(0,0,0,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    .modal-content {
        background: #1e293b;
        padding: 30px;
        border-radius: 12px;
        max-width: 600px;
        width: 90%;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .modal-content h2 { color: #fff; margin-bottom: 20px; }
    .modal-content input, .modal-content textarea, .modal-content select {
        background: #0f172a;
        color: #f1f5f9;
        border: 1px solid #334155;
        margin-bottom: 10px;
    }
    </style>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
</body>
</html>