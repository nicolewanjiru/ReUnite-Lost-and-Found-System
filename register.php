<?php
include 'includes/session.php';
include 'includes/config.php';

$message = "";

if(isset($_POST['register'])){

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = "student";

    $sql = "INSERT INTO users (email, password, role)
            VALUES ('$email', '$password', '$role')";

    if(mysqli_query($conn, $sql)){
        $message = "Account created successfully!";
    } else {
        $message = "Error creating account!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - ReUnite</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="container">
    <div class="panel auth-panel">
        <h1>Create Account</h1>

        <?php if($message != ""): ?>
            <p class="notice success"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit" name="register" class="btn">Register</button>
        </form>

        <p class="form-note">Already have an account? <a href="login.php">Login</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>
