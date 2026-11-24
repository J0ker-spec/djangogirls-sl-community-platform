<?php
$sessionPath = __DIR__ . '/data';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);
session_start();

function getDatabaseConnection(): PDO
{
    $host = getenv('DB_HOST') ?: 'localhost';
    $port = getenv('DB_PORT') ?: '3306';
    $dbname = getenv('DB_NAME') ?: 'dcomp207_assignment1';
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASS') ?: '';

    try {
        $pdo = new PDO(
            "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        createTables($pdo, 'mysql');
        return $pdo;
    } catch (PDOException $exception) {
        $sqlitePath = __DIR__ . '/data/users.sqlite';
        $pdo = new PDO('sqlite:' . $sqlitePath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        createTables($pdo, 'sqlite');
        return $pdo;
    }
}

function createTables(PDO $pdo, string $driver): void
{
    $autoIncrement = $driver === 'mysql'
        ? 'INT AUTO_INCREMENT PRIMARY KEY'
        : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $timestamp = $driver === 'mysql'
        ? 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        : 'DATETIME DEFAULT CURRENT_TIMESTAMP';

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id {$autoIncrement},
        username VARCHAR(50) NOT NULL UNIQUE,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'member',
        profile_picture VARCHAR(255),
        bio TEXT,
        location VARCHAR(100),
        created_at {$timestamp}
    )");

    if ($driver === 'sqlite') {
        $columns = $pdo->query('PRAGMA table_info(users)')->fetchAll();
        $names = array_column($columns, 'name');
        foreach ([
            'username' => 'VARCHAR(50)',
            'role' => "VARCHAR(20) NOT NULL DEFAULT 'member'",
            'profile_picture' => 'VARCHAR(255)',
            'bio' => 'TEXT',
            'location' => 'VARCHAR(100)',
        ] as $column => $definition) {
            if (!in_array($column, $names, true)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN {$column} {$definition}");
            }
        }
        $rows = $pdo->query('SELECT id, username FROM users')->fetchAll();
        $update = $pdo->prepare('UPDATE users SET username = :username WHERE id = :id');
        foreach ($rows as $row) {
            if (trim((string) ($row['username'] ?? '')) === '') {
                $update->execute([
                    ':username' => 'member' . (int) $row['id'],
                    ':id' => (int) $row['id'],
                ]);
            }
        }
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS events (
        id {$autoIncrement},
        title VARCHAR(150) NOT NULL,
        description TEXT,
        event_date {$timestamp},
        location VARCHAR(150),
        capacity INT NOT NULL DEFAULT 50,
        created_at {$timestamp}
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS event_registrations (
        id {$autoIncrement},
        event_id INT NOT NULL,
        user_id INT NOT NULL,
        registered_at {$timestamp},
        UNIQUE(event_id, user_id)
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS resources (
        id {$autoIncrement},
        title VARCHAR(150) NOT NULL,
        description TEXT,
        category VARCHAR(50) NOT NULL,
        external_url VARCHAR(255),
        file_path VARCHAR(255),
        created_at {$timestamp}
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS forum_topics (
        id {$autoIncrement},
        user_id INT NOT NULL,
        title VARCHAR(150) NOT NULL,
        body TEXT NOT NULL,
        created_at {$timestamp}
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS forum_posts (
        id {$autoIncrement},
        topic_id INT NOT NULL,
        user_id INT NOT NULL,
        body TEXT NOT NULL,
        created_at {$timestamp}
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS mentorship_requests (
        id {$autoIncrement},
        mentor_id INT NOT NULL,
        member_id INT NOT NULL,
        message TEXT,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        created_at {$timestamp}
    )");
    seedSampleContent($pdo);
}

function seedSampleContent(PDO $pdo): void
{
    $count = $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
    if ((int) $count === 0) {
        $stmt = $pdo->prepare(
            'INSERT INTO events (title, description, event_date, location, capacity)
             VALUES (:title, :description, :event_date, :location, :capacity)'
        );
        $events = [
            ['Community Coding Workshop', 'Build confidence with HTML, CSS and Python through guided practical exercises.', '2026-12-05 10:00:00', 'Freetown Tech Hub', 40],
            ['Women in Technology Meetup', 'Meet mentors, share experiences and learn about career opportunities in technology.', '2026-12-19 16:00:00', 'Limkokwing University, Freetown', 60],
        ];
        foreach ($events as $event) {
            $stmt->execute([
                ':title' => $event[0],
                ':description' => $event[1],
                ':event_date' => $event[2],
                ':location' => $event[3],
                ':capacity' => $event[4],
            ]);
        }
    }

    $count = $pdo->query('SELECT COUNT(*) FROM resources')->fetchColumn();
    if ((int) $count === 0) {
        $stmt = $pdo->prepare(
            'INSERT INTO resources (title, description, category, external_url)
             VALUES (:title, :description, :category, :external_url)'
        );
        $resources = [
            ['HTML Basics', 'Learn elements, structure and accessible page markup.', 'Web Development', 'https://developer.mozilla.org/docs/Learn/HTML'],
            ['CSS Layout Guide', 'Understand the box model, Flexbox and responsive layouts.', 'Web Development', 'https://developer.mozilla.org/docs/Learn/CSS'],
            ['Python for Beginners', 'Start coding with variables, loops and functions.', 'Programming', 'https://docs.python.org/3/tutorial/'],
        ];
        foreach ($resources as $resource) {
            $stmt->execute([
                ':title' => $resource[0],
                ':description' => $resource[1],
                ':category' => $resource[2],
                ':external_url' => $resource[3],
            ]);
        }
    }
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function setFlash(string $key, string $message): void
{
    $_SESSION[$key] = $message;
}

function getFlash(string $key): ?string
{
    if (!isset($_SESSION[$key])) {
        return null;
    }
    $message = $_SESSION[$key];
    unset($_SESSION[$key]);
    return $message;
}

function currentUser(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare(
        'SELECT id, username, full_name, email, role, profile_picture, bio, location, created_at
         FROM users WHERE id = :id LIMIT 1'
    );
    $stmt->execute([':id' => $_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function getUserByUsername(string $username): ?array
{
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    return $stmt->fetch() ?: null;
}

function getUserByEmail(string $email): ?array
{
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    return $stmt->fetch() ?: null;
}

function requireLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

function requireAdmin(): void
{
    requireLogin();
    $user = currentUser();
    if (!$user || $user['role'] !== 'admin') {
        redirect('dashboard.php');
    }
}
