<?php
require_once __DIR__ . '/includes/log.php';

clicketOtpEnsureSchema();
startSessionIfNeeded();

$errors = [];
$notif = pullFlashMessage();
$email = strtolower(trim((string) ($_GET['email'] ?? $_POST['email'] ?? ($_SESSION['clicket_pending_verification_email'] ?? ''))));
$pendingSignup = $_SESSION['clicket_pending_signup'] ?? null;
if (
    !is_array($pendingSignup)
    || strtolower(trim((string) ($pendingSignup['email'] ?? ''))) !== $email
    || (int) ($pendingSignup['expires_at'] ?? 0) < time()
) {
    $pendingSignup = null;
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlashMessage('error', 'Please sign in or register before verifying your email.');
    header('Location: auth.php?mode=login');
    exit;
}

$user = findUserByEmail($email);
if (!$user && !$pendingSignup) {
    setFlashMessage('error', 'We could not find an account for that email.');
    header('Location: auth.php?mode=login');
    exit;
}

if ($user && clicketOtpIsVerified($user)) {
    loginUser($user);
    setFlashMessage('success', 'Your email is already verified.');
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? 'verify');

    if ($action === 'resend') {
        $sent = clicketOtpSendForUser($pendingSignup ?: $user);
        if ($sent['success']) {
            setFlashMessage('success', 'A new verification code was sent.');
            header('Location: verify-otp.php?email=' . rawurlencode($email));
            exit;
        }
        $errors[] = (string) ($sent['error'] ?? 'Unable to resend verification code.');
    } else {
        $result = clicketOtpVerify($email, (string) ($_POST['otp_code'] ?? ''), $pendingSignup);
        if ($result['success']) {
            unset($_SESSION['clicket_pending_verification_email']);
            unset($_SESSION['clicket_pending_signup']);
            $verifiedUser = findUserByEmail($email);
            if ($verifiedUser) {
                loginUser($verifiedUser);
            }
            setFlashMessage('success', 'Email verified. Welcome to ClicKet!');
            header('Location: index.php');
            exit;
        }
        $errors[] = (string) ($result['error'] ?? 'Invalid verification code.');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Email Verification | CLICKET</title>
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/auth.css">
  <style>
    .otp-page { min-height: 100vh; display: grid; place-items: center; padding: 24px; background: var(--gray-100, #f5f5f5); }
    .otp-card { width: min(100%, 430px); padding: 28px; border-radius: 16px; background: #fff; box-shadow: 0 18px 48px rgba(0,0,0,.12); }
    .otp-brand { display: flex; align-items: center; gap: 10px; margin-bottom: 18px; }
    .otp-brand img:first-child { width: 38px; height: 38px; object-fit: contain; }
    .otp-brand img:last-child { width: 118px; height: auto; }
    .otp-card h1 { margin: 0 0 8px; color: #111; font-size: 26px; }
    .otp-card p { margin: 0 0 18px; color: #555; line-height: 1.5; }
    .otp-code { width: 100%; min-height: 58px; border: 1px solid rgba(0,0,0,.12); border-radius: 10px; text-align: center; font-size: 26px; font-weight: 900; letter-spacing: 8px; }
    .otp-actions { display: grid; gap: 10px; margin-top: 16px; }
    .otp-actions button, .otp-actions a { display: inline-grid; min-height: 46px; place-items: center; border-radius: 9px; font-weight: 900; text-decoration: none; }
    .otp-actions button { border: 0; background: var(--red-primary, #e8162b); color: #fff; }
    .otp-actions .otp-resend { border: 1px solid rgba(0,0,0,.12); background: #fff; color: #111; }
    .otp-note { display: block; margin-top: 12px; color: #666; font-size: 12px; line-height: 1.45; }
  </style>
</head>
<body>
  <?php if ($notif): ?>
    <div class="ck-notif-bar ck-notif-bar--<?= htmlspecialchars($notif['type']) ?> ck-notif-bar--show" role="status"><?= htmlspecialchars($notif['message']) ?></div>
  <?php endif; ?>
  <main class="otp-page">
    <section class="otp-card" aria-labelledby="otpTitle">
      <a class="otp-brand" href="index.php" aria-label="CLICKET home">
        <img src="assets/Icon_Logo.png" alt="" aria-hidden="true">
        <img src="assets/Name_Logo.png" alt="CLICKET">
      </a>
      <h1 id="otpTitle">Verify your email</h1>
      <p>Enter the 6-digit code sent to <strong><?= htmlspecialchars($email) ?></strong>.</p>

      <?php if ($errors): ?>
        <div class="auth-alert" role="alert">
          <ul><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul>
        </div>
      <?php endif; ?>

      <form method="post" action="verify-otp.php">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <input class="otp-code" type="text" name="otp_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required autofocus>
        <span class="otp-note">The code expires after 10 minutes.</span>
        <div class="otp-actions">
          <button type="submit" name="action" value="verify">Verify Email</button>
          <button class="otp-resend" type="submit" name="action" value="resend">Resend Code</button>
          <a href="auth.php?mode=login">Back to login</a>
        </div>
      </form>
    </section>
  </main>
</body>
</html>
