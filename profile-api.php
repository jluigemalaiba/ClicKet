<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/log.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$user = currentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please sign in again.']);
    exit;
}

clicketRequireCsrfJson('profile_update');
clicketEnsureUserProfileSchema();

$clean = static fn (string $key, int $length = 190): string => mb_substr(trim((string) ($_POST[$key] ?? '')), 0, $length);
$username = preg_replace('/[^a-zA-Z0-9_.-]/', '', $clean('username', 80)) ?? '';
$firstName = $clean('first_name', 80);
$lastName = $clean('last_name', 80);
$email = strtolower($clean('email', 190));
$gender = $clean('gender', 20);
$birthday = $clean('birthday', 10);
$phone = preg_replace('/\D/', '', $clean('phone', 15)) ?? '';
$zip = preg_replace('/\D/', '', $clean('zip', 12)) ?? '';

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Enter a valid email address.']);
    exit;
}
if ($birthday !== '' && !DateTimeImmutable::createFromFormat('Y-m-d', $birthday)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Enter a valid birthday.']);
    exit;
}

$userId = (int) $user['id'];
$duplicate = clicketDbFetch('SELECT id FROM users WHERE LOWER(email) = LOWER(:email) AND id <> :id LIMIT 1', ['email' => $email, 'id' => $userId]);
if ($duplicate) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'That email address is already in use.']);
    exit;
}

$avatarUrl = (string) ($user['avatar_url'] ?? '');
if (!empty($_FILES['avatar']['tmp_name']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
    if ((int) ($_FILES['avatar']['size'] ?? 0) > 5 * 1024 * 1024) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Profile photo must be 5 MB or smaller.']);
        exit;
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['avatar']['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime])) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Use a JPG, PNG, GIF, or WebP profile photo.']);
        exit;
    }
    $directory = __DIR__ . '/storage/profile-images';
    if (!is_dir($directory)) mkdir($directory, 0775, true);
    $filename = 'user-' . $userId . '-' . bin2hex(random_bytes(6)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $directory . '/' . $filename)) {
        throw new RuntimeException('Could not store the profile photo.');
    }
    $avatarUrl = 'storage/profile-images/' . $filename;
}

$displayName = trim($firstName . ' ' . $lastName);
if ($displayName === '') $displayName = $username !== '' ? $username : (string) $user['name'];

$pdo = clicketDb();
$pdo->beginTransaction();
try {
    clicketDbExecute('UPDATE users SET name = :name, email = :email WHERE id = :id', ['name' => $displayName, 'email' => $email, 'id' => $userId]);
    clicketDbExecute(
        'INSERT INTO user_profiles (user_id, username, first_name, last_name, bio, gender, birthday, phone, street, city, province, zip, country, avatar_url)
         VALUES (:user_id, :username, :first_name, :last_name, :bio, :gender, NULLIF(:birthday, ""), :phone, :street, :city, :province, :zip, :country, :avatar_url)
         ON DUPLICATE KEY UPDATE username=VALUES(username), first_name=VALUES(first_name), last_name=VALUES(last_name), bio=VALUES(bio), gender=VALUES(gender), birthday=VALUES(birthday), phone=VALUES(phone), street=VALUES(street), city=VALUES(city), province=VALUES(province), zip=VALUES(zip), country=VALUES(country), avatar_url=VALUES(avatar_url)',
        [
            'user_id' => $userId, 'username' => $username, 'first_name' => $firstName, 'last_name' => $lastName,
            'bio' => $clean('bio', 200), 'gender' => $gender, 'birthday' => $birthday,
            'phone' => $phone !== '' ? '+63' . ltrim($phone, '0') : '', 'street' => $clean('street'),
            'city' => $clean('city', 100), 'province' => $clean('province', 100), 'zip' => $zip,
            'country' => $clean('country', 80), 'avatar_url' => $avatarUrl,
        ]
    );
    $pdo->commit();
} catch (Throwable $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not save the profile.']);
    exit;
}

$_SESSION['clicket_user']['name'] = $displayName;
$_SESSION['clicket_user']['display_name'] = $displayName;
$_SESSION['clicket_user']['email'] = $email;
echo json_encode(['success' => true, 'message' => 'Profile saved.', 'avatar_url' => $avatarUrl]);
