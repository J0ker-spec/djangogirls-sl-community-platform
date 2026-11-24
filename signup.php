<?php
require __DIR__ . '/db.php';

if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$message = null;
$messageType = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = strtolower(trim($_POST['username'] ?? ''));
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($username === '' || $fullName === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $message = 'Please complete all required fields.';
        $messageType = 'error';
    } elseif (!preg_match('/^[a-z0-9_]{3,30}$/', $username)) {
        $message = 'Username must be 3-30 characters using letters, numbers or underscores.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } elseif ($password !== $confirmPassword) {
        $message = 'Passwords do not match.';
        $messageType = 'error';
    } elseif (getUserByUsername($username) !== null) {
        $message = 'This username is already taken.';
        $messageType = 'error';
    } elseif (getUserByEmail($email) !== null) {
        $message = 'An account with this email already exists.';
        $messageType = 'error';
    } else {
        $pdo = getDatabaseConnection();
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO users (username, full_name, email, password)
             VALUES (:username, :full_name, :email, :password)'
        );
        $stmt->execute([
            ':username' => $username,
            ':full_name' => $fullName,
            ':email' => $email,
            ':password' => $hashedPassword,
        ]);

        setFlash('success_message', 'Registration successful. Please log in to continue.');
        redirect('login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an Account</title>
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
                <h1>Create your account</h1>

                <?php if ($message): ?>
                    <div class="notice <?php echo $messageType === 'error' ? 'error' : 'success'; ?>"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <div class="form-row">
                        <label for="username">Username</label>
                        <input id="username" name="username" type="text" autocomplete="username" value="<?php echo htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="form-row">
                        <label for="full_name">Full Name</label>
                        <input id="full_name" name="full_name" type="text" value="<?php echo htmlspecialchars($_POST['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="form-row">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="text" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="form-row">
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" required>
                    </div>

                    <div class="form-row">
                        <label for="confirm_password">Confirm Password</label>
                        <input id="confirm_password" name="confirm_password" type="password" required>
                    </div>

                    <button class="button primary submit-btn" type="submit">Register</button>
                </form>

                <p class="meta-link">Already have an account? <a href="login.php">Log in</a></p>
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
