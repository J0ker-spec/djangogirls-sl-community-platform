<?php
require __DIR__ . '/db.php';
requireAdmin();
$pdo = getDatabaseConnection();
$message = getFlash('admin_message');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_event') {
        $stmt = $pdo->prepare(
            'INSERT INTO events (title, description, event_date, location, capacity)
             VALUES (:title, :description, :event_date, :location, :capacity)'
        );
        $stmt->execute([
            ':title' => trim($_POST['title'] ?? ''),
            ':description' => trim($_POST['description'] ?? ''),
            ':event_date' => $_POST['event_date'] ?? date('Y-m-d H:i:s'),
            ':location' => trim($_POST['location'] ?? ''),
            ':capacity' => (int) ($_POST['capacity'] ?? 50),
        ]);
        setFlash('admin_message', 'Event added successfully.');
    } elseif ($action === 'add_resource') {
        $stmt = $pdo->prepare(
            'INSERT INTO resources (title, description, category, external_url)
             VALUES (:title, :description, :category, :external_url)'
        );
        $stmt->execute([
            ':title' => trim($_POST['title'] ?? ''),
            ':description' => trim($_POST['description'] ?? ''),
            ':category' => trim($_POST['category'] ?? ''),
            ':external_url' => trim($_POST['external_url'] ?? ''),
        ]);
        setFlash('admin_message', 'Resource added successfully.');
    } elseif ($action === 'change_role') {
        $stmt = $pdo->prepare('UPDATE users SET role = :role WHERE id = :id');
        $stmt->execute([
            ':role' => $_POST['role'] ?? 'member',
            ':id' => (int) ($_POST['user_id'] ?? 0),
        ]);
        setFlash('admin_message', 'User role updated.');
    } elseif ($action === 'request_status') {
        $stmt = $pdo->prepare('UPDATE mentorship_requests SET status = :status WHERE id = :id');
        $stmt->execute([
            ':status' => $_POST['status'] ?? 'pending',
            ':id' => (int) ($_POST['request_id'] ?? 0),
        ]);
        setFlash('admin_message', 'Mentorship request updated.');
    }
    redirect('admin.php');
}
$users = $pdo->query('SELECT id, username, full_name, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
$events = $pdo->query('SELECT * FROM events ORDER BY event_date DESC')->fetchAll();
$resources = $pdo->query('SELECT * FROM resources ORDER BY created_at DESC')->fetchAll();
$requests = $pdo->query(
    'SELECT mr.*, m.username AS member_username, mentor.username AS mentor_username
     FROM mentorship_requests mr
     JOIN users m ON m.id = mr.member_id
     JOIN users mentor ON mentor.id = mr.mentor_id
     ORDER BY mr.created_at DESC'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Administration | Django Girls Sierra Leone</title><link rel="stylesheet" href="style.css"></head>
<body>
<header class="site-header"><div class="container nav"><div class="brand"><img src="images/django-girls-logo.png" alt="Django Girls Sierra Leone"><div class="brand-copy"><span class="brand-name">Django Girls</span><span class="brand-tag">Administration</span></div></div><nav class="nav-links"><a href="dashboard.php">Dashboard</a><a href="events.php">Events</a><a href="resources.php">Resources</a><a href="logout.php" class="button-link">Logout</a></nav></div></header>
<main class="dashboard-wrap"><div class="container">
    <section class="dashboard-card"><p class="eyebrow">Admin panel</p><h1>Manage the community</h1><?php if ($message): ?><div class="notice success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?></section>
    <div class="admin-grid">
        <section class="dashboard-card"><h2>Users</h2><div class="table-scroll"><table><thead><tr><th>Username</th><th>Name</th><th>Role</th></tr></thead><tbody><?php foreach ($users as $member): ?><tr><td>@<?php echo htmlspecialchars($member['username']); ?></td><td><?php echo htmlspecialchars($member['full_name']); ?></td><td><form method="post"><input type="hidden" name="action" value="change_role"><input type="hidden" name="user_id" value="<?php echo (int) $member['id']; ?>"><select name="role"><option value="member" <?php if ($member['role'] === 'member') echo 'selected'; ?>>Member</option><option value="mentor" <?php if ($member['role'] === 'mentor') echo 'selected'; ?>>Mentor</option><option value="admin" <?php if ($member['role'] === 'admin') echo 'selected'; ?>>Admin</option></select><button class="chip" type="submit">Save</button></form></td></tr><?php endforeach; ?></tbody></table></div></section>
        <section class="dashboard-card"><h2>Add event</h2><form method="post"><input type="hidden" name="action" value="add_event"><div class="form-row"><label>Title</label><input name="title" required></div><div class="form-row"><label>Description</label><textarea name="description" rows="3" required></textarea></div><div class="form-row"><label>Date and time</label><input name="event_date" type="datetime-local" required></div><div class="form-row"><label>Location</label><input name="location" required></div><div class="form-row"><label>Capacity</label><input name="capacity" type="number" value="50" min="1"></div><button class="button primary" type="submit">Add event</button></form></section>
        <section class="dashboard-card"><h2>Add resource</h2><form method="post"><input type="hidden" name="action" value="add_resource"><div class="form-row"><label>Title</label><input name="title" required></div><div class="form-row"><label>Description</label><textarea name="description" rows="3" required></textarea></div><div class="form-row"><label>Category</label><input name="category" required></div><div class="form-row"><label>Website URL</label><input name="external_url" type="url"></div><button class="button primary" type="submit">Add resource</button></form></section>
        <section class="dashboard-card"><h2>Mentorship requests</h2><?php foreach ($requests as $request): ?><article class="summary-card"><p><strong>@<?php echo htmlspecialchars($request['member_username']); ?></strong> requested <strong>@<?php echo htmlspecialchars($request['mentor_username']); ?></strong></p><p><?php echo htmlspecialchars($request['message']); ?></p><form method="post"><input type="hidden" name="action" value="request_status"><input type="hidden" name="request_id" value="<?php echo (int) $request['id']; ?>"><select name="status"><option value="pending" <?php if ($request['status'] === 'pending') echo 'selected'; ?>>Pending</option><option value="approved" <?php if ($request['status'] === 'approved') echo 'selected'; ?>>Approved</option><option value="rejected" <?php if ($request['status'] === 'rejected') echo 'selected'; ?>>Rejected</option></select><button class="chip" type="submit">Update</button></form></article><?php endforeach; ?></section>
    </div>
    <section class="dashboard-card"><h2>Platform totals</h2><div class="stat-grid"><div><strong><?php echo count($users); ?></strong><span>Users</span></div><div><strong><?php echo count($events); ?></strong><span>Events</span></div><div><strong><?php echo count($resources); ?></strong><span>Resources</span></div><div><strong><?php echo count($requests); ?></strong><span>Mentorship requests</span></div></div></section>
</div></main>
<footer class="footer"><div class="container"><p>© 2026 Django Girls Sierra Leone. All rights reserved.</p></div></footer>
</body>
</html>
