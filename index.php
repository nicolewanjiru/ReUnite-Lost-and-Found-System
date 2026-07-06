<!DOCTYPE html>
<html>
<head>
    <title>ReUnite</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<section class="hero">
    <div class="hero-card">
        <h1>Welcome to ReUnite</h1>
        <p>Campus lost and found management for students and administrators.</p>

        <div class="actions">
            <a href="login.php" class="btn">Get Started</a>
            <a href="register.php" class="btn btn-secondary">Create Account</a>
        </div>
    </div>
</section>

<main class="container">
    <section class="features">
        <div class="feature-card">
            <h3>Report Items</h3>
            <p>Submit lost or found items with the details campus staff need.</p>
        </div>

        <div class="feature-card">
            <h3>Search Campus</h3>
            <p>Look across approved reports by item name, description, category, or location.</p>
        </div>

        <div class="feature-card">
            <h3>Admin Review</h3>
            <p>Review new reports, approve valid entries, and track matched items.</p>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
