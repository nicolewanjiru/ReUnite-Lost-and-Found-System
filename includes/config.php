<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "reunite_db";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Uncomment the line below to test the connection
// echo "Connected successfully";

?> 