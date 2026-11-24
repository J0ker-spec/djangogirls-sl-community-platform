<?php
require __DIR__ . '/db.php';
requireLogin();
$pdo = getDatabaseConnection();
$user = currentUser();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');
    if ($title !== '' && $body !== '') {
        $stmt = $pdo->prepare('INSERT INTO forum_topics (user_id, title, body) VALUES (:user_id, :title, :body)');
        $stmt->execute([':user_id' => $user['id'], ':title' => $title, ':body' => $body]);
        setFlash('forum_message', 'Discussion created.');
    } else {
        setFlash('forum_message', 'Please complete the title and message.');
    }
    redirect('forum.php');
}
$topics = $pdo->query(
    'SELECT forum_topics.*, users.username, users.full_name
     FROM forum_topics JOIN users ON users.id = forum_topics.user_id
     ORDER BY forum_topics.created_at DESC'
)->fetchAll();
$message = getFlash('forum_message');
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Community Forum | Django Girls Sierra Leone</title><link rel="stylesheet" href="style.css"></head>
<body>
<header class="site-header"><div class="container nav"><div class="brand"><img src="images/django-girls-logo.png" alt="Django Girls Sierra Leone"><div class="brand-copy"><span class="brand-name">Django Girls</span><span class="brand-tag">Sierra Leone</span></div></div><nav class="nav-links"><a href="dashboard.php">Dashboard</a><a href="profile.php">Profile</a><a href="events.php">Events</a><a href="resources.php">Resources</a><a href="logout.php" class="button-link">Logout</a></nav></div></header>
<main class="dashboard-wrap"><div class="container">
    <section class="dashboard-card"><p class="eyebrow">Community conversation</p><h1>Forum</h1><?php if ($message): ?><div class="notice success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <form method="post" class="profile-form"><div class="form-row"><label for="title">Discussion title</label><input id="title" name="title" required></div><div class="form-row"><label for="body">Message</label><textarea id="body" name="body" rows="4" required></textarea></div><button class="button primary" type="submit">Start discussion</button></form>
    </section>
    <div class="topic-list"><?php foreach ($topics as $topic): ?><article class="summary-card"><h3><?php echo htmlspecialchars($topic['title']); ?></h3><p><?php echo htmlspecialchars($topic['body']); ?></p><small>Posted by <?php echo htmlspecialchars($topic['full_name']); ?> (@<?php echo htmlspecialchars($topic['username']); ?>)</small></article><?php endforeach; ?></div>
</div></main>
<footer class="footer"><div class="container"><p>© 2026 Django Girls Sierra Leone. All rights reserved.</p></div></footer>
</body>
</html>
