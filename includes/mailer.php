<?php

declare(strict_types=1);

require_once __DIR__ . '/mail-config.php';

function clicketMailSend(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): array {
    $config = clicketMailConfig();
    if ($config['password'] === '') {
        return ['success' => false, 'error' => 'Mail password is not configured.'];
    }

    $autoload = dirname(__DIR__) . '/vendor/autoload.php';
    if (is_file($autoload)) {
        require_once $autoload;
    }

    if (class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
        return clicketMailSendWithPhpMailer($config, $toEmail, $toName, $subject, $htmlBody, $textBody);
    }

    return clicketMailSendWithSmtp($config, $toEmail, $toName, $subject, $htmlBody, $textBody);
}

function clicketMailSendWithPhpMailer(array $config, string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody): array {
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = $config['secure'] === 'ssl'
            ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int) $config['port'];
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody !== '' ? $textBody : trim(strip_tags($htmlBody));
        $mail->send();

        return ['success' => true];
    } catch (Throwable $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function clicketMailSendWithSmtp(array $config, string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody): array {
    $host = (string) $config['host'];
    $port = (int) $config['port'];
    $remote = ($config['secure'] === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
    $socket = @stream_socket_client($remote, $errno, $errstr, 20, STREAM_CLIENT_CONNECT);
    if (!$socket) {
        return ['success' => false, 'error' => $errstr ?: 'Unable to connect to SMTP server.'];
    }

    stream_set_timeout($socket, 20);

    $read = static function () use ($socket): string {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (preg_match('/^\d{3}\s/', $line)) {
                break;
            }
        }
        return $response;
    };

    $send = static function (string $command, array $okCodes) use ($socket, $read): string {
        fwrite($socket, $command . "\r\n");
        $response = $read();
        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $okCodes, true)) {
            throw new RuntimeException(trim($response));
        }
        return $response;
    };

    try {
        $read();
        $send('EHLO clicket.local', [250]);
        if ($config['secure'] === 'tls') {
            $send('STARTTLS', [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('Unable to start TLS for SMTP.');
            }
            $send('EHLO clicket.local', [250]);
        }
        $send('AUTH LOGIN', [334]);
        $send(base64_encode((string) $config['username']), [334]);
        $send(base64_encode((string) $config['password']), [235]);
        $send('MAIL FROM:<' . $config['from_email'] . '>', [250]);
        $send('RCPT TO:<' . $toEmail . '>', [250, 251]);
        $send('DATA', [354]);

        $headers = [
            'Date: ' . date(DATE_RFC2822),
            'From: ' . clicketMailAddress($config['from_email'], $config['from_name']),
            'To: ' . clicketMailAddress($toEmail, $toName),
            'Subject: ' . (function_exists('mb_encode_mimeheader') ? mb_encode_mimeheader($subject, 'UTF-8') : $subject),
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];
        $message = implode("\r\n", $headers) . "\r\n\r\n" . str_replace("\n.", "\n..", $htmlBody);
        fwrite($socket, $message . "\r\n.\r\n");
        $response = $read();
        if ((int) substr($response, 0, 3) !== 250) {
            throw new RuntimeException(trim($response));
        }
        $send('QUIT', [221]);
        fclose($socket);

        return ['success' => true];
    } catch (Throwable $e) {
        fclose($socket);
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function clicketMailAddress(string $email, string $name): string {
    $cleanName = trim(str_replace(['"', "\r", "\n"], '', $name));
    return $cleanName !== '' ? '"' . addcslashes($cleanName, '"\\') . '" <' . $email . '>' : '<' . $email . '>';
}
