<?php
require __DIR__ . '/db.php';

if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$message = getFlash('success_message');
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = strtolower(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $message = 'Please enter both username and password.';
        $messageType = 'error';
    } else {
        $user = getUserByUsername($username);

        if ($user !== null && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            redirect('dashboard.php');
        }

        $message = 'Invalid email or password.';
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
                <a href="login.php">Login</a>
                <a href="signup.php" class="button-link">Sign Up</a>
            </nav>
        </div>
    </header>

    <main class="auth-shell">
        <div class="container">
            <div class="form-shell">
                <h1>Welcome back</h1>

                <?php if ($message): ?>
                    <div class="notice <?php echo $messageType === 'error' ? 'error' : 'success'; ?>"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <div class="form-row">
                        <label for="username">Username</label>
                        <input id="username" name="username" type="text" autocomplete="username" required>
                    </div>

                    <div class="form-row">
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" required>
                    </div>

                    <button class="button primary submit-btn" type="submit">Login</button>
                </form>

                <p class="meta-link">Need an account? <a href="signup.php">Create one</a></p>
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
