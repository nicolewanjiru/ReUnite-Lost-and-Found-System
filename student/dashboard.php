<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
</head>
<body>

<h1>Student Dashboard</h1>

<p>Welcome, <?php echo $_SESSION['email']; ?></p>

<hr>

<h3>Menu</h3>

<ul>
    <li><a href="report_lost.php">Report Lost Item</a></li>
    <li><a href="search_items.php">Search Items</a></li>
    <li><a href="claim_item.php">Claim Item</a></li>
    <li><a href="../logout.php">Logout</a></li>
    
</ul>

</body>
</html>