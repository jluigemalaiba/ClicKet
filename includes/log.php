<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

function startSessionIfNeeded(): void {
    if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
        session_start();
    }
}

startSessionIfNeeded();

if (!isset($_SESSION['clicket_cart'])) {
    $_SESSION['clicket_cart'] = [];
}

function addToCart(array $item): void {
    if (!isset($_SESSION['clicket_cart'])) {
        $_SESSION['clicket_cart'] = [];
    }

    $itemId = $item['id'] ?? null;
    if (!$itemId) {
        return;
    }

    foreach ($_SESSION['clicket_cart'] as &$cartItem) {
        if (($cartItem['id'] ?? null) === $itemId) {
            $cartItem['qty'] = ($cartItem['qty'] ?? 1) + 1;
            return;
        }
    }

    $_SESSION['clicket_cart'][] = array_merge($item, ['qty' => 1]);
}

function removeFromCart(string $itemId): void {
    if (!isset($_SESSION['clicket_cart'])) {
        return;
    }

    $_SESSION['clicket_cart'] = array_values(array_filter(
        $_SESSION['clicket_cart'],
        static fn (array $item): bool => ($item['id'] ?? null) !== $itemId
    ));
}

function updateCartQty(string $itemId, int $qty): void {
    if (!isset($_SESSION['clicket_cart']) || $qty < 1) {
        return;
    }

    foreach ($_SESSION['clicket_cart'] as &$item) {
        if (($item['id'] ?? null) === $itemId) {
            $item['qty'] = $qty;
            return;
        }
    }
}

function getCart(): array {
    return $_SESSION['clicket_cart'] ?? [];
}

function getCartCount(): int {
    return array_sum(array_map(
        static fn (array $item): int => (int) ($item['qty'] ?? 1),
        $_SESSION['clicket_cart'] ?? []
    ));
}

function getCartTotal(): int {
    $total = 0;
    foreach ($_SESSION['clicket_cart'] ?? [] as $item) {
        $price = (int) preg_replace('/[^0-9]/', '', (string) ($item['price'] ?? '0'));
        $total += $price * (int) ($item['qty'] ?? 1);
    }

    return $total;
}

function clearCart(): void {
    $_SESSION['clicket_cart'] = [];
}

function ensureUserStore(): void {
    clicketDb();
}

function ensureStaffStore(): void {
    clicketDb();
}

function clicketUserRowToApp(array $row): array {
    return [
        'id' => (string) $row['id'],
        'name' => (string) $row['name'],
        'email' => (string) $row['email'],
        'password' => (string) $row['password_hash'],
        'role' => 'customer',
        'status' => (string) ($row['status'] ?? 'active'),
        'created_at' => clicketDbDisplayDateTime((string) ($row['created_at'] ?? '')),
    ];
}

function getUsers(): array {
    $rows = clicketDbFetchAll(
        'SELECT * FROM users ORDER BY created_at, id'
    );

    return array_map('clicketUserRowToApp', $rows);
}

function saveUsers(array $users): bool {
    $pdo = clicketDb();
    $pdo->beginTransaction();

    try {
        foreach ($users as $user) {
            $email = strtolower(trim((string) ($user['email'] ?? '')));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            clicketDbExecute(
                'INSERT INTO users (name, email, password_hash, status, created_at)
                 VALUES (:name, :email, :password_hash, :status, :created_at)
                 ON DUPLICATE KEY UPDATE
                   name = VALUES(name),
                   password_hash = VALUES(password_hash),
                   status = VALUES(status)',
                [
                    'name' => trim((string) ($user['name'] ?? 'ClicKet User')),
                    'email' => $email,
                    'password_hash' => (string) ($user['password'] ?? $user['password_hash'] ?? password_hash(bin2hex(random_bytes(12)), PASSWORD_DEFAULT)),
                    'status' => in_array((string) ($user['status'] ?? 'active'), ['active', 'inactive'], true)
                        ? (string) ($user['status'] ?? 'active')
                        : 'active',
                    'created_at' => clicketDbDateTime((string) ($user['created_at'] ?? 'now')),
                ]
            );
        }

        $pdo->commit();
        return true;
    } catch (Throwable) {
        $pdo->rollBack();
        return false;
    }
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
            'name' => 'Tanghalang Pilipino Organizer',
            'email' => 'tanghalang.organizer@clicket.test',
            'password' => password_hash('Organizer@123', PASSWORD_DEFAULT),
            'role' => 'organizer',
            'venues' => ['Tanghalang Pilipino'],
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
            'venues' => ['PhilSports Arena'],
            'created_at' => date('c'),
        ],
    ];
}

