<?php
require __DIR__ . '/db.php';
$pdo = getDatabaseConnection();
$category = trim($_GET['category'] ?? '');
if ($category !== '') {
    $stmt = $pdo->prepare('SELECT * FROM resources WHERE category = :category ORDER BY created_at DESC');
    $stmt->execute([':category' => $category]);
    $resources = $stmt->fetchAll();
} else {
    $resources = $pdo->query('SELECT * FROM resources ORDER BY created_at DESC')->fetchAll();
}
$categories = $pdo->query('SELECT DISTINCT category FROM resources ORDER BY category')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Learning Resources | Django Girls Sierra Leone</title><link rel="stylesheet" href="style.css"></head>
<body>
<header class="site-header"><div class="container nav"><div class="brand"><img src="images/django-girls-logo.png" alt="Django Girls Sierra Leone"><div class="brand-copy"><span class="brand-name">Django Girls</span><span class="brand-tag">Sierra Leone</span></div></div><nav class="nav-links"><a href="index.php">Home</a><a href="events.php">Events</a><a href="forum.php">Forum</a><a href="login.php">Login</a><a href="signup.php" class="button-link">Sign Up</a></nav></div></header>
<main class="dashboard-wrap"><div class="container">
    <section class="dashboard-card"><p class="eyebrow">Learn and build</p><h1>Learning resources</h1><div class="filter-row"><a class="chip" href="resources.php">All</a><?php foreach ($categories as $item): ?><a class="chip" href="resources.php?category=<?php echo urlencode($item['category']); ?>"><?php echo htmlspecialchars($item['category']); ?></a><?php endforeach; ?></div></section>
    <div class="feature-grid"><?php foreach ($resources as $resource): ?><article class="feature-card"><div class="icon-badge">R</div><h3><?php echo htmlspecialchars($resource['title']); ?></h3><p><?php echo htmlspecialchars($resource['description']); ?></p><?php if (!empty($resource['external_url'])): ?><a class="text-link" href="<?php echo htmlspecialchars($resource['external_url']); ?>" target="_blank" rel="noopener">Open resource &rarr;</a><?php endif; ?></article><?php endforeach; ?></div>
</div></main>
<footer class="footer"><div class="container"><p>© 2026 Django Girls Sierra Leone. All rights reserved.</p></div></footer>
</body>
</html>
