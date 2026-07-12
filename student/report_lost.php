<?php
include '../includes/session.php';
include '../includes/config.php';
include '../includes/notifications.php';
require_student();

$message = "";
$message_class = "success";

if(isset($_POST['submit'])){

    $user_id = $_SESSION['user_id'];
    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $date_reported = mysqli_real_escape_string($conn, $_POST['date_reported']);

    $valid_categories = ['Electronics','Clothing','Documents','Keys','Bags','Accessories','Other'];
    if (!in_array($category, $valid_categories)) {
        $category = 'Other';
    }

    $sql = "INSERT INTO items (user_id, item_name, description, category, report_type, location, status, date_reported)
            VALUES ('$user_id', '$item_name', '$description', '$category', 'lost', '$location', 'pending', '$date_reported')";

    if(mysqli_query($conn, $sql)){
        $message = "Lost item reported successfully!";
        $message_class = "success";

        // Notify all admins
        $admin_query = mysqli_query($conn, "SELECT user_id FROM users WHERE role='admin'");
        if ($admin_query) {
            while ($admin = mysqli_fetch_assoc($admin_query)) {
                $admin_id = $admin['user_id'];
                $item_link = "/reunite/admin/dashboard.php";
                $msg = "A new lost item report has been submitted: $item_name";
                add_notification($conn, $admin_id, $msg, $item_link);
            }
        }
    } else {
        $message = "Error submitting report!";
        $message_class = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Report Lost Item - ReUnite</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <div class="panel">
        <h1>Report Lost Item</h1>
        <p>Fill in the details below. The item will appear in the catalogue immediately.</p>

        <?php if($message != ""): ?>
            <p class="notice <?php echo $message_class; ?>"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="item_name" placeholder="Item Name" required>
            <textarea name="description" placeholder="Description" required></textarea>

            <label for="category">Category (Type of Item)</label>
            <select name="category" id="category" required>
                <option value="Electronics">Electronics</option>
                <option value="Clothing">Clothing</option>
                <option value="Documents">Documents</option>
                <option value="Keys">Keys</option>
                <option value="Bags">Bags</option>
                <option value="Accessories">Accessories</option>
                <option value="Other">Other</option>
            </select>

            <input type="text" name="location" placeholder="Last Seen Location" required>
            <input type="date" name="date_reported" required>

            <button type="submit" name="submit" class="btn">Submit Report</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>