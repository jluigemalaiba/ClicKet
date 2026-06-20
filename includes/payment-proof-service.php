<?php

declare(strict_types=1);

require_once __DIR__ . '/order-history-data.php';

function clicketPaymentProofPublicUrl(array $order): string {
    $proof = basename((string) ($order['proof_of_payment'] ?? ''));
    if ($proof === '') {
        return '';
    }

    $path = dirname(__DIR__) . '/uploads/payment_proofs/' . $proof;
    return is_file($path) ? 'uploads/payment_proofs/' . rawurlencode($proof) : '';
}

function clicketPaymentProofStore(array $file): array {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Choose a proof of payment image.'];
    }
    if ((int) ($file['size'] ?? 0) > 5 * 1024 * 1024) {
        return ['success' => false, 'error' => 'Proof image must be 5 MB or smaller.'];
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $tmpName = (string) ($file['tmp_name'] ?? '');
    $mime = function_exists('mime_content_type') && $tmpName !== '' ? mime_content_type($tmpName) : '';
    $imageInfo = $tmpName !== '' && function_exists('getimagesize') ? @getimagesize($tmpName) : false;
    if (!isset($allowed[$mime]) || $imageInfo === false) {
        return ['success' => false, 'error' => 'Upload a valid JPG, PNG, or WEBP image.'];
    }

    $directory = dirname(__DIR__) . '/uploads/payment_proofs';
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        return ['success' => false, 'error' => 'Payment proof storage is unavailable.'];
    }

    $fileName = 'proof-' . date('Ymd-His') . '-' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    $target = $directory . '/' . $fileName;
    if (!move_uploaded_file($tmpName, $target)) {
        return ['success' => false, 'error' => 'Could not save your proof of payment.'];
    }

    return [
        'success' => true,
        'file_name' => $fileName,
        'file_path' => 'uploads/payment_proofs/' . $fileName,
        'mime_type' => $mime,
    ];
}

function clicketPaymentProofAttach(string $orderId, int $userId, array $stored, string $actor): bool {
    $order = clicketDbFetch(
        'SELECT o.id, o.payment_status
         FROM orders o
         WHERE o.order_id = :order_id AND o.user_id = :user_id
         LIMIT 1',
        ['order_id' => $orderId, 'user_id' => $userId]
    );
    if (!$order) {
        return false;
    }

    $status = strtolower((string) $order['payment_status']);
    if (!in_array($status, ['pending', 'rejected'], true)) {
        return false;
    }

    $payment = clicketDbFetch(
        'SELECT id FROM payments WHERE order_id = :order_id ORDER BY id DESC LIMIT 1',
        ['order_id' => (int) $order['id']]
    );
    if (!$payment) {
        return false;
    }

    $pdo = clicketDb();
    $pdo->beginTransaction();
    try {
        clicketDbExecute(
            'INSERT INTO payment_proofs
               (payment_id, order_id, file_name, file_path, mime_type, uploaded_at, review_status)
             VALUES
               (:payment_id, :order_id, :file_name, :file_path, :mime_type, UTC_TIMESTAMP(), "under_review")',
            [
                'payment_id' => (int) $payment['id'],
                'order_id' => (int) $order['id'],
                'file_name' => (string) $stored['file_name'],
                'file_path' => (string) $stored['file_path'],
                'mime_type' => (string) $stored['mime_type'],
            ]
        );
        clicketDbExecute(
            'UPDATE orders
             SET payment_status = "under_review", order_status = "pending",
                 rejected_by_staff_id = NULL, rejected_at = NULL
             WHERE id = :order_id',
            ['order_id' => (int) $order['id']]
        );
        clicketDbExecute(
            'UPDATE payments
             SET status = "under_review", reviewed_by_staff_id = NULL, reviewed_at = NULL
             WHERE order_id = :order_id',
            ['order_id' => (int) $order['id']]
        );
        $pdo->commit();
        return true;
    } catch (Throwable) {
        $pdo->rollBack();
        return false;
    }
}
