<?php
include 'includes/session.php';
include 'includes/config.php';

$is_logged_in = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>ReUnite – Lost & Found Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .hero-section {
            min-height: 75vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px 20px 50px;
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(37, 99, 235, 0.15), transparent 60%),
                radial-gradient(ellipse at 80% 50%, rgba(236, 72, 153, 0.15), transparent 60%),
                linear-gradient(135deg, #0a1628 0%, #1a1a4e 50%, #2d1b69 100%);
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 40%, rgba(103, 232, 249, 0.04), transparent 40%),
                        radial-gradient(circle at 70% 60%, rgba(236, 72, 153, 0.04), transparent 40%);
            animation: float 20s ease-in-out infinite alternate;
            z-index: 0;
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(20px, -20px) rotate(3deg); }
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 750px;
            padding: 0 20px;
        }

        .hero-badge {
            display: inline-block;
            background: rgba(103, 232, 249, 0.12);
            color: #67e8f9;
            padding: 6px 20px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            border: 1px solid rgba(103, 232, 249, 0.15);
            margin-bottom: 20px;
        }

        .hero-badge i {
            margin-right: 8px;
            font-size: 0.75rem;
        }

        .hero-title {
            font-size: clamp(2.5rem, 6vw, 4.2rem);
            font-weight: 900;
            line-height: 1.08;
            margin-bottom: 14px;
            background: linear-gradient(135deg, #ffffff 0%, #a5b4fc 50%, #f9a8d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-title span {
            display: block;
            -webkit-text-fill-color: transparent;
        }

        .hero-tagline {
            font-size: 1.2rem;
            color: #e2e8f0;
            max-width: 580px;
            margin: 0 auto 16px;
            line-height: 1.6;
            font-weight: 400;
        }

        .hero-tagline i {
            color: #f9a8d4;
            margin: 0 4px;
        }

        .hero-subtitle {
            font-size: 1rem;
            color: #94a3b8;
            max-width: 520px;
            margin: 0 auto 24px;
            line-height: 1.6;
        }

       
        .hero-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            justify-content: center;
            margin-bottom: 12px;
        }

        .hero-buttons .btn {
            min-width: 180px;
            padding: 14px 36px;
            font-size: 1.05rem;
            border-radius: 50px;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid rgba(255,255,255,0.25);
            box-shadow: none;
        }

        .btn-outline:hover {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.4);
            transform: translateY(-2px);
        }

        
        .register-link {
            display: inline-block;
            margin-top: 10px;
            color: #94a3b8;
            font-size: 0.9rem;
            text-decoration: none;
            padding: 6px 16px;
            border-radius: 30px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
            transition: all 0.2s;
        }

        .register-link:hover {
            color: #67e8f9;
            background: rgba(103, 232, 249, 0.08);
            border-color: rgba(103, 232, 249, 0.2);
        }

        .register-link i {
            margin-right: 6px;
        }

        /* Features Section */
        .features-section {
            padding: 30px 20px 40px;
            background: rgba(7, 22, 51, 0.3);
            border-top: 1px solid rgba(255,255,255,0.04);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .feature-item {
            text-align: center;
            padding: 22px 16px 20px;
            background: rgba(255,255,255,0.04);
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.05);
            transition: transform 0.3s ease, border-color 0.3s ease;
        }

        .feature-item:hover {
            transform: translateY(-4px);
            border-color: rgba(103, 232, 249, 0.15);
        }

        .feature-item .icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 1.3rem;
            color: #a5b4fc;
            border: 1px solid rgba(255,255,255,0.06);
        }

        .feature-item h3 {
            color: #ffffff;
            font-size: 1rem;
            margin-bottom: 6px;
        }

        .feature-item p {
            color: #94a3b8;
            font-size: 0.88rem;
            margin: 0;
        }

        @media (max-width: 600px) {
            .hero-section {
                min-height: 60vh;
                padding: 25px 16px 30px;
            }
            .hero-tagline {
                font-size: 1rem;
            }
            .hero-buttons .btn {
                min-width: 140px;
                width: 100%;
            }
            .features-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-content">
       
        <h1 class="hero-title">
            Welcome to ReUnite
            <span>Find what's yours.</span>
        </h1>
        <p class="hero-tagline">
            <i class="fa-solid fa-graduation-cap"></i> College is hard enough.
            You deserve to be <strong>ReUnited</strong>
            <i class="fa-solid fa-heart" style="color:#f472b6;"></i>
            with your belongings.
        </p>
        <p class="hero-subtitle">
            A smart, fast, and convenient way to report lost items,
            browse found items, and reclaim what's yours.
        </p>

        <!-- Primary Buttons -->
        <div class="hero-buttons">
            <?php if ($is_logged_in): ?>
                <?php if ($role === 'admin'): ?>
                    <a href="admin/dashboard.php" class="btn"><i class="fa-solid fa-gauge-high"></i> Admin Dashboard</a>
                <?php else: ?>
                    <a href="student/dashboard.php" class="btn"><i class="fa-solid fa-house"></i> Student Dashboard</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-outline"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            <?php else: ?>
                <!-- Two prominent buttons: Student Login and Admin Login -->
                <a href="login.php" class="btn"><i class="fa-solid fa-user"></i> Student Login</a>
                <a href="admin/login.php" class="btn btn-outline"><i class="fa-solid fa-lock"></i> Admin Login</a>
            <?php endif; ?>
        </div>

       
        <?php if (!$is_logged_in): ?>
            <a href="register.php" class="register-link">
                <i class="fa-solid fa-user-plus"></i> New student? Register here
            </a>
        <?php endif; ?>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="features-grid">
        <div class="feature-item">
            <div class="icon-circle"><i class="fa-solid fa-magnifying-glass"></i></div>
            <h3>Search & Browse</h3>
            <p>Easily search through lost and found items by keyword, location, or category.</p>
        </div>
        <div class="feature-item">
            <div class="icon-circle"><i class="fa-solid fa-mobile-screen-button"></i></div>
            <h3>Report Instantly</h3>
            <p>Lost something? Found something? Report it in seconds from your dashboard.</p>
        </div>
        <div class="feature-item">
            <div class="icon-circle"><i class="fa-solid fa-bell"></i></div>
            <h3>Smart Notifications</h3>
            <p>Get real‑time alerts when your lost item is found or your claim is approved.</p>
        </div>
        <div class="feature-item">
            <div class="icon-circle"><i class="fa-solid fa-shield-halved"></i></div>
            <h3>Secure Verification</h3>
            <p>Private proof and admin review ensure items go back to the right owner.</p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>