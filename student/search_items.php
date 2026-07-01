<?php
session_start();
include '../includes/config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$results = null;

if(isset($_POST['search'])){

    $keyword = $_POST['keyword'];

    $sql = "SELECT * FROM items
            WHERE item_name LIKE '%$keyword%'
            OR description LIKE '%$keyword%'
            OR category LIKE '%$keyword%'
            OR location LIKE '%$keyword%'";

    $results = mysqli_query($conn, $sql);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Items</title>
</head>
<body>

<h2>Search Items</h2>

<form method="POST">

    <input type="text"
           name="keyword"
           placeholder="Enter keyword..."
           required>

    <button type="submit" name="search">
        Search
    </button>

</form>

<hr>

<?php

if($results){

    if(mysqli_num_rows($results) > 0){

        while($row = mysqli_fetch_assoc($results)){

            echo "<h3>".$row['item_name']."</h3>";
echo "<p>Description: ".$row['description']."</p>";
echo "<p>Category: ".$row['category']."</p>";
echo "<p>Location: ".$row['location']."</p>";
echo "<p>Status: ".$row['status']."</p>";

echo "<a href='claim_item.php?item_id=".$row['item_id']."'>
        Claim Item
      </a>";

echo "<hr>";
        }

    } else {

        echo "No matching items found.";

    }
}

?>

<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>