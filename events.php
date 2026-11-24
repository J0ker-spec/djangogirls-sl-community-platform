<?php
require __DIR__ . '/db.php';
requireLogin();
$pdo = getDatabaseConnection();
$user = currentUser();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = (int) ($_POST['event_id'] ?? 0);
    if ($eventId > 0) {
        $check = $pdo->prepare('SELECT id FROM event_registrations WHERE event_id = :event_id AND user_id = :user_id');
        $check->execute([':event_id' => $eventId, ':user_id' => $user['id']]);
        if (!$check->fetch()) {
            $insert = $pdo->prepare('INSERT INTO event_registrations (event_id, user_id) VALUES (:event_id, :user_id)');
            $insert->execute([':event_id' => $eventId, ':user_id' => $user['id']]);
            setFlash('event_message', 'You are registered for this event.');
        } else {
            setFlash('event_message', 'You are already registered for this event.');
        }
    }
    redirect('events.php');
}
$events = $pdo->query('SELECT * FROM events ORDER BY event_date ASC')->fetchAll();
$message = getFlash('event_message');
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Events | Django Girls Sierra Leone</title><link rel="stylesheet" href="style.css"></head>
<body>
<header class="site-header"><div class="container nav"><div class="brand"><img src="images/django-girls-logo.png" alt="Django Girls Sierra Leone"><div class="brand-copy"><span class="brand-name">Django Girls</span><span class="brand-tag">Sierra Leone</span></div></div><nav class="nav-links"><a href="dashboard.php">Dashboard</a><a href="profile.php">Profile</a><a href="resources.php">Resources</a><a href="forum.php">Forum</a><a href="logout.php" class="button-link">Logout</a></nav></div></header>
<main class="dashboard-wrap"><div class="container">
    <section class="dashboard-card"><p class="eyebrow">Community calendar</p><h1>Events and workshops</h1><?php if ($message): ?><div class="notice success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?></section>
    <div class="feature-grid">
    <?php foreach ($events as $event): ?>
        <?php
        $registered = $pdo->prepare('SELECT id FROM event_registrations WHERE event_id = :event_id AND user_id = :user_id');
        $registered->execute([':event_id' => $event['id'], ':user_id' => $user['id']]);
        ?>
        <article class="feature-card"><div class="icon-badge"><?php echo htmlspecialchars(date('d', strtotime($event['event_date']))); ?></div><h3><?php echo htmlspecialchars($event['title']); ?></h3><p><?php echo htmlspecialchars($event['description']); ?></p><p><strong><?php echo htmlspecialchars(date('d M Y, g:i A', strtotime($event['event_date']))); ?></strong><br><?php echo htmlspecialchars($event['location']); ?></p>
        <?php if ($registered->fetch()): ?><span class="badge">Registered</span><?php else: ?><form method="post"><input type="hidden" name="event_id" value="<?php echo (int) $event['id']; ?>"><button class="button primary" type="submit">Register</button></form><?php endif; ?></article>
    <?php endforeach; ?>
    </div>
</div></main>
<footer class="footer"><div class="container"><p>© 2026 Django Girls Sierra Leone. All rights reserved.</p></div></footer>
</body>
</html>
