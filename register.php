<?php
include 'includes/config.php';

$message = "";

if(isset($_POST['register'])){

    $email = $_POST['email'];
    $password = $_POST['password'];

    // Encrypt password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users(email, password)
            VALUES('$email', '$hashed_password')";

    if(mysqli_query($conn, $sql)){
        $message = "Registration Successful!";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - ReUnite</title>
</head>
<body>

<h2>Student Registration</h2>

<p><?php echo $message; ?></p>

<form method="POST">

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit" name="register">
        Register
    </button>

</form>

</body>
</html>