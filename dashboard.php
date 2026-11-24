<?php
require __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user = currentUser();
if ($user === null) {
    session_unset();
    session_destroy();
    redirect('login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
                <a href="dashboard.php">Dashboard</a>
                <a href="profile.php">Profile</a>
                <a href="events.php">Events</a>
                <a href="resources.php">Resources</a>
                <a href="forum.php">Forum</a>
                <a href="mentorship.php">Mentorship</a>
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="admin.php">Admin</a>
                <?php endif; ?>
                <a href="logout.php" class="button-link">Logout</a>
            </nav>
        </div>
    </header>

    <main class="dashboard-wrap">
        <div class="container">
            <section class="dashboard-card">
                <div class="user-identity">
                    <div>
                        <p class="eyebrow">Authenticated user</p>
                        <h1>Welcome, <?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></h1>
                    </div>
                    <span class="badge">Account Status: Active</span>
                </div>

                <div class="user-grid">
                    <div class="info-card">
                        <h3>Email</h3>
                        <strong><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    </div>

                    <div class="info-card">
                        <h3>Member Since</h3>
                        <strong><?php echo htmlspecialchars(date('d M Y', strtotime($user['created_at'])), ENT_QUOTES, 'UTF-8'); ?></strong>
                    </div>
                </div>
            </section>

            <section class="summary-grid">
                <div class="summary-card">
                    <h3>Learning Progress</h3>
                    <p>Continue building digital confidence through structured mentor sessions and workshops.</p>
                </div>

                <div class="summary-card">
                    <h3>Community Support</h3>
                    <p>Stay connected with local programmes that promote education and skill-building opportunities.</p>
                </div>

                <div class="summary-card">
                    <h3>Next Steps</h3>
                    <p>Review support resources, update your profile, and keep growing in the community ecosystem.</p>
                </div>
            </section>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>© 2026 Django Girls Sierra Leone. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
