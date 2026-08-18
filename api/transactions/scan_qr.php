<?php
require_once '../../config.php';
$clerk = requireClerk();

$d     = getJSON();
$token = clean($d['token'] ?? '');

if (!$token) {
    respond(false, 'QR token is required.', null, 400);
}

$stmt = $pdo->prepare(
    'SELECT * FROM qr_transactions WHERE qr_token = ? LIMIT 1'
);
$stmt->execute([$token]);
$qr = $stmt->fetch();

if (!$qr) {
    respond(false, 'Invalid QR code. This token does not exist.', null, 404);
}
if ($qr['status'] === 'scanned') {
    respond(false, 'This QR code has already been used and the funds have been released.', null, 409);
}
if ($qr['status'] === 'expired') {
    respond(false, 'This QR code has expired. The member must request a new one.', null, 410);
}

if ($qr['expires_at'] && strtotime($qr['expires_at']) < time()) {
    $pdo->prepare("UPDATE qr_transactions SET status='expired' WHERE id=?")
        ->execute([$qr['id']]);
    respond(false, 'This QR code has expired. The member must request a new one.', null, 410);
}

$type = $qr['transaction_type'];
$refId = (int)$qr['reference_id'];

if ($type === 'withdrawal') {
    $txStmt = $pdo->prepare('SELECT * FROM withdrawals WHERE id = ? LIMIT 1');
    $txStmt->execute([$refId]);
    $tx = $txStmt->fetch();

    if (!$tx) respond(false, 'Withdrawal record not found.', null, 404);
    if ($tx['status'] !== 'approved') {
        respond(false, 'This withdrawal is not in approved status.', null, 409);
    }

    $pdo->prepare(
        'UPDATE withdrawals
         SET status="released", released_by=?, released_at=NOW(), updated_at=NOW()
         WHERE id=?'
    )->execute([$clerk['id'], $refId]);

    $userId = $tx['user_id'];
    $amount = $tx['amount'];
    $label  = 'Withdrawal';

} elseif ($type === 'loan') {
    $txStmt = $pdo->prepare('SELECT * FROM loans WHERE id = ? LIMIT 1');
    $txStmt->execute([$refId]);
    $tx = $txStmt->fetch();

    if (!$tx) respond(false, 'Loan record not found.', null, 404);
    if ($tx['status'] !== 'approved') {
        respond(false, 'This loan is not in approved status.', null, 409);
    }

    $pdo->prepare(
        'UPDATE loans
         SET status="released", released_by=?, released_at=NOW(), updated_at=NOW()
         WHERE id=?'
    )->execute([$clerk['id'], $refId]);

    $userId = $tx['user_id'];
    $amount = $tx['amount'];
    $label  = 'Loan';

} else {
    respond(false, 'Unknown transaction type.', null, 400);
}

$pdo->prepare(
    'UPDATE qr_transactions
     SET status="scanned", scanned_at=NOW(), scanned_by=?
     WHERE id=?'
)->execute([$clerk['id'], $qr['id']]);

createNotification($pdo, $userId,
    "$label Funds Released",
    "Your $label of ₱" . number_format($amount, 2) .
    " has been released by a clerk. Please confirm you have received the funds.",
    strtolower($label),
    'transactions.html'
);

logAction($pdo, $clerk['id'], 'SCAN_QR_RELEASE', 'qr_transactions', $qr['id'],
    "Released $label #$refId — ₱$amount for user #$userId");

respond(true, "Funds released successfully.", [
    'type'       => $label,
    'amount'     => $amount,
    'reference'  => $refId,
    'released_at'=> date('Y-m-d H:i:s'),
    'released_by'=> $clerk['first_name'] . ' ' . $clerk['last_name'],
]);
?>
