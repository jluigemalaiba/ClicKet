<?php

declare(strict_types=1);

require_once __DIR__ . '/mailer.php';

function clicketOtpEnsureSchema(): void {
    static $ready = false;
    if ($ready) {
        return;
    }

    clicketDbExecute(
        'CREATE TABLE IF NOT EXISTS email_otps (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NULL,
            email VARCHAR(190) NOT NULL,
            otp_code VARCHAR(10) NOT NULL,
            expires_at DATETIME NOT NULL,
            is_used TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_email_otps_email (email),
            KEY idx_email_otps_user_id (user_id),
            KEY idx_email_otps_expires_at (expires_at),
            CONSTRAINT fk_email_otps_user
              FOREIGN KEY (user_id) REFERENCES users (id)
              ON UPDATE CASCADE
              ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $column = clicketDbFetch('SHOW COLUMNS FROM users LIKE "email_verified_at"');
    if (!$column) {
        clicketDbExecute('ALTER TABLE users ADD COLUMN email_verified_at DATETIME NULL AFTER status');
    }

    $ready = true;
}

function clicketOtpIsVerified(array $user): bool {
    return trim((string) ($user['email_verified_at'] ?? '')) !== '';
}

function clicketOtpSendForUser(array $user): array {
    clicketOtpEnsureSchema();

    $email = strtolower(trim((string) ($user['email'] ?? '')));
    $userId = (int) ($user['id'] ?? 0);
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Valid email address is required.'];
    }

    $latest = clicketDbFetch(
        'SELECT created_at FROM email_otps WHERE email = :email ORDER BY created_at DESC LIMIT 1',
        ['email' => $email]
    );
    if ($latest && clicketOtpTimestamp((string) $latest['created_at']) > time() - 60) {
        return ['success' => false, 'cooldown' => true, 'error' => 'Please wait before requesting another code.'];
    }

    $code = (string) random_int(100000, 999999);
    $expiresAt = clicketDbTimestamp(time() + 600);

    clicketDbExecute(
        'UPDATE email_otps SET is_used = 1 WHERE email = :email AND is_used = 0',
        ['email' => $email]
    );
    clicketDbExecute(
        'INSERT INTO email_otps (user_id, email, otp_code, expires_at, is_used)
         VALUES (:user_id, :email, :otp_code, :expires_at, 0)',
        [
            'user_id' => $userId > 0 ? $userId : null,
            'email' => $email,
            'otp_code' => $code,
            'expires_at' => $expiresAt,
        ]
    );

    $name = (string) ($user['name'] ?? 'Clicket user');
    $mail = clicketMailSend(
        $email,
        $name,
        'Your Clicket verification code',
        clicketOtpEmailHtml($code),
        'Your Clicket verification code is ' . $code . '. It expires in 10 minutes.'
    );

    if (!$mail['success']) {
        return ['success' => false, 'error' => $mail['error'] ?? 'Unable to send OTP email.'];
    }

    return ['success' => true, 'expires_at' => $expiresAt];
}

function clicketOtpVerify(string $email, string $code): array {
    clicketOtpEnsureSchema();

    $email = strtolower(trim($email));
    $code = preg_replace('/\D+/', '', $code) ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($code) !== 6) {
        return ['success' => false, 'error' => 'Enter the 6-digit verification code.'];
    }

    $otp = clicketDbFetch(
        'SELECT * FROM email_otps
         WHERE email = :email AND otp_code = :otp_code AND is_used = 0
         ORDER BY created_at DESC
         LIMIT 1',
        ['email' => $email, 'otp_code' => $code]
    );

    if (!$otp) {
        return ['success' => false, 'error' => 'Invalid verification code.'];
    }
    if (clicketOtpTimestamp((string) $otp['expires_at']) < time()) {
        return ['success' => false, 'error' => 'That verification code has expired.'];
    }

    clicketDbExecute(
        'UPDATE email_otps SET is_used = 1 WHERE id = :id',
        ['id' => (int) $otp['id']]
    );
    clicketDbExecute(
        'UPDATE users SET email_verified_at = UTC_TIMESTAMP(), status = "active" WHERE LOWER(email) = LOWER(:email)',
        ['email' => $email]
    );

    return ['success' => true];
}

function clicketOtpTimestamp(string $value): int {
    try {
        return (new DateTimeImmutable($value, new DateTimeZone('UTC')))->getTimestamp();
    } catch (Throwable) {
        return 0;
    }
}

function clicketOtpEmailHtml(string $code): string {
    $safeCode = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');

    return '<!doctype html><html><body style="margin:0;background:#f6f7f9;font-family:Arial,sans-serif;color:#171717;">'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:28px 12px;"><tr><td align="center">'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:460px;background:#ffffff;border:1px solid #eeeeee;border-radius:14px;overflow:hidden;">'
        . '<tr><td style="padding:24px 26px 10px;text-align:center;"><div style="font-size:22px;font-weight:900;color:#e8162b;letter-spacing:.4px;">Clicket</div></td></tr>'
        . '<tr><td style="padding:8px 26px 6px;text-align:center;font-size:15px;">Your Clicket verification code is</td></tr>'
        . '<tr><td style="padding:10px 26px;text-align:center;"><div style="display:inline-block;padding:16px 24px;border-radius:12px;background:#fff1f3;color:#111;font-size:34px;font-weight:900;letter-spacing:8px;">' . $safeCode . '</div></td></tr>'
        . '<tr><td style="padding:8px 26px 28px;text-align:center;color:#666;font-size:13px;line-height:1.5;">This code expires in 10 minutes. If you did not request it, you can ignore this email.</td></tr>'
        . '</table></td></tr></table></body></html>';
}