function clicketStaffRowToApp(array $row): array {
    $venues = [];
    if (($row['role'] ?? '') === 'admin') {
        $venues = ['all'];
    } elseif (!empty($row['venues'])) {
        $venues = array_values(array_filter(explode("\n", (string) $row['venues'])));
    }

    return [
        'id' => (string) $row['id'],
        'session_user_id' => (string) $row['id'],
        'name' => (string) $row['name'],
        'email' => (string) $row['email'],
        'password' => (string) $row['password_hash'],
        'role' => (string) $row['role'],
        'status' => (string) ($row['status'] ?? 'active'),
        'venues' => $venues,
        'created_at' => clicketDbDisplayDateTime((string) ($row['created_at'] ?? '')),
    ];
}

function getStaffAccounts(): array {
    $rows = clicketDbFetchAll(
        'SELECT sa.*,
                GROUP_CONCAT(v.name ORDER BY v.name SEPARATOR "\n") AS venues
         FROM staff_accounts sa
         LEFT JOIN staff_venue_assignments sva ON sva.staff_id = sa.id
         LEFT JOIN venues v ON v.id = sva.venue_id
         GROUP BY sa.id
         ORDER BY FIELD(sa.role, "admin", "organizer"), sa.name'
    );

    return array_map('clicketStaffRowToApp', $rows);
}

function findStaffByEmail(string $email): ?array {
    $row = clicketDbFetch(
        'SELECT sa.*,
                GROUP_CONCAT(v.name ORDER BY v.name SEPARATOR "\n") AS venues
         FROM staff_accounts sa
         LEFT JOIN staff_venue_assignments sva ON sva.staff_id = sa.id
         LEFT JOIN venues v ON v.id = sva.venue_id
         WHERE LOWER(sa.email) = LOWER(:email)
         GROUP BY sa.id
         LIMIT 1',
        ['email' => trim($email)]
    );

    return $row ? clicketStaffRowToApp($row) : null;
}

function clicketNormalizeRole(string $role): string {
    $role = strtolower(trim(str_replace([' ', '-'], '_', $role)));

    return in_array($role, ['customer', 'organizer', 'admin'], true) ? $role : '';
}

function clicketSetAuthSession(array $account): void {
    startSessionIfNeeded();

    $role = clicketNormalizeRole((string) ($account['role'] ?? ''));
    if ($role === '') {
        throw new InvalidArgumentException('Invalid CLICKET auth role.');
    }

    $payload = [
        'user_id' => (string) ($account['user_id'] ?? $account['id'] ?? ''),
        'role' => $role,
        'email' => strtolower(trim((string) ($account['email'] ?? ''))),
        'display_name' => trim((string) ($account['display_name'] ?? $account['name'] ?? '')),
        'account_table' => (string) ($account['account_table'] ?? ($role === 'customer' ? 'users' : 'staff_accounts')),
        'venues' => is_array($account['venues'] ?? null) ? array_values($account['venues']) : [],
        'authenticated_at' => date('c'),
    ];

    $_SESSION['clicket_auth'] = $payload;
    $_SESSION['user_id'] = $payload['user_id'];
    $_SESSION['role'] = $payload['role'];
    $_SESSION['email'] = $payload['email'];
    $_SESSION['display_name'] = $payload['display_name'];
}

function clicketClearAuthSession(): void {
    unset(
        $_SESSION['clicket_auth'],
        $_SESSION['user_id'],
        $_SESSION['role'],
        $_SESSION['email'],
        $_SESSION['display_name']
    );
}

