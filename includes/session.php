<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: /reunite/login.php");
        exit();
    }
}

function require_admin()
{
    require_login();

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: /reunite/student/dashboard.php");
        exit();
    }
}

function require_student()
{
    require_login();

    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: /reunite/admin/dashboard.php");
        exit();
    }
}
?>
