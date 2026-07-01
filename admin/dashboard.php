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

<h1>Welcome Student</h1>

<p>You are logged in as:
<?php echo $_SESSION['email']; ?>
</p>

<a href="../logout.php">Logout</a>

</body>
</html>