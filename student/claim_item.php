<?php
session_start();
include '../includes/config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$message = "";

// Check if item ID was passed from search page
if(isset($_GET['item_id'])){
    $item_id = $_GET['item_id'];
} else {
    die("No item selected.");
}

if(isset($_POST['submit'])){

    $item_id = $_POST['item_id'];
    $user_id = $_SESSION['user_id'];
    $proof = $_POST['proof'];
    $claim_date = date('Y-m-d');

    $sql = "INSERT INTO claims
            (item_id, user_id, proof, claim_date)
            VALUES
            ('$item_id', '$user_id', '$proof', '$claim_date')";

    if(mysqli_query($conn, $sql)){
        $message = "Claim submitted successfully!";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Claim Item</title>
</head>
<body>

<h2>Claim Item</h2>

<p><?php echo $message; ?></p>

<form method="POST">

<input type="hidden"
       name="item_id"
       value="<?php echo $item_id; ?>">

<label>Proof of Ownership:</label><br>
<textarea name="proof" required></textarea><br><br>

<button type="submit" name="submit">
    Submit Claim
</button>

</form>

<br>

<a href="search_items.php">Back to Search</a>

</body>
</html>