<?php
// includes/log.php - simple file-based auth for ClicKet

function startSessionIfNeeded(): void {
    if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
        session_start();
    }
}

startSessionIfNeeded();

// Initialize cart session
if (!isset($_SESSION['clicket_cart'])) {
    $_SESSION['clicket_cart'] = [];
}

if (!defined('CLICKET_USERS_FILE')) {
    define('CLICKET_USERS_FILE', __DIR__ . '/../storage/users.json');
}
if (!defined('CLICKET_STAFF_FILE')) {
    define('CLICKET_STAFF_FILE', __DIR__ . '/../storage/staff.json');
}

// ── CART SESSION FUNCTIONS ──────────────────────────────────

function addToCart(array $item): void {
    if (!isset($_SESSION['clicket_cart'])) {
        $_SESSION['clicket_cart'] = [];
    }
    
    $itemId = $item['id'] ?? null;
    if (!$itemId) return;
    
    // Check if item exists and increment qty
    foreach ($_SESSION['clicket_cart'] as &$cartItem) {
        if ($cartItem['id'] === $itemId) {
            $cartItem['qty'] = ($cartItem['qty'] ?? 1) + 1;
            return;
        }
    }
    
    // Add new item
    $_SESSION['clicket_cart'][] = array_merge($item, ['qty' => 1]);
}

function removeFromCart(string $itemId): void {
    if (!isset($_SESSION['clicket_cart'])) return;
    $_SESSION['clicket_cart'] = array_filter(
        $_SESSION['clicket_cart'],
        fn($item) => ($item['id'] ?? null) !== $itemId
    );
    $_SESSION['clicket_cart'] = array_values($_SESSION['clicket_cart']);
}

function updateCartQty(string $itemId, int $qty): void {
    if (!isset($_SESSION['clicket_cart']) || $qty < 1) return;
    foreach ($_SESSION['clicket_cart'] as &$item) {
        if ($item['id'] === $itemId) {
            $item['qty'] = $qty;
            return;
        }
    }
}

function getCart(): array {
    return $_SESSION['clicket_cart'] ?? [];
}

function getCartCount(): int {
    return array_sum(array_map(fn($item) => $item['qty'] ?? 1, $_SESSION['clicket_cart'] ?? []));
}

function getCartTotal(): int {
    $total = 0;
    foreach ($_SESSION['clicket_cart'] ?? [] as $item) {
        $price = (int) preg_replace('/[^0-9]/', '', $item['price'] ?? '0');
        $total += $price * ($item['qty'] ?? 1);
    }
    return $total;
}

function clearCart(): void {
    $_SESSION['clicket_cart'] = [];
}

