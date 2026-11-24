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

$pdo = getDatabaseConnection();
$userId = (int) $user['id'];
$firstName = explode(' ', trim($user['full_name']))[0];

$upcomingEventCount = (int) $pdo->query(
    'SELECT COUNT(*) FROM events WHERE event_date >= CURRENT_TIMESTAMP'
)->fetchColumn();

$registeredEventCountStmt = $pdo->prepare(
    'SELECT COUNT(*)
     FROM event_registrations registrations
     INNER JOIN events ON events.id = registrations.event_id
     WHERE registrations.user_id = :user_id AND events.event_date >= CURRENT_TIMESTAMP'
);
$registeredEventCountStmt->execute([':user_id' => $userId]);
$registeredEventCount = (int) $registeredEventCountStmt->fetchColumn();

$resourceCount = (int) $pdo->query('SELECT COUNT(*) FROM resources')->fetchColumn();
$discussionCount = (int) $pdo->query('SELECT COUNT(*) FROM forum_topics')->fetchColumn();

$upcomingEventsStmt = $pdo->prepare(
    'SELECT events.title, events.event_date, events.location,
            registrations.id AS registration_id
     FROM events
     LEFT JOIN event_registrations registrations
        ON registrations.event_id = events.id AND registrations.user_id = :user_id
     WHERE events.event_date >= CURRENT_TIMESTAMP
     ORDER BY events.event_date ASC
     LIMIT 3'
);
$upcomingEventsStmt->execute([':user_id' => $userId]);
$upcomingEvents = $upcomingEventsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Django Girls Sierra Leone</title>
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
                <a href="dashboard.php" aria-current="page">Dashboard</a>
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
            <section class="dashboard-hero">
                <div class="dashboard-hero-copy">
                    <p class="eyebrow">MEMBER DASHBOARD</p>
                    <h1>Welcome back, <?php echo htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8'); ?>.</h1>
                    <p>Find your next workshop, explore learning materials, and stay connected with the Django Girls Sierra Leone community.</p>
                    <div class="button-row">
                        <a class="button primary" href="events.php">Explore events</a>
                        <a class="button secondary" href="resources.php">Browse resources</a>
                    </div>
                </div>
                <aside class="dashboard-hero-member" aria-label="Membership details">
                    <span class="dashboard-member-mark" aria-hidden="true"><?php echo htmlspecialchars(strtoupper(substr($firstName, 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span>
                    <p class="eyebrow">YOUR COMMUNITY</p>
                    <strong><?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    <span>Member since <?php echo htmlspecialchars(date('M Y', strtotime($user['created_at'])), ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="profile.php">View your profile <span aria-hidden="true">→</span></a>
                </aside>
            </section>

            <section class="dashboard-stats" aria-label="Community overview">
                <a class="dashboard-stat" href="events.php">
                    <span class="dashboard-stat-icon events-icon" aria-hidden="true">E</span>
                    <span class="dashboard-stat-copy">
                        <span>Upcoming events</span>
                        <strong><?php echo number_format($upcomingEventCount); ?></strong>
                        <small>Workshops and meetups</small>
                    </span>
                </a>
                <a class="dashboard-stat" href="events.php">
                    <span class="dashboard-stat-icon registered-icon" aria-hidden="true">R</span>
                    <span class="dashboard-stat-copy">
                        <span>Your registrations</span>
                        <strong><?php echo number_format($registeredEventCount); ?></strong>
                        <small>Upcoming events joined</small>
                    </span>
                </a>
                <a class="dashboard-stat" href="resources.php">
                    <span class="dashboard-stat-icon resources-icon" aria-hidden="true">L</span>
                    <span class="dashboard-stat-copy">
                        <span>Learning resources</span>
                        <strong><?php echo number_format($resourceCount); ?></strong>
                        <small>Materials in the library</small>
                    </span>
                </a>
                <a class="dashboard-stat" href="forum.php">
                    <span class="dashboard-stat-icon community-icon" aria-hidden="true">C</span>
                    <span class="dashboard-stat-copy">
                        <span>Community topics</span>
                        <strong><?php echo number_format($discussionCount); ?></strong>
                        <small>Open a discussion</small>
                    </span>
                </a>
            </section>

            <div class="dashboard-main-grid">
                <section class="dashboard-panel">
                    <div class="dashboard-section-heading">
                        <div>
                            <p class="eyebrow">WHAT'S HAPPENING</p>
                            <h2>Upcoming events</h2>
                        </div>
                        <a class="text-link" href="events.php">View all events <span aria-hidden="true">→</span></a>
                    </div>

                    <?php if ($upcomingEvents): ?>
                        <div class="dashboard-event-list">
                            <?php foreach ($upcomingEvents as $event): ?>
                                <article class="dashboard-event">
                                    <div class="dashboard-event-date">
                                        <span><?php echo htmlspecialchars(date('M', strtotime($event['event_date'])), ENT_QUOTES, 'UTF-8'); ?></span>
                                        <strong><?php echo htmlspecialchars(date('d', strtotime($event['event_date'])), ENT_QUOTES, 'UTF-8'); ?></strong>
                                    </div>
                                    <div class="dashboard-event-info">
                                        <h3><?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                        <p><?php echo htmlspecialchars(date('l, g:i A', strtotime($event['event_date'])), ENT_QUOTES, 'UTF-8'); ?>
                                            <?php if ($event['location']): ?>
                                                <span aria-hidden="true"> · </span><?php echo htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8'); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <span class="dashboard-event-status<?php echo $event['registration_id'] ? ' is-registered' : ''; ?>">
                                        <?php echo $event['registration_id'] ? 'Registered' : 'Open'; ?>
                                    </span>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="dashboard-empty-state">
                            <strong>No upcoming events yet</strong>
                            <p>Check back soon for workshops and community meetups.</p>
                            <a class="text-link" href="events.php">Visit the events page <span aria-hidden="true">→</span></a>
                        </div>
                    <?php endif; ?>
                </section>

                <aside class="dashboard-side-column">
                    <section class="dashboard-panel dashboard-toolkit">
                        <p class="eyebrow">KEEP GROWING</p>
                        <h2>Your learning toolkit</h2>
                        <p>Pick a next step that works for you. Your resource library currently has <strong><?php echo number_format($resourceCount); ?></strong> materials.</p>
                        <a class="dashboard-toolkit-link" href="resources.php">
                            <span class="dashboard-toolkit-mark" aria-hidden="true">01</span>
                            <span><strong>Explore resources</strong><small>Guides and learning materials</small></span>
                            <span aria-hidden="true">→</span>
                        </a>
                        <a class="dashboard-toolkit-link" href="mentorship.php">
                            <span class="dashboard-toolkit-mark" aria-hidden="true">02</span>
                            <span><strong>Find a mentor</strong><small>Connect with the community</small></span>
                            <span aria-hidden="true">→</span>
                        </a>
                        <a class="dashboard-toolkit-link" href="forum.php">
                            <span class="dashboard-toolkit-mark" aria-hidden="true">03</span>
                            <span><strong>Join a discussion</strong><small>Share ideas and ask questions</small></span>
                            <span aria-hidden="true">→</span>
                        </a>
                    </section>
                </aside>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>© 2026 Django Girls Sierra Leone. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