function currentAuth(): ?array {
    startSessionIfNeeded();

    if (is_array($_SESSION['clicket_auth'] ?? null)) {
        $auth = $_SESSION['clicket_auth'];
        $role = clicketNormalizeRole((string) ($auth['role'] ?? ''));
        if ($role !== '' && (string) ($auth['user_id'] ?? '') !== '' && (string) ($auth['email'] ?? '') !== '') {
            $auth['role'] = $role;
            $auth['user_id'] = (string) $auth['user_id'];
            $auth['email'] = (string) $auth['email'];
            $auth['display_name'] = (string) ($auth['display_name'] ?? '');
            $auth['venues'] = is_array($auth['venues'] ?? null) ? $auth['venues'] : [];

            return $auth;
        }
    }

    if (is_array($_SESSION['clicket_staff'] ?? null)) {
        $staff = $_SESSION['clicket_staff'];
        return [
            'user_id' => (string) ($staff['session_user_id'] ?? $staff['id'] ?? ''),
            'role' => clicketNormalizeRole((string) ($staff['role'] ?? '')),
            'email' => (string) ($staff['email'] ?? ''),
            'display_name' => (string) ($staff['display_name'] ?? $staff['name'] ?? ''),
            'account_table' => 'staff_accounts',
            'venues' => is_array($staff['venues'] ?? null) ? $staff['venues'] : [],
        ];
    }

    if (is_array($_SESSION['clicket_user'] ?? null)) {
        $user = $_SESSION['clicket_user'];
        return [
            'user_id' => (string) ($user['user_id'] ?? $user['id'] ?? ''),
            'role' => 'customer',
            'email' => (string) ($user['email'] ?? ''),
            'display_name' => (string) ($user['display_name'] ?? $user['name'] ?? ''),
            'account_table' => 'users',
            'venues' => [],
        ];
    }

    $role = clicketNormalizeRole((string) ($_SESSION['role'] ?? ''));
    if ($role !== '' && !empty($_SESSION['user_id']) && !empty($_SESSION['email'])) {
        return [
            'user_id' => (string) $_SESSION['user_id'],
            'role' => $role,
            'email' => (string) $_SESSION['email'],
            'display_name' => (string) ($_SESSION['display_name'] ?? ''),
            'account_table' => $role === 'customer' ? 'users' : 'staff_accounts',
            'venues' => [],
        ];
    }

    return null;
}

function clicketAuthHasRole(string|array $roles): bool {
    $auth = currentAuth();
    if (!$auth) {
        return false;
    }

    $allowed = array_map('clicketNormalizeRole', (array) $roles);

    return in_array((string) ($auth['role'] ?? ''), $allowed, true);
}

function clicketAuthRedirectForRole(?string $role = null): string {
    $role = clicketNormalizeRole((string) ($role ?? (currentAuth()['role'] ?? '')));

    return match ($role) {
        'admin' => 'admin-panel.php',
        'organizer' => 'organizer-panel.php',
        default => 'index.php',
    };
}

function clicketLoginUrlForRoles(string|array $roles): string {
    $roles = array_map('clicketNormalizeRole', (array) $roles);
    if (in_array('admin', $roles, true)) {
        return 'auth.php?mode=admin';
    }
    if (in_array('organizer', $roles, true)) {
        return 'auth.php?mode=organizer';
    }

    return 'auth.php?mode=login';
}

function clicketRequireRole(string|array $roles, ?string $message = null): array {
    $auth = currentAuth();
    if ($auth && clicketAuthHasRole($roles)) {
        return $auth;
    }

    if ($auth) {
        setFlashMessage('error', $message ?: 'You do not have permission to open that page.');
        header('Location: ' . clicketAuthRedirectForRole((string) ($auth['role'] ?? '')));
        exit;
    }

    setFlashMessage('error', $message ?: 'Please sign in to continue.');
    header('Location: ' . clicketLoginUrlForRoles($roles));
    exit;
}

