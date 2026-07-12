<?php
include '../includes/session.php';
include '../includes/config.php';
include '../includes/matching.php';
include '../includes/notifications.php';
require_student();

$message = "";
$message_class = "success";
$item = null;
$lost_reports = array();
$item_id = isset($_GET['item_id']) ? (int) $_GET['item_id'] : 0;

if(isset($_POST['submit'])){
    $item_id = (int) $_POST['item_id'];
    $lost_item_id = (int) $_POST['lost_item_id'];
    $proof = mysqli_real_escape_string($conn, $_POST['proof']);
    $claimant_id = (int) $_SESSION['user_id'];
    $proof_photo = "";

    // Verify found item
    $found_check = "SELECT * FROM items 
                    WHERE item_id='$item_id' 
                    AND report_type='found' 
                    AND status NOT IN ('matched','donated','returned') 
                    LIMIT 1";
    $found_result = mysqli_query($conn, $found_check);

    // Verify lost item belongs to student
    $lost_check = "SELECT * FROM items 
                   WHERE item_id='$lost_item_id' 
                   AND user_id='$claimant_id' 
                   AND report_type='lost' 
                   AND status NOT IN ('matched','donated','returned') 
                   LIMIT 1";
    $lost_result = mysqli_query($conn, $lost_check);

    // Check duplicate claim
    $duplicate_check = "SELECT claim_id FROM claims 
                        WHERE item_id='$item_id' 
                        AND claimant_id='$claimant_id' 
                        AND status='pending' 
                        LIMIT 1";
    $duplicate_result = mysqli_query($conn, $duplicate_check);

    // File upload handling
    if(isset($_FILES['proof_photo']) && $_FILES['proof_photo']['error'] !== UPLOAD_ERR_NO_FILE){
        $allowed_types = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
        $tmp_name = $_FILES['proof_photo']['tmp_name'];
        $file_size = (int) $_FILES['proof_photo']['size'];
        $mime_type = mime_content_type($tmp_name);

        if(!isset($allowed_types[$mime_type])){
            $message = "Proof photo must be JPG, PNG, or WEBP.";
            $message_class = "error";
        } elseif($file_size > 2 * 1024 * 1024){
            $message = "Proof photo must be smaller than 2MB.";
            $message_class = "error";
        } else {
            $file_name = "claim_" . $claimant_id . "_" . time() . "." . $allowed_types[$mime_type];
            $target_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "claims";
            $target_path = $target_dir . DIRECTORY_SEPARATOR . $file_name;

            if(move_uploaded_file($tmp_name, $target_path)){
                $proof_photo = "uploads/claims/" . $file_name;
            } else {
                $message = "Proof photo could not be uploaded.";
                $message_class = "error";
            }
        }
    }

    if($message_class !== "error"){
        if(!$found_result || mysqli_num_rows($found_result) !== 1) {
            $message = "This found item is not available for claiming.";
            $message_class = "error";
        } elseif(!$lost_result || mysqli_num_rows($lost_result) !== 1) {
            $message = "Choose one of your active lost reports before submitting a claim.";
            $message_class = "error";
        } elseif($duplicate_result && mysqli_num_rows($duplicate_result) > 0) {
            $message = "You already have a pending claim for this item.";
            $message_class = "error";
        } else {
            $found_item = mysqli_fetch_assoc($found_result);
            $lost_item = mysqli_fetch_assoc($lost_result);
            $match_score = calculate_match_score($lost_item, $found_item, $_POST['proof']);
            $safe_photo = mysqli_real_escape_string($conn, $proof_photo);

            $insert_sql = "INSERT INTO claims (item_id, lost_item_id, claimant_id, user_id, proof, proof_photo, match_score, status)
                           VALUES ('$item_id', '$lost_item_id', '$claimant_id', '$claimant_id', '$proof', '$safe_photo', '$match_score', 'pending')";

            if(mysqli_query($conn, $insert_sql)){
                $message = "Claim submitted for administrator review.";
                $message_class = "success";
                $item_id = 0;

                // Notify all admins
                $admin_query = mysqli_query($conn, "SELECT user_id FROM users WHERE role='admin'");
                if ($admin_query) {
                    while ($admin = mysqli_fetch_assoc($admin_query)) {
                        $admin_id = $admin['user_id'];
                        $claim_link = "/reunite/admin/dashboard.php";
                        $msg = "A new claim has been submitted for item: " . $found_item['item_name'] . " by " . $_SESSION['email'];
                        add_notification($conn, $admin_id, $msg, $claim_link);
                    }
                }
            } else {
                $message = "Unable to submit claim.";
                $message_class = "error";
            }
        }
    }
}

// Fetch found item details
if($item_id > 0){
    $item_sql = "SELECT * FROM items 
                 WHERE item_id='$item_id' 
                 AND report_type='found' 
                 AND status NOT IN ('matched','donated','returned') 
                 LIMIT 1";
    $item_result = mysqli_query($conn, $item_sql);

    if($item_result && mysqli_num_rows($item_result) == 1){
        $item = mysqli_fetch_assoc($item_result);
    } else {
        header("Location: search_items.php");
        exit();
    }
}


$user_id = (int) $_SESSION['user_id'];
$lost_sql = "SELECT * FROM items 
             WHERE user_id='$user_id' 
             AND report_type='lost' 
             AND status NOT IN ('matched','donated','returned') 
             ORDER BY date_reported DESC";
$lost_result = mysqli_query($conn, $lost_sql);

if($lost_result) {
    while($lost = mysqli_fetch_assoc($lost_result)) {
        $lost_reports[] = $lost;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Claim Item - ReUnite</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <div class="panel">
        <div class="page-heading">
            <span class="ui-icon">C</span>
            <div>
                <h1>Claim Item</h1>
                <p>Submit private proof of ownership. The administrator will review your claim.</p>
            </div>
        </div>

        <?php if($message != ""): ?>
            <p class="notice <?php echo $message_class; ?>"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if($item): ?>
            <div class="item-card single-item">
                <div>
                    <h3>Found Item Details</h3>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($item['item_name']); ?></p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($item['category']); ?></p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($item['description']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($item['date_reported']); ?></p>
                </div>
            </div>

            <?php if(count($lost_reports) > 0): ?>
                <form method="POST" enctype="multipart/form-data" class="claim-form">
                    <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">

                    <label for="lost_item_id">Which of your lost reports matches this found item?</label>
                    <select id="lost_item_id" name="lost_item_id" required>
                        <?php foreach($lost_reports as $lost): ?>
                            <option value="<?php echo $lost['item_id']; ?>">
                                <?php echo htmlspecialchars($lost['item_name']); ?> – <?php echo htmlspecialchars($lost['location']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="proof">Private proof of ownership</label>
                    <textarea id="proof" name="proof" placeholder="Add details only the true owner would know..." required></textarea>

                    <label for="proof_photo">Upload proof photo (optional)</label>
                    <input id="proof_photo" type="file" name="proof_photo" accept="image/jpeg,image/png,image/webp">
                    <p class="form-note">JPG, PNG, or WEBP. Max 2MB.</p>

                    <button type="submit" name="submit" class="btn">Submit Claim</button>
                </form>
            <?php else: ?>
                <p class="empty-state">You don't have any active lost reports. Please report your lost item first.</p>
                <a class="text-link" href="report_lost.php">Report a Lost Item</a>
            <?php endif; ?>
        <?php else: ?>
            
        <?php endif; ?>

        <a class="text-link" href="search_items.php">← Back to Catalog</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>