<?php
require __DIR__ . '/db.php';

$loggedIn = isset($_SESSION['user_id']);
$user = $loggedIn ? currentUser() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Django Girls Sierra Leone | Community Platform</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="site-header">
        <div class="container nav">
            <div class="brand">
                <img src="images/django-girls-logo.png" alt="Django Girls Sierra Leone logo">
                <div class="brand-copy">
                    <span class="brand-name">Django Girls</span>
                    <span class="brand-tag">Sierra Leone</span>
                </div>
            </div>

            <nav class="nav-links" aria-label="Main navigation">
                <a href="index.php">Home</a>
                <?php if ($loggedIn && $user): ?>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="logout.php" class="button-link">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="signup.php" class="button-link">Sign Up</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container hero-grid">
                <div>
                    <p class="eyebrow">Django Girls Sierra Leone</p>
                    <h1>Build digital confidence for a stronger future.</h1>
                    <p class="lead">
                        Django Girls Sierra Leone is a community platform designed to help learners, mentors, and volunteers access trusted resources, join workshops, and stay connected with local technology programmes.
                    </p>

                    <div class="button-row">
                        <a class="button primary" href="signup.php">Create an account</a>
                        <a class="button secondary" href="login.php">Login</a>
                    </div>
                </div>

                <div class="hero-image">
                    <img src="images/django-girls-logo.png" alt="Django Girls Sierra Leone">
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <aside class="stat-panel" aria-label="Platform highlights" style="margin-bottom: 4rem;">
                    <div class="stat-grid">
                        <div>
                            <strong>1,200+</strong>
                            <span>Community learners</span>
                        </div>
                        <div>
                            <strong>45</strong>
                            <span>Mentor volunteers</span>
                        </div>
                        <div>
                            <strong>18</strong>
                            <span>Local programmes</span>
                        </div>
                        <div>
                            <strong>24/7</strong>
                            <span>Access portal</span>
                        </div>
                    </div>
                </aside>
                <div class="section-header">
                    <p class="eyebrow">What we offer</p>
                    <h2>Practical tools for learners and community leaders</h2>
                </div>

                <div class="feature-grid">
                    <article class="feature-card">
                        <div class="icon-badge">01</div>
                        <h3>Simple Registration</h3>
                        <p>New users can create a secure account, verify their information, and gain access to their personalised dashboard.</p>
                    </article>

                    <article class="feature-card">
                        <div class="icon-badge">02</div>
                        <h3>Protected Access</h3>
                        <p>Only authenticated users can view the dashboard, with secure sessions and password verification in place.</p>
                    </article>

                    <article class="feature-card">
                        <div class="icon-badge">03</div>
                        <h3>Community Impact</h3>
                        <p>Members can access supporting information that helps them grow skills, connect with mentors, and contribute to the local learning ecosystem.</p>
                    </article>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>© 2026 Django Girls Sierra Leone. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
