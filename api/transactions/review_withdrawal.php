<?php
require_once '../../config.php';
$reviewer = requireClerk();

$d            = getJSON();
$withdrawalId = (int)($d['withdrawal_id'] ?? 0);
$decision     = clean($d['decision']      ?? '');  
$notes        = clean($d['notes']         ?? '');

if (!$withdrawalId || !in_array($decision, ['approved', 'rejected'])) {
    respond(false, 'withdrawal_id and a valid decision (approved/rejected) are required.', null, 400);
}

$stmt = $pdo->prepare('SELECT * FROM withdrawals WHERE id = ? LIMIT 1');
$stmt->execute([$withdrawalId]);
$withdrawal = $stmt->fetch();

if (!$withdrawal) respond(false, 'Withdrawal not found.', null, 404);
if ($withdrawal['status'] !== 'pending') {
    respond(false, 'This withdrawal has already been processed.', null, 409);
}

if ($decision === 'approved') {
    $pdo->prepare(
        'UPDATE withdrawals
         SET status="approved", reviewed_by=?, reviewed_at=NOW(), notes=?, updated_at=NOW()
         WHERE id=?'
    )->execute([$reviewer['id'], $notes, $withdrawalId]);

    
    $token   = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+3 days'));

    $pdo->prepare(
        "INSERT INTO qr_transactions
         (transaction_type, reference_id, qr_token, status, expires_at)
         VALUES ('withdrawal', ?, ?, 'active', ?)"
    )->execute([$withdrawalId, $token, $expires]);

    createNotification($pdo, $withdrawal['user_id'],
        'Withdrawal Approved! Present Your QR Code',
        'Your withdrawal request for ₱' . number_format($withdrawal['amount'], 2) .
        ' has been approved. Log in to view your QR code and present it to a clerk within 3 days to collect your funds.',
        'withdrawal',
        'transactions.html'
    );

    logAction($pdo, $reviewer['id'], 'APPROVE_WITHDRAWAL', 'withdrawals', $withdrawalId,
        "Approved withdrawal #$withdrawalId");

    respond(true, 'Withdrawal approved. QR code generated and user notified.', ['qr_token' => $token]);

} else {
    $pdo->prepare(
        'UPDATE withdrawals
         SET status="rejected", reviewed_by=?, reviewed_at=NOW(),
             rejection_reason=?, notes=?, updated_at=NOW()
         WHERE id=?'
    )->execute([$reviewer['id'], $notes, $notes, $withdrawalId]);

    createNotification($pdo, $withdrawal['user_id'],
        'Withdrawal Request Not Approved',
        'Your withdrawal request for ₱' . number_format($withdrawal['amount'], 2) .
        ' was not approved. Reason: ' . ($notes ?: 'Not specified'),
        'withdrawal',
        'transactions.html'
    );

    logAction($pdo, $reviewer['id'], 'REJECT_WITHDRAWAL', 'withdrawals', $withdrawalId,
        "Rejected withdrawal #$withdrawalId. Reason: $notes");

    respond(true, 'Withdrawal rejected. User has been notified.', null);
}
?>
