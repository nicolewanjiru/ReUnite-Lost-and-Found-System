<?php
session_start();
include "../includes/config.php";

$message = "";

if(isset($_POST['login'])){

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email' AND role='admin' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if($result && mysqli_num_rows($result) == 1){

        $admin = mysqli_fetch_assoc($result);

        if(password_verify($password, $admin['password'])){

            $_SESSION['user_id'] = $admin['user_id'];
            $_SESSION['role'] = $admin['role'];
            $_SESSION['email'] = $admin['email'];

            header("Location: dashboard.php");
            exit();

        } else {
            $message = "Incorrect password!";
        }

    } else {
        $message = "Administrator account not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - ReUnite</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<?php include "../includes/navbar.php"; ?>

<div class="container">
    <div class="panel auth-panel">
        <h1>Administrator Login</h1>

        <?php if($message != ""): ?>
            <p class="notice error"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="email" placeholder="Admin Email" required>

            <input type="password" name="password" placeholder="Password" required>

            <button type="submit" name="login" class="btn">Login</button>
        </form>

        <p class="form-note">
            Return to <a href="../index.php">Home</a>
        </p>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

</body>
</html>