function clicketRequireRoleJson(string|array $roles, string $message = 'Unauthorized.'): array {
    $auth = currentAuth();
    if ($auth && clicketAuthHasRole($roles)) {
        return $auth;
    }

    http_response_code($auth ? 403 : 401);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

function clicketRequireCustomer(): array {
    return clicketRequireRole('customer', 'Please sign in with a customer account.');
}

function clicketRequireStaff(?string $role = null): array {
    return clicketRequireRole($role ?: ['admin', 'organizer'], 'Please sign in with an admin or organizer account.');
}

function clicketRequireAdmin(): array {
    return clicketRequireRole('admin', 'Admin access required.');
}

function clicketRequireOrganizer(): array {
    return clicketRequireRole('organizer', 'Organizer access required.');
}

function currentStaff(): ?array {
    startSessionIfNeeded();

    if (is_array($_SESSION['clicket_staff'] ?? null)) {
        return $_SESSION['clicket_staff'];
    }

    $auth = currentAuth();
    if (!$auth || !in_array((string) ($auth['role'] ?? ''), ['admin', 'organizer'], true)) {
        return null;
    }

    $staff = findStaffByEmail((string) ($auth['email'] ?? ''));
    if (!$staff) {
        return null;
    }

    return [
        'id' => (string) $staff['id'],
        'user_id' => (string) $staff['id'],
        'session_user_id' => (string) ($staff['session_user_id'] ?? $staff['id']),
        'name' => (string) $staff['name'],
        'display_name' => (string) $staff['name'],
        'email' => (string) $staff['email'],
        'role' => (string) $staff['role'],
        'venues' => is_array($staff['venues'] ?? null) ? $staff['venues'] : [],
    ];
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
    unset($_SESSION['clicket_user']);

    clicketSetAuthSession([
        'id' => (string) $staff['id'],
        'name' => (string) $staff['name'],
        'email' => (string) $staff['email'],
        'role' => (string) $staff['role'],
        'venues' => is_array($staff['venues'] ?? null) ? $staff['venues'] : [],
        'account_table' => 'staff_accounts',
    ]);

    $_SESSION['clicket_staff'] = [
        'id' => (string) $staff['id'],
        'user_id' => (string) $staff['id'],
        'session_user_id' => (string) ($staff['session_user_id'] ?? $staff['id']),
        'name' => (string) $staff['name'],
        'display_name' => (string) $staff['name'],
        'email' => (string) $staff['email'],
        'role' => (string) $staff['role'],
        'venues' => is_array($staff['venues'] ?? null) ? $staff['venues'] : [],
    ];
}

function loginStaffWithEmail(string $email, string $password, string $role): array {
    $staff = findStaffByEmail($email);

    if (
        !$staff
        || ($staff['status'] ?? 'active') !== 'active'
        || !in_array((string) ($staff['role'] ?? ''), ['admin', 'organizer'], true)
        || ($staff['role'] ?? '') !== $role
        || !password_verify($password, (string) ($staff['password'] ?? ''))
    ) {
        return ['success' => false, 'errors' => ['Invalid email, password, or portal role.']];
    }

    loginStaff($staff);

    return ['success' => true];
}

function logoutStaff(): void {
    startSessionIfNeeded();
    $auth = currentAuth();
    unset($_SESSION['clicket_staff']);
    if ($auth && in_array((string) ($auth['role'] ?? ''), ['admin', 'organizer'], true)) {
        clicketClearAuthSession();
    }
}

function findUserByEmail(string $email): ?array {
    $row = clicketDbFetch(
        'SELECT * FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1',
        ['email' => trim($email)]
    );

    return $row ? clicketUserRowToApp($row) : null;
}

function currentUser(): ?array {
    startSessionIfNeeded();

    if (is_array($_SESSION['clicket_user'] ?? null)) {
        return $_SESSION['clicket_user'];
    }

    $auth = currentAuth();
    if (!$auth || (string) ($auth['role'] ?? '') !== 'customer') {
        return null;
    }

    return [
        'id' => (string) $auth['user_id'],
        'user_id' => (string) $auth['user_id'],
        'name' => (string) ($auth['display_name'] ?? ''),
        'display_name' => (string) ($auth['display_name'] ?? ''),
        'email' => (string) ($auth['email'] ?? ''),
        'role' => 'customer',
    ];
}

function isLoggedIn(): bool {
    return currentUser() !== null;
}

function loginUser(array $user): void {
    startSessionIfNeeded();
    session_regenerate_id(true);
    unset($_SESSION['clicket_staff']);

    clicketSetAuthSession([
        'id' => (string) $user['id'],
        'name' => (string) $user['name'],
        'email' => (string) $user['email'],
        'role' => 'customer',
        'account_table' => 'users',
    ]);

    $_SESSION['clicket_user'] = [
        'id' => (string) $user['id'],
        'user_id' => (string) $user['id'],
        'name' => (string) $user['name'],
        'display_name' => (string) $user['name'],
        'email' => (string) $user['email'],
        'role' => 'customer',
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

    if (findUserByEmail($email) || findStaffByEmail($email)) {
        $errors[] = 'An account with that email already exists.';
    }

    if ($errors) {
        return ['success' => false, 'errors' => $errors];
    }

    try {
        clicketDbExecute(
            'INSERT INTO users (name, email, password_hash, status)
             VALUES (:name, :email, :password_hash, "active")',
            [
                'name' => $name,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]
        );
    } catch (Throwable) {
        return ['success' => false, 'errors' => ['Unable to create your account right now.']];
    }

    $user = findUserByEmail($email);
    if ($user) {
        loginUser($user);
    }

    return ['success' => true];
}

function loginWithEmail(string $email, string $password): array {
    $user = findUserByEmail($email);

    if (!$user || ($user['status'] ?? 'active') !== 'active' || !password_verify($password, (string) ($user['password'] ?? ''))) {
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

    $name = trim((string) ($user['name'] ?? ''));
    if ($name === '') {
        return '';
    }

    $parts = preg_split('/\s+/', $name);

    return $parts[0] ?? $name;
}
