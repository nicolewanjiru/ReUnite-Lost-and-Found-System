<?php

if (!isset($page_title)) $page_title = "Admin - ReUnite";
if (!isset($page_heading)) $page_heading = "Admin Page";
if (!isset($page_content)) $page_content = "<p>Content coming soon.</p>";
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    <main class="admin-main">
        <div class="container wide-container">
            <div class="panel">
                <div class="page-heading">
                    <span class="ui-icon"><?php echo substr($page_heading, 0, 1); ?></span>
                    <div>
                        <h1><?php echo htmlspecialchars($page_heading); ?></h1>
                        <p><?php echo htmlspecialchars($page_description ?? ''); ?></p>
                    </div>
                </div>
                <?php echo $page_content; ?>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>