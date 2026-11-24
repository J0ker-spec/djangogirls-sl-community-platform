<?php
require __DIR__ . '/db.php';
requireLogin();
$pdo = getDatabaseConnection();
$user = currentUser();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mentorId = (int) ($_POST['mentor_id'] ?? 0);
    $messageText = trim($_POST['message'] ?? '');
    if ($mentorId > 0 && $mentorId !== $user['id']) {
        $check = $pdo->prepare('SELECT id FROM mentorship_requests WHERE mentor_id = :mentor_id AND member_id = :member_id');
        $check->execute([':mentor_id' => $mentorId, ':member_id' => $user['id']]);
        if (!$check->fetch()) {
            $stmt = $pdo->prepare('INSERT INTO mentorship_requests (mentor_id, member_id, message) VALUES (:mentor_id, :member_id, :message)');
            $stmt->execute([':mentor_id' => $mentorId, ':member_id' => $user['id'], ':message' => $messageText]);
            setFlash('mentorship_message', 'Your mentorship request was sent.');
        } else {
            setFlash('mentorship_message', 'You already sent a request to this mentor.');
        }
    }
    redirect('mentorship.php');
}
$mentors = $pdo->query("SELECT id, username, full_name, bio, location FROM users WHERE role = 'admin' OR role = 'mentor'")->fetchAll();
$requests = $pdo->prepare('SELECT * FROM mentorship_requests WHERE member_id = :member_id ORDER BY created_at DESC');
$requests->execute([':member_id' => $user['id']]);
$message = getFlash('mentorship_message');
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Mentorship | Django Girls Sierra Leone</title><link rel="stylesheet" href="style.css"></head>
<body>
<header class="site-header"><div class="container nav"><div class="brand"><img src="images/django-girls-logo.png" alt="Django Girls Sierra Leone"><div class="brand-copy"><span class="brand-name">Django Girls</span><span class="brand-tag">Sierra Leone</span></div></div><nav class="nav-links"><a href="dashboard.php">Dashboard</a><a href="profile.php">Profile</a><a href="events.php">Events</a><a href="resources.php">Resources</a><a href="logout.php" class="button-link">Logout</a></nav></div></header>
<main class="dashboard-wrap"><div class="container">
    <section class="dashboard-card"><p class="eyebrow">Find guidance</p><h1>Mentorship</h1><?php if ($message): ?><div class="notice success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?></section>
    <div class="feature-grid"><?php foreach ($mentors as $mentor): ?><article class="feature-card"><div class="avatar small"><?php echo strtoupper(substr($mentor['full_name'], 0, 1)); ?></div><h3><?php echo htmlspecialchars($mentor['full_name']); ?></h3><p>@<?php echo htmlspecialchars($mentor['username']); ?><br><?php echo htmlspecialchars($mentor['location'] ?? 'Sierra Leone'); ?></p><p><?php echo htmlspecialchars($mentor['bio'] ?? 'Django Girls community mentor.'); ?></p><form method="post"><input type="hidden" name="mentor_id" value="<?php echo (int) $mentor['id']; ?>"><div class="form-row"><label for="message_<?php echo $mentor['id']; ?>">Message</label><textarea id="message_<?php echo $mentor['id']; ?>" name="message" rows="3" required></textarea></div><button class="button primary" type="submit">Request mentorship</button></form></article><?php endforeach; ?></div>
    <section class="dashboard-card"><h2>Your requests</h2><div class="topic-list"><?php foreach ($requests->fetchAll() as $request): ?><article class="summary-card"><p><?php echo htmlspecialchars($request['message']); ?></p><span class="badge"><?php echo htmlspecialchars(ucfirst($request['status'])); ?></span></article><?php endforeach; ?></div></section>
</div></main>
<footer class="footer"><div class="container"><p>© 2026 Django Girls Sierra Leone. All rights reserved.</p></div></footer>
</body>
</html>
