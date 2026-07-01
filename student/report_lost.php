<?php
session_start();
include '../includes/config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$message = "";

if(isset($_POST['submit'])){

    $user_id = $_SESSION['user_id'];
    $item_name = $_POST['item_name'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $location = $_POST['location'];
    $date_reported = date('Y-m-d');

    $sql = "INSERT INTO items
            (user_id, item_name, description,
             category, location, status, date_reported)

            VALUES
            ('$user_id', '$item_name', '$description',
             '$category', '$location', 'Lost',
             '$date_reported')";

    if(mysqli_query($conn, $sql)){
        $message = "Lost item reported successfully!";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Report Lost Item</title>
</head>
<body>

<h2>Report Lost Item</h2>

<p><?php echo $message; ?></p>

<form method="POST">

<label>Item Name:</label><br>
<input type="text" name="item_name" required><br><br>

<label>Description:</label><br>
<textarea name="description" required></textarea><br><br>

<label>Category:</label><br>
<select name="category">
    <option>Electronics</option>
    <option>Documents</option>
    <option>Clothing</option>
    <option>Accessories</option>
    <option>Other</option>
</select><br><br>

<label>Location Lost:</label><br>
<input type="text" name="location" required><br><br>

<button type="submit" name="submit">
    Submit Report
</button>

</form>

<br>

<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>