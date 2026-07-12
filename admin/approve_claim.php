<?php
include '../includes/session.php';
include '../includes/config.php';
require_admin();


header("Location: dashboard.php?message=Please+use+the+dashboard+to+approve+claims");
exit();
?>