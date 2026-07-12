<?php
include '../includes/session.php';
include '../includes/config.php';
require_student();

$user_id = (int) $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard - ReUnite</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <div class="panel">
        <div class="page-heading">
            <span class="ui-icon">D</span>
            <div>
                <h1>Student Dashboard</h1>
                <p>Report items, browse the catalogue, and track your submissions.</p>
            </div>
        </div>

        <div class="actions action-grid">
            <a class="btn" href="report_lost.php">Report Lost Item</a>
            <a class="btn" href="report_found.php">Report Found Item</a>
            <a class="btn" href="search_items.php">Search Items</a>
            <a class="btn" href="my_reports.php">My Reports</a>
        </div>
    </div>

   
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>