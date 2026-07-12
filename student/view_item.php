<?php
include '../includes/session.php';
include '../includes/config.php';
require_student();

$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($item_id <= 0) {
    header("Location: search_items.php");
    exit();
}

$sql = "SELECT * FROM items WHERE item_id=$item_id";
$result = mysqli_query($conn, $sql);
if (!$result || mysqli_num_rows($result) != 1) {
    header("Location: search_items.php");
    exit();
}
$item = mysqli_fetch_assoc($result);

// Check if current user has an approved claim
$approved_message = '';
if (isset($_SESSION['user_id'])) {
    $user_id = (int) $_SESSION['user_id'];
    $check_claim = mysqli_query($conn, "SELECT claim_id FROM claims WHERE item_id=$item_id AND claimant_id=$user_id AND status='approved' LIMIT 1");
    if ($check_claim && mysqli_num_rows($check_claim) > 0) {
        $approved_message = "Your claim for this item has been approved! Please visit the Lost & Found Office (Room 104, Student Centre) to collect your item. Bring your student ID.";
    }
}

$page_title = "View Item - ReUnite";
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="container">
    <div class="panel">
        <h1><?php echo htmlspecialchars($item['item_name']); ?></h1>
        <p><strong>Category:</strong> <?php echo htmlspecialchars($item['category']); ?></p>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($item['description']); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?></p>
        <p><strong>Reported on:</strong> <?php echo htmlspecialchars($item['date_reported']); ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst($item['status']); ?></p>
        
        <?php if ($approved_message): ?>
            <div class="notice success" style="margin-top:20px; border-left:4px solid #10b981; padding:15px; background:rgba(16,185,129,0.1); border-radius:4px;">
                <?php echo $approved_message; ?>
            </div>
        <?php endif; ?>
        
        <a href="search_items.php" class="text-link">← Back to catalog</a>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>