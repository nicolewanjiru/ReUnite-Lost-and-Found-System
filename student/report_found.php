<?php
include '../includes/session.php';
include '../includes/config.php';
require_student();

$message = "";

if(isset($_POST['submit'])){

    $user_id = $_SESSION['user_id'];
    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $date_reported = mysqli_real_escape_string($conn, $_POST['date_reported']);

    $sql = "INSERT INTO items (user_id, item_name, description, category, location, status, date_reported)
            VALUES ('$user_id', '$item_name', '$description', 'found', '$location', 'pending', '$date_reported')";

    if(mysqli_query($conn, $sql)){
        $message = "Found item submitted successfully!";
    } else {
        $message = "Error submitting report!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Report Found Item - ReUnite</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <div class="panel">
        <h1>Report Found Item</h1>
        <p>Help return items to their owners.</p>

        <?php if($message != ""): ?>
            <p class="notice success"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="item_name" placeholder="Item Name" required>
            <textarea name="description" placeholder="Describe the item..." required></textarea>
            <input type="text" name="location" placeholder="Where you found it" required>
            <input type="date" name="date_reported" required>

            <button type="submit" name="submit" class="btn">Submit Report</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>
