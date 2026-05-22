<?php
// includes/log.php - simple file-based auth for ClicKet

function startSessionIfNeeded(): void {
    if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
        session_start();
    }
}

startSessionIfNeeded();

if (!defined('CLICKET_USERS_FILE')) {
    define('CLICKET_USERS_FILE', __DIR__ . '/../storage/users.json');
}

function ensureUserStore(): void {
    $dir = dirname(CLICKET_USERS_FILE);

    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    if (!file_exists(CLICKET_USERS_FILE)) {
        file_put_contents(CLICKET_USERS_FILE, json_encode([], JSON_PRETTY_PRINT));
    }
}

function getUsers(): array {
    ensureUserStore();

    $users = json_decode(file_get_contents(CLICKET_USERS_FILE) ?: '[]', true);

    return is_array($users) ? $users : [];
}

function saveUsers(array $users): bool {
    ensureUserStore();

    return file_put_contents(
        CLICKET_USERS_FILE,
        json_encode(array_values($users), JSON_PRETTY_PRINT),
        LOCK_EX
    ) !== false;
}

function findUserByEmail(string $email): ?array {
    $email = strtolower(trim($email));

    foreach (getUsers() as $user) {
        if (($user['email'] ?? '') === $email) {
            return $user;
        }
    }

    return null;
}

function currentUser(): ?array {
    startSessionIfNeeded();

    return $_SESSION['clicket_user'] ?? null;
}

function isLoggedIn(): bool {
    return currentUser() !== null;
}

function loginUser(array $user): void {
    startSessionIfNeeded();
    session_regenerate_id(true);

    $_SESSION['clicket_user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
    ];
}

function registerUser(string $name, string $email, string $password, string $confirm): array {
    $name = trim($name);
    $email = strtolower(trim($email));
    $errors = [];

    if ($name === '') {
        $errors[] = 'Please enter your full name.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (findUserByEmail($email)) {
        $errors[] = 'An account with that email already exists.';
    }

    if ($errors) {
        return ['success' => false, 'errors' => $errors];
    }

    $users = getUsers();
    $user = [
        'id' => bin2hex(random_bytes(8)),
        'name' => $name,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'created_at' => date('c'),
    ];

    $users[] = $user;

    if (!saveUsers($users)) {
        return ['success' => false, 'errors' => ['Unable to create your account right now.']];
    }

    loginUser($user);

    return ['success' => true];
}

function loginWithEmail(string $email, string $password): array {
    $user = findUserByEmail($email);

    if (!$user || !password_verify($password, $user['password'] ?? '')) {
        return ['success' => false, 'errors' => ['Invalid email or password.']];
    }

    loginUser($user);

    return ['success' => true];
}

function logoutUser(): void {
    startSessionIfNeeded();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

function oldInput(string $key): string {
    return htmlspecialchars($_POST[$key] ?? '', ENT_QUOTES, 'UTF-8');
}

function setFlashMessage(string $type, string $message): void {
    startSessionIfNeeded();

    $_SESSION['clicket_flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function pullFlashMessage(): ?array {
    startSessionIfNeeded();

    if (!isset($_SESSION['clicket_flash']) || !is_array($_SESSION['clicket_flash'])) {
        return null;
    }

    $flash = $_SESSION['clicket_flash'];
    unset($_SESSION['clicket_flash']);

    return $flash;
}

function userDisplayName(?array $user): string {
    if (!$user) {
        return '';
    }

    $name = trim((string)($user['name'] ?? ''));

    if ($name === '') {
        return '';
    }

    $parts = preg_split('/\s+/', $name);

    return $parts[0] ?? $name;
}
