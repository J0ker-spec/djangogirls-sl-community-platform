<?php
require __DIR__ . '/db.php';
requireLogin();
$pdo = getDatabaseConnection();
$user = currentUser();
$message = getFlash('profile_message');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $location = trim($_POST['location'] ?? '');

    if ($fullName === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('profile_message', 'Please provide a name and valid email.');
    } else {
        $profilePicture = $user['profile_picture'];
        if (!empty($_FILES['profile_picture']['name'])) {
            $file = $_FILES['profile_picture'];
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $type = mime_content_type($file['tmp_name']);
            if (!isset($allowed[$type]) || $file['size'] > 2 * 1024 * 1024) {
                setFlash('profile_message', 'Choose a JPG, PNG or WEBP image under 2 MB.');
                redirect('profile.php');
            }
            $uploadDir = __DIR__ . '/uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $name = 'profile_' . $user['id'] . '_' . time() . '.' . $allowed[$type];
            if (move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $name)) {
                $profilePicture = 'uploads/' . $name;
            }
        }

        $stmt = $pdo->prepare(
            'UPDATE users SET full_name = :full_name, email = :email, bio = :bio,
             location = :location, profile_picture = :profile_picture WHERE id = :id'
        );
        $stmt->execute([
            ':full_name' => $fullName,
            ':email' => $email,
            ':bio' => $bio,
            ':location' => $location,
            ':profile_picture' => $profilePicture,
            ':id' => $user['id'],
        ]);
        setFlash('profile_message', 'Profile updated successfully.');
    }
    redirect('profile.php');
}
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Django Girls Sierra Leone</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header"><div class="container nav">
    <div class="brand"><img src="images/django-girls-logo.png" alt="Django Girls Sierra Leone"><div class="brand-copy"><span class="brand-name">Django Girls</span><span class="brand-tag">Sierra Leone</span></div></div>
    <nav class="nav-links"><a href="dashboard.php">Dashboard</a><a href="events.php">Events</a><a href="resources.php">Resources</a><a href="forum.php">Forum</a><a href="logout.php" class="button-link">Logout</a></nav>
</div></header>
<main class="dashboard-wrap"><div class="container">
    <section class="dashboard-card">
        <p class="eyebrow">Member profile</p><h1>Manage your profile</h1>
        <?php if ($message): ?><div class="notice success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <div class="profile-layout">
            <div class="profile-photo">
                <?php if ($user['profile_picture']): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile picture">
                <?php else: ?>
                    <div class="avatar"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
                <?php endif; ?>
            </div>
            <form method="post" enctype="multipart/form-data" class="profile-form">
                <div class="form-row"><label>Username</label><input value="<?php echo htmlspecialchars($user['username']); ?>" disabled></div>
                <div class="form-row"><label for="full_name">Full name</label><input id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required></div>
                <div class="form-row"><label for="email">Email</label><input id="email" name="email" type="email" value="<?php echo htmlspecialchars($user['email']); ?>" required></div>
                <div class="form-row"><label for="location">Location</label><input id="location" name="location" value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>"></div>
                <div class="form-row"><label for="bio">Bio</label><textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea></div>
                <div class="form-row"><label for="profile_picture">Profile picture</label><input id="profile_picture" name="profile_picture" type="file" accept="image/jpeg,image/png,image/webp"></div>
                <button class="button primary" type="submit">Save profile</button>
            </form>
        </div>
    </section>
</div></main>
<footer class="footer"><div class="container"><p>© 2026 Django Girls Sierra Leone. All rights reserved.</p></div></footer>
</body>
</html>