function ensureUserStore(): void {
    $dir = dirname(CLICKET_USERS_FILE);

    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    if (!file_exists(CLICKET_USERS_FILE)) {
        file_put_contents(CLICKET_USERS_FILE, json_encode([], JSON_PRETTY_PRINT));
    }

    $users = json_decode(file_get_contents(CLICKET_USERS_FILE) ?: '[]', true);
    $users = is_array($users) ? $users : [];
    $changed = false;

    foreach ($users as &$user) {
        if (!isset($user['role']) || !in_array((string) $user['role'], ['admin', 'organizer', 'customer'], true)) {
            $user['role'] = 'customer';
            $changed = true;
        }
    }
    unset($user);

    $hasAdmin = false;
    $knownEmails = [];
    foreach ($users as $user) {
        $email = strtolower((string) ($user['email'] ?? ''));
        if ($email !== '') {
            $knownEmails[$email] = true;
        }
        if (($user['role'] ?? '') === 'admin') {
            $hasAdmin = true;
        }
    }

    if (!$hasAdmin) {
        $adminEmail = 'admin@clicket.local';
        if (isset($knownEmails[$adminEmail])) {
            foreach ($users as &$user) {
                if (strtolower((string) ($user['email'] ?? '')) === $adminEmail) {
                    $user['role'] = 'admin';
                    $user['venues'] = ['all'];
                    if (empty($user['password'])) {
                        $user['password'] = password_hash('admin123', PASSWORD_DEFAULT);
                    }
                    $changed = true;
                    break;
                }
            }
            unset($user);
        } else {
            $users[] = [
                'id' => 'admin-root',
                'name' => 'CLICKET Admin',
                'email' => $adminEmail,
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin',
                'venues' => ['all'],
                'created_at' => date('c'),
            ];
            $knownEmails[$adminEmail] = true;
            $changed = true;
        }
    }

    if (file_exists(CLICKET_STAFF_FILE)) {
        $legacyStaff = json_decode(file_get_contents(CLICKET_STAFF_FILE) ?: '[]', true);
        if (is_array($legacyStaff)) {
            foreach ($legacyStaff as $account) {
                if (($account['role'] ?? '') !== 'organizer') {
                    continue;
                }
                $email = strtolower((string) ($account['email'] ?? ''));
                if ($email === '' || isset($knownEmails[$email])) {
                    continue;
                }
                $users[] = [
                    'id' => (string) ($account['id'] ?? bin2hex(random_bytes(8))),
                    'name' => (string) ($account['name'] ?? 'Organizer'),
                    'email' => (string) ($account['email'] ?? ''),
                    'password' => (string) ($account['password'] ?? password_hash('organizer123', PASSWORD_DEFAULT)),
                    'role' => 'organizer',
                    'venues' => is_array($account['venues'] ?? null) ? $account['venues'] : [],
                    'created_at' => (string) ($account['created_at'] ?? date('c')),
                    'migrated_from' => 'staff_store',
                ];
                $knownEmails[$email] = true;
                $changed = true;
            }
        }
    }

    if ($changed) {
        file_put_contents(
            CLICKET_USERS_FILE,
            json_encode(array_values($users), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );
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

function defaultStaffAccounts(): array {
    return [
        [
            'id' => 'admin-root',
            'name' => 'ClicKet Admin',
            'email' => 'admin@clicket.test',
            'password' => password_hash('Admin@123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'venues' => ['all'],
            'created_at' => date('c'),
        ],
        [
            'id' => 'org-moa',
            'name' => 'MOA Organizer',
            'email' => 'moa.organizer@clicket.test',
            'password' => password_hash('Organizer@123', PASSWORD_DEFAULT),
            'role' => 'organizer',
            'venues' => ['Mall of Asia Arena'],
            'created_at' => date('c'),
        ],
        [
            'id' => 'org-philippine-arena',
            'name' => 'Philippine Arena Organizer',
            'email' => 'philippine.organizer@clicket.test',
            'password' => password_hash('Organizer@123', PASSWORD_DEFAULT),
            'role' => 'organizer',
            'venues' => ['Philippine Arena'],
            'created_at' => date('c'),
        ],
        [
            'id' => 'org-araneta',
            'name' => 'Smart Araneta Organizer',
            'email' => 'araneta.organizer@clicket.test',
            'password' => password_hash('Organizer@123', PASSWORD_DEFAULT),
            'role' => 'organizer',
            'venues' => ['Smart Araneta Coliseum'],
            'created_at' => date('c'),
        ],
        [
            'id' => 'org-tanghalang',
            'name' => 'Tanghalang Ignacio Jimenez Organizer',
            'email' => 'tanghalang.organizer@clicket.test',
            'password' => password_hash('Organizer@123', PASSWORD_DEFAULT),
            'role' => 'organizer',
            'venues' => ['Tanghalang Ignacio Jimenez'],
            'created_at' => date('c'),
        ],
        [
            'id' => 'org-newport',
            'name' => 'Newport Organizer',
            'email' => 'newport.organizer@clicket.test',
            'password' => password_hash('Organizer@123', PASSWORD_DEFAULT),
            'role' => 'organizer',
            'venues' => ['Newport Performing Arts Theater'],
            'created_at' => date('c'),
        ],
        [
            'id' => 'org-solaire',
            'name' => 'Solaire Organizer',
            'email' => 'solaire.organizer@clicket.test',
            'password' => password_hash('Organizer@123', PASSWORD_DEFAULT),
            'role' => 'organizer',
            'venues' => ['The Theatre at Solaire'],
            'created_at' => date('c'),
        ],
        [
            'id' => 'org-philsports',
            'name' => 'Philsports Organizer',
            'email' => 'philsports.organizer@clicket.test',
            'password' => password_hash('Organizer@123', PASSWORD_DEFAULT),
            'role' => 'organizer',
            'venues' => ['Philsports Arena'],
            'created_at' => date('c'),
        ],
    ];
}

function ensureStaffStore(): void {
    $dir = dirname(CLICKET_STAFF_FILE);

    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    if (!file_exists(CLICKET_STAFF_FILE)) {
        file_put_contents(
            CLICKET_STAFF_FILE,
            json_encode(defaultStaffAccounts(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );
    }
}

function getStaffAccounts(): array {
    return array_values(array_filter(getUsers(), static function (array $user): bool {
        return in_array((string) ($user['role'] ?? ''), ['admin', 'organizer'], true);
    }));
}

function findStaffByEmail(string $email): ?array {
    $email = strtolower(trim($email));

    foreach (getUsers() as $account) {
        if (strtolower((string) ($account['email'] ?? '')) === $email) {
            return $account;
        }
    }

    return null;
}

function currentStaff(): ?array {
    startSessionIfNeeded();

    return $_SESSION['clicket_staff'] ?? null;
}

function isStaffLoggedIn(?string $role = null): bool {
    $staff = currentStaff();
    if (!$staff) {
        return false;
    }

    return $role === null || ($staff['role'] ?? '') === $role;
}

function loginStaff(array $staff): void {
    startSessionIfNeeded();
    session_regenerate_id(true);

    $_SESSION['clicket_staff'] = [
        'id' => $staff['id'],
        'session_user_id' => $staff['id'],
        'name' => $staff['name'],
        'email' => $staff['email'],
        'role' => $staff['role'],
        'venues' => is_array($staff['venues'] ?? null) ? $staff['venues'] : [],
    ];
}

function loginStaffWithEmail(string $email, string $password, string $role): array {
    $staff = findStaffByEmail($email);

    if (
        !$staff
        || !in_array((string) ($staff['role'] ?? ''), ['admin', 'organizer'], true)
        || ($staff['role'] ?? '') !== $role
        || !empty($staff['disabled'])
        || strtolower((string) ($staff['status'] ?? 'active')) !== 'active'
        || !password_verify($password, $staff['password'] ?? '')
    ) {
        return ['success' => false, 'errors' => ['Invalid email, password, or portal role.']];
    }

    loginStaff($staff);

    return ['success' => true];
}

function logoutStaff(): void {
    startSessionIfNeeded();
    unset($_SESSION['clicket_staff']);
}

function findUserByEmail(string $email): ?array {
    $email = strtolower(trim($email));

    foreach (getUsers() as $user) {
        if (strtolower((string) ($user['email'] ?? '')) === $email) {
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
        'role' => (string) ($user['role'] ?? 'customer'),
    ];
}

function registerUser(string $name, string $email, string $password, string $confirm): array {
    $name = trim($name);
    $email = strtolower(trim($email));
    $errors = [];

    if ($name === '') {
        $errors[] = 'Please enter your username.';
    } elseif (strlen($name) < 6) {
        $errors[] = 'Username must be at least 6 characters.';
    } elseif (preg_match('/\s/', $name)) {
        $errors[] = 'Username cannot contain spaces.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must include at least one uppercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must include at least one number.';
    } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must include at least one special character.';
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
        'role' => 'customer',
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

    if (!$user || !empty($user['disabled']) || strtolower((string) ($user['status'] ?? 'active')) !== 'active' || !password_verify($password, $user['password'] ?? '')) {
        return ['success' => false, 'errors' => ['Invalid email or password.']];
    }

    if (in_array((string) ($user['role'] ?? 'customer'), ['admin', 'organizer'], true)) {
        return ['success' => false, 'errors' => ['Please use the admin or organizer portal for this account.']];
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
