<?php
include 'includes/session.php';
include 'includes/config.php';

$message = "";

if(isset($_POST['login'])){

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if($result && mysqli_num_rows($result) == 1){

        $user = mysqli_fetch_assoc($result);

        if(password_verify($password, $user['password'])){

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];

            if($user['role'] == 'admin'){
                header("Location: admin/dashboard.php");
            } else {
                header("Location: student/dashboard.php");
            }

            exit();

        } else {
            $message = "Incorrect password!";
        }

    } else {
        $message = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - ReUnite</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="container">
    <div class="panel auth-panel">
        <h1>Login</h1>

        <?php if($message != ""): ?>
            <p class="notice error"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit" name="login" class="btn">Login</button>
        </form>

        <p class="form-note">
            Don't have an account?
            <a href="register.php">Register</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>
